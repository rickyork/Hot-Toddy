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

class hFrameworkServerShell extends hShell {

    private $hFrameworkServer;

    public function hConstructor()
    {
        $arguments = array();

        if ($this->shellArgumentExists('address', '--address'))
        {
            $arguments['address'] = $this->getShellArgumentValue('address', '--address');
        }

        if ($this->shellArgumentExists('port', '--port'))
        {
            $arguments['port'] = $this->getShellArgumentValue('port', '--port');
        }

        $this->hFrameworkServer = $this->plugin('hFramework/hFrameworkServer', $arguments);
        $this->hFrameworkServer->run();
    }
}

?>