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

class hDashboardUserGuides extends hPlugin {

    private $hDocumentation;

    public function hConstructor()
    {
        $this->hDocumentation = $this->library('hDocumentation');
        
        $this->hDocumentation->setUpTemplate();

        $this->hDocumentation->setDocument(
            $this->getTemplate($this->hFileName)
        );
    }
}

?>