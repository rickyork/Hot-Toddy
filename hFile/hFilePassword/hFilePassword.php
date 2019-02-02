<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Password Plugin
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

class hFilePassword extends hPlugin {

    private $hFilePasswordDatabase;

    public function hConstructor()
    {
        $this->hFileAuthorized = false;
        $this->hFilePassword = false;

        $this->hFilePasswordDatabase = $this->database('hFile/hFilePassword');

        $hasAccess = $this->hFiles->hasPermission($this->hFileId, 'r');

        if ($this->hFilePasswordDatabase->hasRequiredPasswords($this->hFileId) && $hasAccess)
        {
            $this->setVariables($data);
            $this->hFilePassword = true;

            // This file has an access code that is always applied regardless of
            // user privileges.  Prompt the user for the access code.
            if (isset($_POST['hFilePassword']))
            {
                $results = $this->hFilePasswordDatabase->getRequiredPasswords();

                foreach ($results as $data)
                {
                    $this->checkPassword($data['hFilePassword']);
                }

                if (!$this->hFileAuthorized)
                {
                    $this->passwordForm(true);
                }
            }
            else
            {
                $this->passwordForm();
            }
        }
        else if ($hasAccess)
        {
            // See if the user is privileged to access the file.
            // If so, grant them access without entering an access code.
            $this->hFileAuthorized = true;
        }
        else if (!$hasAccess)
        {
            // Last ditch effort,
            // See if there are any active access codes
            if ($this->hFilePasswordDatabase->hasOptionalPasswords($this->hFileId))
            {
                if (!$this->isSSLEnabled())
                {
                    header('Location: https://'.$this->hServerHost.$this->href($this->hFilePath));
                    exit;
                }

                $this->hFilePassword = true;

                if (isset($_POST['hFilePassword']))
                {
                    $results = $this->hFilePasswordDatabase->getOptionalPasswords($this->hFileId);

                    foreach ($results as $data)
                    {
                        $this->checkPassword($data['hFilePassword']);
                    }

                    if (!$this->hFileAuthorized)
                    {
                        $this->passwordForm(true);
                    }
                }
                else
                {
                    $this->passwordForm();
                }
            }
        }
    }

    private function checkPassword($password)
    {
        if (trim($password) === trim($_POST['hFilePassword']))
        {
            $this->hFileAuthorized = true;
        }
    }

    private function passwordForm($invalid = false)
    {
        $this->hFileTitle = 'This Document Requires a Password';

        $this->hFileDocument = $this->getTemplate(
            $this->hFilePasswordFormTemplate('Form'),
            array(
                'hFilePasswordFormAction' => $this->hFilePath
            )
        );
    }
}

?>