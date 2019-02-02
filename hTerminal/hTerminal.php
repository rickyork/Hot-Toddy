<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Terminal
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
# @description
# <h1>Terminal Application</h1>
# <p>
#    Hot Toddy's Terminal application provides a command line interface for interacting
#    with the framework.  Presently, this CLI is very crude and only supports passing 
#    through terminal commands.  The goal with this application is to provide CLI support
#    for framework functionality in a completely custom web-based interactive terminal.
# </p>
# @end

class hTerminal extends hPlugin {

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();
    
        $this->plugin('hApplication/hApplicationForm');

        $this->getPluginFiles();

        $this->hFileDocument = $this->getTemplate(
            'Terminal'
        );
    }
}

?>