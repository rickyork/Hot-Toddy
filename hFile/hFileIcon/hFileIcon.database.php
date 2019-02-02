<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
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