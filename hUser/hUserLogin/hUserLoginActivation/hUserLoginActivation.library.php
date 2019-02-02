<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Login Activation Library
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

class hUserLoginActivationLibrary extends hPlugin {

    private $hUserDatabase;

    public function sendActivation($hUserName)
    {
        $this->hUser = $this->library('hUser');

        $hFrameworkNickName = $this->hFrameworkNickName($this->hFrameworkName);

        $this->hUserDatabase = $this->database('hUser');

        $hFileActivationURL = $this->hUserLoginActivationURL($this->getURL());

        $hFileActivationURL .= (!strstr($hFileActivationURL, '?')? '?' : '&');

        $this->sendMail(
            'hUserLoginActivation',
            array(
                'hServerHost'                 => $this->hServerHost,
                'hFilePath'                   => $this->hFilePath,
                'hFileActivationURL'          => $hFileActivationURL,
                'hFrameworkName'              => $this->hFrameworkName,
                'hFrameworkNickName'          => $hFrameworkNickName,
                'hContactDisplayName'         => $this->user->getFullName($hUserName),
                'hUserEmailAddress'           => $this->user->getUserEmail($hUserName),
                'hUserConfirmation'           => $this->hUserDatabase->getConfirmation($hUserName),
                'hUserName'                   => $hUserName,
                'hFrameworkAdministrator'     => $this->hFrameworkAdministrator
            )
        );
    }
}

?>