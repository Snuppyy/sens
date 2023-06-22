<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\User;
use Validator;
use Storage;
use DB;

class UsersController extends Controller
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

		$users = User::orderBy($request->input('sort', 'firstname'),
						$request->input('desc') ? 'desc' : 'asc')
			->with(['sessions'])
			->when($search, function ($query) use ($search) {
				$query->where(function($query) use ($search) {
					$query->where('id', $search)
						->orWhere('email', 'like', "%$search%")
						->orWhere('phone', 'like', "%$search%")
						->orWhere('firstname', 'like', "%$search%")
						->orWhere('lastname', 'like', "%$search%")
						->orWhere('firstname_lat', 'like', "%$search%")
						->orWhere('lastname_lat', 'like', "%$search%");
				});
			})
			->when(count($roles), function ($query) use ($roles) {
				$query->whereIn('role', $roles);
			});

		$per_page = (int) $request->input('per_page');

		return $users->paginate($per_page != -1 /* && empty($search) */ ? $per_page : $users->count());
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
	public function show(User $user)
	{
		$user->load('sessions:id');
		return $user;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\User                $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $user)
	{
		$data = $request->input();

		$this->validator($request, $user->id);

		$user->email = $data['email'] ?? null;
		$user->phone = $data['phone'] ?? null;
		if(!empty($data['password'])) {
			$user->password = bcrypt($data['password']);
		}
		$user->role = $data['role'] ?: 'user';
		$user->photo = $request->file('photo');
		$user->update($data);
		$user->sessions()->sync($data['sessions'] ?? []);

		return $user;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User $user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(User $user)
	{
		if ($user->delete())
		{
			if ($user->photo)
			{
				Storage::delete($user->photo);
			}

			return [];
		}

		return error();
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
}
