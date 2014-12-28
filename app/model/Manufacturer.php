<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


/**
 * Users management.
 */
class Manufacturer extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('manufacturer')->get($id);
	}

	public function getNames()
	{
		$result = array();

		foreach($this->database->table('manufacturer')->select('id, name') as $value)
		{
			$result[$value->id] = $value->name;
		}

		return $result;
	}

}
