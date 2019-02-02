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

class hUserLogin extends hPlugin {

    public function hConstructor()
    {
        $this->hUserLoginForm = true;

        if (!$this->hUserRegisterRedirect(null))
        {
            $this->hUserRegisterRedirect = $this->hFilePath;
        }

        if (!$this->isLoggedIn())
        {
            if (isset($_POST['hUserLoginExists']) && empty($_POST['hUserLoginExists']))
            {
                $this->plugin('hUser/hUserRegister');
            }
            else
            {
                //$this->getPluginCSS();
                $this->hFileTitle = $this->hUserLoginTitle('Sign In');
                $this->hFileDocument = $this->getLoginForm();
            }
        }
    }
}

?>