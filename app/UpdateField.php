<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UpdateField extends Model
{

	protected $guarded = [];

	public function update(array $attributes = [])
	{
		if ($attributes) {
			return parent::update($attributes);
		}

		return $this->belongsTo('App\Update');
	}

}
