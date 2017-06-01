<?php
namespace App\ScrapeResults;

use DateTime;
use DateTimeZone;
use App\ScrapeResults\Document;
use App\ScrapeResults\Hearing;
use App\ScrapeResults\Party;

class Application
{

	private $title = '';
	private $type = '';
	private $date_filed = '';
	private $status = '';
	private $date_finalised = '';

	private $documents = [];
	private $hearings = [];
	private $parties = [];

	public function setTitle($value) { $this->title = $value; }
	public function setType($value) { $this->type = $value; }
	public function setDateFiled($value) { $this->date_filed = $this->_normaliseDateTime($value); }
	public function setStatus($value) { $this->status = $value; }
	public function setDateFinalised($value) { $this->date_finalised = $this->_normaliseDateTime($value); }
	public function addDocument(Document $doc) { $this->documents[] = $doc; }
	public function addHearing(Hearing $hearing) { $this->hearings[] = $hearing; }

	public function addParty(Party $party) {
		$party->determineAbn();
		$party->determineAcn();
		$this->parties[] = $party;
	}

	public function getTitle() { return $this->title; }
	public function getType() { return $this->type; }
	public function getDateFiled() { return $this->date_filed; }
	public function getStatus() { return $this->status; }
	public function getDateFinalised() { return $this->date_finalised; }
	public function getDocuments() { return $this->documents; }
	public function getHearings() { return $this->hearings; }
	public function getParties() { return $this->parties; }

	public function asArray()
	{
		$documents = [];
		$hearings = [];
		$parties = [];

		foreach ($this->documents as $document) {
			$documents[] = $document->asArray();
		}

		foreach ($this->hearings as $hearing) {
			$hearings[] = $hearing->asArray();
		}

		foreach ($this->parties as $party) {
			$parties[] = $party->asArray();
		}

		return [
			'title'          => $this->title,
			'type'           => $this->type,
			'date_filed'     => $this->date_filed,
			'status'         => $this->status,
			'date_finalised' => $this->date_finalised,
			'documents'      => $documents,
			'hearings'       => $hearings,
			'parties'        => $parties,
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
