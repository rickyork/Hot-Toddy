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

class hDashboardAccount extends hPlugin {

    private $hDashboard;
    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        $this->hDashboard = $this->library('hDashboard');

        if ($this->isLoggedIn())
        {
            $this->getPluginFiles();

            $contact = $this->contact->getFlatRecord();

            $this->hFileDocument = $this->getTemplate(
                'Account',
                array(
                    'contact' => $contact,
                    'userDialogue' => $this->getUserForm()
                )
            );
        }
        else
        {
            $this->notLoggedIn();
        }
    }
    
    private function getUserForm()
    {
        $this->hDialogue = $this->library('hDialogue');
        $this->hForm = $this->library('hForm');

        $this->hForm
            ->addDiv(
                'hDashboardAdminUserAccount',
                'Account'
            )
            ->addFieldset(
                'Account Information',
                '100%',
                '175px,'
            )
                ->addTextInput(
                    'hUserName',
                    'User Name:',
                    '25,255',
                    $this->hUser->getUserName()
                )
                ->addTextInput(
                    'hUserEmail',
                    'Email Address:',
                    '30,255',
                    $this->hUser->getUserEmail()
                )
                ->addPasswordInput(
                    'hUserOldPassword',
                    'Old Password:',
                    25
                )
                ->addPasswordInput(
                    'hUserPassword',
                    'New Password:',
                    25
                )
                ->addPasswordInput(
                    'hUserConfirmPassword',
                    'Confirm New Password',
                    25
                );

        $dialogue = $this->hDialogue
            ->newDialogue('hDashboardAdminUser')
            ->setForm($this->hForm)
            ->addButtons('Save', 'Cancel')
            ->getDialogue(nil, 'Modify User Account');

        $this->hForm->reset();

        return $dialogue;
    }
}

?>