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

class hUserRegister extends hPlugin {

    private $hForm;
    private $hUserValidation;
    private $hUserDatabase;
    private $hUserLogin;
    private $hContactForm;

    private $hUserRegisterPrepend;
    private $hUserRegisterAppend;
    private $hUserLoginActivation;

    private $hMail;
    private $form;

    function hConstructor()
    {
        $this->hUserValidation = $this->library('hUser/hUserValidation');
        $this->hContactForm = $this->library('hContact/hContactForm');
        $this->hUserLogin = $this->library('hUser/hUserLogin');
        $this->hUserDatabase = $this->database('hUser');

        $this->hFileDocumentSelector = '';

        $this->hFileDisableCache = true;
        $this->hFileEnableCache  = false;

        $this->getPrivateForm();

        $this->redirectIfSecureIsEnabled();

        $this->getPluginFiles();
        $this->hForm = $this->library('hForm');

        if ($this->hUserRegisterCallbackPlugin(null))
        {
            $this->plugin($this->hUserRegisterCallbackPlugin);
        }

        if (isset($_POST['hUserRegisterActivation']))
        {
            $this->activationForm();
        }
        else
        {
            $this->form();

            if ($this->hForm->passesValidation())
            {
                $this->insertRegistration();
            }
            else
            {
                if ($this->hUserRegisterDefaultMessage(null))
                {
                    $copy = $this->hUserRegisterDefaultMessage(null);
                }
                else if ($this->hPlugin != 'hUser/hUserRegister')
                {
                    $copy = $this->hUserRegisterMessage($this->getTemplate('Copy'));
                }
                else
                {
                    $copy = '';
                }

                $this->hFileDocument = $this->getTemplate(
                    'Form',
                    array(
                        'hUserRegisterMessage'    => $copy,
                        'hUserRegisterFormAction' => $this->hUserRegisterFormAction($this->getURL()),
                        'hUserRegisterForm'       => $this->form,
                        'hUserRegisterRenderForm' => $this->hForm->renderMode('form'),
                        'hUserRegisterButtons'    => $this->hUserRegisterButtons(null)
                    )
                );
            }
        }
    }

    private function redirect()
    {
        header(
            'Location: '.
            $this->href(
                $this->hUserRegisterRedirect(
                    $this->getFilePathByPlugin('hUser/hUserAccount')
                )
            )
        );

        exit;
    }

