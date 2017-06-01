<?php
namespace App\ScrapeResults;

use DateTime;
use DateTimeZone;
use App\ScrapeResults\Order;

class Hearing
{

	private $datetime = '';
	private $reason = '';
	private $officer = '';
	private $court_room = '';
	private $court_name = '';
	private $court_phone = '';
	private $court_address = '';
	private $court_suburb = '';
	private $type = '';
	private $list_no = '';
	private $orders_filename = '';
	private $outcome = '';

	private $orders = [];

	public function setDateTime($value) { $this->datetime = $this->_normaliseDateTime($value); }
	public function setReason($value) { $this->reason = $value; }
	public function setOfficer($value) { $this->officer = $value; }
	public function setCourtRoom($value) { $this->court_room = $value; }
	public function setCourtName($value) { $this->court_name = $value; }
	public function setCourtPhone($value) { $this->court_phone = $value; }
	public function setCourtAddress($value) { $this->court_address = $value; }
	public function setCourtSuburb($value) { $this->court_suburb = $value; }
	public function setType($value) { $this->type = $value; }
	public function setListNumber($value) { $this->list_no = $value; }
	public function setOrdersFilename($value) { $this->orders_filename = $value; }
	public function setOutcome($value) { $this->outcome = $value; }
	public function addOrder(Order $order) { $this->orders[] = $order; }

	public function getDateTime() { return $this->datetime; }
	public function getReason() { return $this->reason; }
	public function getOfficer() { return $this->officer; }
	public function getCourtRoom() { return $this->court_room; }
	public function getCourtName() { return $this->court_name; }
	public function getCourtPhone() { return $this->court_phone; }
	public function getCourtAddress() { return $this->court_address; }
	public function getCourtSuburb() { return $this->court_suburb; }
	public function getType() { return $this->type; }
	public function getListNumber() { return $this->list_no; }
	public function getOrdersFilename() { return $this->orders_filename; }
	public function getOutcome() { return $this->outcome; }
	public function getOrders() { return $this->orders; }

	public function asArray()
	{
		$orders = $this->orders;

		foreach ($orders as $index => $order) {
			$orders[$index] = $order->asArray();
		}

		return [
			'datetime'        => $this->datetime,
			'reason'          => $this->reason,
			'officer'         => $this->officer,
			'court_room'      => $this->court_room,
			'court_name'      => $this->court_name,
			'court_phone'     => $this->court_phone,
			'court_address'   => $this->court_address,
			'court_suburb'    => $this->court_suburb,
			'type'            => $this->type,
			'list_no'         => $this->list_no,
			'outcome'         => $this->outcome,
			'orders_filename' => $this->orders_filename,
			'orders'          => $orders,
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