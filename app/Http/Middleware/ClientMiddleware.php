<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Request;

class ClientMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (Auth::guest()) {
			abort(403);
		}

		if (Request::is('client/watchlists*')) {
			if (!Auth::user()->can_access_watchlists) {
				return redirect('/not-subscribed');
			}
		}

		return $next($request);
	}
}
