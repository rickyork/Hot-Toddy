<?php
  class hApplicationColumnsLibrary extends hPlugin { public function hConstructor() { $this->plugin('hApplication/hApplicationForm'); $this->getPluginFiles(); $this->jQuery('Datepicker', 'Sortable'); } public function get($column, $form) { return $this->getTemplate( 'Columns', array( 'hApplicationColumn' => $column, 'hApplicationForm' => $form ) ); } } ?>