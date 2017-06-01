<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use App\User;
use Log;
use Mail;
use SparkPost\SparkPost;
use GuzzleHttp\Client;
use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use DateTime;
use DateTimeZone;
use App\Company;
use Session;
use App\StatesSubscribed;

class UsersController extends Controller
{

	/**
	 * Shows list of users page
	 * @return view
	 */
	public function getIndex()
	{
		$users = User::getAllClients();
		return view('pages.admin.users-list')
				->with('data',[])
				->with('users',$users)
				->with('title','User Management');
	}

	/**
	 * Shows list of users with filters
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function postIndex(Request $request)
	{
		$users = User::getAllClients($request);
		return view('pages.admin.users-list')
				->with('users',$users)
				->with('data',$request->all())
				->with('title','User Management');
	}

	/**
	 * Shows the add users form
	 * @return
	 */
	public function getAdd()
	{
		$companies = Company::active()->orderBy('name','asc')->get();
		return view('pages.admin.users-create')
					->with('companies',$companies)
					->with('title','User Create');
	}

	/**
	 * Handles the saving of users and states subscribed
	 * @param  Request $request
	 * @return redirect
	 */
	public function postAdd(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:255',
			'email' => 'required|email|unique:users',
			'password' => 'required|same:confirm_password',
			'confirm_password' => 'required',
			'states' => '',
			'company_subscribed' => '',
		]);
		User::createUser($request);
		return redirect('/admin/users');
	}

	/**
	 * Soft deletes user. Sets status to inactive
	 * @param  int $id
	 * @return null
	 */
	public function getDelete($id)
	{
		User::deleteUser($id);
		return redirect('/admin/users');
	}

	public function postImpersonate(Request $request,$id)
	{
		Session::put('impersonate', Auth::user()->id);
		Auth::login(User::findOrFail($id));
		return redirect('/');
	}

	/**
	 * User edit page
	 * @param  int $id
	 * @return view
	 */
	public function getEdit($id)
	{
		$user = User::where('status','!=','inactive')->where('id',$id);
		$companies = Company::active()->orderBy('name','asc')->get();
		if ($user->get()->isEmpty()) {
			return redirect('/admin/users');
		}
		return view('pages.admin.users-edit')
					->with('data',$user->first())
					->with('companies',$companies)
					->with('title','User Edit');
	}

	/**
	 * Handles editing of user
	 * @param  Request $request
	 * @param  int  $id
	 * @return redirect
	 */
	public function postEdit(Request $request, $id)
	{
		$this->validate($request, [
			'name'               => 'required|max:255',
			'email'              => 'required|email|unique:users,email,'.$id,
			'password'           => 'confirmed',
			'states'             => '',
			'company_subscribed' => '',
		]);

		$user = User::findOrFail($id);

		$user->name = ucwords($request->name);
		$user->email = $request->email;
		$user->type = $request->type;

		if ($request->password != '') {
			$user->password = bcrypt($request->password);
		}

		$user->company_subscribed = $request->type == 'client' ? $request->company_subscribed : 0;
		$user->can_access_watchlists = $request->get('can_access_watchlists', 0);
		$user->save();

		StatesSubscribed::where('user_id', $id)->delete();

		if ($request->states) {
			foreach ($request->states as $state) {
				StatesSubscribed::create([
					'user_id' => $user->id,
					'states'  => $state,
				]);
			}
		}

		return redirect('/admin/users');
	}
}
