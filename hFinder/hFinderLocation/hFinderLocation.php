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

class hFinderLocation extends hPlugin {

    public function hConstructor()
    {
        $this->getPluginFiles();
    }

    public function getLocationTemplate()
    {
        if (isset($_GET['path']) && isset($_GET['setDefaultPath']))
        {
            $path = $_GET['path'];
        }
        else if ($this->inGroup('root'))
        {
            $path = '/';
        }
        else
        {
            $path = $this->hFinderDefaultPath('/');
        }

        return $this->getTemplate(
            'Location',
            array(
                'hFinderDefaultPath' => $path,
                'hFinderDiskName'    => $this->hFinderDiskName($this->hServerHost)
            )
        );
    }
}

?>