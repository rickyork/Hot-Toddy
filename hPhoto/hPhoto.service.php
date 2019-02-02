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

class hPhotoService extends hService {

    private $hPhoto;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }
    
        $this->hPhoto = $this->library('hPhoto');
    }

    public function getPhotos()
    {
        if (!isset($_GET['path']))
        {
            $this->JSON('-5');
            return;
        }
        
        $path = $_GET['path'];
        
        if ($path == '/Categories/.Photos/Photos')
        {
            $path = '/Categories/.Photos';
        }

        $this->HTML($this->hPhoto->getPhotos($path));
    }

    public function saveSliderPosition()
    {
        if (!isset($_GET['slider']) || empty($_GET['slider']))
        {
            $this->JSON(-5);
            return;
        }
    
        $this->user->saveVariable('hPhotoSliderPosition', (int) $_GET['slider']);
    }
    
    public function crop()
    {
        
    }
    
    public function resize()
    {
        
    }
}

?>