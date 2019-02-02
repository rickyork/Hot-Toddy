<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Forgotten Password
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

class hUserForgottenPassword extends hPlugin {

    private $hForm;
    private $hUserLogin;
    private $hUserDatabase;
    private $hUserValidation;

    public function hConstructor()
    {
        $this->getPluginFiles();

        $this->hUserLogin = $this->library('hUser/hUserLogin');
        $this->hUserDatabase = $this->database('hUser');
        $this->hForm = $this->library('hForm');

        if (isset($_SESSION['hUserSecurityQuestionAttempts']) && $_SESSION['hUserSecurityQuestionAttempts'] > 3)
        {
            $hUserSecurityQuestion = $this->hUserDatabase->getSecurityQuestionByEmail(
                $_POST['hUserEmail']
            );

            $this->hFileDocument = $this->getTemplate(
                "Too Many Failed Attempts",
                array(
                    'hUserSecurityQuestion' => $hUserSecurityQuestion
                )
            );

            return;
        }

        if (!isset($_GET['token']))
        {
            $this->getPrivateForm();
            $this->form();

            if ($this->hForm->renderMode('verify'))
            {
                $password = $this->hUserLogin->generatePassword();

                $this->hUsers->update(
                    array(
                        'hUserConfirmation' => $password
                    ),
                    array(
                        'hUserEmail' => $_POST['hUserEmail']
                    )
                );

                $this->sendMail(
                    'hUserForgottenPasswordToken',
                    array(
                        'emailAddress' => $_POST['hUserEmail'],
                        'passwordToken' => $password,
                        'emailAddressEncoded' => urlencode($_POST['hUserEmail'])
                    )
                );

                $this->hFileDocument = $this->getTemplate(
                    'Reset Confirmation',
                    array(
                        'hUserEmail' => $_POST['hUserEmail']
                    )
                );
            }
        }
        else
        {
            $exists = $this->hUsers->selectExists(
                'hUserConfirmation',
                array(
                    'hUserEmail' => $_GET['email'],
                    'hUserConfirmation' => $_GET['token']
                )
            );

            if ($exists)
            {
                if ($this->hUserForgottenPasswordSetRandom(false))
                {
                    $this->generateRandomPassword();
                }
                else
                {
                    $this->setNewPasswordForm();

                    if ($this->hForm->passedValidation())
                    {
                        $this->setPassword($_POST['hUserPassword']);
                        $this->hFileDocument = $this->getTemplate('Reset Password');
                    }
                }
            }
            else
            {
                $this->hFileDocument = $this->getTemplate('Reset Failure');
            }
        }
    }

    public function setPassword($password)
    {
        $this->hUsers->update(
            array(
                'hUserPassword' => $this->hUserLogin->encryptPassword($password)
            ),
            array(
                'hUserEmail' => $_GET['email']
            )
        );
    }

    public function setNewPasswordForm()
    {
        $this->hUserValidation = $this->library('hUser/hUserValidation');

        $this->hUserValidation->setPassword($_POST['hUserPassword']);

        $form = &$this->hForm;

        $form
            ->addDiv('hUserForgottenPasswordDiv')
            ->addFieldset(
                'Set a New Password',
                '100%',
                '175px,auto'
            )
            ->addRequiredField('Please create a password to use for logging into this website.');

        if ($this->hUserPasswordMinimumLength(false))
        {
            $form->addValidationByComparison(
                'Please create a password six characters or more in length.',
                '>=',
                $this->hUserPasswordMinimumLength(6)
            );
        }

        if ($this->hUserPasswordMaximumLength(false))
        {
            $form->addValidationByComparison(
                'Please create a password less than 40 characters in length.',
                '<=',
                $this->hUserPasswordMaximumLength(40)
            );
        }

        $form
            ->addPasswordInput(
                'hUserPassword',
                'p:Create a Password:',
                '20'
            )
            ->addRequiredField('Please confirm the password you created by entering it again.')
            ->addValidationByCallback(
                'Passwords do not match.',
                $this->hUserValidation,
                'confirmPasswordMatches'
            )
            ->addPasswordInput(
                'hUserPasswordConfirm',
                'Confirm Your Password:',
                '20,40'
            )
            ->addTableCell('')
            ->addSubmitButton(
                'hUserFormSubmit',
                'Submit'
            );

        $this->hFileDocument = $form->getForm('hUserForgottenPassword');
    }

