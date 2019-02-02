<?php

class hUsers_3to4 extends hPlugin {

    public function hConstructor()
    {
        // Add two columns...
        $this->hDatabase->query(
            "ALTER TABLE `hUsers` ADD FULLTEXT `hUserFullText` (
                `hUserName`,
                `hUserEmail`
            )"
        );
    }
}

?>