<?php

namespace eyelevel\security\presenters;

use App;
use Nette;
use Nette\Application\IRouter;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

class UnsecuredBasePresenter extends Presenter
{
	/**
	 * @var Nette\Application\IRouter
	 */
	protected $router;

	protected function startup()
	{
		parent::startup();
		if ($this->user->storage instanceof Nette\Http\UserStorage) {
			// If you have more projects in one virtual host than there is a problem with sessions
			$this->getUser()->getStorage()->setNamespace(dirname(APP_DIR . '/../'));
		}
	}

	protected function redirectToLogin($redirectLink = NULL)
	{
		if ($redirectLink === NULL) {
			$redirectLink = $this->getHttpRequest()->getUrl()->getAbsoluteUrl();
		}
		$this->getSession('default')->redirectLinkUrl = $redirectLink;

		$loginUrl = $this->getLoginUrl();
		$consumeUrl = $this->constructActionUrl($this->getConsumeAction());

		$this->redirectUrl($loginUrl . '?acsUrl=' . $consumeUrl);
	}

	protected function getLoginUrl()
	{
		return $this->context->getParameters()['sso']['loginUrl'];
	}

	protected function getLogoutUrl()
	{
		return $this->context->getParameters()['sso']['logoutUrl'];
	}

	protected function getDefaultAction()
	{
		return $this->context->getParameters()['sso']['defaultAction'];
	}

	protected function getConsumeAction()
	{
		return $this->context->getParameters()['sso']['consumeAction'];
	}

	protected function constructActionUrl($fullActionLink)
	{
		list($module, $presenter, $action) = explode(':', $fullActionLink);

		$httpRequest = $this->getHttpRequest();

		$params = [
			Nette\Application\UI\Presenter::ACTION_KEY => $action
		];

		$refUrl = new Nette\Http\Url($httpRequest->getUrl());
		return $this->router->constructUrl(new Request($module . ':' . $presenter, Request::FORWARD, $params), $refUrl);
	}

	/**
	 * @param IRouter $router
	 * @throws \Exception
	 */
	public function injectRouter(IRouter $router)
	{
		if ($this->router !== NULL) {
			throw new \Exception('Router cannot be injected twice.');
		}

		$this->router = $router;
	}
}