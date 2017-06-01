<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WatchlistEntity extends Model
{
    public function watchlist()
    {
    	return $this->belongsTo('App\Watchlist');
    }

	public function notifications()
	{
		return $this->hasMany('App\WatchlistNotification');
	}

}
