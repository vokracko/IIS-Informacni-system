<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI\Form,
	App\Model;


/**
 * Homepage presenter.
 */
class OffencePresenter extends BasePresenter
{
	private $types = array('parkování','rychlost','jízda na červenou','nezastavení na stopce');
	private $states = array('čekající','v jednání','projednáno','zaplaceno','stornováno');

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
		$offence = $this->offence->get($id);

		if($offence)
		{
			$this->flashMessage("Přestupek {$id} smazán", 'success');
			$offence->delete();
		}
		else
		{
			$this->flashMessage("Přestupek {$id} neexistuje", 'error');
		}

		$this->redirect('Offence:default');
	}

	public function renderDetail($id)
	{
		$this->template->offence = $this->getDetails($this->offence->get($id));
	}

	public function renderDefault()
	{
		$this->template->offences = array();

		$all = $this->offence->getAll();

		foreach($all as $offence)
		{
			$this->template->offences[] = $this->getDetails($offence);
		}
	}

	public function renderSearch($query)
	{
		$this->template->offences = array();

		$all = $this->offence->search($query);

		foreach($all as $offence)
		{
			$this->template->offences[] = $this->getDetails($offence);
		}
	}

	public function renderVehicleOffences($id)
	{
		$this->template->offences = array();
		$all = $this->offence->vehicleOffences($id);

		foreach($all as $offence)
		{
			$this->template->offences[] = $this->getDetails($offence);
		}

		$this->template->vehicle = $this->vehicle->get($id);
	}

	public function renderPersonOffences($id)
	{
		$this->template->offences = array();
		$all = $this->offence->personOffences($id);

		foreach($all as $offence)
		{
			$this->template->offences[] = $this->getDetails($offence);
		}

		$this->template->person = $this->person->get($id);
	}

	public function renderCompanyOffences($id)
	{
		$this->template->offences = array();
		$all = $this->offence->companyOffences($id);

		foreach($all as $offence)
		{
			$this->template->offences[] = $this->getDetails($offence);
		}

		$this->template->company = $this->company->get($id);
	}

	private function getDetails($offence)
	{
		$offenceArr = $offence->toArray();
		$offenceArr['person'] = $this->offence->person($offence->id);
		$offenceArr['vehicle'] = $this->offence->vehicle($offence->id);
		return $offenceArr;
	}

	protected function createComponentInsertOffenceForm()
	{
		$form = new Form();
		$form->onValidate[] = array($this, 'validateInsertOffenceForm');

		$form->addSelect('person_id', 'Osoba')
					->setRequired("%label je povinná položka")
					->setPrompt('Vyber osobu')
					->setItems($this->person->getNames());

		$form->addSelect('vehicle_id', 'Vozidlo')
					->setRequired("%label je povinná položka")
					->setPrompt('Vyber vozidlo')
					->setItems($this->vehicle->getNames());

		$form->addSelect('type', 'Typ přestupku')
					->setRequired("%label je povinná položka")
					->setItems($this->types, false);

		$form->addSelect('proceed_state', 'Stav přestupku')
					->setRequired("%label je povinná položka")
					->setItems($this->states, false);

		$today = new \DateTime();

		$form->addText('date', 'Datum spáchání')
					->setOption('description', "YYYY-MM-DD")
					->addRule(Form::PATTERN, 'Špatný formát', BasePresenter::DATE)
					->setRequired("%label je povinná položka")
					->setDefaultValue($today->format("Y-m-d"));

		$form->addText('maturity', 'Datum splatnosti')
						->addCondition(Form::FILLED)
						->addRule(Form::PATTERN, 'Špatný formát', BasePresenter::DATE);

		$form->addText('points', 'Počet trestných bodů')
					->addRule(Form::PATTERN, 'Lze zadávat pouze čísla', '^[0-9]+$')
					->setRequired("%label je povinná položka")
					->setDefaultValue(0);

		$form->addText('penalty', 'Výše pokuty')
					->addRule(Form::PATTERN, 'Lze zadávat pouze čísla', '^[0-9]+$')
					->setDefaultValue(0);

		$form->addSubmit('insert', 'Vložit přestupek do systému');

		return $form;
	}

	public function validateInsertOffenceForm($form)
	{
		$values = $form->getValues();

		if (false) {
			$form->addError('Tato kombinace není možná.');
		}

		if(empty($values['maturity']))
		{
			$values['maturity'] = NULL;
		}

		if(empty($values['proceed_date']))
		{
			$values['proceed_date'] = NULL;
		}

		if($values['proceed_state'] == 'projednáno')
		{
			$today = new \DateTime();
			$values['proceed_date'] = $today->format("Y-m-d");
		}

		if($values['proceed_state'] == 'zaplaceno' || $values['proceed_state'] == 'projednáno')
		{
			$this->person->get($values['person_id'])->update(array('points' => new Nette\Database\SqlLiteral('points+'.$values['points'])));
		}

		$row = $this->offence->insert($values);
		$this->flashMessage('Přestupek přidán', 'success');
		$this->redirect('Offence:detail', $row->id);
	}

	protected function createComponentUpdateOffenceForm()
	{
		if(!$this->offence->get($this->id))
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$defaults = $this->offence->get($this->id)->toArray();
		$form = $this->createComponentInsertOffenceForm();
		$form->setDefaults($defaults);

		$form->onValidate = NULL;
		$form->onValidate[] = array($this, 'validateUpdateOffenceForm');
		$form->addSubmit('update', 'Aktualizovat údaje o přestupku');
		$today = new \DateTime();
		$form['date']->setDefaultValue($today->format('Y-m-d'));

		unset($form['insert']);
		unset($form['id']);

		return $form;
	}

	public function validateUpdateOffenceForm($form)
	{
		$values = $form->getValues();

		try
		{
			if(empty($values['maturity']))
			{
				$values['maturity'] = NULL;
			}

			if(empty($values['proceed_date']))
			{
				$values['proceed_date'] = NULL;
			}

			if($values['proceed_state'] == 'projednáno')
			{
				$today = new \DateTime();
				$values['proceed_date'] = $today->format("Y-m-d");
			}

			if($values['proceed_state'] == 'zaplaceno' || $values['proceed_state'] == 'projednáno')
			{
				$this->person->get($values['person_id'])->update(array('points' => new Nette\Database\SqlLiteral('points+'.$values['points'])));
			}

			$this->offence->get($this->id)->update($values);
		}
		catch(Exception $e)
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$this->flashMessage("Údaje o přestupku s jednacím číslem {$this->id} aktualizovány", 'success');
		$offence_id = $this->id;
		$this->id = NULL;
		$this->redirect('Offence:detail', $offence_id);
	}


	protected function createComponentSearchForm()
	{
		return parent::searchForm($this);
	}

	public function validateSearchForm($form)
	{
		$values = $form->getValues();
		$this->redirect('Offence:search', $values['query']);
	}
}
