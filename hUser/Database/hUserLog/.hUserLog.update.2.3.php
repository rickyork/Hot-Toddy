<?php
 class hUserLog_2to3 extends hPlugin { public function hConstructor() { $this->hDatabase->query( "ALTER TABLE `hUserLog`
                  CHANGE `hUserAccountCreated` `hUserCreated` INT(32) NOT NULL DEFAULT '0'" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                  CHANGE `hUserAccountLastLogin` `hUserLastLogin` INT(32) NOT NULL DEFAULT '0'" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                  CHANGE `hUserAccountLastModified` `hUserLastModified` INT(32) NOT NULL DEFAULT '0'" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                  CHANGE `hUserAccountLastModifiedBy` `hUserLastModifiedBy` INT(11) NOT NULL DEFAULT '0'" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                     ADD `hUserLastFailedLogin` INT(32) NOT NULL
                   AFTER `hUserLastLogin`" ); $this->hDatabase->query( "ALTER TABLE `hUserLog`
                     ADD `hUserFailedLoginCount` INT(11) NOT NULL
                   AFTER `hUserLoginCount`" ); } } ?>