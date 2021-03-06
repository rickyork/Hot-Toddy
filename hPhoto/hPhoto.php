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

class hPhoto extends hPlugin {

    private $hPhoto;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            $this->hFileCSS = '';
            $this->hFileJavaScript = '';
            $this->hFileTitlePrepend = '';
            $this->hFileTitleAppend  = '';

            $this->plugin('hApplication/hApplicationForm');
            
            $this->getPluginCSS('/Library/jQuery/jCrop', true);
            
            $this->jQuery('jCrop');

            $this->hPhoto = $this->library('hPhoto');

            $this->getPluginFiles('/hFinder/hFinderTree/hFinderTree', true);
            $this->getPluginFiles();
            
            $this->hFileFavicon = '/hPhoto/Pictures/iPhoto.ico';  
            
            $this->hFileDocument = $this->getTemplate(
                'Photo App',
                array(
                    'tree' => $this->hPhoto->getTree(),
                    'view' => $this->hPhoto->getView()
                )
            );
        }
        else
        {
            $this->notLoggedIn();
        }
    }
}


?>