<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Icon Database Library
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hFileIconDatabase extends hPlugin {

    public function hConstructor()
    {

    }

    public function &save($fileMIME, $fileName, $fileICNS, $fileExtension = nil)
    {
        $this->hFileIcons->save(
            array(
                'hFileIconId' => $this->hFileIcons->selectColumn(
                    'hFileIconId',
                    array(
                        'hFileMIME'      => $fileMIME,
                        'hFileName'      => $fileName,
                        'hFileICNS'      => $fileICNS,
                        'hFileExtension' => $fileExtension
                    )
                ),
                'hFileMIME'      => $fileMIME,
                'hFileName'      => $fileName,
                'hFileICNS'      => $fileICNS,
                'hFileExtension' => $fileExtension
            )
        );

        return $this;
    }
}

?>