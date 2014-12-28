<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI\Form,
	App\Model;

class CompanyPresenter extends BasePresenter
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
		$company = $this->company->get($id);

		if($company)
		{
			$this->flashMessage("Společnost {$id} smazána", 'success');
			$company->delete();
		}
		else
		{
			$this->flashMessage("Společnost {$id} neexistuje", 'error');
		}

		$this->redirect('Company:default');
	}

	public function renderDetail($id)
	{
		$this->template->company = $this->getDetails($this->company->get($id));
	}

	public function renderDefault()
	{
		$this->template->companies = array();

		$all = $this->company->getAll();

		foreach($all as $company)
		{
			$this->template->companies[] = $this->getDetails($company);
		}
	}

	public function renderSearch($query)
	{
		$this->template->companies = array();

		$all = $this->company->search($query);

		foreach($all as $company)
		{
			$this->template->companies[] = $this->getDetails($company);
		}
	}

	private function getDetails($company)
	{
		$companyArr = $company->toArray();
		// $personArr['owner'] = $this->person->licepersonnse($person->id);
		$companyArr['street'] = $this->street->get($company->street_id)->name;
		$companyArr['city'] = $this->city->get($company->city_id)->name;
		return $companyArr;
	}

	protected function createComponentInsertCompanyForm()
	{
		$form = new Form();
		$form->onValidate[] = array($this, 'validateInsertCompanyForm');

		$form->addText('id', 'IČ')
					->addRule(Form::PATTERN, 'Špatný formát', '^[0-9]+$')
					->setRequired("%label je povinná položka");

		$form->addText('name', 'jméno')
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

		$form->addSubmit('insert', 'Vložit společnost do systému');

		return $form;
	}

	public function validateInsertCompanyForm($form)
	{
		$values = $form->getValues();

		if (false) {
			$form->addError('Tato kombinace není možná.');
		}

		$this->company->insert($values);
		$this->flashMessage('Společnost přidána', 'success');
		$this->redirect('Company:detail', $values['id']);
	}

	protected function createComponentUpdateCompanyForm()
	{
		if(!$this->company->get($this->id))
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$defaults = $this->company->get($this->id)->toArray();
		$form = $this->createComponentInsertCompanyForm();
		$form->setDefaults($defaults);

		$form->onValidate = NULL;
		$form->onValidate[] = array($this, 'validateUpdateCompanyForm');
		$form->addSubmit('update', 'Aktualizovat údaje o společnosti');

		unset($form['insert']);
		unset($form['id']);

		return $form;
	}

	public function validateUpdateCompanyForm($form)
	{
		$values = $form->getValues();

		try
		{
			$this->company->get($this->id)->update($values);
		}
		catch(Exception $e)
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$this->flashMessage("Údaje společnosti s IČ {$this->id} aktualizovány", 'success');
		$company_id = $this->id;
		$this->id = NULL;
		$this->redirect('Company:detail', $company_id);
	}


	protected function createComponentSearchForm()
	{
		return parent::searchForm($this);
	}

	public function validateSearchForm($form)
	{
		$values = $form->getValues();
		$this->redirect('Company:search', $values['query']);
	}
}
