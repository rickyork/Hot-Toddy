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