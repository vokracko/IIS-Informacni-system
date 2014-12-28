<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form,
	Nette\Diagnostics\Debugger;


/**
 * Homepage presenter.
 */
class LicensePresenter extends BasePresenter
{
	protected $permissions = array('a','a1','b','b1','c','c1','d','d1','be','c1e','ce','de','d1e','t','am');

	private $id;

	protected function createComponentInsertLicenseForm()
	{
		$form = new Form();
		$form->onValidate[] = array($this, 'validateInsertLicenseForm');

		$_5years = new \DateTime("+5years");
		$form->addText('expiration', 'Expirace (YYYY-MM-DD)')
					->setDefaultValue($_5years->format("Y-m-d"))
					->addRule(Form::PATTERN, 'Špatný formát', '^[0-9]{4}-[0-9]{2}-[0-9]{2}$')
					->setRequired("%label je povinná položka");

		$form->addSelect('city_id', 'Město vydání')
					->setItems($this->city->getNames())
					->setRequired("%label je povinná položka");
					//->setDefault($this->user->city); //TODO

		$form->addCheckbox('a', 'A');
		$form->addCheckbox('a1', 'A1');
		$form->addCheckbox('am', 'AM');
		$form->addCheckbox('b', 'B');
		$form->addCheckbox('b1', 'B1');
		$form->addCheckbox('c', 'C');
		$form->addCheckbox('c1', 'C1');
		$form->addCheckbox('d', 'D');
		$form->addCheckbox('d1', 'D1');
		$form->addCheckbox('be', 'BE');
		$form->addCheckbox('c1e', 'C1E');
		$form->addCheckbox('ce', 'CE');
		$form->addCheckbox('de', 'DE');
		$form->addCheckbox('d1e', 'D1E');
		$form->addCheckbox('t', 'T');
		$form->addSubmit('insert', 'Vytvořit řidičský průkaz');

		return $form;
	}

	public function validateInsertLicenseForm($form)
	{
		$values = $form->getValues();

		if (false) {
			$form->addError('Tato kombinace není možná.');
		}
		$today = new \DateTime;
		$values['created'] = $today->format('Y-m-d');
		$row = $this->license->insert($values);


		$this->flashMessage('Ričiský průkaz přidán', 'success');
		$this->redirect('License:detail', $row->id);
	}

	protected function createComponentUpdateLicenseForm()
	{
		if(!$this->license->get($this->id))
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$defaults = $this->license->get($this->id)->toArray();
		$form = $this->createComponentInsertLicenseForm();
		$form->setDefaults($defaults);

		$form->onValidate = NULL;
		$form->onValidate[] = array($this, 'validateUpdateLicenseForm');
		$form->addSubmit('update', 'Aktualizovat údaje přidičského průkazu');

		unset($form['insert']);
		unset($form['id']);

		return $form;
	}

	public function validateUpdateLicenseForm($form)
	{
		$values = $form->getValues();

		try
		{
			$this->license->get($this->id)->update($values);
		}
		catch(Exception $e)
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$this->flashMessage("Údaje řidičského průkazu {$this->id} aktualizovány", 'success');
		$license_id = $this->id;
		$this->id = NULL;
		$this->redirect('License:detail', $license_id);
	}



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
		$license = $this->license->get($id);

		if($license)
		{
			$this->flashMessage("Řidičský průkaz {$id} smazán", 'success');
			$license->delete();
		}
		else
		{
			$this->flashMessage("Ričiský průkaz {$id} neexistuje", 'error');
		}

		$this->redirect('License:default');
	}

	public function renderDetail($id)
	{
		$this->template->license = $this->getDetails($this->license->get($id));
	}

	public function renderDefault()
	{
		$this->template->licenses = array();

		$all = $this->license->getAll();

		foreach($all as $license)
		{
			$this->template->licenses[] = $this->getDetails($license);
		}
	}

	public function renderSearch($query)
	{
		$this->template->licenses = array();

		$all = $this->license->search($query);

		foreach($all as $license)
		{
			$this->template->licenses[] = $this->getDetails($license);
		}
	}


	private function getDetails($license)
	{
		$licenseArr = $license->toArray();
		$licenseArr['owner'] = $this->person->license($license->id);
		$licenseArr['city'] = $this->city->get($license->city_id)->name;
		return $licenseArr;
	}


	protected function createComponentSearchForm()
	{
		return parent::searchForm($this);
	}

	public function validateSearchForm($form)
	{
		$values = $form->getValues();
		$this->redirect('License:search', $values['query']);
	}

}
