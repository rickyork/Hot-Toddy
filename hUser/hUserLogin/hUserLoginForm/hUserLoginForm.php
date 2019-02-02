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

class hUserLoginForm extends hPlugin implements hUserLoginFormTemplate {

    public function getLoginForm($isDialogue = false)
    {
        $this->getPluginCSS();

        return $this->getTemplate(
            'Form',
            array(
                'hUserLoginFormTags'                     => !$isDialogue,
                'hUserLoginFormAction'                   => $this->absolutePathToSelf(),
                'hUserLoginMessage'                      => $this->hUserLoginMessage(null),
                'hFrameworkName'                         => $this->hFrameworkName,
                'hUserLoginForgottenPasswordPath'        => $this->getFilePathByPlugin('hUser/hUserForgottenPassword'),
                'hUserLoginInvalidAccount'               => $this->hUserLoginInvalidAccount(false),
                'hUserLoginInvalidPassword'              => $this->hUserLoginInvalidPassword(false),
                'hUserLoginNotActivated'                 => $this->hUserLoginNotActivated(false),
                'hUserLoginDisabled'                     => $this->hUserLoginDisabled(false),
                'hUserLoginTooManyFailedAttempts'        => $this->hUserLoginTooManyFailedAttempts(false),
                'hUserLoginFailedAttemptResetThreshold'  => $this->hUserLoginFailedAttemptResetThreshold(10),
                'hUserLoginMaximumFailedAttempts'        => $this->hUserLoginMaximumFailedAttempts(3),
                'hUserLoginFormRegistrationOption'       => $this->hUserLoginFormRegistrationOption(true),
                'hUserLoginUserNameLabel'                => $this->hUserLoginUserNameLabel('User Name or Email Address:'),
                'hUserLoginPasswordLabel'                => $this->hUserLoginPasswordLabel('Password:'),
                'hUserLoginButtonSignInLabel'            => $this->hUserLoginButtonSignInLabel('Continue')
            )
        );
    }
}

?>