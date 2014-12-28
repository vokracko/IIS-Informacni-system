<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class License extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('license')->get($id);
	}

	public function getAll()
	{
		return $this->database->table('license')->limit(20);
	}

	public function search($keyword)
	{
		return $this->database->table('license')->where('id LIKE ?', '%'.$keyword.'%');
	}

	public function insert($values)
	{
		return $this->database->table('license')->insert($values);
	}

	public function getNames()
	{
		$result = array();

		foreach($this->database->table('license') as $license)
		{
			$result[$license->id] = $license->id;
		}

		return $result;
	}
}
