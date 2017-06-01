<?php
namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;

class DashboardController extends Controller
{

	/**
	 * Dashboard page.
	 *
	 * @return view
	 */
	public function getIndex()
	{
		$states = Auth::user()->getStates();

		if (count($states)) {
			return redirect('/client/cases?state=' . $states[0]);
		}

		if (Auth::user()->can_access_watchlists) {
			return redirect('/client/watchlists');
		}

		return redirect('/client/settings');
	}

}
