<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Login
Route::get('login',                             array('uses' => 'LoginController@showLoginForm'))->name(Helper::getBePrefix().'login');
Route::post('login',                            array('uses' => 'LoginController@postLogin'));
Route::any('logout',                            array('uses' => 'LoginController@logout'));

Route::group(['middleware' => 'auth.admin'], function ()
{
	Route::get('/',                             array('uses' => 'HomeController@index'))->name(Helper::getBePrefix().'home');
	// Code removed

	Route::get('setting/file-manager',          array('uses' => 'FileManagerController@index'));

	// File Manager
	// \Route::get('file-manager', 								array('uses' => '\Barryvdh\Elfinder\ElfinderController@showIndex'));
	\Route::any('elfinder/connector', 							array('uses' => 'Barryvdh\Elfinder\ElfinderController@showConnector'));
	Route::get('elfinder/standalonepopup/{input_id}', 			array('uses' => 'Barryvdh\Elfinder\ElfinderController@showPopup'));
});
