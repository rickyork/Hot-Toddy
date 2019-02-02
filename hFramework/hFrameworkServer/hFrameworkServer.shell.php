<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Server Shell
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