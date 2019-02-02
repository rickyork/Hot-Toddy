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

class hUserLogout extends hPlugin {

    private $hUserLogout;

    public function hConstructor()
    {
        $this->hUserLogout = $this->library('hUser/hUserLogout');
        $this->hUserLogout->logout();

        if (!isset($_GET['redirect']))
        {
            $_GET['redirect'] = '/';
        }

        $this->hFileDocument = $this->getTemplate('Logout');

        $this->hFileJavaScript .= $this->getTemplate(
            'Redirect',
            array(
                'hUserLogoutRedirectPath' => $this->href($_GET['redirect'])
            )
        );
    }
}

?>