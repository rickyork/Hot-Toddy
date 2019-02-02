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