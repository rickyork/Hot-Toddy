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

class hFinderDialogueImage extends hPlugin implements hFinderDialogueTemplate {

    public function hConstructor()
    {
        $this->hFileTitle = 'Select an Image...';

        // $this->hFileJavaScript .=
        //  "\n<script type='text/javascript' src='{$this->hFrameworkLibraryRoot}/xinha/popups/popup.js'></script>\n";
        //  "<script type='text/javascript' src='{$this->hFrameworkLibraryRoot}/xinha/modules/InsertImage/insert_image.js'></script>\n";

        $this->getPluginFiles();

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
