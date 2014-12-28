<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class Person extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('person')->get($id);
	}

	public function getNames()
	{
		$result = array();

		foreach($this->database->table('person') as $key => $person)
		{
			$result[$key] = $person->id . ' ' . $person->first_name . ' ' . $person->last_name;
		}

		return $result;
	}

	public function license($id)
	{
		$person = $this->database->table('person')->select('*')->where('license_id = ?', $id)->fetch();

		if(!$person) return null;
		return array('id' => $person->id, 'name' => $person->first_name . " " . $person->last_name);
	}

	public function insert($values)
	{
		return $this->database->table('person')->insert($values);
	}

	public function getAll()
	{
		return $this->database->table('person')->limit(20);
	}

	public function search($keyword)
	{
		return $this->database->table('person')->where('id LIKE ?', '%'.$keyword.'%');
	}

	public function vehicleOffences($id)
	{
		$persons = $this->database->table('offence')->select('person_id')->where('vehicle_id = ?', $id);

		dd($id);

		return $this->database->table('person')->where('id', $persons);
	}

	public function companyOffences($id)
	{
		$owner = $this->database->table('owner')->select('id')->where('company_id = ?', $id)->fetch();
		$vehicles = $this->database->table('vehicle')->select('id')->where('owner_id = ?', $owner->id);
		$persons = $this->database->table('offence')->select('person_id')->where('vehicle_id', $vehicles);

		return $this->database->table('person')->where('id', $persons);
	}
}
