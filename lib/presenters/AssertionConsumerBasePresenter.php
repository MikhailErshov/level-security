<?php

namespace eyelevel\security\presenters;

use App;
use Nette;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;

use eyelevel\security\IdentitySerializerInterface;

abstract class AssertionConsumerBasePresenter extends UnsecuredBasePresenter
{
	/** @var IdentitySerializerInterface */
	protected $identitySerializer;

	public function actionConsume()
	{
		$request = $this->getRequest();

		if (!$request->isMethod('POST')) {
			$this->redirectToLogin();
		}

		$postData = $request->getPost();
		$jwt = $postData['identity'];
		try {
			$identity = $this->identitySerializer->deserialize($jwt);
			$this->user->login($identity);
		} catch (Nette\InvalidArgumentException $e) {
			$this->redirectToLogin();
		}

		if (($url = $this->getSession('default')->redirectUrl) !== NULL) {
			if ($this->getLoginUrl !== $url) {
				$this->redirectUrl($url);
			}
		}

		$this->redirect($this->getDefaultActionUrl());
	}

	/**
	 * @param IdentitySerializerInterface $identitySerializer
	 * @throws \Exception
	 */
	public function injectIdentitySerializer(IdentitySerializerInterface $identitySerializer)
	{
		if ($this->identitySerializer !== NULL) {
			throw new \Exception('Identity serializer cannot be injected twice.');
		}

		$this->identitySerializer = $identitySerializer;
	}
}