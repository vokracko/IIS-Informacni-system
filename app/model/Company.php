<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class Company extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('company')->get($id);
	}

	public function getNames()
	{
		$result = array();

		foreach($this->database->table('company') as $key => $company)
		{
			$result[$key] = $company->id . ' ' . $company->name;
		}

		return $result;
	}

	public function insert($values)
	{
		return $this->database->table('company')->insert($values);
	}

	public function getAll()
	{
		return $this->database->table('company')->limit(20);
	}

	public function search($keyword)
	{
		return $this->database->table('company')->where('id LIKE ?', '%'.$keyword.'%');
	}

}
