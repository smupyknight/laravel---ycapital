<?php
namespace App\ScrapeResults;

use DateTime;
use DateTimeZone;

class Document
{

	private $datetime = '';
	private $title = '';
	private $description = '';
	private $filed_by = '';

	public function setDateTime($value) { $this->datetime = $this->_normaliseDateTime($value); }
	public function setTitle($value) { $this->title = $value; }
	public function setDescription($value) { $this->description = $value; }
	public function setFiledBy($value) { $this->filed_by = $value; }

	public function getDateTime() { return $this->datetime; }
	public function getTitle() { return $this->title; }
	public function getDescription() { return $this->description; }
	public function getFiledBy() { return $this->filed_by; }

	public function asArray()
	{
		return [
			'datetime'    => $this->datetime,
			'title'       => $this->title,
			'description' => $this->description,
			'filed_by'    => $this->filed_by,
		];
	}

	private function _normaliseDateTime($value)
	{
		if (!$value instanceof DateTime) {
			return $value;
		}

		return $value->setTimeZone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
	}

}