public function processBallots(Election $election): array
{
	$this->election = $election;
	$decrypted = $this->ballotDecryptor->setElection($election)->decryptBallots();
	[$valid, $invalid, $errors] = $this->ballotValidator->setElection($election)->validateBallots();
		$this->counter = [
			'valid' => count($valid),
			'invalid' => count($invalid),
			'error' => count($errors),
		];
		$this->prepareCounter();
	$this->countResults($valid);
	return $this->counter;
}

private function prepareCounter()
{
	foreach ($this->election->getQuestions() as $question) {
		$this->counter[$question->getId()] =
			array_fill_keys(array_keys($question->getAnswers()->getIdValuePairs()), 0);
	}
}

/**
 * @var DecryptedBallot[] $ballots
 */
private function countResults(iterable $ballots)
{
	try {
		foreach ($ballots as $ballot) {
			$data = $ballot->unpackData();
			foreach ($data['questions'] as $questionId => $answers) {
				foreach ($answers as $answerId => $value) {
					$this->counter[$questionId][$answerId]++;
				}
			}
			$ballot->setCountedAt(new \DateTime())
				->setCountedBy(UserId::fromValue($this->user->getId()));
			$this->ballotRepository->save($ballot);
		}
	} catch (\JsonException $e) {
		$this->log($e, Logger::CRITICAL, $ballot);
	} catch (SavingErrorException $e) {
		$this->log($e, Logger::WARNING, $ballot);
	}
}