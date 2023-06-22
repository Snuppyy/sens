<?php
Route::get('/placenames/{type}/{parent}', 'ProfileController@placenames');
Route::post('/placenames', 'ProfileController@addPlacename');
Route::get('/phone/{phone}/{token?}', 'Auth\RegisterController@getCode');
Route::get('/code/{phone}/{token?}', 'Auth\RegisterController@confirmPhone');
Route::get('/email/check/{token?}', 'Auth\RegisterController@checkEmail');
Route::get('/email/{email}/{token?}', 'Auth\RegisterController@verificationEmail');
