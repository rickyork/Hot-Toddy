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