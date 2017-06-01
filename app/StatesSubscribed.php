<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatesSubscribed extends Model
{
	/**
	 * Table associated with states subscribed
	 * @var string
	 */
    protected $table = 'states_subscribed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','states'];

}
