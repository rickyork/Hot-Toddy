<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar Events Shell
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

# ./hot -p hCalendar/hCalendarEvents

class hCalendarEventsShell extends hShell {

    private $hCalendarEvents;

    public function hConstructor()
    {
        $this->hCalendarEvents = $this->library('hCalendar/hCalendarEvents');
    
        $hCalendarId         = ($this->shellArgumentExists('-c', '--calendar'))? (int) $this->getShellArgumentValue('-c', '--calendar')  : 1;
        $hCalendarCategoryId = ($this->shellArgumentExists('-y', '--category'))? (int) $this->getShellArgumentValue('-y', '--category')  : 3;
        $count               = ($this->shellArgumentExists('-n', '--count'))?    (int) $this->getShellArgumentValue('-n', '--count')     : 0;
        $date                = ($this->shellArgumentExists('-d', '--date'))?     strtotime($this->getShellArgumentValue('-d', '--date')) : time();

        echo $this->hCalendarEvents->get($date, $hCalendarId, $hCalendarCategoryId, $count);
    }
}

?>