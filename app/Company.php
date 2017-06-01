<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
	/**
	 * Query to get active companies only
	 * @param  query $query 
	 * @return null        
	 */
    public function scopeActive($query)
    {
    	$query->where('is_active',1);
    }
}
