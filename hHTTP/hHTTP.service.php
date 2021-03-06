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

class hHTTPService extends hService {

    public function hConstructor()
    {

    }

    public function getErrorMessage()
    {
        if (!isset($_GET['errorCode']))
        {
            $this->JSON(-5);
            return;
        }

        $template = null;

        switch ((int) $_GET['errorCode'])
        {
            case -34:
            case -33:
            case -32:
            case -31:
            case -24:
            case -23:
            case -13:
            case -14:
            case -6:
            case -5:
            case -3:
            case -1:
            case 0:
            {
                $template = "{$_GET['errorCode']}.html";
                break;
            }
            default:
            {
                $template = ((string) ((int) $_GET['errorCode'])).'.html';
            }
        }

        $this->JSON(
            array(
                'action' => $_GET['action'],
                'error' => $this->getTemplate($template)
            )
        );
    }

    public function getDebugTemplate()
    {
        $this->HTML($this->getTemplate('Debug'));
    }

    public function getPopupDebugTemplate()
    {
        $this->HTML($this->getTemplate('Debug Popup'));
    }
}

?>