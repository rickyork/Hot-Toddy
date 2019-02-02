<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Choose Dialogue
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

class hFinderDialogueChoose extends hPlugin implements hFinderDialogueTemplate {

    private $hFinderDialogueChoose;

    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        $this->getPluginFiles();
        $this->hFinderHasFiles = true;
        $this->hFileTitle = 'Choose...';

        $this->hFinderButtons = true;
        $this->hFinderButtonUpload = true;
        $this->hFinderButtonsRight = false;
    }

    public function getControls()
    {
        return $this->getTemplate('Buttons');
    }
}

?>