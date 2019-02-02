<?php
  class hEditorTemplateImage extends hPlugin { public function hConstructor() { $this->getPluginFiles(); $this->hFileDocumentAppend .= $this->getTemplate( 'Image', array( ) ); } } ?>