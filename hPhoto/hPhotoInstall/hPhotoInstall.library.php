<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Photo Install Library
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

class hPhotoInstallLibrary extends hPlugin {

    private $hFile;
    private $hCategoryDatabase;
    private $hPhotoDatabase;

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');
        $this->hCategoryDatabase = $this->database('hCategory');
        $this->hPhotoDatabase = $this->database('hPhoto');

        $this->hFile->newDirectory('/Categories', '.Photos', 1);
        $this->hFile->newDirectory('/Categories/.Photos', 'Events', 1);
        $this->hFile->newDirectory('/Categories/.Photos', 'Photos', 1);

        $files = $this->hPhotoDatabase->getAllPhotoFiles();

        if (is_array($files))
        {
            foreach ($files as $fileId)
            {
                $this->hPhotoDatabase->addPhoto($fileId);
            }
        }
        else
        {
            $this->notice(
                "No photo files were found.",
                __FILE__,
                __LINE__
            );
        }
    }
}

?>