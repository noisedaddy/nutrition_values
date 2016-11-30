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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');
Route::get('/curl',  ['as' => 'curl', 'uses' => 'ManageController@multicurl_testing']);
Route::post('upload', ['as' => 'upload', 'uses' => 'UploadController@index']);
Route::post('tags', ['as' => 'tags', 'uses' => 'ClarifaiController@tags']);
Route::post('getReport', ['as' => 'getReport', 'uses' => 'ClarifaiController@getReport']);
Route::post('getSingleTagReport', ['as' => 'getSingleTagReport', 'uses' => 'ClarifaiController@getSingleTagReport']);