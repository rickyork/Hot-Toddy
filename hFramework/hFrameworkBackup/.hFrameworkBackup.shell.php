<?php
  class hFrameworkBackupShell extends hShell { private $hFrameworkBackup; public function hConstructor() { $this->hFrameworkBackup = $this->library('hFramework/hFrameworkBackup'); $this->hFrameworkBackup->backup(); $this->log("Hot Toddy Backup completed @ ".date('m/d/Y h:i:s A')); } } ?>