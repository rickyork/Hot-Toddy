<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User ActivityLog
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