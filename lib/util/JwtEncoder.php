<?php

namespace eyelevel\security\util;

use Nette\InvalidArgumentException;

class JwtEncoder
{
	public function encode($payload, $key, $algo = 'RS256')
	{
		$header = array('typ' => 'JWT', 'alg' => $algo);

		$segments = array(
			$this->urlsafeB64Encode(json_encode($header)),
			$this->urlsafeB64Encode(json_encode($payload))
		);

		$signing_input = implode('.', $segments);

		$signature = $this->sign($signing_input, $key, $algo);
		$segments[] = $this->urlsafeB64Encode($signature);

		return implode('.', $segments);
	}

	public function decode($jwt, $key = null)
	{
		if (!strpos($jwt, '.')) {
			throw new InvalidArgumentException("JWT format is invalid: invalid number of tokens (3 expected, 1 found)");
		}

		$tks = explode('.', $jwt);

		$tokenCount = count($tks);
		if ($tokenCount != 3) {
			throw new InvalidArgumentException("JWT format is invalid: invalid number of tokens (3 expected, $tokenCount found)");
		}

		list($headb64, $payloadb64, $cryptob64) = $tks;

		if (null === ($header = json_decode($this->urlsafeB64Decode($headb64), true))) {
			throw new InvalidArgumentException("JWT format is invalid: unable to decode header");
		}

		if (null === $payload = json_decode($this->urlsafeB64Decode($payloadb64), true)) {
			throw new InvalidArgumentException("JWT format is invalid: unable to decode payload");
		}

		$sig = $this->urlsafeB64Decode($cryptob64);

		if (!isset($header['alg'])) {
			throw new InvalidArgumentException("JWT format is invalid: 'alg' was not found in header");
		}

		if (!$this->verifySignature($sig, "$headb64.$payloadb64", $key, $header['alg'])) {
			throw new InvalidArgumentException("JWT format is invalid: signature is invalid");
		}

		return $payload;
	}

	private function verifySignature($signature, $input, $key, $algo = 'RS256')
	{
		switch ($algo) {
			case'HS256':
			case'HS384':
			case'HS512':
				return $this->sign($input, $key, $algo) === $signature;

			case 'RS256':
				return @openssl_verify($input, $signature, $key, OPENSSL_ALGO_SHA256) === 1;

			case 'RS384':
				return @openssl_verify($input, $signature, $key, OPENSSL_ALGO_SHA384) === 1;

			case 'RS512':
				return @openssl_verify($input, $signature, $key, OPENSSL_ALGO_SHA512) === 1;

			default:
				throw new InvalidArgumentException("Unsupported or invalid signing algorithm.");
		}
	}

	private function sign($input, $key, $algo = 'RS256')
	{
		switch ($algo) {
			case 'HS256':
				return hash_hmac('sha256', $input, $key, true);

			case 'HS384':
				return hash_hmac('sha384', $input, $key, true);

			case 'HS512':
				return hash_hmac('sha512', $input, $key, true);

			case 'RS256':
				return $this->generateRSASignature($input, $key, 'sha256');

			case 'RS384':
				return $this->generateRSASignature($input, $key, 'sha384');

			case 'RS512':
				return $this->generateRSASignature($input, $key, 'sha512');

			default:
				throw new InvalidArgumentException("Unsupported or invalid signing algorithm.");
		}
	}

	private function generateRSASignature($input, $key, $algo)
	{
		if (!openssl_sign($input, $signature, $key, $algo)) {
			throw new InvalidArgumentException("Unable to sign data.");
		}

		return $signature;
	}

	public function urlSafeB64Encode($data)
	{
		$b64 = base64_encode($data);
		$b64 = str_replace(array('+', '/', '\r', '\n', '='),
			array('-', '_'),
			$b64);

		return $b64;
	}

	public function urlSafeB64Decode($b64)
	{
		$b64 = str_replace(array('-', '_'),
			array('+', '/'),
			$b64);

		return base64_decode($b64);
	}
}