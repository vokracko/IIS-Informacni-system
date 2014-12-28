<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class Vehicle extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('vehicle')->get($id);
	}

	public function insert($values)
	{
		return $this->database->table('vehicle')->insert($values);
	}

	public function getAll()
	{
		return $this->database->table('vehicle')->limit(20);
	}

	public function search($keyword)
	{
		return $this->database->table('vehicle')->where('spz LIKE ?', '%'.$keyword.'%');
	}

	public function getNames()
	{
		$result = array();
		$vehicles = $this->database->table('vehicle')->select('id, spz');

		foreach($vehicles as $vehicle)
		{
			$result[$vehicle->id] = $vehicle->spz;
		}

		return $result;
	}

	public function personOffences($id)
	{
		$vehicles = $this->database->table('offence')->select('vehicle_id')->where('person_id = ?', $id);
		return $this->database->table('vehicle')->where('id', $vehicles);
	}
}
