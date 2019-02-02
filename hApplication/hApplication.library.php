<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Application Library
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

class hApplicationLibrary extends hPlugin {

    private $ButtonShaded;
    private $UIPath = 'Application/ApplicationUI/ApplicationUI';

    public function hConstructor()
    {
        /**
          ButtonShaded
          ButtonShadedCombo
          ButtonAqua
          ButtonAquaCombo
        **/

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