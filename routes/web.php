<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'FirebaseController@regression')->name('regression');
Route::get('/regression', 'FirebaseController@regression')->name('regression');
Route::get('/chart-data', 'FirebaseController@chartData')->name('chart-data');
Route::get('/realtime-data', 'FirebaseController@realtimeData')->name('realtime-data');
Route::get('/clone-data', 'FirebaseController@cloneData')->name('clone-data');
