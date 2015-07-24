<?php

namespace eyelevel\security\presenters;

use App;
use Nette;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

abstract class SecuredBasePresenter extends UnsecuredBasePresenter
{
	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->redirectToLogin();
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout(TRUE);
		$this->flashMessage('You have been signed out.');

		$this->redirectToLogin($this->getDefaultAction());
	}
}