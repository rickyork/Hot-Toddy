<?php
 class hUserLog_1to2 extends hPlugin { public function hConstructor() { $this->hDatabase->query( "ALTER TABLE `hUserLog`
                     ADD `hUserReferredBy` INT(11) NOT NULL" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                     ADD `hUserRegistrationTrackingId` INT(11) NOT NULL" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                     ADD `hFileId` INT(11) NOT NULL" ); } } ?>