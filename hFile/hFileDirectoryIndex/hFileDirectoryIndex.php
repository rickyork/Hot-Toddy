<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Directory Index
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

class hFileDirectoryIndex extends hPlugin {

    private $hFinder;

    public function hConstructor()
    {
        $this->hFileTitle = $this->hFileDirectoryIndexPath;

        if ($this->hDirectories->hasPermission($this->hFileDirectoryIndexId, 'r'))
        {
            $this->hFinderLoadPluginFiles = false;

            $this->getPluginCSS();

            //$this->hFileCSS .= $this->getTemplate('CSS');

            //$this->hFileDocumentBodyClassName = 'hFinderFilesXDetails';

            $this->hFinder = $this->library('hFinder');

            $this->hFileDocument =
                $this->getTemplate(
                    'Copy',
                    array(
                        'hFileDirectoryIndexPath' => $this->hFileDirectoryIndexPath
                    )
                ).
                $this->hFinder->getDirectory(
                    $this->hFileDirectoryIndexPath,
                    'XDetails',
                    'name',
                    true,
                    true
                );

            if (!$this->hFinderDirectoryCount && !$this->hFinderFileCount)
            {
                $this->hFileDocument = $this->getTemplate('No Files');
            }
        }
        else
        {
            // Reset the path to what it was.. otherwise you'll login at the
            // path of this plugin, instead of the listing path.
            $this->hFilePath = $this->hFileDirectoryIndexPath;

            if (!$this->isLoggedIn())
            {
                $this->notLoggedIn();
            }
            else
            {
                $this->notAuthorized();
            }
        }
    }
}

?>