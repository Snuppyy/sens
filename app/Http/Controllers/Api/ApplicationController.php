<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Training;
use App\TrainingApplication;
use App\Notifications\ApplicationStatusChanged;

class ApplicationController extends Controller
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

		$applications = TrainingApplication::with(['user', 'training'])
			->when(!empty($search), function ($query) use ($search) {
				$query->whereHas('user', function($query) use ($search) {
					$query->where(function($query) use ($search) {
						$query->where('firstname', 'like', "%$search%")
							->orWhere('lastname', 'like', "%$search%")
							->orWhere('phone', 'like', "%$search%")
							->orWhere('position', 'like', "%$search%")
							->orWhere('place_of_work', 'like', "%$search%");
					});
				});
			})
			->when($trainings = $request->input('trainings'), function ($query) use ($trainings) { 
				$query->whereIn('training_id', explode(',', $trainings));
			})
			->when(!$request->has('all'), function($query) {
				$query->where('rated', 0);
			})
			// ->where('id', '>', 68)
			->when(!in_array('superuser', $request->user()->role), function($query) use ($request) {
				$query->whereHas(
					'training',
					function($query) use ($request) {
						$query->where('user_id', $request->user()->id);	
					}
				);
			})
			->where('status', '<>', 'draft')
			->orderBy($request->input('sort', 'created_at'),
						$request->input('desc') ? 'desc' : 'asc');

		return $applications->paginate($per_page != -1 ? $per_page : $applications->count());
	}

	public function assign(TrainingApplication $application, $selected) {
		$application->selected = $selected;
		$application->timestamps = false;
		$application->save();
	}

	public function view(TrainingApplication $application) {
		$application->viewed_at = now();
		$application->timestamps = false;
		$application->save();
	}

	public function finish(Training $training) {
		foreach(TrainingApplication::where('training_id', $training->id)->get() as $application) {
			if($application->selected) {
				$application->status = 'accepted';
			} else {
				$application->status = 'rejected';
			}

			$application->timestamps = false;
			$application->save();

			$input = [
				'messages' => [
					[
						'recipient' => $application->user->phone,
						'message-id' => '0',
						'sms' => [
							'originator' => '3700',
							'content' => [
								'text' => __('Статус Вашей заявки №:id изменился. Подробности в Вашем аккаунте на sens.uz.', ['id' => $application->id])
							]
						]
					]
				]
			];
	
			$url = "http://91.204.239.44/broker-api/send";
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
			curl_setopt($ch, CURLOPT_USERPWD, 'intilish:t6Ne4cc');
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			curl_close($ch);

			//$application->user->notify(new ApplicationStatusChanged($application));
		}

		$training->status = 'complete';
		$training->save();
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

		if ($data['url'] === null)
		{
			$data['url'] = '';
		}

		$page = new Page($data);
		$page->save();

		$this->storeRelations($page, $data);

		$page->imageFile = $request->file('image');
		$page->save();

		return [
			'id' => $page->id
		];
	}

	/**
	 * Show single resource record.
	 *
	 * @param  \App\TrainingApplication $application
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(TrainingApplication $application)
	{
		$application->load(['user', 'training']);
		return $application;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\TrainingApplication $application
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, TrainingApplication $application)
	{
		$application->application = json_decode($request->getContent());
		$application->status = 'consideration';
		$application->filled = $application->application['filled'] ?? 0;
		$application->rated = $application->application['rated'] ?? 0;
		$application->flagged = $application->application['flagged'] ?? 0;
		$application->timestamps = false;
		$application->save();

		return $application;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Page $page
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Page $page)
	{
		if ($page->delete())
		{
			if ($page->image)
			{
				Storage::delete($page->image);
			}

			return [];
		}

		return error();
	}

	private function storeRelations($page, $data)
	{
		$categories = [];

		if (isset($data['categories']))
		{
			foreach (array_filter($data['categories']) as $title)
			{
				$category = Category::where('title', $title)->first();

				if (!$category)
				{
					$category = new Category(['title' => $title]);
					$category->save();
				}

				$categories[] = $category->id;
			}
		}

		$page->categories()->sync($categories);
		$page->loadMissing('categories');

		if (!isset($data['options']))
		{
			$data['options'] = [];
		}

		$page->options()->sync($data['options']);
		$page->loadMissing('options');

		if (!isset($data['pages']))
		{
			$data['pages'] = [];
		}

		$page->pages()->sync($data['pages']);
		$page->loadMissing('pages');

		if ($page->facilities)
		{
			$page->facilities_count = \App\Facility::whereHas('options', function ($query) use ($data) {
				$query->whereIn('id', $data['options']);
				if (count($data['options']) > 1)
				{
					$query->select(\DB::raw('count(distinct id)'));
				}
			}, '>=', count($data['options']))->count();
		}
	}

	private function validator(Request $request, $id = null)
	{
		Validator::make($request->all(), [
			'url'          => 'nullable|alpha_dash|max:64',
			'categories.*' => 'max:64|distinct',
			'name'         => 'required|max:255',
			'title'        => 'required|max:255',
			'hide'         => 'nullable',
			'min_persons'  => 'nullable|numeric',
			'max_persons'  => 'nullable|numeric',
			'min_bill'     => 'nullable|numeric',
			'max_bill'     => 'nullable|numeric',
			'options.*'    => 'distinct|exists:options,id',
			'image'        => 'nullable|dimensions:width=277,height=265'
		])->after(function ($validator) use ($request, $id) {
			$url = $request->input('url');
			if (Page::where('url', $url === null ? '' : $url)
				->when(!!$id, function ($query) use ($id) {
					return $query->where('id', '<>', $id);
				})->first())
			{
				return $validator->errors()->add('url', 'Такое значение поля url уже существует.');
			}
		})->validate();
	}

	/**
	 * Import pages
	 */
	public function import()
	{
		set_time_limit(0);

		$results = DB::select('select t1.name as name, t1.title, t1.link as url, t1.`text` as content, t2.name as category, t1.description, t1.keywords, t1.disabled as hidden, t1.`datetime` as updated_at from `text` t1 left join `text` t2 on t2.id = t1.parent_id');

		foreach ($results as $data)
		{
			$data = json_decode(json_encode($data), true);

			if ($data['name'] && $data['title'])
			{
				if (empty($data['hidden']))
				{
					unset($data['hidden']);
				}

				$page             = new Page($data);
				$page->updated_at = $page->created_at = $data['updated_at'];
				$page->save();

				$data['categories'] = [$data['category']];
				$this->storeRelations($page, $data);
			}
		}

		return 'ok';
	}

	/**
	 * Import metro
	 */
	public function subways()
	{
		function files_to_lat($text)
		{
		$text=str_replace("а", "a", $text);
		$text=str_replace("б", "b", $text);
		$text=str_replace("в", "v", $text);
		$text=str_replace("г", "g", $text);
		$text=str_replace("д", "d", $text);
		$text=str_replace("е", "e", $text);
		$text=str_replace("ё", "yo", $text);
		$text=str_replace("ж", "j", $text);
		$text=str_replace("з", "z", $text);
		$text=str_replace("и", "i", $text);
		$text=str_replace("й", "y", $text);
		$text=str_replace("к", "k", $text);
		$text=str_replace("л", "l", $text);
		$text=str_replace("м", "m", $text);
		$text=str_replace("н", "n", $text);
		$text=str_replace("о", "o", $text);
		$text=str_replace("п", "p", $text);
		$text=str_replace("р", "r", $text);
		$text=str_replace("с", "s", $text);
		$text=str_replace("т", "t", $text);
		$text=str_replace("у", "u", $text);
		$text=str_replace("ф", "f", $text);
		$text=str_replace("х", "h", $text);
		$text=str_replace("ц", "ts", $text);
		$text=str_replace("ч", "ch", $text);
		$text=str_replace("ш", "sh", $text);
		$text=str_replace("щ", "sch", $text);
		$text=str_replace("ъ", "", $text);
		$text=str_replace("ы", "y", $text);
		$text=str_replace("ь", "", $text);
		$text=str_replace("э", "e", $text);
		$text=str_replace("ю", "yu", $text);
		$text=str_replace("я", "ya", $text);
		
		$text=str_replace("А", "a", $text);
		$text=str_replace("Б", "b", $text);
		$text=str_replace("В", "v", $text);
		$text=str_replace("Г", "g", $text);
		$text=str_replace("Д", "d", $text);
		$text=str_replace("Е", "e", $text);
		$text=str_replace("Ё", "yo", $text);
		$text=str_replace("Ж", "j", $text);
		$text=str_replace("З", "z", $text);
		$text=str_replace("И", "i", $text);
		$text=str_replace("Й", "y", $text);
		$text=str_replace("К", "k", $text);
		$text=str_replace("Л", "l", $text);
		$text=str_replace("М", "m", $text);
		$text=str_replace("Н", "n", $text);
		$text=str_replace("О", "o", $text);
		$text=str_replace("П", "p", $text);
		$text=str_replace("Р", "r", $text);
		$text=str_replace("С", "s", $text);
		$text=str_replace("Т", "t", $text);
		$text=str_replace("У", "u", $text);
		$text=str_replace("Ф", "f", $text);
		$text=str_replace("Х", "h", $text);
		$text=str_replace("Ц", "c", $text);
		$text=str_replace("Ч", "ch", $text);
		$text=str_replace("Ш", "sh", $text);
		$text=str_replace("Щ", "sch", $text);
		$text=str_replace("Ъ", "", $text);
		$text=str_replace("Ы", "y", $text);
		$text=str_replace("Ь", "", $text);
		$text=str_replace("Э", "e", $text);
		$text=str_replace("Ю", "yu", $text);
		$text=str_replace("Я", "ya", $text);
		
		$text=strtolower($text); //mb_strtolower	
		
		$text=str_replace(" ", "-", $text);
		$text=str_replace("+", "-", $text);
		$text=str_replace('"', '', $text);
		$text=str_replace("'", "", $text);
		$text=str_replace("`", "", $text);
		$text=str_replace("«", "", $text);
		$text=str_replace("»", "", $text);
		$text=str_replace("&quot;", "", $text);
		$text=str_replace("&", "-and-", $text);
		$text=str_replace("#039;", "", $text);	
		$text=str_replace("@", "", $text);
		$text=str_replace('"', "", $text);
		$text=str_replace('.', "", $text);
		$text=str_replace(',', "", $text);
		
		$text=preg_replace("/[^A-Za-z0-9\/\-]/i","",$text);
		$text=iconv('utf-8', 'utf-8//IGNORE', $text);
		
		$text=str_replace('--', "-", $text);
		$text=str_replace('--', "-", $text);
		
		return $text;
		}

		set_time_limit(0);

		foreach(Option::where('category', 12)->get() as $station) {
			$url = 'restorany-metro-' . files_to_lat($station->title);

			if(!Page::where('url', $url)->exists()) {
				$texts = DB::table('texts_metro')->where('id', $station->details)->first();

				$page = new Page([
					'url' => $url,
					'name' => 'Метро ' . $station->title,
					'title' => $texts ? $texts->title1 : 'Лучшие рестораны, кафе и банкетные залы возле метро ' . $station->title,
					'description' => $texts ? $texts->description1 : 'Лучшие рестораны, кафе и банкетные залы у метро ' . $station->title,
					'keywords' => $texts ? $texts->keywords1 : str_replace('$', $station->title, 'рестораны метро $, кафе метро $, ресторан на $, банкетный зал на $'),
					'facilities' => '1'
				]);

				if(!$page->save()) {
					var_dump($url);
				}

				$this->storeRelations($page, [
					'categories' => ['Метро'],
					'options' => [$station->id]
				]);
			} else {
				var_dump($url);
			}
		}

		return 'ok';
	}

	/**
	 * Update facilities counts for all pages
	 */
	public function updateCounts()
	{
		foreach (Page::where('facilities', '1')->get() as $page)
		{
			$options = $page->options->map(function ($option) {
				return $option->id;
			})->toArray();

			$page->facilities_count = \App\Facility::whereNull('hidden')
				->whereHas('options', function ($query) use ($options) {
					$query->whereIn('id', $options);
					if (count($options) > 1)
					{
						$query->select(\DB::raw('count(distinct id)'));
					}
				}, '>=', count($options))
				->when($page->min_persons, function ($query) use ($page) {
					return $query->where('min_persons', '>=', $page->min_persons);
				})
				->when($page->max_persons, function ($query) use ($page) {
					return $query->where('max_persons', '<=', $page->max_persons);
				})
				->when($page->min_bill, function ($query) use ($page) {
					return $query->where('bill', '>=', $page->min_bill);
				})
				->when($page->max_bill, function ($query) use ($page) {
					return $query->where('bill', '<=', $page->max_bill);
				})->count();

			if(!$page->facilities_count) {
				$page->facilities_count = \App\Facility::whereNull('hidden')->count();
			}

			$page->save();
		}

		return 'ok';
	}

	public function generate() {
		$compilations = Page::whereNotNull('image')->get();

		Option::where('category', 12)
			->get()
			->map(function($option) {
				return [
					'text' => 'около метро ' . $option->title,
					'options' => [$option->id]
				];
			})
			->concat(Option::where('category', 11)
				->get()
				->map(function($option) {
					return [
						'text' => 'в районе ' . $option->title,
						'options' => [$option->id]
					];
				})
			)
			->values()
			->take(300)
			->each(function($item, $index) use ($compilations) {
				$short = 'Выпускной ' . $item['text'];
				$long = 'Самые лучшие заведения Москвы для выпускного. Акции. Скидки. Подарки. Обзоры с фото. Бесплатный подбор. Удобный поиск. Большой выбор вариантов для выпускного '  . $item['text'] . '.';

				$page = new Page([
					'url' => Slug::make($short),
					'name' => $short,
					'title' => $short,
					'description' => $long,
					'keywords' => $short,
					'short_text'  => $long,
					'facilities' => 1,
					'compilations' => 1,
					'generated' => 1
				]);

				$page->image = $compilations->get($index)->image;

				$page->save();

				$page->options()->sync($item['options']);
			});

		return 'ok';
	}
}
