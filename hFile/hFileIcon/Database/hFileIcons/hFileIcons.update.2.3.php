<?php

//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy Database Structure Update
//\\ @@@@  @@@@\\\\\|
//\\\@@@@| @@@@\\\\\|
//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
//\\\\  \\_   \\\\\\|
//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
//\\\\\  ----  \@@@@| http://www.hframework.com/license
//@@@@@\       \@@@@|
//@@@@@@\     \@@@@@|
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hFileIcons_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileIcons->insert(
            array(
                'hFileIconId' => 0,
                'hFileMIME' => 'directory/volumes',
                'hFileName' => 'sharepoint.png',
                'hFileICNS' => 'GenericSharepoint.icns',
                'hFileExtension' => ''
            ),
            array(
                'hFileIconId' => 0,
                'hFileMIME' => 'directory/sharepoint',
                'hFileName' => 'file_server.png',
                'hFileICNS' => 'GenericFileServerIcon.icns',
                'hFileExtension' => ''
            )
        );
    }
}

?>