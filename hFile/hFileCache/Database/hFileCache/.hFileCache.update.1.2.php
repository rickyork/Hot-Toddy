<?php
 class hFileCache_1to2 extends hPlugin { public function hConstructor() { $this->hDatabase->query( "ALTER TABLE `hFileCache`
                  CHANGE `hFileCacheResource` `hFileCacheResource` VARCHAR(50) 
           CHARACTER SET utf8 
                 COLLATE utf8_general_ci NOT NULL DEFAULT  ''" ); } } ?>