<?php
  class hFinderDialogueImage extends hPlugin implements hFinderDialogueTemplate { public function hConstructor() { $this->hFileTitle = 'Select an Image...';    $this->getPluginFiles(); $this->hFinderButtons = true; $this->hFinderButtonUpload = true; $this->hFinderButtonsRight = false; } public function getControls() { return $this->getTemplate('Buttons'); } } ?>
