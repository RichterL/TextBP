abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	public function checkRequirements($element): void
	{
		...
		
		if ($element instanceof Nette\Application\UI\MethodReflection && $element->hasAnnotation('restricted')) {
			$resource = $element->getAnnotation('resource');
			$privilege = $element->getAnnotation('privilege');
			if (!$user->isAllowed($resource, $privilege)) {
				$this->flashMessage('You do not have permission to do that', 'warning');
				if (!$user->isAllowed($resource, 'view')) {
					$this->redirect('Homepage:');
				}
				if ($this->isAjax()) {
					$this->forward('this');
				} else {
					$this->forward(':default');
				}
			}
		}
	}
	
	...
}