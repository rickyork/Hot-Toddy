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

class hFinderDialogueDirectory extends hPlugin implements hFinderDialogueTemplate {

    public function hConstructor()
    {
        if (isset($_GET['path']) && $_GET['path'] == '/Categories')
        {
            $this->hFileTitle = 'Select Category...';
            $this->hFinderTreeHomeDirectory = false;
        }
        else
        {
            $this->hFileTitle = 'Select Folder...';
        }

        $this->getPluginFiles();

        $this->hFinderHasSearch = false;
        $this->hFinderHasSideColumn = false;
        $this->hFinderHasTree  = true;
        $this->hFinderHasFiles = false;
    }

    public function getControls()
    {
        return $this->getTemplate(
            'Buttons',
            array(
                'item' => (isset($_GET['path']) && $_GET['path'] == '/Categories')? 'Category' : 'Folder'
            )
        );
    }
}

?>