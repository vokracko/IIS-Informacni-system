<?php

namespace App\Model;

use Nette,
	Nette\Utils\Strings,
	Nette\Security\Passwords;


class Owner extends Nette\Object
{
	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function get($id)
	{
		return $this->database->table('owner')->get($id);
	}

	public function getNames()
	{
		$result = array();
		$persons = array();
		$companies = array();

		foreach($this->database->table('owner') as $key => $value)
		{
			if($value['person_id'])
			{
				$person = $value->ref('person', 'person_id');
				$persons[$key] = $person->id . ' ' . $person->first_name . ' ' . $person->last_name;
			}
			else
			{
				$company = $value->ref('company', 'company_id');
				$companies[$key] = $company->id . ' ' . $company->name;
			}
		}

		return $persons + $companies;
	}

	public function getName($id)
	{
		$owner = $this->database->table('owner')->get($id);

		if($owner['person_id'])
		{
			$person = $owner->ref('person', 'person_id');
			return array('id' => $person->id, 'name' => $person->first_name . " " . $person->last_name, 'type' => 'person');
		}
		else
		{
			$company = $owner->ref('company', 'company_id');
			return array('id' => $company->id, 'name' => $company->name, 'type' => 'company');
		}
	}

}
