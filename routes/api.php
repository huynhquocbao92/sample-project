<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'namespace' => 'Api\v12'], function () {
	Route::post('/user-entry', 					'UsersController@register');
	Route::post('/user-login', 					'UsersController@login');
});

Route::group(['prefix' => 'v1', 'namespace' => 'Api\v12', 'middleware' => 'api.auth'], function () {
	Route::post('/update-info', 				'UsersController@updateInfo');
	Route::get('/get-notice', 					'NoticeController@getNotice');
	Route::get('/get-friend', 					'CharacterController@getFriend');
	// Code removed
});
