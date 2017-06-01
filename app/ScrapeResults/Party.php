<?php
namespace App\ScrapeResults;

class Party
{

	private $id = '';
	private $name = '';
	private $given_names = '';
	private $last_name = '';
	private $type = 'Other';
	private $role = '';
	private $rep_name = '';
	private $address = '';
	private $phone = '';
	private $fax = '';
	private $abn = '';
	private $acn = '';

	public function setId($value)
	{
		$this->id = $value;
	}

	public function setName($value)
	{
		$this->name = $value;
	}

	public function setIndividualNames($given_names, $last_name)
	{
		$this->name = $given_names . ' ' . $last_name;
		$this->given_names = $given_names;
		$this->last_name = $last_name;
		$this->type = 'Individual';
	}

	public function setCompanyName($value)
	{
		$this->name = $value;
		$this->type = 'Company';
	}

	public function setType($value)
	{
		$this->type = $value;
	}

	public function setRole($value)
	{
		$this->role = $value;
	}

	public function setRepName($value)
	{
		$this->rep_name = $value;
	}

	public function setAddress($value)
	{
		$this->address = $value;
	}

	public function setPhone($value)
	{
		$this->phone = $value;
	}

	public function setFax($value)
	{
		$this->fax = $value;
	}

	public function setAbn($value)
	{
		$value = preg_replace('/[^\d]+/', '', $value);

		if (strlen($value) != 11) {
			return;
		}

		$this->abn = $value;

		$this->setAcn(substr($value, 2));
	}

	public function setAcn($value)
	{
		$value = preg_replace('/[^\d]+/', '', $value);

		if (strlen($value) != 9) {
			return;
		}

		$this->acn = $value;
	}

	public function getId() { return $this->id; }
	public function getName() { return $this->name; }
	public function getType() { return $this->type; }
	public function getRole() { return $this->role; }
	public function getRepName() { return $this->rep_name; }
	public function getAddress() { return $this->address; }
	public function getPhone() { return $this->phone; }
	public function getFax() { return $this->fax; }
	public function getAbn() { return $this->abn; }
	public function getAcn() { return $this->acn; }

	public function determineAbn()
	{
		if (preg_match('/ABN\s*([0-9\s]+)/i', $this->getName(), $match)) {
			$this->setAbn(str_replace(' ', '', $match[1]));
		}
	}

	public function determineAcn()
	{
		if (preg_match('/ACN\s*([0-9\s]+)/i', $this->getName(), $match)) {
			$this->setAcn(str_replace(' ', '', $match[1]));
		}
	}

	public function asArray()
	{
		return [
			'id'          => $this->id,
			'name'        => $this->name,
			'given_names' => $this->given_names,
			'last_name'   => $this->last_name,
			'type'        => $this->type,
			'role'        => $this->role,
			'rep_name'    => $this->rep_name,
			'address'     => $this->address,
			'phone'       => $this->phone,
			'fax'         => $this->fax,
			'abn'         => $this->abn,
			'acn'         => $this->acn,
		];
	}

}
