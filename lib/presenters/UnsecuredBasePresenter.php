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
		$storage = $this->getUser()->getStorage();
		if ($storage instanceof Nette\Http\UserStorage) {
			// If you have more projects in one virtual host than there is a problem with sessions
			$storage->setNamespace(dirname(APP_DIR . '/../'));
		}
	}

	protected function redirectToLogin($redirectUrl = NULL)
	{
		if ($redirectUrl === NULL) {
			$redirectUrl = $this->getHttpRequest()->getUrl()->getAbsoluteUrl();
		}
		$this->getSession('default')->$redirectUrl = $redirectUrl;

		$consumeUrl = $this->constructActionUrl($this->getSsoParameter('consumeAction'));

		$this->redirectUrl($this->getSsoParameter('loginUrl') . '?acsUrl=' . $consumeUrl);
	}

	protected function redirectToLogout($redirectUrl = NULL)
	{
		if ($redirectUrl === NULL) {
			$redirectUrl = $this->getDefaultActionUrl();
		}
		$this->getSession('default')->$redirectUrl = $redirectUrl;

		$this->redirectUrl($this->getSsoParameter('logoutUrl') . '?redirectTo=' . $redirectUrl);
	}

	protected function getDefaultActionUrl()
	{
		return $this->constructActionUrl($this->getSsoParameter('defaultAction'));
	}

	private function getSsoParameter($parameter)
	{
		return $this->context->getParameters()['sso'][$parameter];
	}

	private function constructActionUrl($fullActionLink)
	{
		list($module, $presenter, $action) = explode(':', $fullActionLink);

		$httpRequest = $this->getHttpRequest();

		$params = [
			Nette\Application\UI\Presenter::ACTION_KEY => $action
		];

		$refUrl = new Nette\Http\Url($httpRequest->getUrl());
		$refUrl->setPath($httpRequest->getUrl()->getScriptPath());

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