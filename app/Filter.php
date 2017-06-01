<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{

	public $guarded = [];

	public function getQueryString()
	{
		$fields = [
			'state'                 => $this->state,
			'court_type'            => $this->court_type,
			'notification_date'     => $this->notification_date,
			'case_types'            => json_decode($this->case_types),
			'hearing_types'         => json_decode($this->hearing_types),
			'hearing_date'          => $this->hearing_date,
			'document_date'         => $this->document_date,
			'court_suburbs'         => json_decode($this->court_suburbs),
			'party_representatives' => json_decode($this->party_representatives),
			'per_page'              => $this->per_page,
		];

		return http_build_query(array_filter($fields));
	}

}
