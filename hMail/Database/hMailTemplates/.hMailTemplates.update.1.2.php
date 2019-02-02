<?php
  class hMailTemplates_1to2 extends hPlugin { public function hConstructor() { $this->hDatabase->query( "ALTER TABLE `hMailTemplates`
                     ADD `hMailJSONLastModified` INT(32) NOT NULL" ); } } ?>