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
#
# Creates a desktop application package of hFinder and all requisite files.
#

class hFinderDesktop extends hPlugin {

    private $hDesktopApplication;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            if ($this->inGroup('root'))
            {
                $this->getFinder();
            }
            else
            {
                $this->notAuthorized();
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    public function getFinder()
    {
        $this->hDesktopApplication = $this->library('hDesktopApplication');

        # Begin with an desktop application packaging of the default Finder App...
        #
        # To do this, request the finder document using a normal HTTP request to the
        # server.
        # Force the server to respond with the base directory selected (e.g., path=/
        # Parse all paths in HTML document
        # Gather all of the files in all of the associated documents and build a desktop
        # application package dynamically at
        #
        # /Install Folder/Applications/hFinder

        $hUserPassword = $this->hUsers->selectColumn('hUserPassword', 1);

        $path = 'http://'.$this->hServerHost.'/Applications/Finder?'.$this->getQueryString(
            array(
                'path' => '/',
                'hUserAuthenticationToken' => '1,'.$hUserPassword,
                'hDesktopApplication' => 1
            )
        );

        $document = file_get_contents($path);

        $this->hDesktopApplication->makePackage(
            $document,
            'hFinder',
            null
            //$this->getTemplateXML('hFinder-app')
        );

        $this->hDesktopApplication->addDocumentToPackage(
            '/Applications/Finder/Desktop%20Login.html',
            'index'
        );

        //echo htmlspecialchars($document);

        $this->hFileDocument = $this->getTemplate(
            'Desktop',
            array(
                'hFinderDesktopPackagePath' => $this->hDesktopApplication->getPackagePath()
            )
        );
    }
}

?>