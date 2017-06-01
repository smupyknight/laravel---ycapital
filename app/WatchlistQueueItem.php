<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WatchlistQueueItem extends Model
{

	public $table = 'watchlist_queue';

	protected $fillable = ['id'];

}
