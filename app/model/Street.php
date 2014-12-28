<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class Street extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('street')->get($id);
	}

	public function getNames()
	{
		$result = array();

		foreach($this->database->table('street') as $key => $street)
		{
			$result[$key] = $street->name;
		}

		return $result;
	}

}
