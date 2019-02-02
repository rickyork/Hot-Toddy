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