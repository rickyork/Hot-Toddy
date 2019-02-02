<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Toolbar
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

class hFinderToolbar extends hPlugin {

    private $hFinderLocation;

    public function hConstructor()
    {
        $this->hFinderLocation = $this->plugin('hFinder/hFinderLocation');

        $this->hFileDocument .= $this->getTemplate(
            'Toolbar',
            array(
                'hFinderActionsMenu' => $this->hFinderActionsMenu(true) || $this->inGroup('root'),
                'hFinderLocation'    => $this->hFinderLocation->getLocationTemplate()
            )
        );

        $this->getPluginFiles();
        $this->getPluginCSS('ie6');
        $this->getPluginCSS('ie7');
    }
}

?>