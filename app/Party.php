<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{

	public function getCalculatedSearchableName()
	{
		if ($this->type == 'Individual') {
			return $this->processIndividual();
		}

		return $this->processUnknown();
	}

	private function processUnknown()
	{
		$stopwords = ['and','co','for','inc','limited','ltd','mr','mrs','of','pty'];

		$words = explode(' ', strtolower($this->name));
		$words = array_filter($words, function($word) use ($stopwords) {
			return !in_array($word, $stopwords);
		});

		return ' ' . implode(' ', $words) . ' ';
	}

	private function processIndividual()
	{
		$given_names = $this->given_names;
		$given_names = strtolower($given_names);
		$given_names = str_replace(",", '', $given_names);
		$given_names = str_replace("'", '', $given_names);

		$stopwords = ['mr', 'mrs', 'ms', 'miss'];

		$words = explode(' ', strtolower($given_names));
		$words = array_filter($words, function($word) use ($stopwords) {
			return !in_array($word, $stopwords);
		});

		return ' ' . implode(' ', $words) . ' ';
	}

	public function determineType()
	{
		if ($this->acn || $this->role == 'Company' || $this->role == 'Ship') {
			return 'Company';
		}

		if ($this->name == 'R') {
			return 'Other';
		}

		$roles = ['Liquidator', 'Barrister', 'Judgment Action Officer', 'Prosecutor', 'Administrator', 'Commissioner', 'Executor/Administrator', 'Tribunal'];
		if (in_array($this->role, $roles)) {
			return 'Other';
		}

		$name = strtolower($this->name);
		$name = str_replace("'", '', $name);

		preg_match_all('/[a-z0-9]+/', $name, $matches);
		$words = $matches[0];

		if (strpos($name, 'australian taxation office') !== false) return 'ATO';
		if (strpos($name, 'deputy commissioner of taxation') !== false) return 'ATO';
		if (strpos($name, 'strata plan') !== false) return 'Company';
		if (strpos($name, 'trading as') !== false) return 'Company';
		if (strpos($name, 't/a') !== false) return 'Company';
		if (strpos($name, 't/as') !== false) return 'Company';
		if (strpos($name, 'trading/a') !== false) return 'Company';
		if (strpos($name, 'p/l') !== false) return 'Company';

		$ato_terms = ['ato', 'australian taxation office', 'deputy commissioner of taxation'];

		$company_terms = ['co','inc','limited','ltd','pty', 'trust', 'qld', 'nsw', 'vic', 'tas', 'wa', 'act', 'nt', 'acn', 'council', 'services', 'sa', 'partnership', 'company', 'community', 'district', 'diocese'];

		foreach ($words as $word) {
			if (in_array($word, $ato_terms)) {
				return 'ATO';
			}
			if (in_array($word, $company_terms)) {
				return 'Company';
			}
			if ($word == 'asic') {
				return 'ASIC';
			}
			if ($word == 'accc') {
				return 'ACCC';
			}
		}

		return 'Individual';
	}

}
