<?php
namespace App\ScrapeResults;

use App\ScrapeResults\Application;

class Result
{

	private $unique_id = '';
	private $state = '';
	private $court_type = '';
	private $case_no = '';
	private $case_name = '';
	public  $case_type = '';
	private $suburb = '';
	public $jurisdiction = '';
	private $related_cases = '';
	private $url = '';
	private $applications = [];

	public function setUniqueId($value) { $this->unique_id = $value; }
	public function setState($value) { $this->state = $value; }
	public function setCourtType($value) { $this->court_type = $value; }
	public function setCaseNumber($value) { $this->case_no = $value; }
	public function setCaseName($value) { $this->case_name = $value; }
	public function setCaseType($value) { $this->case_type = $value; }
	public function setSuburb($value) { $this->suburb = $value; }
	public function setJurisdiction($value) { $this->jurisdiction = $value; }
	public function setRelatedCases($value) { $this->related_cases = $value; }
	public function setUrl($value) { $this->url = $value; }
	public function addApplication(Application $app) { $this->applications[] = $app; }

	public function getUniqueId() { return $this->unique_id; }
	public function getState() { return $this->state; }
	public function getCourtType() { return $this->court_type; }
	public function getCaseNumber() { return $this->case_no; }
	public function getCaseName() { return $this->case_name; }
	public function getCaseType() { return $this->case_type; }
	public function getSuburb() { return $this->suburb; }
	public function getJurisdiction() { return $this->jurisdiction; }
	public function getRelatedCases() { return $this->related_cases; }
	public function getUrl() { return $this->url; }
	public function getApplications() { return $this->applications; }

	public function asArray()
	{
		$applications = [];

		foreach ($this->applications as $application) {
			$applications[] = $application->asArray();
		}

		return [
			'unique_id'     => $this->unique_id,
			'state'         => $this->state,
			'court_type'    => $this->court_type,
			'case_no'       => $this->case_no,
			'case_name'     => $this->case_name,
			'case_type'     => $this->case_type,
			'suburb'        => $this->suburb,
			'jurisdiction'  => $this->jurisdiction,
			'related_cases' => $this->related_cases,
			'url'           => $this->url,
			'applications'  => $applications,
		];
	}

}