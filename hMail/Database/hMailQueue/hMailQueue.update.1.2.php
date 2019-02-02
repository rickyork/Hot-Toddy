<?php

class hMailQueue_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabase->query("ALTER TABLE `hMailQueue` ADD `hMailLibrary` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `hMailMessage`");
        $this->hDatabase->query("ALTER TABLE `hMailQueue` CHANGE `hMailMessage` `hMailMIME` MEDIUMTEXT NOT NULL DEFAULT ''");
    }
}

?>