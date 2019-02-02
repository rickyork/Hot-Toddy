<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Save As Dialogue
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

class hFinderDialogueSaveAs extends hPlugin implements hFinderDialogueTemplate {

    public function hConstructor()
    {
        $this->getPluginFiles();
        $this->hFinderHasFiles = true;
        $this->hFileTitle = 'Save As...';

        $this->hFinderButtons = true;
        $this->hFinderButtonUpload = true;
        $this->hFinderButtonsRight = false;
    }

    public function getControls()
    {
        return $this->getTemplate(
            'Buttons',
            array(
                'hFileName' => isset($_GET['hFileName'])? $_GET['hFileName'] : ''
            )
        );
    }
}

?>