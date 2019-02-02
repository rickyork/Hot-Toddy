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

class hFinderEditFile extends hPlugin {

    private $hFinder;

    public function hConstructor()
    {
        $this->hFinder = $this->library('hFinder');

        $this->getPluginFiles();

        $this->hFileDocument .= $this->hFinder->getBottomBox(
            'hFinderEditFile',
            'Edit File',
            $this->getTemplate(
                'Edit File',
                array(
                    'hFinderEditFileAction'        => $this->hFinderEditFileAction('/hFile/saveFinderProperties'),
                    'hFinderEditFileActivity'      => '/images/themes/aqua/activity/upload.gif',
                    'hFinderEditFileWorldRead'     => !$this->hFinderEditFileSetAutoAccess(false)? $this->getTemplate('Permissions') : '',
                    'hFinderEditFileSetAutoAccess' => $this->hFinderEditFileSetAutoAccess(false)?  $this->getTemplate('Auto Access') : '',
                    'hFileSystemAllowDuplicates'   => $this->hFileSystemAllowDuplicates(0)? 1 : 0
                )
            )
        );
    }
}

?>