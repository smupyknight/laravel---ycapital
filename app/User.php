<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\StatesSubscribed;
use Mail;
use DB;


class User extends Model implements AuthenticatableContract,
									AuthorizableContract,
									CanResetPasswordContract
{
	use Authenticatable, Authorizable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password', 'type','status','company_subscribed','can_access_watchlists'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

	/**
	 * Gets the states associated with the client
	 * @return	relationship
	 */
	public function states()
	{
		return $this->hasMany('App\StatesSubscribed','user_id');
	}

	public function scopeGetUsersWithStates($query)
	{
		return $query->where('type','client')->where('status','!=','inactive')->has('states')->with('states')->get();
	}

	/**
	 * Creates user account
	 *
	 * Also creates states subscribed id user type is client
	 * @param  query $query
	 * @param  array $request
	 * @return null
	 */
	public function scopeCreateUser($query,$request)
	{
		$user = User::create([
			'name'                  => ucwords($request->name),
			'email'                 => $request->email,
			'password'              => bcrypt($request->password),
			'type'                  => $request->type,
			'status'                => 'active',
			'company_subscribed'    => $request->type == 'client' ? $request->company_subscribed : 0,
			'can_access_watchlists' => $request->get('can_access_watchlists', 0),
		]);

		if ($request->type == 'client' && $request->states != '') {
			foreach ($request->states as $state) {
				StatesSubscribed::create([
					'user_id' => $user->id,
					'states'  => $state,
				]);
			}

			$this->sendWelcomeEmail($request);
		}
	}

	/**
	 * Send welcome email to new user
	 * @param  Request $request
	 * @return null
	 */
	private function sendWelcomeEmail($request)
	{
		$data = $request->all();
		Mail::send('email.welcome',array('data' => $data),function($message) use ($data){
			$message->subject('Welcome!');
			$message->from('info@' . env('MAIL_FROM_DOMAIN'), 'ALARES');
			$message->to($data['email']);
		});
	}

	/**
	 * Gets all clients
	 * @param  query $query
	 * @param  array $filter
	 * @return array
	 */
	public function scopeGetAllClients($query,$filter = null)
	{
		$query->with('states')->where('type','client')->orderBy('id','desc');

		if ($filter['name']) {
			$query->where('name','like','%'.$filter['name'].'%');
		}
		if ($filter['id']) {
			$query->where('id',$filter['id']);
		}
		if (isset($filter['status']) && $filter['status'] != 'all') {
			$query->where('status',$filter['status']);
		}
		if ($filter['date_added']) {
			$query->whereBetween('created_at',[date('Y-m-d 00:00:00',strtotime($filter['date_added'])),date('Y-m-d 23:59:59',strtotime($filter['date_added']))]);
		}
		if ($filter['date_modified']) {
			$query->whereBetween('created_at',[date('Y-m-d 00:00:00',strtotime($filter['date_modified'])),date('Y-m-d 23:59:59',strtotime($filter['date_modified']))]);
		}
		if ($filter['states']) {
			$query->whereHas('states',function($query) use ($filter) {
				$query->where(function($query) use ($filter) {
					foreach ($filter['states'] as $state) {
							$query->orWhere('states',$state);
					}
				});
			});
		}
		if ($filter['global_search']) {
			$query->where(function($query) use ($filter) {
				$query->orWhere('id','like','%'.$filter['global_search'].'%');
				$query->orWhere('status','like','%'.$filter['global_search'].'%');
				$query->orWhere('name','like','%'.$filter['global_search'].'%');
			});
		}
		return $query->get();
	}

	/**
	 * Soft deletes user by setting status to inactive
	 * @param  query $query
	 * @param  int $id
	 * @return null
	 */
	public function scopeDeleteUser($query,$id)
	{
		$query->where('id',$id)
				->update(['status' => 'inactive']);
	}

	public function getStates()
	{
		if ($this->type == 'admin') {
			return collect(['act','nt','nsw','qld','sa','tas','vic','wa','federal']);
		}

		return $this->states->pluck('states');
	}

	public function watchlists()
	{
		return $this->hasMany('App\Watchlist','created_by');
	}

	public function getNumWatchlistEntities($id)
	{
		$query = DB::table('watchlists as w')
					->leftJoin('watchlist_entities as e','e.watchlist_id','=','w.id')
					->where('w.created_by',$id)
					->count();

		return $query;
	}

	public function isClient()
	{
		return $this->type == 'client';
	}

	public function isAdmin()
	{
		return $this->type == 'admin';
	}

	public function filters()
	{
		return $this->hasMany('App\Filter');
	}

}
