<?php
  class hContactProfilePhoto extends hPlugin { public function hConstructor() { $this->hTemplatePath = ''; if (isset($_GET['hContactId'])) { if (!$this->getContactFileId($_GET['hContactId'])) { $this->defaultImage(); } } else if (isset($_GET['hUserId'])) { if (!$this->getContactFileId($this->user->getContactId((int) $_GET['hUserId']))) { $this->getNetworkProfilePhoto($userId); } } else if (isset($_GET['hUserName'])) { $userName = $_GET['hUserName'];  $userId = $this->user->getUserId($userName); if (!empty($userId)) { if (!$this->getContactFileId($this->user->getContactId($userId))) { $this->getNetworkProfilePhoto($userId, $userName); } } else { $this->defaultImage(); } } else { $this->defaultImage(); } } private function getNetworkProfilePhoto($userId, $userName = null) {                     if (empty($userName)) { $userName = $this->user->getUserName($userId); } if (!empty($userName)) { $exists = $this->hUserUnixProperties->selectExists( 'hUserId', array( 'hUserId' => $userId ) ); if ($exists) { $command = $this->pipeCommand( '/usr/bin/dscl', escapeshellarg($this->hContactDirectoryPath('.')).' '. '-read '.escapeshellarg('/Users/'.$userName).' JPEGPhoto | tail -1 | xxd -r -p' ); if ($command) { $this->hFileMIME = 'image/jpeg'; echo $command; } else { $this->defaultImage(); } } else { $this->defaultImage(); } } else { $this->defaultImage(); } } private function getContactFileId($contactId) {                     $fileId = $this->hContactFiles->selectColumn( 'hFileId', array( 'hContactId' => (int) $contactId, 'hContactIsProfilePhoto' => 1, 'hContactIsDefaultProfilePhoto' => 1 ) ); if (!empty($fileId)) { header('Location: '.$this->getFilePathByFileId($fileId)); exit; } else { return false; } } private function defaultImage() {             $this->hFileWildcardPath = '/images/icons/48x48/user.png'; $this->plugin('hFile/hFileIcon'); } } ?>