validateForm(event) {
	$('form input[type=submit]').attr('disabled', true)
	const { element, originalEvent } = event.detail;
	Nette.formErrors = [];
	for (let el of element.form.getElementsByTagName('input')) {
		if (el.dataset.netteRules !== undefined) {
			el.setCustomValidity(Nette.validateControl(el) ? '' : 'invalid')
		}
	}
	
	if (originalEvent) {
		originalEvent.stopImmediatePropagation();
		originalEvent.preventDefault();
	}
	event.preventDefault();

	if (Nette.formErrors.length) {
		$(element.form).addClass('was-validated')
		this.showErrors();
		toastr.error('There were errors in the form')
		$('form input[type=submit]').attr('disabled', false)
		return;
	}
	
	...
}