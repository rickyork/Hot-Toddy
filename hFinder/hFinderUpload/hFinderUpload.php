<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Upload
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

class hFinderUpload extends hPlugin {

    private $hFinder;

    public function hConstructor()
    {
        $this->hFinder = $this->library('hFinder');

        $this->getPluginFiles();

        $this->hFileDocument .= $this->hFinder->getBottomBox(
            'hFinderUpload',
            'Upload a File',
            $this->getTemplate(
                'Upload',
                array(
                    'hFinderUploadAction'        => $this->hFinderUploadAction('/hFile/upload'),
                    'hFinderUploadActivity'      => '/images/themes/aqua/activity/upload.gif',
                    'hFinderUploadWorldRead'     => !$this->hFinderUploadSetAutoAccess(false),
                    'hFinderUploadSetAutoAccess' => $this->hFinderUploadSetAutoAccess(false),
                    'hFileSystemAllowDuplicates' => $this->hFileSystemAllowDuplicates(0)? 1 : 0
                )
            )
        );
    }
}

?>