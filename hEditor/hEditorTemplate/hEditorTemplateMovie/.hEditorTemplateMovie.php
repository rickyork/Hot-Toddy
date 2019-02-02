<?php
  class hEditorTemplateMovie extends hPlugin { public function hConstructor() { $this->getPluginFiles(); $this->hFileDocumentAppend .= $this->getTemplate( 'Movie', array( ) ); } } ?>