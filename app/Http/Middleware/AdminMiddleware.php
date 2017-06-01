<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AdminMiddleware
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
		if (!Auth::user() || !Auth::user()->isAdmin()) {
			abort(403);
		}

		return $next($request);
	}
}
