<?php
  class hUserAuthenticationLibrary extends hPlugin { private $isGroupCache = array(); private $inGroupCache = array(); private $worldReadCache = array(); private $cache = array(); private $groupMembers = array(); private $permissionsIds = array(); private $calendarDates = array(); private $backtrace = false; public function isLoggedIn($userId = 0) {             if (empty($userId)) { return (isset($_SESSION['hUserId']) && !empty($_SESSION['hUserId'])); } else { $this->user->setNumericUserId($userId);  return $this->hUserSessions->selectExists( 'hUserSessionId', array( 'hUserSessionData' => array( 'LIKE', "%hUserId|i:{$userId};%" ) ) ); } } public function setHTTPAuthentication($realm = null) {        $isLoggedIn = (!empty($_SERVER['PHP_AUTH_USER']) && ( $_SERVER['PHP_AUTH_USER'] == $_SESSION['hUserName'] || $_SERVER['PHP_AUTH_USER'] == $_SESSION['hUserEmail'] )); if (empty($_SERVER['PHP_AUTH_USER']) || !$isLoggedIn) { header('WWW-Authenticate: Basic realm="'.($realm? $realm : $this->hFrameworkName).'"'); header('HTTP/1.0 401 Unauthorized'); echo 'Login required.'; exit; } } public function isActivated($userId = 0) {             $this->user ->setNumericUserId($userId) ->whichUserId($userId); return (bool) $this->hUsers->selectColumn( 'hUserIsActivated', (int) $userId ); } public function isSSLEnabled() {          return ($this->hFileSSLEnabled(false)? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') : true); } public function SSL() {         if (!$this->isSSLEnabled()) { header('Location: https://'.$this->hServerHost.$this->href($this->hFilePath, $_GET)); exit; } } public function setCache($userId, $userPermissionsType, $userPermissionsVariable, $userPermissionsValue) {              $exists = $this->hUserPermissionsCache->selectExists( 'hUserPermissionsValue', array( 'hUserId' => (int) $userId, 'hUserPermissionsType' => $userPermissionsType, 'hUserPermissionsVariable' => $userPermissionsVariable ) ); if (!$exists) { $this->hUserPermissionsCache->insert( array( 'hUserId' => (int) $userId, 'hUserPermissionsType' => $userPermissionsType, 'hUserPermissionsVariable' => $userPermissionsVariable, 'hUserPermissionsValue' => (int) $userPermissionsValue ) ); } return $userPermissionsValue; } public function getCache($userId, $userPermissionsType, $userPermissionsVariable) {                                          $key = $userId.':'.$userPermissionsType.':'.$userPermissionsVariable; if (!isset($this->cache[$key])) { $results = $this->hUserPermissionsCache->select( 'hUserPermissionsValue', array( 'hUserId' => (int) $userId, 'hUserPermissionsType' => $userPermissionsType, 'hUserPermissionsVariable' => $userPermissionsVariable ) ); $result = (count($results)? (bool) $results[0]['hUserPermissionsValue'] : -1); if ($result !== -1) {  $this->cache[$key] = $result; } return $result; } else { return $this->cache[$key]; } } public function hasWorldRead($resource) {               if (!isset($this->worldReadCache[$resource])) { list($table, $key) = explode(':', $resource); $result = $this->hUserPermissions->selectExists( 'hUserPermissionsId', array( 'hFrameworkResourceId' => $this->getResourceId($table), 'hFrameworkResourceKey' => (int) $key, 'hUserPermissionsWorld' => array('LIKE', 'r%') ) ); $this->worldReadCache[$resource] = $result; return $result; } else { return $this->worldReadCache[$resource]; } } public function checkPermissions(&$checkPermissions) {                   $checkPermissions = $this->inGroup('root')? false : $checkPermissions;  } public function checkWorldPermissions(&$checkPermissions) {                   if ($checkPermissions === 'auto') { return $this->isLoggedIn() && !$this->inGroup('root') || !$this->isLoggedIn(); } $checkPermissions = $this->inGroup('root') ? false : $checkPermissions;  } public function getUserGroupsForTemplate($checkPermissions, $userId) {                          $groups = array(); if ($checkPermissions && $this->isLoggedIn()) { $groupMembers = $this->getGroupMembership($userId); $groups = array( 'hUserGroupId' => $groupMembers, 'userGroupId' => $groupMembers ); } return $groups; } public function getPermissionsVariablesForTemplate($checkPermissions, $checkWorldPermissions = false, $level = 'r', $userId = 0) {                     $this->checkPermissions($checkPermissions); $this->checkWorldPermissions($checkWorldPermissions); if (empty($userId)) { $userId = $this->isLoggedIn()? $_SESSION['hUserId'] : 0; } $userGroups = $this->getUserGroupsForTemplate($checkPermissions, $userId); return array( 'checkPermissions' => $checkPermissions, 'checkWorldPermissions' => $checkWorldPermissions, 'hUserId' => $userId, 'userId' => $userId, 'hUserGroups' => $userGroups, 'userGroups' => $userGroups, 'level' => $level ); } public function hasPermission($resource, $userId = 0) {                                  $this->user ->setNumericUserId($userId) ->whichUserId($userId); $type = 'hUserPermissions';  if ($this->inGroup('root', $userId)) { return $this->setCache($userId, $type, $resource, true); } list($table, $key, $access) = explode(':', $resource);  if ($table == 'hFiles' && $this->hFileSymbolicLinkTo(0, $key)) { $field[1] = $ln; } $frameworkResourceId = $this->getResourceId($table); $table = $this->getResource($frameworkResourceId); if (empty($userId) && $frameworkResourceId == 1) {   if (!isset($this->calendarDates[$key])) { $calendarFiles = $this->hCalendarFiles->select( array( 'hCalendarBegin', 'hCalendarEnd' ), array( 'hFileId' => (int) $key ) ); $this->calendarDates[$key] = false; foreach ($calendarFiles as $calendarFile) { if ($calendarFile['hCalendarBegin'] > 0 && $calendarFile['hCalendarBegin'] >= time() || $calendarFile['hCalendarEnd'] > 0 && $calendarFile['hCalendarEnd'] <= time()) { $this->calendarDates[$key] = true; break; } } } if ($this->calendarDates[$key]) { return false; } } if (!isset($this->permissionsIds[$frameworkResourceId.','.$key])) { $userPermissionsId = $this->hUserPermissions->selectColumn( 'hUserPermissionsId', array( 'hFrameworkResourceId' => (int) $frameworkResourceId, 'hFrameworkResourceKey' => (int) $key ) ); $this->permissionsIds[$frameworkResourceId.','.$key] = (int) $userPermissionsId; } $userPermissionsId = $this->permissionsIds[$frameworkResourceId.','.$key]; if (empty($userPermissionsId)) { return $this->setCache($userId, $type, $resource, false); } $hasWorldAccess = $this->hUserPermissions->selectExists( 'hUserPermissionsId', array( 'hUserPermissionsId' => (int) $userPermissionsId, 'hUserPermissionsWorld' => array( $access == 'r'? 'LIKE' : '=', $access == 'r'? 'r%' : 'rw' ) ) ); if (!empty($hasWorldAccess)) { return $this->setCache($userId, $type, $resource, true); }   if (!empty($userId)) { $columns[$table['hFrameworkResourcePrimaryKey']] = (int) $key; $columns['hUserId'] = (int) $userId; $isResourceOwner = $this->hDatabase->selectColumn( 'hUserId', $table['hFrameworkResourceTable'], $columns );  if (!empty($isResourceOwner)) { $hasOwnerAccess = $this->hUserPermissions->selectExists( 'hUserPermissionsId', array( 'hUserPermissionsId' => (int) $userPermissionsId, 'hUserPermissionsOwner' => array( $access == 'r'? 'LIKE' : '=', $access == 'r'? 'r%' : 'rw' ) ) ); if (!empty($hasOwnerAccess)) { return $this->setCache( $userId, $type, $resource, true ); } }     $userGroups = $this->hUserPermissionsGroups->select( 'hUserGroupId', array( 'hUserPermissionsId' => (int) $userPermissionsId, 'hUserPermissionsGroup' => array( $access == 'r'? 'LIKE' : '=', $access == 'r'? 'r%' : 'rw' ) ) );    foreach ($userGroups as $userGroupId) { if ($this->inGroup($userGroupId, $userId) || (int) $userGroupId === (int) $userId) { return $this->setCache( $userId, $type, $resource, true ); } } return $this->setCache( $userId, $type, $resource, false ); } else { return false; } return false; } public function notLoggedIn() {           $this->plugin('hUser/hUserLogin');  } public function loggedIn() {               if ($this->isLoggedIn()) { return true; } else { $this->notLoggedIn(); return false; } } public function notAuthorized($fileTitle = null, $fileDocument = null) {             if ($this->isLoggedIn()) { if (!empty($fileTitle)) { $this->hFileTitle = $fileTitle; } else { $this->hFileTitle = $this->hUserAuthenticationNotAuthorizedTitle('Not Authorized'); } if (!empty($fileDocument)) { $this->hFileDocument = $fileDocument; } else { $this->hFileDocument = $this->getTemplate( $this->hUserAuthenticationNotAuthorizedTemplate('Not Authorized') ); } } else { $this->plugin('hUser/hUserLogin'); }  } public function inGroup($userGroupId, $userId = 0, $root = true) {                   $this->hUser ->setNumericUserId($userId) ->whichUserId($userId); if (empty($userId)) { return false; } if (!is_numeric($userGroupId)) { if ($userGroupId == 'root') { $root = false; } $userGroupId = $this->getGroupId($userGroupId); } else if ($userGroupId == $this->getGroupId('root')) { $root = false; }  if ($userId === $userGroupId) { return true; } if (($value = $this->getCache($userId, 'inGroup:'.(int) $root, $userGroupId)) !== -1) { if (empty($value) && $root) { return $this->inGroup( 'root', $userId, false ); } return $value; } $group = $this->getGroupMembers($userGroupId); if (in_array($userId, $group['hUserGroups']) || in_array($userId, $group['hUsers'])) { $this->setCache( $userId, 'inGroup:'.(int) $root, $userGroupId, true ); return true; } else if ($root) { $this->setCache( $userId, 'inGroup:'.(int) $root, $userGroupId, false ); return $this->inGroup( 'root', $userId, false ); } else { $this->setCache( $userId, 'inGroup:'.(int) $root, $userGroupId, false ); return false; } } public function inAnyOfTheFollowingGroups(array $groups, $userId = 0) {         $this->hUser ->setNumericUserId($userId) ->whichUserId($userId); foreach ($groups as $group) { if ($this->inGroup($group, $userId)) { return true; } } return false; } public function inAllOfTheFollowingGroups(array $groups, $userId = 0) {         $this->hUser ->setNumericUserId($userId) ->whichUserId($userId); foreach ($groups as $group) { if (!$this->inGroup($group, $userId)) { return false; } } return true; } public function deleteCachedGroupData($userGroupId) {        $this->user->setNumericUserId($userGroupId); if (isset($this->isGroupCache[$userGroupId])) { unset($this->isGroupCache[$userGroupId]); } if (isset($this->groupMembers[$userGroupId])) { unset($this->groupMembers[$userGroupId]); }  } public function getGroupMembership($userId = 0, $results = array(), $recursive = true) {             $this->user ->setNumericUserId($userId) ->whichUserId($userId);  $userGroups = $this->hUserGroups->select( 'hUserGroupId', array( 'hUserId' => (int) $userId ) ); foreach ($userGroups as $userGroupId) { if (!in_array((int) $userGroupId, $results)) { $results[] = (int) $userGroupId; } if ($recursive) { $results = array_merge( $this->getGroupMembership((int) $userGroupId), $results ); } } return array_unique($results); } public function isDomainGroup($group) {          $domain = $this->hUserWinbindDomain.$this->hUserWinbindSeparator; return (substr($group, 0, strlen($domain)) == $domain); } public function isElevated($userGroupId) {         $this->user->setNumericUserId($userGroupId); return (bool) $this->hUserGroupProperties->selectColumn( 'hUserGroupIsElevated', array( 'hUserId' => $userGroupId ) ); } public function isInElevated($userId = 0) {              $this->user ->setNumericUserId($userId) ->whichUserId($userId); if (empty($userId)) { return false; } if (($value = $this->getCache($userId, 'isInElevated', 'isInElevated')) >= 0) { return $value; } $userGroups = $this->hUserGroups->select( 'hUserGroupId', array( 'hUserId' => (int) $userId ) ); foreach ($userGroups as $i => $userGroupId) { $userGroupIsElevated = (int) $this->hUserGroupProperties->selectColumn( 'hUserGroupIsElevated', array( 'hUserId' => $userGroupId ) ); if (!empty($userGroupIsElevated)) { return $this->setCache( $userId, 'isInElevated', 'isInElevated', true ); } } return $this->setCache( $userId, 'isInElevated', 'isInElevated', false ); } public function getGroupLiaison($userGroupId) {            $this->user->setNumericUserId($userGroupId); return $this->hUserGroupProperties->selectColumn( 'hUserGroupOwnerId', array( 'hUserId' => (int) $userGroupId ) ); } public function getGroupMembers($userGroupId) {                  $this->user->setNumericUserId($userGroupId); if (!isset($this->groupMembers[$userGroupId])) { $this->setGroupMembers($userGroupId); } return $this->groupMembers[$userGroupId]; } private function setGroupMembers($userGroupId, $userSubGroupId = 0) {               $this->user->setNumericUserId($userGroupId); $userGroupId = (int) $userGroupId; $userSubGroupId = (int) $userSubGroupId; if (!isset($this->groupMembers[$userGroupId])) { $this->groupMembers[$userGroupId] = array( 'hUserGroups' => array(), 'hUsers' => array() ); } $users = $this->hUserGroups->select( 'hUserId', array( 'hUserGroupId' => empty($userSubGroupId)? (int) $userGroupId : (int) $userSubGroupId ) ); if (count($users)) { foreach ($users as $userId) { $userId = (int) $userId; if ($this->isGroup($userId)) { if (!in_array($userId, $this->groupMembers[$userGroupId]['hUserGroups'])) { $this->groupMembers[$userGroupId]['hUserGroups'][] = $userId; if (!empty($userSubGroupId)) { $this->groupMembers[$userSubGroupId]['hUserGroups'][] = $userId; } $this->setGroupMembers($userGroupId, $userId); } } else { if (!in_array($userId, $this->groupMembers[$userGroupId]['hUsers'])) { $this->groupMembers[$userGroupId]['hUsers'][] = $userId; if (!empty($userSubGroupId)) { $this->groupMembers[$userSubGroupId]['hUsers'][] = $userId; } } } } } return; } public function getUsersInGroup($userGroupId) {                   $group = $this->getGroupMembers($userGroupId); if (isset($group['hUsers'])) { return $group['hUsers']; } return array(); } public function getGroupsInGroup($userGroupId) {                   $group = $this->getGroupMembers($userGroupId); if (isset($group['hUserGroups'])) { return $group['hUserGroups']; } return array(); } public function getGroupId($userGroup) {                if (!empty($userGroup)) { $userId = $this->user->getUserId($userGroup); if (!empty($userId)) { if ($this->isGroup($userId)) { return $userId; } else { $this->warning( "Group name provided '{$userGroup}' is not a group.", __FILE__, __LINE__ ); return false; } } else { $this->warning( "Unable to get a user id from group name '{$userGroup}'.", __FILE__, __LINE__ ); return false; } } else { return false; } } public function isGroup($userGroupId) {        $this->user->setNumericUserId($userGroupId); if (isset($this->isGroupCache[$userGroupId])) { return $this->isGroupCache[$userGroupId]; } $isGroup = (bool) $this->hUserGroupProperties->selectColumn( 'hUserId', array( 'hUserId' => (int) $userGroupId ) ); return ($this->isGroupCache[$userGroupId] = $isGroup); } public function groupExists($userGroup) {        if (is_numeric($userGroup)) { $userGroup = $this->user->getUserName($userGroup); } return (bool) $this->hDatabase->selectColumn( array( 'hUserGroupProperties' => 'hUserId' ), array( 'hUsers', 'hUserGroupProperties' ), array( 'hUsers.hUserId' => 'hUserGroupProperties.hUserId', 'hUsers.hUserName' => $userGroup ) ); } public function searchForGroup($term, $userId = 0) {               $this->user->setNumericUserId($userId) ->whichUserId($userId); return $this->hDatabase->selectColumn( array( 'hUsers' => 'hUserName' ), array( 'hUsers', 'hUserGroups', 'hUserGroupProperties' ), array( 'hUsers.hUserId' => array( array('=', 'hUserGroupProperties.hUserId'), array('=', 'hUserGroups.hUserGroupId') ), 'hUserGroups.hUserId' => (int) $userId, 'hUsers.hUserName' => array('LIKE', $term) ) ); } public function searchForGroups($term, $userId = 0) {               $this->user->setNumericUserId($userId) ->whichUserId($userId); return $this->hDatabase->select( array( 'hUsers' => array( 'hUserId', 'hUserName' ) ), array( 'hUsers', 'hUserGroups', 'hUserGroupProperties' ), array( 'hUsers.hUserId' => array( array('=', 'hUserGroupProperties.hUserId'), array('=', 'hUserGroups.hUserGroupId') ), 'hUserGroups.hUserId' => (int) $userId, 'hUsers.hUserName' => array('LIKE', $term) ) ); } public function isAuthor() {         return (isset($_SESSION['hUserId']) && $_SESSION['hUserId'] == $this->hUserId); } } ?>