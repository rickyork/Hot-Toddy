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

class hApplicationLibrary extends hPlugin {

    private $ButtonShaded;
    private $UIPath = 'Application/ApplicationUI/ApplicationUI';

    public function hConstructor()
    {

    }

    public function includeLibrary($UIPlugin)
    {
        if (!class_exists('HApplicationUI'.$UIPlugin.'Library'))
        {
            $pluginPath = $this->UIPath.$UIPlugin;
            $this->$UIPlugin = $this->library($pluginPath);
        }
    }

    public function getButton($style, $label, $properties = array())
    {
        $this->includeLibrary('Button');

        $this->Button->{"get{$style}"}($label, $properties);
    }

    public function getListView()
    {

    }

    public function getTabView()
    {

    }
}

?>