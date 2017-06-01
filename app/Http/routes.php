<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::post('auth/login', 'Auth\AuthController@postAuthenticate');

Route::controller('auth', 'Auth\AuthController');
Route::controller('password', 'Auth\PasswordController');

Route::get('/', function () {
	return redirect('/auth/login');
});

Route::get('/notification-email',function(){
	return view('emails.scrape_notif');
});

Route::controller('clients', 'ClientController');

Route::controller('/contact-us', 'ContactController');

Route::group(['prefix' => 'client', 'namespace' => 'Client', 'middleware' => ['auth', 'client']], function() {
	Route::get('/', 'DashboardController@getIndex');
	Route::controller('dashboard', 'DashboardController');
	Route::controller('cases', 'CasesController');
	Route::controller('filters', 'FiltersController');
	Route::controller('watchlists', 'WatchlistsController');
	Route::controller('settings', 'SettingsController');
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => ['auth', 'admin']], function() {
	Route::get('/', 'DashboardController@getIndex');
	Route::controller('dashboard', 'DashboardController');
	Route::controller('data-review', 'DataReviewController');
	Route::controller('users', 'UsersController');
	Route::controller('companies', 'CompanyController');
	Route::controller('settings', 'SettingsController');
});

Route::get('not-subscribed', function() {
	return view('errors.not-subscribed');
});

Route::get('terms-and-conditions', function () {
	return view('pages.terms-and-conditions');
});

Route::get('privacy-policy', function () {
	return view('pages.privacy-policy');
});