public function create(): Authorizator
{
	$authorizator = new Permission();
	foreach ($this->resourceRepository->findAll() as $resource) {
		$authorizator->addResource($resource->key, $resource->parent->key ?? null);
	}
	foreach ($this->roleRepository->findAll(true) as $role) {
		$authorizator->addRole($role->key, $role->parent->key ?? null);
		$rules = $role->rules->getByTypes();
		foreach ($rules as $type => $ruleResources) {
			foreach ($ruleResources as $ruleResource => $rulePrivileges) {
				$authorizator->$type($role->key, $ruleResource, $rulePrivileges);
			}
		}
	}

	// allow all resources and privileges for superAdmin
	$authorizator->allow('superAdmin');
	return $authorizator;
}
