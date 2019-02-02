<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Passwords
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

class hFinderPasswords extends hPlugin {

    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        $this->getPluginFiles();

        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $this->hDialogue->newDialogue('hFinderPasswords');
        $this->hDialogue->setForm($this->hForm);

        $this->hForm->addDiv('hFinderPasswords');
        $this->hForm->addFieldset('Password', '100%', '30%,70%');

        $this->hForm->addPasswordInput('hFilePassword', 'p:Password:', '25,25');
        $this->hForm->addSelectInput(
            'hFilePasswordLifetime',
            'e:This Password Expires:',
            array(
                0  => 'Never',
                24 => '24 Hours',
                48 => '48 Hours',
                72 => '72 Hours'
            )
        );

        $this->hForm->addSelectInput(
            'hFilePasswordExpirationAction',
            'e:When this Password Expires:',
            array(
                0 => 'Do nothing',
                1 => 'Delete this password',
                2 => 'Delete the protected file'
            )
        );

        $this->hForm->addSelectInput(
            'hFilePasswordRequired',
            'e:Ask for this password:',
            array(
                0 => 'Only if it\'s needed',
                1 => 'Every time'
            )
        );

        $this->hForm->hFormElement = false;
        $form = $this->hForm->getForm('hFinderPasswordsForm');

        $this->hFileDocument .= $this->hDialogue->getDialogue($this->getTemplate('Passwords'));

        $this->hDialogue->newDialogue('hFinderPassword');

        $this->hFileDocument .= $this->hDialogue->getDialogue($form.$this->getTemplate('Buttons'));
    }
}

?>