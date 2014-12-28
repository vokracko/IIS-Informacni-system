<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form,
	Nette\Diagnostics\Debugger;


/**
 * Homepage presenter.
 */
class UserPresenter extends BasePresenter
{
	protected $roles = array('správce','úředník','policista');

	private $id;

	public function startup()
	{
		parent::startup();
	}

	protected function createComponentInsertUserForm()
	{
		$form = new Form();
		$form->onValidate[] = array($this, 'validateInsertUserForm');

		$form->addSelect('city_id', 'Město')
					->setItems($this->city->getNames())
					->setRequired("%label je povinná položka");
					//->setDefault($this->user->city); //TODO

		$form->addText('first_name', "Křestní jméno")
					->setRequired("%label je povinná položka")
					->addRule(Form::PATTERN, 'Křestní jméno smí obsahovat pouze písmena', '^[a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ]+$');

		$form->addText('last_name', "Příjmení")
					->setRequired("%label je povinná položka")
					->addRule(Form::PATTERN, 'Příjmení smí obsahovat pouze písmena', '^[a-zA-ZáčďéěíňóřšťůúýžÁČĎÉĚÍŇÓŘŠŤŮÚÝŽ]+$');

		$form->addSelect('role', 'Role')->setItems($this->roles, false)
					->setRequired("%label je povinná položka")
					->setPrompt("Výběr role");

		$form->addText('username', 'Přihlašovací jméno')
					->setRequired("%label je povinná položka")
					->addRule(Form::PATTERN, 'Uživatelské jméno smí obsahovat pouze malá písmena a čísla', '^[a-z0-9]+$');

		$form->addPassword('password', 'Heslo')
					->setRequired("%label je povinná položka");

		$form->addPassword('retry', 'Heslo')
					->setRequired("%label je povinná položka");

		$form->addSubmit('insert', 'Vytvořit uživatele');

		return $form;
	}

	public function validateInsertUserForm($form)
	{
		$values = $form->getValues();

		if($values['password'] !== $values['retry'])
		{
			$form->addError("Zadaná hesla se neshodují");
			return;
		}

		unset($values['retry']);

		$row = $this->user->insert($values);


		$this->flashMessage('Uživatel vytvořen', 'success');
		$this->redirect('User:detail', $row->id);
	}

	protected function createComponentUpdateUserForm()
	{
		if(!$this->user->get($this->id))
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$defaults = $this->user->get($this->id)->toArray();
		$form = $this->createComponentInsertUserForm();
		$form->setDefaults($defaults);

		$form->onValidate = NULL;
		$form->onValidate[] = array($this, 'validateUpdateUserForm');
		$form->addSubmit('update', 'Aktualizovat údaje uživatele');

		unset($form['retry']);
		unset($form['insert']);
		unset($form['id']);

		return $form;
	}

	public function validateUpdateUserForm($form)
	{
		$values = $form->getValues();

		try
		{
			$this->user->get($this->id)->update($values);
		}
		catch(Exception $e)
		{
			throw new \Nette\Application\ForbiddenRequestException;
		}

		$this->flashMessage("Údaje uživatele {$this->id} aktualizovány", 'success');
		$user_id = $this->id;
		$this->id = NULL;
		$this->redirect('User:detail', $user_id);
	}



	public function renderUpdate($id)
	{
		$this->template->id = $id;
	}

	public function actionUpdate($id)
	{
		$this->id = $id;
	}

	public function renderLogin()
	{
			$this->template->username = NULL;

	}

	public function actionDelete($id)
	{
		$user = $this->user->get($id);

		if($user)
		{
			$this->flashMessage("Uživatel {$id} smazán", 'success');
			$user->delete();
		}
		else
		{
			$this->flashMessage("Uživatel {$id} neexistuje", 'error');
		}

		$this->redirect('User:default');
	}

	public function renderDetail($id)
	{
		$this->template->user = $this->getDetails($this->user->get($id));
	}

	public function renderDefault()
	{
		$this->template->users = array();

		$all = $this->user->getAll();

		foreach($all as $user)
		{
			$this->template->users[] = $this->getDetails($user);
		}
	}

	public function renderSearch($query)
	{
		$this->template->users = array();

		$all = $this->user->search($query);

		foreach($all as $user)
		{
			$this->template->users[] = $this->getDetails($user);
		}
	}


	private function getDetails($user)
	{
		$userArr = $user->toArray();
		$userArr['city'] = $this->city->get($user->city_id)->name;
		return $userArr;
	}


	protected function createComponentSearchForm()
	{
		return parent::searchForm($this);
	}

	public function validateSearchForm($form)
	{
		$values = $form->getValues();
		$this->redirect('User:search', $values['query']);
	}

	protected function createComponentLoginForm()
	{
		$form = new Form;

		$form->addText('username', 'Přihlašovací jméno')
					->setRequired('Uživatelské jméno musí být zadáno');

		$form->addPassword('password', 'Password:')
					->setRequired('Heslo musí být zadáno');

		$form->addSubmit('login', 'Přihlásit');
		$form->onSuccess[] = $this->loginFormSucceeded;

		return $form;
	}
	public function loginFormSucceeded($form)
	{
		$values = $form->getValues();

		try
		{
			$this->getUser()->login($values['username'],$values['password']);
			$this->getUser()->setExpiration(new \DateTime("+30min"));
			$this->redirect('Offence:default');
		}
		catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}
	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhlášení proběhlo úspěšně');
		$this->redirect('User:login');
	}

	public function createComponentChangePasswordForm()
	{
		$form = new Form;

		$form->addPassword('password', 'Nové heslo')
					->setRequired("%label je povinná položka");

		$form->addPassword('retry', 'Nové heslo znovu')
					->setRequired("%label je povinná položka");

		$form->addSubmit('change', 'Změnit heslo');

		$form->onValidate[] = array($this, 'validateChangePasswordForm');

		return $form;
	}

	public function validateChangePasswordForm($form)
	{
		$values = $form->getValues();

		if($values['password'] !== $values['retry'])
		{
			$form->addError("Zadaná hesla se neshodují");
			return;
		}

		$this->user->changePassword($this->getUser()->getId(), $values['password']);
		$this->flashMessage('Heslo úspěšně změněno', 'success');
		$this->getUser()->logout();
		$this->redirect('User:login');


	}
}
