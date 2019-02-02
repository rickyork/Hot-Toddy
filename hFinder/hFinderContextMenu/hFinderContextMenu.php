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

class hFinderContextMenu extends hPlugin {

    private $hFinderLabel;

    public function hConstructor()
    {
        $this->hFinderLabel = $this->library('hFinder/hFinderLabel');

        $this->getPluginFiles();
        $this->getPluginCSS('ie6');
        $this->getPluginCSS('ie7');

        $this->hFinderContextMenu = $this->getTemplate(
            'Context Menu',
            array(
                'hFinderLabels' => $this->hFinderLabel->get(),
                'isRoot' => $this->inGroup('root'),
                'hFilePasswordsEnabled' => $this->hFilePasswordsEnabled(false)
            )
        );
    }
}

?>