<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Login
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