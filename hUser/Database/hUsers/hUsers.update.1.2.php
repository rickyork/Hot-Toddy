<?php

class hUsers_1to2 extends hPlugin {

    public function hConstructor()
    {
        // Add two columns...
        $this->hDatabase->query(
            "ALTER TABLE `hUsers`
                     ADD `hUserSecurityQuestionId` INT(3) NOT NULL
                   AFTER `hUserConfirmation`"
        );

        $this->hDatabase->query(
            "ALTER TABLE `hUsers`
                     ADD `hUserSecurityAnswer` VARCHAR(75) NOT NULL
                   AFTER `hUserSecurityQuestion`"
        );
    }
}

?>