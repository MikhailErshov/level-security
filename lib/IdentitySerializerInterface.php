<?php

namespace eyelevel\security;

use Nette\Security\Identity;
use Nette\Security\IIdentity;

interface IdentitySerializerInterface
{
	/**
	 * @param IIdentity $identity
	 * @param $privateKey
	 * @return string
	 */
	public function serialize(IIdentity $identity, $privateKey);

	/**
	 * @param string $jwt
	 * @param string|null $publicKey
	 * @return Identity
	 */
	public function deserialize($jwt, $publicKey = NULL);
}