    private function form()
    {
        if (isset($_SESSION['hUserRegisterForm']) && !isset($_POST['hUserRegister']))
        {
            $_POST = $_SESSION['hUserRegisterForm'];
        }

        $form =& $this->hForm;

        if ($this->hUserRegisterPrependPlugin)
        {
            $this->hUserRegisterPrepend = $this->plugin($this->hUserRegisterPrependPlugin);
        }

        if ($this->hUserRegisterPrepend && method_exists($this->hUserRegisterPrepend, 'setForm'))
        {
            $this->hUserRegisterPrepend->setForm($form, false);
        }

        $this->hUserValidation->setPassword($_POST['hUserPassword']);
        $this->hUserValidation->setEmailAddress($_POST['hUserEmail']);

        $form
            ->addDiv('hUserRegister')
            ->addFieldset(
                'Account Information',
                '100%',
                '175px,auto'
            )
            ->addRequiredField('Please create a screen name.')

            ->addValidationByCallback(
                'The screen name you created is already in use, please select another.',
                $this->hUserValidation,
                'isUniqueUserName'
            )

            ->addValidationByCallback(
                'The screen name you selected contains invalid characters, is too short, or is too long. Please try again.',
                $this->hUserValidation,
                'isValidUserName'
            )

            ->addTextInput(
                'hUserName',
                'Create a Screen Name:<br /><i>e.g., John1985</i>',
                '25,50'
            )

            ->addRequiredField('Please create a password to use for logging into this website.');

        if ($this->hUserPasswordMinimumLength(false))
        {
            $form->addValidationByComparison('Please create a password six characters or more in length.', '>=', $this->hUserPasswordMinimumLength(6));
        }

        if ($this->hUserPasswordMaximumLength(false))
        {
            $form->addValidationByComparison('Please create a password less than 40 characters in length.', '<=', $this->hUserPasswordMaximumLength(40));
        }

        $form
            ->addPasswordInput(
                'hUserPassword',
                'Create a Password:',
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

            ->addRequiredField('Please enter a valid email address.')

            ->addValidationByCallback(
                'The email address you entered is not valid.',
                $this->hUserValidation,
                'isValidEmailAddress'
            )

            ->addValidationByCallback(
                'The email address you entered has already been registered.',
                $this->hUserValidation,
                'isUniqueEmailAddress'
            )

            ->addTextInput(
                'hUserEmail',
                'Your Email Address:',
                '25,40',
                isset($_REQUEST['username'])? $_REQUEST['username'] : ''
            )

            ->addRequiredField('Please confirm your email address by entering it again.')

            ->addValidationByCallback(
                'Email confirmation does not match the original email address.',
                $this->hUserValidation,
                'confirmEmailMatches'
            )

            ->addTextInput(
                'hUserEmailConfirm',
                'Confirm Your Email Address:',
                '25,40'
            )

            ->addHiddenInput('hUserLoginExists', '0');

        if ($this->hUserRegisterEnableSecurityQuestion(false))
        {
            $form
                ->addTableCell('')
                ->addTableCell(
                    $this->getTemplate('Security Question')
                )

                ->addRequiredField('Please select a security question.')

                ->addSelectInput(
                    'hUserSecurityQuestionId',
                    'Security Question:',
                    $this->hUserDatabase->getSecurityQuestions()
                )

                ->addRequiredField('Please provide a security answer.')

                ->addTextInput(
                    'hUserSecurityAnswer',
                    'Security Answer:',
                    '25,50'
                );
        }

        $form->hFormElement = false;

        //$form->addSubmitButton('hUserRegister', 'Create Account', 2);

        if ($this->hUserRegisterContactFormPlugin(nil))
        {
            $this->plugin($this->hUserRegisterContactFormPlugin);
        }
        else
        {
            $this->hContactForm->addContactForm(false);
        }

        if ($this->hUserRegisterAppendPlugin)
        {
            $this->hUserRegisterAppend = $this->plugin($this->hUserRegisterAppendPlugin);
        }

        if ($this->hUserRegisterAppend && method_exists($this->hUserRegisterAppend, 'setForm'))
        {
            $this->hUserRegisterAppend->setForm($form, true);
        }

        //$this->hFileTitle = $this->hUserRegisterTitle;

        //$this->hFileTitle = 'Create a Free '.$this->hFrameworkName.' Web Account';

        $this->form = $form->getForm('hUserRegister');
    }

    private function activationForm($reset = false)
    {
        if (!empty($_POST['hUserConfirmation']))
        {
            $this->hUserRegisterConfirmationIsValid = $this->hUserDatabase->activateUser(
                $_POST['hUserName'],
                $_POST['hUserConfirmation']
            );
        }

        $form =& $this->hForm;

        if ($reset)
        {
            $form->resetForm();
        }

        $form
            ->addDiv('hUserRegister')
            ->addFieldset(
                'Account Activation',
                '100%',
                '175px,auto'
            )

            ->defineCell(2)
            ->addTableCell(
                $this->getTemplate(
                    'Activation',
                    array(
                        'hUserEmail' => $_POST['hUserEmail']
                    )
                )
            )

            ->addHiddenInput(
                'hUserLoginExists',
                '0'
            )
            ->addHiddenInput(
                'hUserName',
                $_POST['hUserName']
            )
            ->addHiddenInput(
                'hUserEmail',
                $_POST['hUserEmail']
            )
            ->addHiddenInput(
                'hUserPassword',
                $_POST['hUserPassword']
            )

            ->addRequiredField('Please enter a valid username.')
            ->addValidationByCallback(
                'The user name you specified is not valid.',
                $this->hUserDatabase,
                'userNameExists'
            )
            ->addTextInput(
                'hUserName',
                'Username:',
                '25,40',
                $_POST['hUserName']
            )

            ->addRequiredField('Please enter an activation code.')
            ->addValidationByCallback(
                'The activation code is not valid.',
                $this,
                'confirmationIsValid'
            )

            ->addTextInput(
                'hUserConfirmation',
                'Activation Code:',
                '25,40'
            )

            ->addTableCell('')
            ->addSubmitButton(
                'hUserRegisterActivation',
                'Activate Account'
            );

        $this->hFileDocument = $form->getForm('hUserRegisterActivation');

        if ($form->passesValidation())
        {
            if ($this->hUserRegisterConfirmationIsValid(false))
            {
                $this->login();
            }
        }
    }

    public function confirmationIsValid($confirmation)
    {
        return $this->hUserRegisterConfirmationIsValid(false);
    }

    private function insertRegistration()
    {
        $_POST['hContactDisplayName'] = $_POST['hContactFirstName'].' '.$_POST['hContactLastName'];

        // Used to make sure that POST data only contains the fields
        // intended for use in a query
        $fieldNames = $this->hForm->getFieldNames();

        $userReferredBy = $this->get('userId', 0);
        $userRegistrationTracker = $this->get('fileId', 0);

        // 1. Make User Account
        // 2. Save Contact Record
        // 3. Save Address Record
        $userId = $this->hUserDatabase->save(
            0,
            $_POST['hUserName'],
            $_POST['hUserEmail'],
            $_POST['hUserPassword'],
            false,
            $userReferredBy,
            $userRegistrationTracker
        );

        if ($this->hUserRegisterEnableSecurityQuestion(false))
        {
            $this->hUserDatabase->saveSecurityQuestion(
                (int) $userId,
                (int) $_POST['hUserSecurityQuestionId'],
                $_POST['hUserSecurityAnswer']
            );
        }

        if ($this->hUserRegisterDefaultGroup(''))
        {
            $this->hUserDatabase->addUserToGroup(
                $this->hUserRegisterDefaultGroup,
                $userId
            );
        }

        $this->hContactForm->saveContactForm($userId);

        if ($this->hUserRegisterOnRegister(nil))
        {
            $plugin = $this->plugin($this->hUserRegisterOnRegister);

            if (method_exists($plugin, 'onRegister'))
            {
                $plugin->onRegister($userId);
            }
            else
            {
                $this->warning(
                    'Failed to execute onRegister event, method "onRegister" does not exist in the '.
                    'plugin "'.$this->hUserRegisterOnRegister.'".',
                    __FILE__,
                    __LINE__
                );
            }
        }

        if ($this->hUserActivation(false))
        {
            $this->hUserLoginActivation = $this->library('hUser/hUserLogin/hUserLoginActivation');
            $this->hUserLoginActivation->sendActivation($_POST['hUserName']);

            $this->activationForm(true);
        }
        else
        {
            $this->login();
        }
    }

    private function login()
    {
        $this->hMail = $this->library('hMail');

        $user = ' Username: "'.$_POST['hUserName'].'" Password: "'.$_POST['hUserPassword'].'"';

        // Login!
        if ($this->hUserLogin->login($_POST['hUserName'], $_POST['hUserPassword']))
        {
            $contactData = $this->hContactForm->getData();

            if (!is_array($contactData))
            {
                $contactData = array();
            }

            $data = array_merge(
                $_POST,
                $contactData
            );

            if ($this->hMail->templateExists('hUserRegisterAutoReply'))
            {
                $this->hMail->getMessageFromTemplate(
                    'hUserRegisterAutoReply',
                    $data
                );
            }

            if ($this->hUserRegisterMailer(null))
            {
                if ($this->hMail->templateExists($this->hUserRegisterMailer))
                {
                    $this->hMail->getMessageFromTemplate(
                        $this->hUserRegisterMailer,
                        $data
                    );
                }
            }
        }
        else
        {
            $reason = false;

            if ($this->hUserAuthenticateLoginFailed(false))
            {
                $reason = true;

                $this->warning(
                    'Unable to complete registration, login failed!'.$user,
                    __FILE__,
                    __LINE__
                );
            }

            if ($this->hUserAuthenticateInvalidPassword(false))
            {
                $reason = true;

                $this->warning(
                    'The supplied password was invalid.'.$user,
                    __FILE__,
                    __LINE__
                );
            }

            if ($this->hUserAuthenticateInvalidAccount(false))
            {
                $reason = true;

                $this->warning(
                    'The supplied account was invalid.'.$user,
                    __FILE__,
                    __LINE__
                );
            }

            if ($this->hUserAuthenticateActivation(false))
            {
                $reason = true;

                $this->warning(
                    'The supplied account is not activated.'.$user,
                    __FILE__,
                    __LINE__
                );
            }

            if (!$reason)
            {
                $this->warning(
                    'Login failed for no apparent reason. Dig this, it may mean that the '.
                    'reason for failure is not yet defined. '.$user,
                    __FILE__,
                    __LINE__
                );
            }
        }

        $this->redirect();
    }

    private function loginError($error)
    {
        $this->hFileDocument .= "<p class='hUserRegisterLoginError'>{$error}</p>\n";
    }
}

?>