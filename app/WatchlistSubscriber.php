<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WatchlistSubscriber extends Model
{

	public function creator()
	{
		return $this->belongsTo('App\User', 'created_by');
	}

	public function watchlist()
	{
		return $this->belongsTo('App\Watchlist');
	}

}
