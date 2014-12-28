<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI\Form,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	* @inject
	* @var Model\Manufacturer
	*/
	public $manufacturer;

	/**
	* @inject
	* @var Model\Vehicle
	*/
	public $vehicle;

	/**
	* @inject
	* @var Model\License
	*/
	public $license;

	/**
	* @inject
	* @var Model\Owner
	*/
	public $owner;

	/**
	* @inject
	* @var Model\Person
	*/
	public $person;

	/**
	* @inject
	* @var Model\Company
	*/
	public $company;

	/**
	* @inject
	* @var Model\Offence
	*/
	public $offence;

	/**
	* @inject
	* @var Model\City
	*/
	public $city;

	/**
	* @inject
	* @var Model\Street
	*/
	public $street;

	/**
	* @inject
	* @var Model\User
	*/
	public $user;

	const DATE = '^[0-9]{4}-[0-9]{2}-[0-9]{2}$';

	public function searchForm($presenter)
	{
		$form = new Form();
		$form->addText("query")->setRequired("Je nutné zadat vyhledávaný výraz");
		$form->addSubmit('search', 'Vyhledat');
		$form->onValidate[] = array($presenter, 'validateSearchForm');

		return $form;
	}

	public function startup()
	{
		parent::startup();

		// dd($this->getAction());
		// $this->terminate();

		if(!$this->getUser()->isLoggedIn() && $this->getName() != 'User' && $this->getAction() != 'login' && $this->getAction() != 'changePassword')
		{
			$this->redirect('User:login');
		}

		if($this->getUser()->isLoggedIn())
		{
			$role = $this->user->get($this->getUser()->getId())->role;
			$presenter = $this->getName();

			$this->template->presenter = $presenter;
			$this->template->role = $role;
			$this->template->username = $this->user->get($this->getUser()->getId())->username;
		}
		else
		{
			$this->template->presenter = NULL;
			$this->template->role = NULL;
			$this->template->username = NULL;
		}
	}

}
