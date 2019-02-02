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

class hUserActivityLog extends hPlugin {

    public function activity($component, $activity)
    {
        if (!empty($_SESSION['hUserId']) && isset($_SERVER['REMOTE_ADDR']))
        {
            $this->hUserActivityLog->insert(
                array(
                    'hUserId'                => (int) $_SESSION['hUserId'],
                    'hUserActivity'          => hString::escapeAndEncode($activity),
                    'hUserActivityComponent' => hString::escapeAndEncode($component),
                    'hUserActivityTime'      => time(),
                    'hUserActivityIP'        => $_SERVER['REMOTE_ADDR']
                )
            );
        }
    }
}

?>