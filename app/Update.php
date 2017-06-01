<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CourtCase;

/**
 * This model represents an update to a case or one of its child entities.
 *
 * For example, if a hearing is added to a case, this model represents the
 * creation of the hearing.
 *
 * An Update can contain multiple UpdateFields, which are a
 * 'name/old value/new value' changelog of what fields were changed.
 */
class Update extends Model
{

	protected $guarded = [];

	public function fields()
	{
		return $this->hasMany('App\UpdateField')->orderBy('id', 'asc');
	}

}
