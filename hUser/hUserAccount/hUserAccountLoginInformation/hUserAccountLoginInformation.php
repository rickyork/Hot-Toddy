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

class hUserAccountLoginInformation extends hPlugin {

    private $hForm;
    private $hUserValidation;
    private $hUserLogin;
    private $hUserDatabase;

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        $this->hFileDocumentSelector = '';

        if ($this->isLoggedIn())
        {
            $this->hForm = $this->library('hForm');
            $this->hUserValidation = $this->library('hUser/hUserValidation');
            $this->hUserDatabase = $this->database('hUser');
            $this->getPrivateForm();

            $this->loginForm();

            if ($this->hForm->renderMode('verify'))
            {
                $this->hUserDatabase->save((int) $_SESSION['hUserId'], $_POST['hUserName'], $_POST['hUserEmail'], $_POST['hUserPassword']);
                header('Location: '.$this->href($this->getFilePathByPlugin('hUser/hUserAccount')));
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    public function loginForm()
    {
        $form = $this->hForm;

        $form->addDiv('hUserAccountLoginInformationDiv');

        $this->hUserValidation->setPassword($_POST['hUserPassword']);
        $this->hUserValidation->setEmailAddress($_POST['hUserEmail']);

        $form->addFieldset('Account Information', '100%', '175px,auto');

        $form->addRequiredField('Please create a screen name.');

        if (isset($_POST['hUserName']) && $_POST['hUserName'] != $this->user->getUserName())
        {
            $form->addValidationByCallback(
                'The screen name you created is already in use, please select another.',
                $this->hUserValidation,
                'isUniqueUserName'
            );
        }

        $form->addValidationByCallback(
            'The screen name you selected contains invalid characters, or is too short, or is too long, please try again.',
            $this->hUserValidation,
            'isValidUserName'
        );

        $form->addTextInput('hUserName', 'e:Create a Screen Name:<br /><i>e.g., John1985</i>', '25,50', isset($_POST['hUserName'])? $_POST['hUserName'] : $this->user->getUserName());

        if (!empty($_POST['hUserPassword']))
        {
            $form->addRequiredField('Please create a password to use for logging onto this website.');
            $form->addValidationByComparison('Please create a password six characters or more in length.', '>=', 6);
            $form->addValidationByComparison('Please create a password less than 40 characters in length.', '<=', 40);
        }

        $form->setAttribute('autocomplete', 'off');
        $form->addPasswordInput('hUserPassword', 'p:Create a Password:', '20,40');

        if (!empty($_POST['hUserPassword']))
        {
            $form->addRequiredField('Please confirm the password you created by entering it again.');
            $form->addValidationByCallback(
                'Passwords do not match.',
                $this->hUserValidation,
                'confirmPasswordMatches'
            );
        }

        $form->addPasswordInput('hUserPasswordConfirm', 'm:Confirm Your Password:', '20,40');

        $form->addRequiredField('Please enter a valid email address.');

        if (isset($_POST['hUserEmail']) && $_POST['hUserEmail'] != $this->user->getUserEmail())
        {
            $form->addValidationByCallback(
                'The email address you entered is not valid.',
                $this->hUserValidation,
                'isValidEmailAddress'
            );
            $form->addValidationByCallback(
                'The email address you entered has already been registered.',
                $this->hUserValidation,
                'isUniqueEmailAddress'
            );
        }

        $form->addEmailInput('hUserEmail', 'l:Your Email Address:', '35,50', isset($_POST['hUserEmail'])? $_POST['hUserEmail'] : $this->user->getUserEmail());

        $form->addRequiredField('Please confirm your email address by entering it again.');
        if (isset($_POST['hUserEmail']) && $_POST['hUserEmail'] != $this->user->getUserEmail())
        {
            $form->addValidationByCallback(
                'Email confirmation does not match the original email address.',
                $this->hUserValidation,
                'confirmEmailMatches'
            );
        }

        $form->addEmailInput('hUserEmailConfirm', 'n:Confirm Your Email Address:', '35,50', isset($_POST['hUserEmailConfirm'])? $_POST['hUserEmailConfirm']: $this->user->getUserEmail());

        $this->hForm->addTableCell('');
        $form->addSubmitButton('hUserAccountLoginInformationSubmit', 'Save');

        //$form->addSubmitButton('hUserRegister', 'Create Account', 2);
       // $form->setFormAttribute('action', $this->absolutePathToSelf());

        $this->hFileDocument = $form->getForm('hUserRegister');
    }
}

?>