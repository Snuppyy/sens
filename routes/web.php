<?php

Route::domain(config('app.creator_domain'))->group(function() {
	Route::get('/convert', 'Api\SessionsController@convert');

	Route::get('/sessions/export/{session}/pdf', 'Api\SessionsController@exportPDF');

	Route::get('{path}', function () {
		return view('backend');
	})->where('path', '(.*)');
});

Route::get('/', 'PageController@index')->name('welcome');
Route::get('/drawing/make', 'PageController@makeDrawing');
Route::get('/drawing/{day}/{level}', 'PageController@drawing')->name('drawing');
Route::get('/auth/{social}', 'Auth\LoginController@redirectToLogin');
Route::get('/auth/{social}/return', 'Auth\LoginController@handleSocialCallback');
Route::get('/auth/{social}/detach', 'Auth\LoginController@detachSocial');

Auth::routes(['register' => false]);

Route::middleware('guest')->group(function() {
	Route::post('/register', 'Auth\RegisterController@register');

	Route::get('/register', 'Auth\RegisterController@showPhoneForm')->name('register');
	Route::post('/register/phone', 'Auth\RegisterController@sendConfirmationCode')->name('register-phone');

	Route::get('/register/code', 'Auth\RegisterController@showCodeForm')->name('register-code-form');
	Route::post('/register/code', 'Auth\RegisterController@attemptConfirmPhone')->name('register-code');

	Route::get('/register/password', 'Auth\RegisterController@showPasswordForm')->name('register-password');

	Route::get('email-verification/success', 'Auth\RegisterController@emailVerificationSuccess')->name('email-verification.success');

	Route::get('/password/otp/{user}', 'Auth\ForgotPasswordController@showOtpForm')->name('password.otp');
	Route::post('/password/otp/{user}', 'Auth\ForgotPasswordController@checkOtp');
});

Route::middleware(['auth'])->group(function() {
	Route::get('/profile/{require?}', 'ProfileController@index')->name('profile');
	Route::post('/profile/{require?}', 'ProfileController@store')->name('profile.save');
	Route::get('/register/success', 'ProfileController@showFirstTime')->name('register-success');
});

Route::middleware(['auth', 'registered'])->group(function() {
	Route::get('/dump', 'PageController@dump');

	Route::get('/home', 'HomeController@index')->name('home');
	Route::get('/questionnaire/{level}', 'QuestionnaireController@index')->name('questionnaire');
	Route::post('/answer', 'QuestionnaireController@answer')->name('answer');
	Route::get('/training/{level}', 'QuestionnaireController@training')->name('training');
	Route::get('/finish/{level}', 'QuestionnaireController@finish')->name('finish');
	Route::get('/participate/{level}/{answer?}', 'QuestionnaireController@participate')->name('participate');
	Route::get('/compare/{level}/{question?}', 'QuestionnaireController@compare')->name('compare');

	Route::get('/history', 'QuestionnaireController@history')->name('history');
	Route::get('/map/{questionnaire}', 'QuestionnaireController@map')->name('map');

	Route::get('/import', 'QuestionnaireController@import');

	Route::get('/application/{training?}', 'TrainingController@application')->name('training.application');
	Route::post('/application', 'TrainingController@apply')->name('training.apply');

	Route::get('/stats/{level}', 'PageController@stats')->name('stats');

	Route::get('/results/{training}/{viewedQuestion1?}/{viewedQuestion2?}/{user_id?}', 'QuestionnaireController@groupCompare')->name('results');

	Route::get('/bom/{session}', 'TrainingController@bom');
});

Route::get('/trainings', 'PageController@trainings')->name('trainings.list');
Route::get('/trainings/{training}', 'PageController@training')->name('trainings.view');