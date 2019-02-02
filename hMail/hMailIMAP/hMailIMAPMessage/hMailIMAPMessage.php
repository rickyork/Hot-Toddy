<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#/\\\       \\\\\\\\|
#/\\\ @@    @@\\\\\\| Hot Toddy Mail IMAP Message
#/\\ @@@@  @@@@\\\\\| 
#/\\\@@@@| @@@@\\\\\|
#/\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#/\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#/\\\\  \\_   \\\\\\|
#/\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#/\\\\\  ----  \@@@@| http://www.hframework.com/license
#/@@@@@\       \@@@@|
#/@@@@@@\     \@@@@@|
#/\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hMailIMAPMessage extends hPlugin {

    private $hMailIMAP;

    public function hConstructor()
    {
         $this->plugin('hApplication/hApplicationForm');
         $this->getPluginCSS();
    }
}

?>