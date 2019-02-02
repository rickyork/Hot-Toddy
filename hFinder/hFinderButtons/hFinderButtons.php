<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Buttons
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

interface hFinderButtonTemplate {
    public function get();
}

class hFinderButtons extends hPlugin {

    private $hFinderButtons;
    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        $this->getPluginFiles();

        $html = '';

        if ($this->hFinderButtonsPlugin(false))
        {
            $this->hFinderButtons = $this->plugin($this->hFinderDialogueChoosePlugin);
            $html .= $this->hFinderButtons->get();
        }
        else
        {
            $html = $this->getTemplate(
                'Buttons',
                array(
                    'hFinderButtonUpload'         => $this->hFinderButtonUpload(true),
                    'hFinderButtonsRight'         => $this->hFinderButtonsRight(true),
                    'hFinderButtonEditFile'       => $this->hFinderButtonEditFile(true),
                    'hFinderButtonEditProperties' => $this->hFinderButtonEditProperties(false),
                    'hFinderButtonDelete'         => $this->hFinderButtonDelete(true)
                )
            );
        }

        if ($this->hFinderBodyClass)
        {
            $this->hFinderBodyClass .= ' hFinderButtons';
        }
        else
        {
            $this->hFinderBodyClass = 'hFinderButtons';
        }

        if (!$this->hFinderBodyId)
        {
            $this->hFinderBodyId = 'hFinderButtonDialogue';
        }

        $this->hFileDocument .= $html;
    }
}

?>