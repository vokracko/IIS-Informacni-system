<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class City extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('city')->get($id);
	}

	public function getNames()
	{
		$result = array();

		foreach($this->database->table('city') as $key => $city)
		{
			$result[$key] = $city->name;
		}

		return $result;
	}

}
