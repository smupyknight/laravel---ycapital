<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{

    public function entities()
    {
    	return $this->hasMany('App\WatchlistEntity');
    }

    public function notifications()
    {
    	return $this->hasManyThrough('App\WatchlistNotification','App\WatchlistEntity','watchlist_id', 'watchlist_entity_id');
    }

    public function subscribers()
    {
    	return $this->hasMany('App\WatchlistSubscriber');
    }

    public function creator()
    {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function getLatestNotificationTime()
    {
        if ($this->notifications()->count()) {
            return $this->notifications()->orderBy('id', 'DESC')->first()->created_at;
        }
    }

}
