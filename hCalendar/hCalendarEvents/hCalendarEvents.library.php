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
# <h1>Calendar Events API</h1>
#
#
# @end

class hCalendarEventsLibrary extends hPlugin {

    private $hCalendarDatabase;
    private $hCalendarView;
    private $hCalendar;
    private $singleFileId = 0;
    private $eventsPath = '';

    public function hConstructor()
    {
        $this->hCalendar         = $this->library('hCalendar');
        $this->hCalendarView     = $this->library('hCalendar/hCalendarView');
        $this->hCalendarDatabase = $this->database('hCalendar');
    }
    
    public function setSingleFile($fileId, $eventsPath = '')
    {
        # @return void
        
        # @description 
        # <h2>Setting Single File Properties</h2>
        # <p>
        #
        # </p>
        # @end
    
        $this->singleFileId = (int) $fileId;
        $this->eventsPath   = $eventsPath;
    }

    public function get($calendarId = 1, $calendarCategoryId = 3, $calendarDate = null, $count = 0)
    {
        # @return string
        
        # @description 
        # <h2>Getting an Events Calendar</h2>
        # <p>
        #
        # </p>
        # @end
    
        $calendarEvents = array();
        $eventDates = array();

        if ($this->hCalendarEventPosts(true))
        {        
            $calendarEvents = $this->hCalendarDatabase->getFilesForTemplate(
                array(
                    'hCalendarDate'      => $this->hCalendarDateFormat('l, F j, Y'),
                    'hCalendarBeginTime' => $this->hCalendarBeginTimeFormat('l, F j, Y g:i A'),
                    'hCalendarEndTime'   => $this->hCalendarEndTimeFormat('g:i A')
                ),
                $calendarId,
                $calendarCategoryId,
                $count,
                !empty($this->singleFileId)? null : (is_null($calendarDate)? 'Events' : 'Month'),
                true,
                'ASC',
                !empty($this->singleFileId)? $this->singleFileId : 0,
                $calendarDate
            );

            $eventDates = $this->hCalendarDatabase->getDatesInLastFileQuery();
        }
        else
        {
            if (!empty($this->singleFileId))
            {
                $eventDates = array($this->hCalendarDatabase->getFileDate($this->singleFileId));
            }
        }

        $this->hCalendarWeekday = 'initial';

        return $this->getTemplate(
            $this->hCalendarEventsTemplate('Events'),
            array(
                'upcomingEvents' => is_null($calendarDate),
                'hCalendarEvents' => $calendarEvents,
                'hCalendarEventPosts' => $this->hCalendarEventPosts(true),
                'hCalendar' => !empty($this->singleFileId)? null : $this->hCalendarView->get($calendarDate, 'hCalendar', 0, $eventDates),
                'singleFile' => !empty($this->singleFileId),
                'hCalendarEventPost' => !empty($this->singleFileId),
                'hCalendarEventsPath' => $this->eventsPath,
                'hCalendarMonth' => date('F', $calendarDate),
                'hCalendarPreviousDate' => mktime(
                    0, 0, 0,
                    ($calendarDate? date('m', $calendarDate) : date('m')) - 1,
                    1,
                    $calendarDate? date('Y', $calendarDate) : date('Y')
                ),
                'hCalendarNextDate' => mktime(
                    0, 0, 0,
                    ($calendarDate? date('m', $calendarDate) : date('m')) + 1,
                    1,
                    $calendarDate? date('Y', $calendarDate) : date('Y')
                )
            )
        );
    }
}

?>