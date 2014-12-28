<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI\Form,
	App\Model;

class PersonPresenter extends BasePresenter
{

	private $id;

	public function renderUpdate($id)
	{
		$this->template->id = $id;
	}

	public function actionUpdate($id)
	{
		$this->id = $id;
	}

	public function actionDelete($id)
	{
		$person = $this->person->get($id);

		if($person)
		{
			$this->flashMessage("Osoba {$id} smazána", 'success');
			$person->delete();
		}
		else
		{
			$this->flashMessage("Osoba {$id} neexistuje", 'error');
		}

		$this->redirect('Person:default');
	}

	public function renderVehicleOffences($id)
	{
		$this->template->persons = array();
		$all = $this->person->vehicleOffences($id);

		foreach($all as $person)
		{
			$this->template->persons[] = $this->getDetails($person);
		}

		$this->template->vehicle = $this->vehicle->get($id);
	}

	public function renderCompanyOffences($id)
	{
		$this->template->persons = array();
		$all = $this->person->companyOffences($id);

		foreach($all as $person)
		{
			$this->template->persons[] = $this->getDetails($person);
		}

		$this->template->company = $this->company->get($id);
	}

	public function renderDetail($id)
	{
		$this->template->person = $this->getDetails($this->person->get($id));
	}

	public function renderDefault()
	{
		$this->template->persons = array();

		$all = $this->person->getAll();

		foreach($all as $person)
		{
			$this->template->persons[] = $this->getDetails($person);
		}
	}

	public function renderSearch($query)
	{
		$this->template->persons = array();

		$all = $this->person->search($query);

		foreach($all as $person)
		{
			$this->template->persons[] = $this->getDetails($person);
		}
	}

	private function getDetails($person)
	{
		$personArr = $person->toArray();
		// $personArr['owner'] = $this->person->licepersonnse($person->id);
		$personArr['street'] = $this->street->get($person->street_id)->name;
		$personArr['city'] = $this->city->get($person->city_id)->name;
		return $personArr;
	}

	protected function createComponentInsertPersonForm()
	{
		$form = new Form();
		$form->onValidate[] = array($this, 'validateInsertPersonForm');

		$form->addText('id', 'Rodné číslo')
					->addRule(Form::PATTERN, 'Špatný formát', '^[0-9]{10}$')
					->setRequired("%label je povinná položka");

		$form->addText('first_name', 'Křestní jméno')
					->setRequired("%label je povinná položka");

		$form->addText('last_name', 'Příjmení')
					->setRequired("%label je povinná položka");

		$form->addSelect('city_id', 'Město')
					->setItems($this->city->getNames())
					->setRequired("%label je povinná položka");
					//->setDefault($this->user->city); //TODO

		$form->addSelect('street_id', 'Ulice')
					->setItems($this->street->getNames())
					->setRequired("%label je povinná položka");

		$form->addText('street_number', 'Číslo popisné')
					->addRule(Form::PATTERN, 'Lze zadávat pouze čísla', '^[0-9]+$')
					->setRequired("%label je povinná položka");

		$form->addSelect('license_id', 'Řidičský průkaz')
					->setItems($this->license->getNames())
					->setRequired("%label je povinná položka");

		$form->addSubmit('insert', 'Vložit osobu do systému');

		return $form;
	}

	public function validateInsertPersonForm($form)
	{
		$values = $form->getValues();

		if (false) {
			$form->addError('Tato kombinace není možná.');
		}

		$this->person->insert($values);
		$this->flashMessage('Osoba přidána', 'success');
		$this->redirect('Person:detail', $values['id']);
	}

	protected function createComponentUpdatePersonForm()
	{
		if(!$this->person->get($this->id))
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$defaults = $this->person->get($this->id)->toArray();
		$form = $this->createComponentInsertPersonForm();
		$form->setDefaults($defaults);

		$form->onValidate = NULL;
		$form->onValidate[] = array($this, 'validateUpdatePersonForm');

		$form->addText('points', 'Počet trestných bodů')
					->addRule(Form::PATTERN, 'Lze zadávat pouze čísla', '^[0-9]+$')
					->setRequired("%label je povinná položka")
					->setDefaultValue(0);

		$form->addSubmit('update', 'Aktualizovat údaje o osobě');


		unset($form['insert']);
		unset($form['id']);

		return $form;
	}

	public function validateUpdatePersonForm($form)
	{
		$values = $form->getValues();

		try
		{
			$this->person->get($this->id)->update($values);
		}
		catch(Exception $e)
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$this->flashMessage("Údaje osoby s rodným číslem {$this->id} aktualizovány", 'success');
		$person_id = $this->id;
		$this->id = NULL;
		$this->redirect('Person:detail', $person_id);
	}


	protected function createComponentSearchForm()
	{
		return parent::searchForm($this);
	}

	public function validateSearchForm($form)
	{
		$values = $form->getValues();
		$this->redirect('Person:search', $values['query']);
	}
}
