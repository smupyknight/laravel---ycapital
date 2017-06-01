<?php
namespace App\ScrapeResults;

class Order
{

	private $type = '';
	private $decision_type = '';

	public function setType($value) { $this->type = $value; }
	public function setDecisionType($value) { $this->decision_type = $value; }

	public function getType() { return $this->type; }
	public function getDecisionType() { return $this->decision_type; }

	public function asArray()
	{
		return [
			'type'          => $this->type,
			'decision_type' => $this->decision_type,
		];
	}

}