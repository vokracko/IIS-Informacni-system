<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class Offence extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function get($id)
	{
		return $this->database->table('offence')->get($id);
	}

	public function insert($values)
	{
		return $this->database->table('offence')->insert($values);
	}

	public function getAll()
	{
		return $this->database->table('offence')->limit(20);
	}

	public function search($keyword)
	{
		return $this->database->table('offence')->where('id LIKE ?', '%'.$keyword.'%');
	}

	public function vehicle($id)
	{
		$vehicle = $this->database->table('offence')->get($id)->ref('vehicle');

		return array('id' => $vehicle->id, 'spz' => $vehicle->spz);
	}

	public function person($id)
	{
		$person = $this->database->table('offence')->get($id)->ref('person');

		return array('id' => $person->id, 'name' => $person->first_name . " " . $person->last_name);
	}

	public function personOffences($id)
	{
		return $this->database->table('offence')->where('person_id = ?', $id);
	}

	public function vehicleOffences($id)
	{
		return $this->database->table('offence')->where('vehicle_id = ?', $id);
	}

	public function companyOffences($id)
	{
		$owner = $this->database->table('owner')->where('company_id = ?', $id)->fetch();
		$vehicles = $this->database->table('vehicle')->where('owner_id = ?', $owner->id);

		return $this->database->table('offence')->where('vehicle_id', $vehicles);
	}
}
