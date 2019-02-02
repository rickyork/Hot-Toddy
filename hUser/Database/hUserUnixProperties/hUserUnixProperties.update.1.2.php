<?php

class hUserUnixProperties_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabase->query("ALTER TABLE `hUserUnixProperties` ADD UNIQUE (`hUserId`)");
    }
}

?>