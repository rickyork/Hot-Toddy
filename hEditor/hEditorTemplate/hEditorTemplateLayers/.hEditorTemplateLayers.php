<?php
  class hEditorTemplateLayers extends hPlugin { public function hConstructor() { $this->getPluginFiles(); $this->hFileDocumentAppend .= $this->getTemplate('Layers'); } } ?>