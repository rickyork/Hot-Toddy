<?php
  class hUserLibrary extends hPlugin { public $userId = 0; public $userName = ''; private $hContactDatabase; private $users = array(); private $cache = true; private $methods = array( 'getUserId', 'getUserName', 'getUserEmail' ); private $contactMethods = array( 'getFullName', 'getDisplayName', 'getFirstName', 'getLastName', 'getCompany', 'getTitle', 'getContactId', 'getDateOfBirth', 'getGender' ); private $counter = 0; public function &setCache($cache) {        $this->cache = $cache; return $this; } public function __call($method, $arguments) {        $isUnderscoreMethod = substr($method, 0, 1) == '_'; $underscoreMethodName = null; if ($isUnderscoreMethod) { $underscoreMethodName = substr($method, 1); } $isUserMethod = ( in_array($method, $this->methods, true) || in_array($method, $this->contactMethods, true) || in_array($underscoreMethodName, $this->methods, true) || in_array($underscoreMethodName, $this->contactMethods, true) ); if (!$isUserMethod) { return parent::__call($method, $arguments); } else { if ($isUnderscoreMethod) { $user = isset($arguments[0])? $arguments[0] : 0; if (in_array($underscoreMethodName, $this->methods)) { switch ($method) { case '_getUserId': { if (empty($user) && $this->isLoggedIn()) { return $_SESSION['hUserId']; } return (int) $this->hUsers->selectColumn( 'hUserId', array( 'hUserName' => $user, 'hUserEmail' => $user ), 'OR' ); } case '_getUserName': { if (empty($user) && $this->isLoggedIn()) { return $_SESSION['hUserName']; } return $this->hUsers->selectColumn( 'hUserName', is_numeric($user)? array( 'hUserId' => (int) $user ) : array( 'hUserEmail' => $user ) ); } case '_getUserEmail': { if (empty($user) && $this->isLoggedIn()) { return $_SESSION['hUserEmail']; } return $this->hUsers->selectColumn( 'hUserEmail', is_numeric($user)? array( 'hUserId' => (int) $user ) : array( 'hUserName' => $user ) ); } } } else if (in_array($underscoreMethodName, $this->contactMethods)) { $userId = $arguments[0]; $contactAddressBookId = $arguments[1]; return $this->getContactField( $this->getContactFieldName($underscoreMethodName), $userId, $contactAddressBookId ); } } else { $user = isset($arguments[0])? $arguments[0] : 0; if (in_array($method, $this->methods, true)) { $sessionKey = ''; $key = ''; switch ($method) { case 'getUserId': { $sessionKey = 'hUserId'; break; } case 'getUserName': { $sessionKey = 'hUserName'; break; } case 'getUserEmail': { $sessionKey = 'hUserEmail'; break; } } if (!empty($sessionKey) && empty($user)) { if ($this->isLoggedIn()) { if (!empty($_SESSION[$sessionKey])) { $this->users[$method.':'.$user] = $_SESSION[$sessionKey]; return $this->users[$method.':'.$user]; } else { $this->warning("Unable to map a default value to $user using '{$method}' because there is no session.", __FILE__, __LINE__); } } } $key = $method.':'.$user; if (!isset($this->users[$key])) { $this->users[$key] = $this->{"_{$method}"}($user); } } else if (in_array($method, $this->contactMethods, true)) { $this->whichUserId($user)->setNumericUserId($user); $contactAddressBookId = isset($arguments[1])? (int) $arguments[1] : 1; $key = $method.':'.$contactAddressBookId.':'.$user; if (!isset($this->users[$key])) { $this->users[$key] = $this->{"_{$method}"}($user, $contactAddressBookId); } } if ($this->cache) { return $this->users[$key]; } else { $data = $this->users[$key]; unset($this->users[$key]); return $data; } } } } private function getContactFieldName($method) {          if ($method == 'getFullName') { $method = 'getDisplayName'; } if ($method == 'getContactId') { return 'hContactId'; } return 'hContact'.str_replace('get', '', $method); } public function getFilePath($userId = 0, $contactAddressBookId = 1, array $options = array()) {         $this->whichUserId($userId) ->setNumericUserId($userId); return $this->getFilePathByFileId( $this->getFileId( $userId, $contactAddressBookId, $options ) ); } public function getFileId($userId = 0, $contactAddressBookId = 1, array $options = array()) {         $this->whichUserId($userId) ->setNumericUserId($userId); $options['hContactId'] = $this->getContactId($userId, $contactAddressBookId); if (!isset($options['hContactFileCatgoryId'])) { $options['hContactFileCategoryId'] = 1; } if (!isset($options['hContactIsProfilePhoto'])) { $options['hContactIsProfilePhoto'] = 1; } if (!isset($options['hContactIsDefaultProfilePhoto'])) { $options['hContactIsDefaultProfilePhoto'] = 1; } return $this->hContactFiles->selectColumn('hFileId', $options); } public function getGenderLabel($userId = 0, $contactAddressBookId = 1, $male = 'Male', $female = 'Female') {          $this->whichUserId($userId) ->setNumericUserId($userId); if ($this->getGender($userId, $contactAddressBookId)) { return $male; } else { return $female; } } public function &deleteContacts($userId = 0, $contactAddressBookId = 1) {        $this->whichUserId($userId) ->setNumericUserId($userId); $contacts = $this->hContacts->select( 'hContactId', array( 'hContactAddressBookId' => $contactAddressBookId, 'hUserId' => $userId ) ); $this->hContactDatabase = $this->database('hContact'); foreach ($contacts as $contactId) { $this->hContactDatabase->delete($contactId); } return $this; } public function getContactField($field, $userId = 0, $contactAddressBookId = 1) {                 $this->whichUserId($userId) ->setNumericUserId($userId); $this->hDatabase->setDefaultResult(null); return $this->hContacts->selectColumn( $field, array( 'hUserId' => (int) $userId, 'hContactAddressBookId' => $contactAddressBookId ) ); } public function &whichUserName(&$userName, $setToAuthor = false) {               if (empty($userName)) { if ($this->isLoggedIn()) { $userName = $_SESSION['hUserName']; } else if ($setToAuthor && !empty($this->hUserName)) { $userName = $this->hUserName; } } else { $userName = is_numeric($userName)? $this->getUserName($userName) : $userName; } return $this; } public function &whichUserId(&$userId, $setToAuthor = false) {               if (empty($userId)) { if ($this->isLoggedIn()) { $userId = (int) $_SESSION['hUserId']; } else if ($setToAuthor && !empty($this->hUserId) && !empty($this->hUserId)) { $userId = (int) $this->hUserId; } } else { $userId = is_numeric($userId)? (int) $userId : $this->getUserId($userId); } return $this; } public function &setNumericUserId(&$userId) {           if (!is_numeric($userId)) { $userId = $this->getUserId(trim($userId)); } return $this; } public function getVariable($userVariable, $default = '', $userId = 0) {              $this->whichUserId($userId)->setNumericUserId($userId); switch ($userVariable) { case 'hUserUnixUId': case 'hUserUnixGId': case 'hUserUnixHome': case 'hUserUnixShell': { $this->hDatabase->setDefaultResult($default); return $this->queryUnixProperty($userVariable, $userId); break; } default: { return $this->hDatabase->getResult($this->queryVariable($userVariable, $userId), $default); } } } public function &saveVariable($userVariable, $userValue, $userId = 0) {              $this->whichUserId($userId)->setNumericUserId($userId); if (!empty($userId)) { if ($this->hDatabase->resultsExist($this->queryVariable($userVariable, $userId))) { $this->hUserVariables->update( array( 'hUserValue' => $userValue ), array( 'hUserId' => $userId, 'hUserVariable' => $userVariable ) ); } else { $this->hUserVariables->insert( array( 'hUserId' => $userId, 'hUserVariable' => $userVariable, 'hUserValue' => $userValue ) ); } } return $this; } public function &deleteVariables($userId = 0) {             $this->whichUserId($userId)->setNumericUserId($userId); if (!empty($userId)) { $this->hUserVariables->delete('hUserId', $userId); } return $this; } public function &deleteVariable($userVariable, $userId = 0) {             $this->whichUserId($userId)->setNumericUserId($userId); if (!empty($userId)) { $this->hUserVariables->delete( array( 'hUserId' => $userId, 'hUserVariable' => $userVariable ) ); } return $this; } public function queryUnixProperty($userVariable, $userId = 0) {                                  $this->whichUserId($userId)->setNumericUserId($userId); return $this->hUserUnixProperties->selectColumn( $userVariable, array( 'hUserId' => (int) $userId ) ); } private function queryVariable($userVariable, $userId = 0) {           $this->whichUserId($userId)->setNumericUserId($userId); return $this->hUserVariables->selectQuery( 'hUserValue', array( 'hUserId' => $userId, 'hUserVariable' => $userVariable ) ); } public function isDirectoryUser($userId = 0) {            $this->whichUserId($userId)->setNumericUserId($userId); return $this->hUserUnixProperties->selectExists('hUserId', $userId); } public function &setVariables() {         if ($this->isLoggedIn()) { $this->setVariables( $this->hUserVariables->selectAssociative( array( 'hUserVariable', 'hUserValue' ), array( 'hUserId' => (int) $_SESSION['hUserId'] ) ) ); } return $this; } } ?>