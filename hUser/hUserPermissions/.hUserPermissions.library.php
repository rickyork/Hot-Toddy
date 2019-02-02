<?php
  class hUserPermissionsLibrary extends hPlugin { private $groups = array(); private $setInherit = 0; private $resourceId; private $resourceKey; private $resourceTable; private $resourcePrimaryKey; private $resourceNameColumn; private $resourceName; private $resourceOwner; public function saveForm($resourceId, $resourceKey, $owner, $world, array $users, array $groups) { if (empty($resourceId) || empty($resourceKey)) { return -5; } if (!$this->isLoggedIn()) { return -6; } $resource = $this->getResource($resourceId); $where = array(); $where[$resource['hFrameworkResourcePrimaryKey']] = $resourceKey; $authentication = false; $isOwner = (bool) $this->hDatabase->selectExists( $resource['hFrameworkResourcePrimaryKey'], $resource['hFrameworkResourceTable'], array_merge( $where, array( 'hUserId' => (int) $_SESSION['hUserId'] ) ) ); switch (true) { case $this->inGroup('Website Administrators'): case $this->inGroup('root'): { $authentication = true; break; } case $isOwner: { $authentication = true; break; } } if (!$authentication) { return -1; } if (count($groups)) { foreach ($groups as $group => $level) { $this->setGroup($group, $level); } } if (count($users)) { foreach ($users as $user => $level) { $this->setGroup($user, $level); } } $this->save($resourceId, $resourceKey, $owner, $world); $this->activity( $resource['hFrameworkResourceTable'], "Modified Permissions: on '{$resource['hFrameworkResourceTable']}, {$resourceKey}' to ". "owner: '{$owner}', world: '{$world}', users: '".implode(', ', $users)."' groups: '".implode(', ', $groups)."'" ); return 1; } public function inheritToCategories($categoryId, $groups, $owner, $world) { $categories = $this->getCategoriesInCategory($categoryId); if (count($categories)) { foreach ($categories as $categoryId) { $this->setGroups($groups); $this->save( 'hCategories', $categoryId, $owner, $world ); if (isset($_POST['category']) && (int) $_POST['category'] == 2 && !empty($categoryId)) { $this->inheritToCategories( $categoryId, $groups, $owner, $world ); } } } } private function getCategoriesInCategory($categoryId) { return $this->hCategories->selectResults( 'hCategoryId', array( 'hCategoryParentId' => (int) $categoryId ) ); } private function getFilesInDirectory($directoryId) { return $this->hFiles->selectResults( 'hFileId', array( 'hDirectoryId' => (int) $directoryId ) ); } private function getAllSubDirectories() { $path = $this->getDirectoryPath($this->resourceKey); return $this->hDirectories->selectResults( 'hDirectoryId', array( 'hDirectoryPath' => array( array('=', $path), array('LIKE', $path.'/%') ) ), 'OR', 'hDirectoryPath' ); } private function inheritToFiles($files, $owner, $world, $groups) { foreach ($files as $fileId) { $this->setGroups($groups); $this->save( 'hFiles', $fileId, $owner, $world ); } } private function inheritToDirectory($directories, $owner, $world, $groups) { foreach ($directories as $directoryId) { $this->setGroups($groups); $this->save( 'hDirectories', $directoryId, $owner, $world ); } } public function saveWorldAccess($frameworkResource, $frameworkResourceKey, $userPermissionsWorld = '') { $this->deleteCache( $frameworkResource, $frameworkResourceKey ); $frameworkResourceId = $this->getResourceId($frameworkResource); $userPermissionsId = $this->getPermissionsId( $frameworkResourceId, $frameworkResourceKey ); if ($this->validate($userPermissionsWorld)) { if (!$userPermissionsId) { $userPermissionsId = $this->insert( $frameworkResourceId, $frameworkResourceKey, 'rw', $userPermissionsWorld ); } else { $this->update( $userPermissionsId, $frameworkResourceId, $frameworkResourceKey, $this->getField( $userPermissionsId, 'hUserPermissionsOwner' ), $userPermissionsWorld ); } } } public function exist($frameworkResourceId, $frameworkResourceKey) { return ( $this->numericResource($frameworkResourceId) ->hUserPermissions ->selectExists( 'hUserPermissionsId', array( 'hFrameworkResourceId' => (int) $frameworkResourceId, 'hFrameworkResourceKey' => (int) $frameworkResourceKey ) ) ); } public function createIfNot($frameworkResourceId, $frameworkResourceKey) { } public function &numericResource(&$frameworkResource) { if (!is_numeric($frameworkResource)) { $frameworkResource = $this->getResourceId($frameworkResource); } return $this; } public function save($frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner = 'rw', $userPermissionsWorld = '') { $this->deleteCache( $frameworkResourceId, $frameworkResourceKey ) ->numericResource( $frameworkResourceId ); $userPermissionsId = $this->getPermissionsId( $frameworkResourceId, $frameworkResourceKey ); if ($this->setInherit > 0) { $userPermissionsOwner = $this->getField( $this->setInherit, 'hUserPermissionsOwner' ); $userPermissionsWorld = $this->getField( $this->setInherit, 'hUserPermissionsWorld' ); } if ($this->validate($userPermissionsOwner) && $this->validate($userPermissionsWorld)) { if (!$userPermissionsId) { $userPermissionsId = $this->insert( $frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner, $userPermissionsWorld ); } else { $this->update( $userPermissionsId, $frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner, $userPermissionsWorld ); } } if (!empty($userPermissionsId)) { $this->hUserPermissionsGroups->delete( 'hUserPermissionsId', (int) $userPermissionsId ); if ($this->setInherit > 0) { $this->groups = $this->hUserPermissionsGroups->selectColumnsAsKeyValue( array( 'hUserGroupId', 'hUserPermissionsGroup' ), array( 'hUserPermissionsId' => (int) $this->setInherit ) ); } if (is_array($this->groups) && count($this->groups)) { foreach ($this->groups as $userGroupId => $userPermissionsGroup) { if (!is_numeric($userGroupId)) { $userGroupId = $this->user->getUserId($userGroupId); } if (!empty($userGroupId) && $this->validate($userPermissionsGroup)) { $this->hUserPermissionsGroups->insert( array( 'hUserPermissionsId' => (int) $userPermissionsId, 'hUserGroupId' => (int) $userGroupId, 'hUserPermissionsGroup' => $userPermissionsGroup ) ); } } } } $this->setInherit = 0; } public function delete($frameworkResource, $frameworkResourceKey) { $this->deleteCache( $frameworkResource, $frameworkResourceKey ); $userPermissionsId = $this->getPermissionsId( $frameworkResource, $frameworkResourceKey ); $this->hDatabase->delete( array( 'hUserPermissionsGroups', 'hUserPermissions' ), 'hUserPermissionsId', $userPermissionsId ); } public function &deleteCache($frameworkResource, $frameworkResourceKey, $userPermissionsType = 'hUserPermissions') { if (is_numeric($frameworkResource)) { $frameworkResource = $this->getResource($frameworkResource); $frameworkResource = $frameworkResource['hFrameworkResourceTable']; } $this->hUserPermissionsCache->delete( array( 'hUserPermissionsType' => $userPermissionsType, 'hUserPermissionsVariable' => array( 'LIKE', "{$frameworkResource}:{$frameworkResourceKey}:%" ) ) ); return $this; } private function validate($access) { switch ($access) { case 'r': case 'rw': case 'w': case '': { return true; } default: { $this->warning( 'Permissions access level: '.$access.' has not been defined.', __FILE__, __LINE__ ); } } } private function getField($userPermissionsId, $field) { $query = $this->hUserPermissions->selectQuery( $field, (int) $userPermissionsId ); if ($this->hDatabase->resultsExist($query)) { return $this->hDatabase->getColumn($query); } else { $this->warning( 'Unable to query hUserPermissions for field: '.$field .' '. 'using hUserPermissionsId: '.$userPermissionsId.'.', __FILE__, __LINE__ ); } } private function insert($frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner, $userPermissionsWorld) { $args = func_get_args(); return $this->hUserPermissions->insert( array( 'hUserPermissionsId' => null, 'hFrameworkResourceId' => (int) $frameworkResourceId, 'hFrameworkResourceKey' => (int) $frameworkResourceKey, 'hUserPermissionsOwner' => $userPermissionsOwner, 'hUserPermissionsWorld' => $userPermissionsWorld ) ); } private function update($userPermissionsId, $frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner, $userPermissionsWorld) { $this->deleteCache( $frameworkResourceId, $frameworkResourceKey ); $this->hUserPermissions->update( array( 'hUserPermissionsId' => $userPermissionsId, 'hFrameworkResourceId' => $frameworkResourceId, 'hFrameworkResourceKey' => $frameworkResourceKey, 'hUserPermissionsOwner' => $userPermissionsOwner, 'hUserPermissionsWorld' => $userPermissionsWorld ), $userPermissionsId ); } public function setGroup($userGroupId, $userPermissionsGroup = 'r') { $this->user->setNumericUserId($userGroupId); $this->groups[$userGroupId] = $userPermissionsGroup; } public function addGroup($userGroupId, $userPermissionsGroup = 'r') { $this->setGroup( $userGroupId, $userPermissionsGroup ); } public function setGroups(array $userGroupIds) { $this->groups = $userGroupIds; } public function addGroups(array $userGroupIds) { $this->setGroups($userGroupIds); } private function getPermissionsId($frameworkResourceId, $frameworkResourceKey) { return $this->numericResource($frameworkResourceId) ->hUserPermissions ->selectColumn( 'hUserPermissionsId', array( 'hFrameworkResourceId' => (int) $frameworkResourceId, 'hFrameworkResourceKey' => (int) $frameworkResourceKey ) ); } public function setInherit($frameworkResourceId = 0, $frameworkResourceKey = 0) { $this->numericResource($frameworkResourceId); if (empty($frameworkResourceId) && empty($frameworkResourceKey)) { $this->setInherit = 0; } else if (!empty($frameworkResourceKey)) { $this->setInherit = $this->getPermissionsId( $frameworkResourceId, $frameworkResourceKey ); if (!$this->setInherit) { $this->warning( 'hUserPermissionsId could not be pulled from hFrameworkResourceId and hFrameworkResourceKey.', __FILE__, __LINE__ ); } } else { $this->warning( 'hFrameworkResourceId and hFrameworkResourceKey are required to '. 'inherit permissions from another resource.', __FILE__, __LINE__ ); } } public function getPermissions($frameworkResourceId, $frameworkResourceKey) { $rtn = ( $this->numericResource($frameworkResourceId) ->hUserPermissions ->selectAssociative( array( 'hUserPermissionsId', 'hUserPermissionsOwner', 'hUserPermissionsWorld' ), array( 'hFrameworkResourceId' => (int) $frameworkResourceId, 'hFrameworkResourceKey' => (int) $frameworkResourceKey ) ) ); if (count($rtn)) { $query = $this->hUserPermissionsGroups->select( array( 'hUserGroupId', 'hUserPermissionsGroup' ), array( 'hUserPermissionsId' => (int) $rtn['hUserPermissionsId'] ) ); if (count($query)) { foreach ($query as $data) { if ($this->hUserGroupProperties->selectExists('hUserId', (int) $data['hUserGroupId'])) { $rtn['hUserGroups'][$data['hUserGroupId']] = $data['hUserPermissionsGroup']; } else { $rtn['hUsers'][$data['hUserGroupId']] = $data['hUserPermissionsGroup']; } } } } return $rtn; } public function isAuthorized($frameworkResourceId, $frameworkResourceKey, $userId = 0) { $this->numericResource($frameworkResourceId); $this->user->whichUserId($userId); switch (true) { case (empty($frameworkResourceId) || empty($frameworkResourceKey)): { return false; } case (!$this->isLoggedIn()): { return false; } } $resource = $this->getResource($frameworkResourceId); $frameworkResourceTable = $resource['hFrameworkResourceTable']; $frameworkResourcePrimaryKey = $resource['hFrameworkResourcePrimaryKey']; $frameworkResourceNameColumn = $resource['hFrameworkResourceNameColumn']; $columns[$frameworkResourcePrimaryKey] = $frameworkResourceKey; $frameworkResourceName = $this->hDatabase->selectColumn( $frameworkResourceNameColumn, $frameworkResourceTable, $columns ); $frameworkResourceOwner = (int) $this->hDatabase->selectColumn( 'hUserId', $frameworkResourceTable, $columns ); $frameworkIsResourceOwner = $this->isResourceOwner( $frameworkResourceTable, $frameworkResourcePrimaryKey, $frameworkResourceKey, $userId ); if (!$frameworkIsResourceOwner && !$this->inGroup('Website Administrators')) { return false; } return array_merge( $resource, array( 'hFrameworkResourceName' => $frameworkResourceName, 'hFrameworkResourceOwner' => $frameworkResourceOwner, 'hFrameworkIsResourceOwner' => $frameworkIsResourceOwner ) ); } public function isResourceOwner($frameworkResourceTable, $frameworkResourcePrimaryKey, $frameworkResourceKey, $userId = 0) { $this->user->whichUserId($userId); $columns[$frameworkResourcePrimaryKey] = $frameworkResourceKey; return $this->hDatabase->selectExists( $frameworkResourcePrimaryKey, $frameworkResourceTable, array_merge( $columns, array( 'hUserId' => (int) $userId ) ) ); } public function chown($frameworkResourceId, $frameworkResourceKey, $userId = 0) {                       $this->numericResource($frameworkResourceId); $this->user->whichUserId($userId)->setNumericUserId($userId); $resource = $this->getResource($frameworkResourceId); $where = array(); $where[$resource['hFrameworkResourcePrimaryKey']] = $frameworkResourceKey; return $this->hDatabase->update( array( 'hUserId' => (int) $userId ), $where, $resource['hFrameworkResourceTable'] ); } } ?>