<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Training;
use App\Session;
use App\Source;
use App\Questionnaire;
use App\Question;
use App\QuestionText;
use App\Option;
use App\OptionText;
use App\Test;
use App\Lib\QuestionsMaker;
use Carbon;
use Validator;

class TrainingsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$per_page = (int) $request->input('per_page');

		$search = $request->input('search');

		$trainings = Training::with(['module:id,name', 'user:id,firstname,lastname'])
			->when(!empty($search), function ($query) use ($search) {
				$query->where(function($query) use ($search) {
					$query->where('title_ru', 'like', "%$search%")
						->orWhere('short_ru', 'like', "%$search%");
				});
			})
			->when(!in_array('superuser', $request->user()->role), function($query) use ($request) {
				$query->where('user_id', $request->user()->id);
			})
			->orderBy($request->input('sort', 'created_at'),
						$request->input('desc') ? 'desc' : 'asc');

		return $trainings->paginate($per_page != -1 ? $per_page : $trainings->count());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validator($request);

		$data = $request->all();

		$session = Session::find($data['module_id']);
		$dataset = json_decode($session->dataset->data, true);

		$training = new Training($data);
		$training->user_id = $request->user()->id;

		$actions = collect();

		$trainingDataset = [
			'actions' => collect($dataset['actions'])
				->map(function($action) {
					unset($action['steps']);
					unset($action['knowledges']);
					return $action;
				})
				->toArray(),
			'items' => collect($dataset['actions'])
				->flatMap(function($action) {
					return collect($action['steps'])
						->map(function($step) use ($action) {
							$step['action'] = $action['id'];
							return $step;
						});
				})
				->toArray()
		];

		if(!empty($trainingDataset['items'][0])) {
			$trainingDataset['items'][0]['startTime'] = strtotime($training->info['startDate'] . ' 09:00') * 1000;
		}

		$training->dataset = $trainingDataset;

		$training->visible = 1;
		$training->save();

		// $this->makeQuestions($training->id, $dataset);

		return [
			'id' => $training->id
		];
	}

	/**
	 * Show single resource record.
	 *
	 * @param  \App\Training $training
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(Training $training)
	{
		$training->makeVisible('dataset');
		//$training->load(['dataset']);
		return $training;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\Training $training
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Training $training)
	{
		$data = $request->input();
		$this->validator($request, $training->id);

		if(false) {
			$dataset = json_decode($training->module->dataset->data, true);

			$trainingDataset = [
				'actions' => collect($dataset['actions'])
					->map(function($action) {
						unset($action['steps']);
						unset($action['knowledges']);
						return $action;
					})
					->toArray(),
				'items' => collect($dataset['actions'])
					->flatMap(function($action) {
						return collect($action['steps'])
							->map(function($step) use ($action) {
								$step['action'] = $action['id'];
								return $step;
							});
					})
					->toArray()
			];

			if(!empty($trainingDataset['items'][0])) {
				$trainingDataset['items'][0]['startTime'] = strtotime($training->info['startDate'] . ' 09:00') * 1000;
			}

			$data['dataset'] = $trainingDataset;
		}

		if(!empty($training->dataset['running']) && isset($data['dataset']) && (!$data['dataset']['running'] ||
			$training->dataset['step'] != $data['dataset']['step'])
		) {
			$step = $training->dataset['items'][$training->dataset['step'] - 1];

			if(!empty($step['test']) && !empty($step['test_id'])) {
				$test = Test::find($step['test_id']);
				$test->ends = Carbon::now();
				$test->save();

				$results = $this->testResults($test->id);

				$data['dataset']['items'][$training->dataset['step'] - 1]['results'] = [
					'count' => $results['count'],
					'total' => $results['total']
				];

				foreach($data['dataset']['items'] as &$item) {
					if(!empty($item['text'])) {
						$knowledges = 0;
						$result = 0;

						foreach($item['text'] as $text) {
							if(!empty($text['knowledge']) && !empty($results['knowledges'][$text['knowledge']])) {
								$result += $results['knowledges'][$text['knowledge']]['result'];
								$knowledges++;
							}
						}

						if($knowledges) {
							if(!isset($item['tests'])) {
								$item['tests'] = [];
							}

							$item['tests'][$test->id] = round($result * 100 / $knowledges);
						}
					}
				}
			}
		}

		if(isset($data['comments'])) {
			if(!$training->comments) {
				$comments = [];
			} else {
				$comments = $training->comments;
			}

			$comments[$request->user()->id] = [
				'name' => $request->user()->fio,
				'comments' => $data['comments']
			];

			$training->comments = $comments;
			$training->save();
		} else {
			$training->update($data);
		}

		if($training->status != 'ongoing') {
			$this->makeTests($training);
		}

		if(!empty($data['dataset']['step'])) {
			$training->status = 'ongoing';
		}

		$training->save();

		// $this->makeQuestions($training->id, $dataset);

		$training->makeVisible('dataset');

		return $training;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Training $training
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Training $training)
	{
		if ($training->delete()) {
			return [];
		}

		return error();
	}

	/**
	 * Upload image.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function upload(Request $request)
	{
		if($image = $request->file('file')) {
			return ['location' => $image->store('trainings')];
		}
	}

	private function validator(Request $request, $id = null)
	{
		Validator::make($request->all(), $id ? [
		] : [
			'title_ru' => 'required|max:255',
			'short_ru' => 'required',
			'text_ru'  => 'required',
		])->validate();
	}

	private function makeTests($training) {
		$dataset = $training->dataset;

		foreach($dataset['items'] as $index => &$item) {
			if(!empty($item['test'])) {
				$test = Test::firstOrCreate([
					'training_id' => $training->id,
					'index' => $index
				]);

				if($test->wasRecentlyCreated) {
					$item['test_id'] = $test->id;

					QuestionsMaker::makeQuestions($test->id, json_decode($test->training->module->dataset->data, true));
				}

				$test->title = !empty($item['description']) ? $item['description'] : __('Тест');
				$test->starts = Carbon::createFromTimestamp($item['start'] / 1000);
				$test->length = $item['sec'];
				$test->ends = $test->starts->addSeconds($item['sec']);

				if($training->status == 'ongoing') {
					$test->enabled = true;
				}

				$test->save();

				// \Log::debug($test->starts->toDateTimeString());
				// \Log::debug($test->ends->toDateTimeString());
			}
		}

		$training->dataset = $dataset;
	}

	public function startTest(Test $test) {
        // Questionnaire::where('level', $test->id)->delete();

		$test->enabled = 1;
		$test->starts = Carbon::now();
		$test->ends = null; // $test->starts->addSeconds($test->length);
		$test->save();

		return config('app.url') . route('questionnaire', ['level' => $test->id], false);
	}

	private function testResults($id) {
		$questionnaires = Questionnaire::where('level', $id)->get();

		foreach($questionnaires as $questionnaire) {
            $questionnaire->closed = 1;
            $questionnaire->save();
        }

		$total = 0;

		$knowledges = Question::where('level', $id)
			->withCount([
				'results as answered',
				'results as answered_correctly' => function($query) {
					$query->where('correct', 1);
				}
			])
			->get()
			->mapWithKeys(function($question) use (&$total) {
				$result = !$question->answered ? 0 :
					$question->answered_correctly / $question->answered;

				$total += $result;

				return [$question->knowledge => [
					'count' => $question->answered,
					'result' => $result
				]];
			});

		$count = $questionnaires->count();

		$total = Questionnaire::where('level', $id)
			->where('closed', 1)
			->sum('result');

        return [
			'count' => $count,
			'total' => !$count ? 0 : round($total / $count), // round($total * 100 / $knowledges->count() / $count),
            'knowledges' => $knowledges
        ];
    }
}
