<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Select Link Dialogue
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

class hFinderDialogueLink extends hPlugin implements hFinderDialogueTemplate {

    public function hConstructor()
    {
        $this->hFileTitle = 'Select a File...';

        $this->hFileJavaScript .=
            "\n<script type='text/javascript' src='{$this->hFrameworkLibraryRoot}/fckeditor/editor/dialog/common/fck_dialog_common.js'></script>\n";

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