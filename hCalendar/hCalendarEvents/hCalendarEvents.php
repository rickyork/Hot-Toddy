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
# @description
# <h1>Creating an Events Calendar</h1>
#
#
# @end

class hCalendarEvents extends hPlugin {

    private $hCalendarEvents;

    public function hConstructor()
    {
        $this->hCalendarEvents = $this->library('hCalendar/hCalendarEvents');

        if ($this->hCalendarEventPost(false))
        {        
            $this->hCalendarEvents->setSingleFile($this->hFileId, $this->hCalendarEventsPath('/Events.html'));
        }

        $this->hFileDocument = $this->hCalendarEvents->get(
            $this->hCalendarId(1),
            $this->hCalendarCategoryId(2),
            isset($_GET['hCalendarDate'])? (int) $_GET['hCalendarDate'] : null,
            $this->hCalendarEventCount(10)            
        );
    }
}

?>