    public function generateRandomPassword()
    {
        $password = $this->hUserLogin->generatePassword();

        $this->setPassword($password);

        $this->sendMail(
            'hUserForgottenPassword',
            array(
                'emailAddress' => $_GET['email'],
                'password' => $password
            )
        );

        $this->hFileDocument = $this->getTemplate(
            'Random Password',
            array(
                'hUserEmail' => $_GET['email']
            )
        );
    }

    public function form()
    {
        $form = &$this->hForm;

        $form
            ->addDiv('hUserForgottenPasswordDiv')
            ->addFieldset(
                'Registration Information',
                '100%',
                '175px,auto'
            )

            ->addRequiredField('You did not provide an email address.');

            ->addValidationByCallback(
                "We're sorry, the email address you entered was not found. If you feel this is an error, please email \n".
                "<a href='mailto:". $this->hFrameworkAdministrator."'>".$this->hFrameworkAdministrator."</a>.\n",
                $this,
                'isValidEmailAddress'
            )

            ->addTextInput(
                'hUserEmail',
                'Your Email Address:',
                '25,40'
            )

            ->addFieldset(
                'Security Question',
                '100%',
                '175px,auto',
                'hUserSecurityQuestionFieldset'
            )

            ->addTableCell(
                $this->getTemplate('Security Question'),
                2
            );

        $label = 'Security Answer:';

        // When the form is submitted, if there is a security question defined, make that field required.
        if (!empty($_POST['hUserEmail']))
        {
            $hUserSecurityQuestion = $this->hUserDatabase->getSecurityQuestionByEmail($_POST['hUserEmail']);

            if ($hUserSecurityQuestion !== -1 && $hUserSecurityQuestion !== false)
            {
                $label = $hUserSecurityQuestion;

                $form->addRequiredField('Please answer the following security question to continue.');

                $form->addValidationByCallback(
                    "You did not answer the question correctly, please try again. ".
                    "You will be allowed only <b>three</b> failed attempts.",
                    $this,
                    'validateSecurityQuestion'
                );

                // Unhide the fieldset
                $this->hFileCSS .= $this->getTemplate('Security Question CSS');
            }
        }

        $form->addTextInput(
            'hUserSecurityAnswer',
            $label,
            '25,50'
        );

        $form->addFieldset('', '100%', '175px,auto');

        $form->addTableCell('');
        $form->addSubmitButton('hUserFormSubmit', 'Submit');

        $this->hFileDocument =
            $this->getTemplate('Reset Copy').
            $form->getForm('hUserForgottenPassword');
    }

    public function validateSecurityQuestion($hUserSecurityAnswer)
    {
        $hUserSecurityQuestion = $this->hUserDatabase->getSecurityQuestionByEmail($_POST['hUserEmail']);

        if ($hUserSecurityQuestion === false)
        {
            $this->logFailedSecurityQuestion();
            return false;
        }

        if ((int) $hUserSecurityQuestion === -1)
        {
            return true;
        }

        $response = $this->hUserDatabase->isSecurityAnswer($_POST['hUserEmail'], $hUserSecurityAnswer);

        if (!$response)
        {
            $this->logFailedSecurityQuestion();
        }

        return $response;
    }

    public function logFailedSecurityQuestion()
    {
        if (!isset($_SESSION['hUserSecurityQuestionAttempts']))
        {
            $_SESSION['hUserSecurityQuestionAttempts'] = 1;
        }
        else
        {
            $_SESSION['hUserSecurityQuestionAttempts']++;
        }
    }

    public function isValidEmailAddress($userEmail)
    {
        return $this->hUsers->selectExists(
            'hUserId',
            array(
                'hUserEmail' => $userEmail
            )
        );
    }
}

?>