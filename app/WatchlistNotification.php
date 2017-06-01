<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WatchlistNotification extends Model
{

	public $guarded = [];

	public function entity()
	{
		return $this->belongsTo('App\WatchlistEntity', 'watchlist_entity_id');
	}

	public function court_case()
	{
		return $this->belongsTo('App\CourtCase','case_id');
	}
}
