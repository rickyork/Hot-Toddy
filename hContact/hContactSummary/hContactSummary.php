<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Summary
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

class hContactSummary extends hPlugin {

    private $hForm;
    private $hDialogue;
    private $hContactApplication;

    public function hConstructor()
    {
        $this->getPluginFiles();
    }

    public function getSummary(hFormLibrary &$hForm, hDialogueLibrary &$hDialogue, &$hContactApplication)
    {
        $this->hForm = &$hForm;

        $this->hDialogue = &$hDialogue;
        $this->hContactApplication = &$hContactApplication;

        $this->hForm
            ->addDiv('hContactSummaryDiv', 'Contact Information')
            ->addFieldset('Contact', '100%', '100%', 'hContactFieldset')

            ->addTableCell('');

        if (method_exists($this->hContactApplication, 'getContactForm'))
        {
            $this->hContactApplication->getContactForm($this->hForm);
        }

        $this->hDialogue
            ->newDialogue('hContactSummary')
            ->setForm($this->hForm);

        $this->hDialogueDisableFocus = true;
        $this->hDialogueShadow = false;
        $this->hDialogueTitlebar = false;

        return $this->hDialogue->getDialogue();
    }
}

?>