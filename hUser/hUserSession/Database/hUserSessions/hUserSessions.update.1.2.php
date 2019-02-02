<?php

class hUserSessions_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabase->query(
            "ALTER TABLE `hUserSessions` ADD INDEX(`hUserSessionId`)"
        );
    }
}

?>