<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{

	public function delete()
	{
		foreach ($this->hearings as $hearing) {
			$hearing->delete();
		}

		foreach ($this->parties as $party) {
			$party->delete();
		}

		foreach ($this->documents as $document) {
			$document->delete();
		}

		return parent::delete();
	}

	public function hearings()
	{
		return $this->hasMany('\App\Hearing');
	}

	public function parties()
	{
		return $this->hasMany('\App\Party');
	}

	public function documents()
	{
		return $this->hasMany('\App\Document');
	}

	public function courtCase()
	{
		return $this->belongsTo('\App\CourtCase', 'case_id');
	}

}
