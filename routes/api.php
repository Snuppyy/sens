<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->group(function () {
	Route::get('/user', function (Request $request) {
		return $request->user();
	});

	Route::patch('settings/password', 'Settings\UpdatePassword');

	Route::post('refresh', 'Api\LoginController@refresh');
	Route::post('logout', 'Api\LoginController@logout');

	Route::get('sources', 'Api\SessionsController@sources');
	Route::get('source/{source}', 'Api\SessionsController@source');
	Route::get('questionnaire/{id}/{duration}', 'Api\SessionsController@questionnaireResults');
	Route::post('questionnaire/{test}', 'Api\TrainingsController@startTest');

	Route::resource('sessions', 'Api\SessionsController');
	Route::get('dataset/{session}', 'Api\SessionsController@dataset');
	Route::post('dataset/{session}', 'Api\SessionsController@datasave');
	Route::post('overlay/{session}', 'Api\SessionsController@overlay');
	Route::post('sessions/upload/{session}', 'Api\SessionsController@upload');
	Route::post('sessions/comments/{session}', 'Api\SessionsController@comments');

	Route::resource('trainings', 'Api\TrainingsController');
	Route::post('trainings/upload', 'Api\TrainingsController@upload');

	Route::resource('users', 'Api\UsersController');

	Route::post('contacts/invite', 'Api\ContactsController@invite');
	Route::get('contacts/confirm/{user}', 'Api\ContactsController@confirm');
	Route::resource('contacts', 'Api\ContactsController');

	Route::get('results/{level}', 'Api\SessionsController@results');

	Route::resource('applications', 'Api\ApplicationController');
	Route::get('applications/assign/{application}/{selected}', 'Api\ApplicationController@assign');
	Route::get('applications/finish/{training}', 'Api\ApplicationController@finish');
	Route::get('applications/view/{application}', 'Api\ApplicationController@view');

	Route::get('tests', 'Api\TestsController@index');
	Route::get('tests/result/{result}', 'Api\TestsController@result');
	Route::get('tests/answers/{result}', 'Api\TestsController@answers');
	Route::get('tests/{test}/graph', 'Api\TestsController@graph');
	Route::get('tests/{test}/participants', 'Api\TestsController@participants');
	Route::get('tests/{test}/{user}/map', 'Api\TestsController@participant');
	Route::get('tests/{test}/{user}', 'Api\TestsController@results');
	Route::get('tests/{test}', 'Api\TestsController@show');
});

Route::middleware('guest:api')->group(function () {
	Route::post('login', 'Api\LoginController@login');
    Route::post('register', 'Api\RegisterController@register');
    Route::post('password/challenge', 'Api\ForgotPasswordController@sendChallenge');
    Route::post('password/reset', 'Api\ResetPasswordController@reset');
});

Route::get('coauth', 'Api\LoginController@coauth')->name('coauth');
