<?php
  $html .= $this->getTemplate( dirname(__FILE__).'/HTML/Template.html', array( 'hEditorBodyId' => $this->hEditorBodyId('hEditorTemplate'), 'hFileDocument' => $this->getDocument() ) ); ?>