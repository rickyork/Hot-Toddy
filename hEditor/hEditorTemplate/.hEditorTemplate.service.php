<?php
  class hEditorTemplateService extends hService { private $hFileDatabase; public function hConstructor() { if (!$this->isLoggedIn()) { $this->JSON(-6); return; } } public function save() { if (!isset($_POST['hFileId'])) { $this->JSON(-5); return; } if (!$this->hFiles->hasPermission((int) $_POST['hFileId'], 'rw')) { $this->JSON(-1); return; } if (strtolower($_POST['hFileDocument']) == 'undefined' || strtolower($_POST['hFileDocument']) == 'null') { $this->JSON(0); return; } $this->console($_POST['hFileDocument']); if (strtolower($_POST['hFileTitle']) == 'undefined') { unset($_POST['hFileTitle']); } if (strtolower($_POST['hFileKeywords']) == 'undefined') { unset($_POST['hFileKeywords']); } if (strtolower($_POST['hFileDescription']) == 'undefined') { unset($_POST['hFileDescription']); } $this->database('hFile')->save($_POST, false); $this->JSON(1); } } ?>