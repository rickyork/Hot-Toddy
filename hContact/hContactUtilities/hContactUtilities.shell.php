<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Utilities Shell
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

class hContactUtilitiesShell extends hShell {

    private $hContactUtilities;

    public function hConstructor()
    {
        $this->hContactUtilities = $this->library('hContact/hContactUtilities');

        if ($this->shellArgumentExists('cleanUpEmailAddresses', '--cleanUpEmailAddresses'))
        {
            $this->hContactUtilities->cleanUpEmailAddresses();
        }

        if ($this->shellArgumentExists('cleanUpPhoneNumbers', '--cleanUpPhoneNumbers'))
        {
            $this->hContactUtilities->cleanUpPhoneNumbers();
        }
    }
}

?>