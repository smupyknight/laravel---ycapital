<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use Auth;

class SettingsController extends Controller
{

    /**
     * Show client settings page
     * @return view 
     */
    public function getIndex()
    {
        return view('pages.client.settings')
            ->with('title','User Settings');
    }

    /**
     * Handle post data for client settings page
     * @param  Request $request 
     * @return redirect           
     */
    public function postIndex(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->timezone = $request->timezone;
        $user->save();

        return redirect('/client/settings');
    }
}
