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

class hFileIcons_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileIcons->insert(
            array(
                'hFileIconId' => 75,
                'hFileMIME' => 'application/x-iwork-pages-sffpages',
                'hFileName' => 'pages.png',
                'hFileICNS' => 'Pages_Doc.icns',
                'hFileExtension' => 'pages'
            ),
            array(
                'hFileIconId' => 76,
                'hFileMIME' => 'application/x-iwork-numbers-sffnumbers',
                'hFileName' => 'numbers.png',
                'hFileICNS' => 'NumbersDocument.icns',
                'hFileExtension' => 'numbers'
            ),
            array(
                'hFileIconId' => 77,
                'hFileMIME' => 'application/x-iwork-keynote-sffkey',
                'hFileName' => 'keynote.png',
                'hFileICNS' => 'KeyDocument.icns',
                'hFileExtension' => 'keynote'
            )
        );
    }
}

?>