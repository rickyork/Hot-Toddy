<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Location
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