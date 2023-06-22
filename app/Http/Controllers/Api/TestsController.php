<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Test;
use App\Questionnaire;
use App\User;

class TestsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$per_page = (int) $request->input('per_page');

		$items = Test::with('user')
			->orderBy($request->input('sort', 'created_at'),
				$request->input('desc') ? 'desc' : 'asc');

		if (!in_array('superuser', $request->user()->role)) {
			$items->whereHas('training', function($query) use ($request) {
				$query->where('user_id', $request->user()->id);
			});
		} else {
			$items->where(function($query) {
				$query->whereNull('training_id')
					->orWhere('training_id', 55);
			});
		}

		return $items->paginate($per_page != -1 ? $per_page : $items->count());
	}

	/**
	 * Show single resource record.
	 *
	 * @param  \App\Test $test
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, Test $test)
	{
		$type = $request->last ? 'last' : 'first';

		$selections = [];

		$test->questions()->withCount([
				"{$type}Results as answered",
				"{$type}Results as answered_correctly" => function($query) {
					$query->where('correct', 1);
				}
			])
			->get()
			->each(function($question) use (&$selections) {
				foreach($question->selections as $selection) {
					if(!isset($selections[$selection['id']])) {
						$selection['results'] = 0;
						$selection['results_count'] = 0;
						$selections[$selection['id']] = $selection;
					}

					$selections[$selection['id']]['results'] += $question->answered_correctly / $question->answered;
					$selections[$selection['id']]['results_count']++;
				}
			});

		foreach ($selections as &$selection) {
			$selection['result'] = $selection['results'] / $selection['results_count'];
			unset($selection['results'], $selection['results_count']);
		}

		return [
			'test' => $test,
			'source' => $test->questions->get(0)->source,
			'selections' => array_values($selections)
		];
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function participants(Request $request, Test $test)
	{
		$search = $request->search;

		$per_page = (int) $request->per_page;

		$table = (new Questionnaire())->getTable();

		$items = $test->participants()
			->select(['users.*', "$table.level as questionnaire_level"])
			->when(!empty($search), function ($query) use ($search) {
				$query->where(function($query) use ($search) {
					$query->where('firstname', 'like', "%$search%")
						->orWhere('lastname', 'like', "%$search%")
						->orWhere('position', 'like', "%$search%")
						->orWhere('place_of_work', 'like', "%$search%");
				});
			})
			->leftJoin("$table as q", function($join) use ($table) {
				$join->on('q.user_id', "$table.user_id")
					->on('q.created_at', '>', "$table.created_at")
					->on('q.level', "$table.level")
					->where('q.closed', 1);
				})
			->where("$table.closed", 1)
			->whereNull('q.id')
			->with([
				'province',
				'city',
				'firstQuestionnaire' => function($query) use ($table, $test) {
					$query->select(["$table.user_id", "$table.result", "$table.finished_at"])
						->where("$table.level", $test->id)
						->leftJoin("$table as q", function($join) use ($table) {
							$join->on('q.user_id', "$table.user_id")
								->on('q.level', "$table.level")
								->on('q.created_at', '<', "$table.created_at")
								->whereNotNull("q.finished_at");
						})
						->whereNull('q.id')
						->whereNotNull("$table.finished_at");
				}])
			->withCount([
				'questionnaires as takes' => function($query) {
					$query->whereColumn('level', 'questionnaire_level')
						->where('closed', 1);
				},
				'questionnaires as trainings' => function($query) {
					$query->whereColumn('level', 'questionnaire_level')
						->where('training_started', 1);
				}
			])
			->orderBy($request->input('sort', 'created_at'),
				$request->input('desc') ? 'desc' : 'asc');

		return $items->paginate($per_page != -1 ? $per_page : $items->count());
	}

	/**
	 * Show single resource record.
	 *
	 * @param  \App\Test $test
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function participant(Request $request, Test $test, User $user)
	{
		$table = (new Questionnaire())->getTable();

		$selections = [];

		$user->firstQuestionnaire()
			->select("$table.*")
			->where("$table.level", $test->id)
			->leftJoin("$table as q", function($join) use ($table, $request) {
				$join->on('q.user_id', "$table.user_id")
					->on('q.created_at', !empty($request->last) ? '>' : '<', "$table.created_at")
					->on('q.level', "$table.level");
			})
			->whereNull('q.id')
			->first()
			->questions
			->each(function($question) use (&$selections) {
				foreach($question->selections as $selection) {
					if(!isset($selections[$selection['id']])) {
						$selection['results'] = 0;
						$selection['results_count'] = 0;
						$selections[$selection['id']] = $selection;
					}

					$selections[$selection['id']]['results'] += $question->pivot->correct;
					$selections[$selection['id']]['results_count']++;
				}
			});

		foreach ($selections as &$selection) {
			$selection['result'] = $selection['results'] / $selection['results_count'];
			unset($selection['results'], $selection['results_count']);
		}

		return [
			'test' => $test,
			'user' => $user,
			'source' => $test->questions->get(0)->source,
			'selections' => array_values($selections)
		];
	}

	public function result(Questionnaire $result)
	{
		$test = $result->test;

		$selections = [];

		$result->questions->each(function($question) use (&$selections) {
			if($question->selections) {
				foreach($question->selections as $selection) {
					if(!isset($selections[$selection['id']])) {
						$selection['results'] = 0;
						$selection['results_count'] = 0;
						$selections[$selection['id']] = $selection;
					}

					$selections[$selection['id']]['results'] += $question->pivot->correct;
					$selections[$selection['id']]['results_count']++;
				}
			}
		});

		foreach ($selections as &$selection) {
			$selection['result'] = $selection['results'] / $selection['results_count'];
			unset($selection['results'], $selection['results_count']);
		}

		return [
			'test' => $test,
			'user' => $result->user,
			'source' => $test->questions->get(0)->source,
			'selections' => array_values($selections),
			'created_at' => $result->created_at
		];
	}

	public function results(Request $request, Test $test, User $user)
	{
		$items = $user->questionnaires($test->id)
			->with('user')
			->withCount([
				'questions as not_answered' => function($query) {
					$query->whereNull('answered');
				},
				'questions as dont_know' => function($query) {
					$query->whereNotNull('dontknow');
				}
			])
			->orderBy($request->input('sort', 'created_at'),
				$request->input('desc') ? 'desc' : 'asc');

		$per_page = (int) $request->per_page;
		return $items->paginate($per_page != -1 ? $per_page : $items->count());
	}

	public function answers(Questionnaire $result)
	{
		return [
			'questions' => $result->questions()->with([
				'questionnaireOptions' => function($query) use ($result) {
					$query->where('questionnaire_id', $result->id);
				}
			])->orderBy('position')->get(),
			'test' => $result->test,
			'user' => $result->user,
			'created_at' => $result->created_at
		];
	}

	public function graph(Request $request, Test $test)
	{
		$series2 = [];

		$series1 = User::whereHas('questionnaire', function($query) use ($test) {
			$query->where('level', $test->id);
		})
		->with([
			'questionnaires' => function($query) use ($test) {
				$query->where('level', $test->id)
					->where('closed', 1);
			}
		])
		->get()
		->map(function($user) use (&$series2) {
			return [
				'name' => $user->fio,
				'data' => $user->questionnaires
					->reverse()
					->values()
					->map(function($questionnaire, $index) use (&$series2, $user) {
						if(!isset($series2[$index])) {
							$series2[$index] = [
								'name' => __('Прохождение') . ' ' . ($index + 1),
								'data' => []
							];
						}

						$series2[$index]['data'][] = [
							'x' => $user->fio,
							'y' => $questionnaire->result
						];

						return [
							$questionnaire->finished_at->timestamp * 1000,
							$questionnaire->result
						];
					})
					->toArray()
			];
		});

		// $series2 = $series

		return [
			'test' => $test,
			'series1' => $series1,
			'series2' => $series2
		];
	}
}
