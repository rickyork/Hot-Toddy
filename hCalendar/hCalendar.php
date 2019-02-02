<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar Plugin
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
# @description
# <h1>Hot Toddy Calendar Application</h1>
# <p>
#   The calendar application provides a single document GUI for creating and maintaining
#   events.  The calendar application creates event files for any type of data that
#   is time sensitive or assigned to a date.  The default categories of events are
#   as follows:
# </p>
# <ul>
#   <li>News</li>
#   <li>Press Releases</li>
#   <li>Events</li>
#   <li>Blog Posts</li>
#   <li>Job Postings</li>
#   <li>Newsletters</li>
#   <li>Alerts</li>
# </ul>
# <p>
#   The calendar application provides a calendar, which is used to view, create, and
#   maintain event files.
# </p>
# <p>
#   Event files are ordinary files in the Hot Toddy File System, with meta data attached
#   as file variables, as well as meta data stored in separate database tables for
#   defining calendars, calendar categories (the kind of events), dates, times, and
#   configruation data for applying plugins and permissions.
# </p>
# <p>
#   Calendars are a resource in Hot Toddy, and therefore, can be owned, as well as have
#   permissions and meta data assigned to control caching of calendar-related data.
# </p>
# <p>
#   Hot Toddy offers some built-in plugins for viewing Blogs, News, and Events, as well
#   as a full API for creating custom plugins that are capable of accessing calendar-related
#   data.
# </p>
# <p>
#   Hot Toddy also provides an API for generating calendar views via HTML.  Additionally,
#   jQuery UI's datepicker is also used and available.
# </p>
# <p>
#   Hot Toddy provides a variety of services for calendar related data via AJAX/REST,
#   and RSS.
# </p>
# <p>
#   Hot Toddy's calendar application is highly customizable, and offers a built-in content
#   management system, and supports a variety of configurations to simultaeously accomodate
#   both the technically advanced and novice user.  It supports, for example, the ability to
#   enter event data as HTML source if you are a root user, and it also supports content
#   entry via a WYSIWYG editor for novice users.
# </p>
# @end

class hCalendar extends hPlugin {

    private $hCalendar;
    private $hCalendarView;
    private $hCalendarDatabase;
    private $hCalendarEventForm;
    private $hCategoryDatabase;
    private $hDialogue;
    private $hForm;
    private $defaultCalendar;
    private $calendars = array();

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        if ($this->isLoggedIn())
        {
            $this->hFileCSS = '';
            $this->hFileJavaScript = '';

            $this->jQuery('Datepicker');
            $this->plugin('hApplication/hApplicationStatus');

            $this->getPluginCSS('hSearch');
            $this->getPluginFiles();
            $this->getPluginCSS('ie7');

            $this->hFileTitle = $this->hServerHost.' Calendar';
            $this->hFileTitlePrepend = '';
            $this->hFileTitleAppend  = '';

            $this->hTemplatePath = '/hCalendar/hCalendar.template.php';

            $this->hCalendarDatabase = $this->database('hCalendar');
            $this->hCalendar = $this->library('hCalendar');
            $this->hCalendarEventForm = $this->library('hCalendar/hCalendarEventForm');
            $this->hCalendarView = $this->library('hCalendar/hCalendarView');

            $this->hFileFavicon = '/hCalendar/Pictures/Calendar.ico';

            $shared = $this->hCalendarDatabase->getShared();

            $this->hCalendarWeekday = 'initial';

            $hCalendarMini = $this->hCalendarView->get(null, 'hCalendarMini');

            $this->hCalendarWeekday = 'l';
            $this->hCalendarView->dateFormats();

            $hCalendar = $this->hCalendarView->get();

            $events = $this->hCalendar->getEvents();

            $this->hFileDocument = $this->getTemplate(
                'Calendar',
                array(
                    'hCalendarMini'          => $hCalendarMini,
                    'hCalendar'              => $hCalendar,
                    'hCalendarDefault'       => (int) $this->defaultCalendar,
                    'hCalendarMiniState'     => $this->hCalendarMiniState(1),
                    'hCalendarMiniOn'        => $this->hCalendarMiniState(1) == 1? '_on' : '',
                    'hCalendarOwner'         => $this->getCheckboxes($this->hCalendarDatabase->getCalendars()),
                    'hCalendarEvents'                 => $events['events'],
                    'hCalendarEventNavigation'        => $events['navigation'],
                    'hCalendarEventForm'              => $this->hCalendarEventForm->get(),
                    'hCalendarSelectCategoryDialogue' => $this->getCategoryDialogue()
                )
            );
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function getCategoryDialogue()
    {
        if ($this->hCalendarCategoryEnabled(true))
        {
            $this->hDialogue = $this->library('hDialogue');

            $categories = $this->hCalendarDatabase->getCategories();

            $templateCategories = array();

            foreach ($categories as $categoryId => $categoryName)
            {
                $templateCategories['hCalendarCategoryId'][] = $categoryId;
                $templateCategories['hCalendarCategoryName'][] = $categoryName;
            }

            $this->hDialogue->newDialogue('hCalendarSelectCategory');

            $this->hDialogue->addButtons('Continue', 'Cancel');

            return $this->hDialogue->getDialogue(
                $this->getTemplate(
                    'Select Category',
                    array(
                        'categories' => $templateCategories
                    )
                ),
                'New Calendar Document'
            );

        }

        return null;
    }

    private function getCheckboxes($calendars)
    {
        $html = '';

        foreach ($calendars as $calendarId => $calendarName)
        {
            if (!in_array($calendarName, $this->calendars))
            {
                $hasPermission = $this->hCalendars->hasPermission($calendarId, 'rw');
                $isChecked = $hasPermission? (int) $this->user->getVariable('hCalendarToggleState-'.$calendarId, 1) : false;

                $html .= $this->getTemplate(
                    'Checkbox',
                    array(
                        'hCalendarId'            => $calendarId,
                        'hCalendarName'          => $calendarName,
                        'hCalendarOptionClass'   => '',
                        'hCalendarTemplateClass' => '',
                        'hCalendarChecked'       => $isChecked? " checked='checked'" : '',
                        'hasPermission'          => $hasPermission
                    )
                );

                $this->calendars[$calendarId] = $calendarName;
            }
        }

        $html .= $this->getTemplate(
            'Checkbox',
            array(
                'hCalendarId'            => 0,
                'hCalendarName'          => '',
                'hCalendarOptionClass'   => '',
                'hCalendarTemplateClass' => ' hCalendarOptionTemplate',
                'hCalendarChecked'       => ''
            )
        );

        return $html;
    }
}

?>