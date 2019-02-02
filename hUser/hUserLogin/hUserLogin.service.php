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

class hUserLoginService extends hService {

    private $hUserDatabase;
    private $hUserLogin;
    private $hUserLoginActivation;

    public function hConstructor()
    {
        $this->hUserDatabase = $this->database('hUser');
    }

    public function activateUser()
    {
        $userName = $this->get('userName', '');
        $userConfirmation = trim($this->get('userConfirmation', ''));

        if (empty($userName) || empty($userConfirmation))
        {
            $this->JSON(-5);
            return;
        }

        if ($this->hUserDatabase->userNameExists($userName))
        {
            if ($this->hUserDatabase->activateUser($userName, $userConfirmation))
            {
                $this->JSON(1);
                return;
            }
        }

        $this->JSON(0);
        return;
    }

    public function resendActivation()
    {
        $userName = $this->get('userName', '');

        if (empty($userName))
        {
            $this->JSON(-5);
            return;
        }

        if (isset($_GET['hUserLoginActivationURL']))
        {
            $this->hUserLoginActivationURL = htmlspecialchars_decode($_GET['hUserLoginActivationURL']);
        }

        if ($this->hUserDatabase->userNameExists($userName))
        {
            $this->hUserLoginActivation = $this->library('hUser/hUserLogin/hUserLoginActivation');
            $this->hUserLoginActivation->sendActivation($userName);

            $this->JSON(1);
            return;
        }

        $this->JSON(0);
        return;
    }

    public function updateEmailAddress()
    {
        if (empty($_GET['hUserName']) || empty($_GET['hUserEmailOld']) || empty($_GET['hUserEmailNew']) || empty($_GET['hUserPassword']))
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            $this->hUserDatabase->updateEmailAddress(
                $_GET['hUserName'],
                $_GET['hUserEmailOld'],
                $_GET['hUserEmailNew'],
                $_GET['hUserPassword']
            )
        );
    }

    public function login()
    {
        // This function doesn't appear to work...  not sure what the problem is.
        if (empty($_POST['hUserName']) || empty($_POST['hUserPassword']))
        {
            $this->JSON(-5);
            return;
        }

        $this->hUserLogin = $this->library('hUser/hUserLogin');

        $this->hUserLogin->login(
            $_POST['hUserName'],
            $_POST['hUserPassword']
        );

        $this->JSON(
            $this->hUserLoginFailureCode(1)
        );
    }

    public function isActivated()
    {
        if (empty($_POST['hUserName']))
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            (int) $this->hUserDatabase->isActivated(
                $_POST['hUserName']
            )
        );
    }

    public function getUserName()
    {
        if (empty($_POST['hUserEmail']) || empty($_POST['hUserPassword']))
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            $this->hUserDatabase->getUserName(
                $_POST['hUserEmail'],
                $_POST['hUserPassword']
            )
        );
    }

    public function getEmailAddress()
    {
        if (empty($_POST['hUserName']) || empty($_POST['hUserPassword']))
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            $this->hUserDatabase->getEmailAddress(
                $_POST['hUserName'],
                $_POST['hUserPassword']
            )
        );
    }

    public function getSecurityQuestion()
    {
        if (empty($_POST['hUserEmail']))
        {
            $this->JSON(-5);
            return;
        }

        if (false === ($securityQuestion = $this->hUserDatabase->getSecurityQuestionByEmail($_POST['hUserEmail'])))
        {
            $this->JSON(-24);
            return;
        }

        if (-1 === $securityQuestion)
        {
            $this->JSON(-25);
            return;
        }

        $this->JSON($securityQuestion);
    }
}

?>