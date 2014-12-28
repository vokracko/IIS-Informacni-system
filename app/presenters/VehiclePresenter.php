<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Application,
	Nette\Application\UI\Form,
	Nette\Diagnostics\Debugger;


/**
 * Homepage presenter.
 */
class VehiclePresenter extends BasePresenter
{
	protected $types = array('combi','sedan','hatchback','cabriolet','suv','mvp');
	protected $permissions = array('a','a1','b','b1','c','c1','d','d1','be','c1e','ce','de','d1e','t','am');

	private $id = NULL;

	public function renderUpdate($spz)
	{
		$this->template->spz = $spz;
	}

	public function actionUpdate($id)
	{
		$this->id = $id;
	}

	public function actionDelete($id)
	{
		$vehicle = $this->vehicle->get($id);

		if($vehicle)
		{
			$this->flashMessage("Vozidlo s SPZ $vehicle->spz smazáno", 'success');
			$vehicle->delete();
		}
		else
		{
			$this->flashMessage("Vozidlo s SPZ $vehicle->spz neexistuje", 'error');
		}

		$this->redirect('Vehicle:default');
	}

	public function renderDetail($id)
	{
		$this->template->vehicle = $this->getDetails($this->vehicle->get($id));
	}

	public function renderDefault()
	{
		$this->template->vehicles = array();

		$all = $this->vehicle->getAll();

		foreach($all as $vehicle)
		{
			$this->template->vehicles[] = $this->getDetails($vehicle);
		}
	}

	public function renderSearch($query)
	{
		$this->template->vehicles = array();

		$all = $this->vehicle->search($query);

		foreach($all as $vehicle)
		{
			$this->template->vehicles[] = $this->getDetails($vehicle);
		}
	}

	private function getDetails($vehicle)
	{
		$vehicleArr = $vehicle->toArray();
		$vehicleArr['owner'] = $this->owner->getName($vehicle->owner_id);
		$vehicleArr['manufacturer'] = $this->manufacturer->get($vehicle->manufacturer_id)->name;
		return $vehicleArr;
	}

	public function renderPersonOffences($id)
	{
		$this->template->vehicles = array();
		$all = $this->vehicle->personOffences($id);

		foreach($all as $vehicle)
		{
			$this->template->vehicles[] = $this->getDetails($vehicle);
		}

		$this->template->person = $this->person->get($id);
	}

	protected function createComponentInsertVehicleForm()
	{
		$form = new Form();
		$form->onValidate[] = array($this, 'validateInsertVehicleForm');

		$form->addText('spz', 'SPZ')
						->addRule(Form::PATTERN, '%label musí být ve formátu NLN-NNNN', '^[0-9][A-Z][0-9]-[0-9]{4}$')
						->setRequired();

		$form->addSelect('owner_id', 'Vlastník')
						->setItems($this->owner->getNames())
						->setRequired("%label je povinná položka")
						->setPrompt("vlastník");

		$form->addSelect('manufacturer_id', 'Výrobce')
						->setItems($this->manufacturer->getNames())
						->setRequired("%label je povinná položka")
						->setPrompt("výrobce");

		$form->addSelect('type', 'Typ vozidla')
						->setItems($this->types, false)
						->setRequired("%label je povinná položka")
						->setPrompt("typ");

		$form->addText('performance', 'Výkon (kW)')
						->addRule(Form::RANGE, "Výkon musí být kladné číslo", array(1, null))
						->setRequired("%label je povinná položka");

		$form->addText('seats', 'Počet míst')
						->addRule(Form::RANGE, "Počet míst musí být kladné číslo", array(1, null))
						->setRequired("%label je povinná položka");

		$form->addRadioList('permission', 'Potřebné řidičské oprávnění')
						->setItems($this->permissions, false)
						->setRequired("%label je povinná položka");

		$form->addText('manufactured', 'Vyrobeno')
						->addRule(Form::PATTERN, 'Špatný formát', BasePresenter::DATE)
						->setRequired("%label je povinná položka")
						->setOption('description', "YYYY-MM-DD");

		$form->addText('color', 'Barva')
					->addRule(Form::PATTERN, 'Ve formátu A-F{6}', '^[0-9a-fA-F]{6}$')
					->setRequired("%label je povinná položka")
					->setAttribute('class', 'color');

		$form->addSubmit('insert', 'Vložit vozidlo');


		return $form;
	}

	public function validateInsertVehicleForm($form)
	{
		$values = $form->getValues();

		if (false)
		{
			//TODO validace
			$form->addError('Tato kombinace není možná.');
		}

		$row = $this->vehicle->insert($values);
		$this->flashMessage('Vozidlo přidáno', 'success');
		$this->redirect('Vehicle:detail', $row->id);
	}

	protected function createComponentUpdateVehicleForm()
	{
		if(!$this->vehicle->get($this->id))
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$defaults = $this->vehicle->get($this->id)->toArray();
		$exploded = explode(' ', $defaults['manufactured']);
		$defaults['manufactured'] = $exploded[0];
		$form = $this->createComponentInsertVehicleForm();
		$form->setDefaults($defaults);

		$form->onValidate = NULL;
		$form->onValidate[] = array($this, 'validateUpdateVehicleForm');
		$form->addSubmit('update', 'Aktualizovat údaje vozidla');

		unset($form['insert']);
		unset($form['id']);

		return $form;
	}

	public function validateUpdateVehicleForm($form)
	{
		$values = $form->getValues();

		try
		{
			$this->vehicle->get($this->id)->update($values);
		}
		catch(Exception $e)
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$this->flashMessage("Údaje vozidla s SPZ {$this->vehicle->get($this->id)->spz} aktualizovány", 'success');
		$vehicle_id = $this->id;
		$this->id = NULL;
		$this->redirect('Vehicle:detail', $vehicle_id);
	}

	protected function createComponentSearchForm()
	{
		return parent::searchForm($this);
	}

	public function validateSearchForm($form)
	{
		$values = $form->getValues();
		$this->redirect('Vehicle:search', $values['query']);
	}

}
