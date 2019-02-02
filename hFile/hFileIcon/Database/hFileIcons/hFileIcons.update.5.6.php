<?php

class hFileIcons_5to6 extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabase->query(
            "INSERT INTO `hFileIcons` (
                `hFileIconId`,
                `hFileMIME`,
                `hFileName`,
                `hFileICNS`,
                `hFileExtension`
            ) VALUES 
                (null, 'application/json', 'js.png', 'doc-js.icns', 'json'),
                (null, 'text/json', 'js.png', 'doc-js.icns', 'json')"
        );
    }
}

?>