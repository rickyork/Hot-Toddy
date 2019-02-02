<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Directory Shell
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

class hUserDirectoryShell extends hShell {

    private $hUserDirectory;

    public function hConstructor()
    {
        if ($this->shellArgumentExists('user', '--user') || $this->shellArgumentExists('password', '--password'))
        {
            $user = null;

            if ($this->shellArgumentExists('user', '--user'))
            {
                $user = $this->getShellArgumentValue('user', '--user');
            }
            else
            {
                return;
            }

            $password = null;

            if ($this->shellArgumentExists('password', '--password'))
            {
                $password = $this->getShellArgumentValue('password', '--password');
            }

            $this->hUserDirectory = $this->library(
                'hUser/hUserDirectory',
                array(
                    'userName' => $user,
                    'password' => $password
                )
            );
        }
    }
}

?>