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
			$httpRequest =$this->getHttpRequest();
			$this->getSession('default')->redirectLinkUrl = $httpRequest->getUrl()->getAbsoluteUrl();

			$params = [
				Nette\Application\UI\Presenter::ACTION_KEY => 'consume'
			];

			$this->router = $this->context->getService('router');
			$refUrl = new Nette\Http\Url($httpRequest->getUrl());
			$url = $this->router->constructUrl(
				new Request('Frontend:AssertionConsumer', Nette\Application\Request::FORWARD, $params),
				$refUrl);

			$loginUrl = $this->context->getParameters()['sso']['loginUrl'];
			$this->redirectUrl($loginUrl . '?acsUrl=' . $url);
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout(TRUE);
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}
}