<?php

namespace App\Presenters;

use Nette,
	App\Model;

class OwnerPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
