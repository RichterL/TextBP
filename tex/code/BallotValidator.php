/** @param DecryptedBallot[] $ballots */
private function validate(array $ballots): array
{
	$valid = $invalid = $error = [];
	foreach ($ballots as $ballot) {
		try {
			$data = $ballot->unpackData();
			$this->checkElection((int) $data['electionId']);
			$this->checkQuestions($data['questions']);
			$valid[] = $ballot;
		} catch (\JsonException $e) {
			$error[] = $ballot;
			$this->log($e, Logger::CRITICAL, $ballot);
		} catch (ValidationException $e) {
			$invalid[] = $ballot;
			$this->log($e, Logger::WARNING, $ballot);
		}
	}
	return [$valid, $invalid, $error];
}

/** @return Question[] */
private function getQuestions(): array
{
	static $questions;
	if ($questions === null) {
		foreach ($this->election->getQuestions() as $question) {
			$questions[$question->getId()] = $question;
		}
	}
	return $questions;
}

private function getAnswers(int $questionId): array
{
	static $answers;
	if ($answers === null) {
		foreach ($this->getQuestions() as $qId => $question) {
			$answers[$qId] = $question->getAnswers()->getIdValuePairs();
		}
	}
	return $answers[$questionId];
}

/**
 * @throws ValidationException
 */
private function checkElection(int $value): void
{
	static $electionId;
	if ($electionId === null) {
		$electionId = $this->election->getId();
	}
	if ($electionId !== $value) {
		throw new ValidationException('Wrong election id');
	}
}

/**
 * @throws ValidationException
 */
private function checkQuestions(array $tested): void
{
	foreach ($this->getQuestions() as $qId => $question) {
		if (empty($tested[$qId]) && !$question->required) {
			continue;
		}
		if ($question->required && empty($tested[$qId])) {
			throw new ValidationException('missing required question');
		}
		$answerCount = count($tested[$qId]);
		if (($question->getMin() > $answerCount) || ($question->getMax() < $answerCount)) {
			throw new ValidationException('Wrong number of answers');
		}
		$this->checkAnswers($qId, $tested[$qId]);

	}
}

/**
 * @throws ValidationException
 */
private function checkAnswers(int $questionId, array $tested): void
{
	$answers = $this->getAnswers($questionId);
	foreach ($tested as $key => $value) {
		if (!array_key_exists($key, $answers) || $answers[$key] !== $value) {
			throw new ValidationException('answer does not match');
		}
	}
}