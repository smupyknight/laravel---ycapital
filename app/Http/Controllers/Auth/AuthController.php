<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Auth;
use URL;
use Illuminate\Http\Request;
use Session;

class AuthController extends Controller
{
	/**
	 * If there is an error on login, redirect depending on admin or client
	 * @return string
	 */
	public function loginPath()
	{
		$url = explode('/',URL::previous());
		if (end($url) == 'admin') {
			return '/admin';
		}
		return '/';
	}

	/**
	 * Determine where the user should go after logging in.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		$location = session()->get('login_redirect_url');

		if ($location) {
			session()->forget('login_redirect_url');
			return $location;
		}

		if (Auth::user()->isAdmin()) {
			return '/admin';
		}

		return '/client';
	}

	/*
	|--------------------------------------------------------------------------
	| Registration & Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users, as well as the
	| authentication of existing users. By default, this controller uses
	| a simple trait to add these behaviors. Why don't you explore it?
	|
	*/

	use AuthenticatesAndRegistersUsers, ThrottlesLogins;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	/**
	 * Standalone login page
	 *
	 * @return view
	 */
	public function getLogin() {
		return view('pages.admin.login');
	}

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function validator(array $data)
	{
		return Validator::make($data, [
			'name' => 'required|max:255',
			'email' => 'required|email|max:255|unique:users',
			'password' => 'required|confirmed|min:6',
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return User
	 */
	protected function create(array $data)
	{
		return User::create([
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => bcrypt($data['password']),
		]);
	}

	public function getLogout(Request $request)
	{
		if (Session::has('impersonate')) {
			Auth::login(User::findOrFail(Session::get('impersonate')));
			Session::forget('impersonate');
			return redirect($this->loginPath());
		}

		Auth::logout();
		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}

	/**
	 * Custom login authentication to not allow inactive users to be logged in
	 * @param  Request $request
	 * @return redirect
	 */
	public function postAuthenticate(Request $request)
	{
		$this->validate($request, [
			'conditions_agree' => 'required|',
		], [
			'conditions_agree.required' => 'You must agree to the terms and conditions.',
		]);

		$remember = $request->get('remember_me') == 'on';

		$credentials = [
			'email'    => $request->get('email'),
			'password' => $request->get('password'),
		];

		if (Auth::attempt($credentials, $remember)) {
			// Authentication passed...
			if (Auth::user()->status != 'inactive') {
				return redirect($this->redirectPath());
			}

			Auth::logout();
		}

		return redirect($this->loginPath())
			->withInput($request->only('email'))
			->withErrors([
				$this->loginUsername() => $this->getFailedLoginMessage(),
			]);
	}
}
