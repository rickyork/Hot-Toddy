<?php
  class hEditorTemplateLink extends hPlugin { public function hConstructor() { $this->getPluginFiles(); $this->hFileDocumentAppend .= $this->getTemplate( 'Link', array( ) ); } } ?>