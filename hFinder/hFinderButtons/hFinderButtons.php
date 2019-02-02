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