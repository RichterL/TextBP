public function signInFormSuccess(Form $form, \stdClass $values): void
{
	$user = $this->getUser();
	try {
		$user->setAuthenticator($this->passwordAuthenticator);
		$user->login($values->username, $values->password);
		$this->redirect('Homepage:');
	} catch (AuthenticationException $ex) {
		try {
			$user->setAuthenticator($this->ldapAuthenticator);
			$user->login($values->username, $values->password);
			$this->redirect('Homepage:');
		} catch (AuthenticationException $ex) {
			$form->addError('Username or password invalid');
		} catch (NoConnectionException $ex) {
			$form->addError('LDAP server not available');
		}
	}
}