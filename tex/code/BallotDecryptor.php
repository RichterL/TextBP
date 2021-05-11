/**
 * @throws DecryptionException
 */
private function decryptKey(string $encryptedKey): DecryptingKey
{
	static $decryptingKey;
	if ($decryptingKey === null) {
		$decryptingKey = $this->election->getPrivateEncryptionKey();
		if ($decryptingKey === null) {
			throw new InvalidStateException('Decrypting key has not been set yet.');
		}
	}

	try {
		$decrypted = $decryptingKey->decrypt(base64_decode($encryptedKey));
		['key' => $key, 'iv' => $iv] = json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
		return new DecryptingKey($key, $iv);
	} catch (\Exception $e) {
		throw new DecryptionException('Decrypting AES key failed');
	}
}

/**
 * @throws VerificationException
 */
private function verify(EncryptedBallot $ballot): void
{
	$hash = $this->hash($ballot->encryptedKey);
	if (bin2hex($hash) !== $ballot->hash) {
		throw new VerificationException('hash does not match the stored value');
	}
	$signature = $this->sign($hash);
	if (bin2hex($signature) !== $ballot->signature) {
		throw new VerificationException('signature does not match the stored value');
	}
}

private function hash(string $message): string
{
	static $sha;
	if ($sha === null) {
		$sha = new Hash('sha256');
	}
	return $sha->hash($message);
}

private function sign(string $message): string
{
	static $key;
	if ($key === null) {
		$key = $this->election->getPrivateSigningKey();
	}
	return $key->decrypt($message);
}