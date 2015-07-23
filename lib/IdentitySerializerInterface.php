<?php

namespace eyelevel\security;

use Nette\Security\Identity;

interface IdentitySerializerInterface
{
	/**
	 * @param Identity $identity
	 * @param $privateKey
	 * @return string
	 */
	public function serialize(Identity $identity, $privateKey);

	/**
	 * @param string $jwt
	 * @param string|null $publicKey
	 * @return Identity
	 */
	public function deserialize($jwt, $publicKey = NULL);
}