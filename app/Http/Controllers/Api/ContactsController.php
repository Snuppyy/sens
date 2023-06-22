<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\User;
use Validator;
use Storage;
use DB;

class ContactsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$search = $request->input('search');
		$roles = array_filter(explode(',', $request->input('roles')));

		$users = $request->user()->contacts()
			->orderBy($request->input('sort', 'firstname'),
						$request->input('desc') ? 'desc' : 'asc')
			->with([
				'contacts' => function($query) use ($request) {
					$query->select('id')
						->where('user_id', $request->user()->id);
				},
				'sessions' => function($query) use ($request) {
					$query->where('sessions.user_id', $request->user()->id);
				},
				/*
				'sessions.users' => function($query) use ($request) {
					$query->where('session_user.user_id', 'users.id');
				}
				*/
			])
			->when($search, function ($query) use ($search) {
				$query->where(function($query) use ($search) {
					$query->where('email', 'like', "%$search%")
						->orWhere('phone', 'like', "%$search%")
						->orWhere('firstname', 'like', "%$search%")
						->orWhere('lastname', 'like', "%$search%")
						->orWhere('firstname_lat', 'like', "%$search%")
						->orWhere('lastname_lat', 'like', "%$search%")
						->orWhere('place_of_work', 'like', "%$search%")
						->orWhere('position', 'like', "%$search%");
				});
			})
			->when(count($roles), function ($query) use ($roles) {
				$query->whereIn('role', $roles);
			});

		$per_page = (int) $request->input('per_page');

		return $users->paginate($per_page != -1 /*&& empty($search)*/ ? $per_page : $users->count());
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

		$user = new User($data);
		$user->email = $data['email'] ?? null;
		$user->phone = $data['phone'] ?? null;
		if(!empty($data['password'])) {
			$user->password = bcrypt($data['password']);
		}
		$user->role = $data['role'];
		$user->photo = $request->file('photo');
		$user->save();

		$user->sessions()->sync($data['sessions']);

		return [
			'id' => $user->id
		];
	}

	/**
	 * Show single resource record.
	 *
	 * @param  \App\User $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, User $contact)
	{
		$contact->load([
			'sessions' => function($query) use ($request) {
				$query->whereHas('user', function($query) use ($request) {
					$query->where('id', $request->user()->id);
				});
			}
		]);

		return $contact;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\User                $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $contact)
	{
		$data = $request->input();

		/*
		foreach($data['sessions'] ?? [] as $id => $roles) {
			$contact->usersSessions($request->user_id);
		}
		*/

		$contact->sessions()->sync($data['sessions'] ?? []);

		return $contact;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, $userId)
	{
		$request->user()->contacts()->detach($userId);
	}

	private function validator(Request $request, $id = null)
	{
		Validator::make($request->all(), [
			'firstname' => 'required|max:64',
			'lastname' => 'required|max:255',
			'photo' => 'nullable|dimensions:width=320,height=320',
			'email' => ['nullable', 'email', 'max:255',
				Rule::unique('users')->ignore($id)],
		])->validate();
	}

	public function invite(Request $request)
	{
		$input = trim($request->input('identity'));

		if(filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'phone';
            $input = preg_replace('/[^0-9]/', '', $input);
            if(strlen($input) < 12) {
                $input = '998' . $input;
            }
		}
		
		$user = User::where($field, $input)
			->with([
				'contacts' => function($query) use ($request) {
					$query->select('id')
						->where('user_id', $request->user()->id);
				}
			])
			->where('id', '<>', $request->user()->id)->first();

		if($user) {
			if(!$user->contacts->count()) {
				$user->contacts()->attach($request->user()->id);
			}

			return [
				'done' => true,
				'sent' => !!$user->contacts->count()
			];
		}

		return [];
	}

	public function confirm(Request $request, User $user)
	{

		$user->contacts()->attach($request->user()->id);
	}
}
