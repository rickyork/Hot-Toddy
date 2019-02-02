<?php
  class hEditorTemplateProperties extends hPlugin { public function hConstructor() { $this->getPluginFiles(); $this->hFileDocumentAppend .= $this->getTemplate('Properties'); } } ?>