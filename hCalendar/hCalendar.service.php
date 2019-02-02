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

class hCalendarService extends hService {

    private $hCalendar;
    private $hCalendarDatabase;
    private $hRSS;
    private $hFile;
    private $hFileDatabase;
    private $hListDatabase;

    public function hConstructor()
    {
        $this->hCalendar = $this->library('hCalendar');
        $this->hCalendarDatabase = $this->database('hCalendar');
    }

    public function hasAccessToCalendar($calendarId = 0, $fileId = 0, $level = 'r')
    {
        # @return boolean

        # @description
        # <h2>Determining Calendar Access</h2>
        # <p>
        #
        # </p>
        # @end

        if ($this->hCalendarDatabase->hasAccessToCalendar($calendarId, $fileId, $level))
        {
            return true;
        }

        $this->JSON(-1);
        return false;
    }

    public function newCategory()
    {
        # @return JSON

        # @description
        # <h2>Creating a Calendar Category</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($_GET['hCalendarCategoryName']))
        {
            $this->JSON(-5);
            return;
        }

        if ($this->hasAccessToCalendar())
        {
            $this->JSON(
                $this->hCalendarDatabase->newCategory(
                    hString::scrubString($_GET['hCalendarCategoryName'])
                )
            );
        }
    }

    public function deleteCategory()
    {
        # @return JSON

        # @description
        # <h2>Deleting a Calendar Category</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($_GET['hCalendarCategoryId']))
        {
            $this->JSON(-5);
            return;
        }

        if ($this->hasAccessToCalendar())
        {
            $this->hCalendarDatabase->deleteCategory(
                (int) $_GET['hCalendarCategoryId']
            );

            $this->JSON(1);
        }
    }

    public function duplicateEvent()
    {
        # @return JSON

        # @description
        # <h2>Duplicating an Event</h2>
        # <p>
        #
        # </p>
        # @end

        $calendarId = (int) $this->get('calendarId');
        $fileId = (int) $this->get('fileId');
        $calendarDate = (int) $this->get('calendarDate');

        if (!$calendarId || !$fileId || !$calendarDate)
        {
            $this->JSON(-5);
            return;
        }

        if ($this->hasAccessToCalendar($calendarId, $fileId, 'rw'))
        {
            $this->hFile = $this->library('hFile');

            $file = array();

            $directoryPath = $this->get('directoryPath');
            $fileName = $this->get('fileName');

            $filePath = $this->getConcatenatedPath(
                $this->hCalendar->getEventPath($directoryPath),
                $this->hCalendar->getEventFileName($fileName)
            );

            $replaceExisting = $this->get('replaceExisting');

            if ($replaceExisting == 1)
            {
                $this->hFile->delete($filePath);
            }

            $fileId = $this->hFile->copy(
                $this->getFilePathByFileId($fileId),
                $filePath
            );

            $this->hCalendarDatabase->updateFileDate(
                $fileId,
                $calendarDate
            );

            $this->JSON($fileId);
        }
    }

    public function deleteEvent()
    {
        # @return JSON

        # @description
        # <h2>Deleting an Event</h2>
        # <p>
        #
        # </p>
        # @end

        $fileId = (int) $this->get('fileId');

        if (!$fileId)
        {
            $this->JSON(-5);
            return;
        }

        # Get all the calendars associated with this event...
        $calendars = $this->hCalendarDatabase->getFileCalendars($fileId);

        if ($this->hasAccessToCalendar($calendars, $fileId, 'rw'))
        {
            $this->hCalendarDatabase->deleteEvent($fileId);
            $this->JSON(1);
        }
    }

    public function newCalendar()
    {
        # @return JSON

        # @description
        # <h2>Creating a Calendar</h2>
        # <p>
        #
        # </p>
        # @end

        $calendarName = $this->get('calendarName');

        if (!$calendarName)
        {
            $this->JSON(-5);
            return;
        }

        if ($this->hasAccessToCalendar())
        {
            $this->JSON(
                $this->hCalendarDatabase->newCalendar(
                    hString::scrubString($calendarName)
                )
            );
        }
    }

    public function deleteCalendar()
    {
        # @return JSON

        # @description
        # <h2>Deleting a Calendar</h2>
        # <p>
        #
        # </p>
        # @end

        $calendarId = (int) $this->get('calendarId');

        if (!$calendarId)
        {
            $this->JSON(-5);
            return;
        }

        if ($this->hasAccessToCalendar($calendarId, 0, 'rw'))
        {
            $this->hCalendarDatabase->deleteCalendar($calendarId);
            $this->JSON(1);
        }
    }

    public function saveCalendarToggleState()
    {
        # @return JSON

        # @description
        # <h2>Remembering Toggle State</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $calendarId = (int) $this->get('calendarId');
        $toggle = (int) $this->get('toggle');

        if (!$calendarId || !$toggle)
        {
            $this->JSON(-5);
            return;
        }

        $this->user->saveVariable('hCalendarToggleState-'.$calendarId, $toggle);
        $this->JSON(1);
    }

    public function saveMiniCalendarState()
    {
        # @return JSON

        # @description
        # <h2>Remembering Mini Calendar Toggle State</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $toggle = (int) $this->get('toggle');

        if (!$toggle)
        {
            $this->JSON(-5);
            return;
        }

        $this->user->saveVariable('hCalendarMiniState', $toggle);
        $this->JSON(1);
    }

    public function updateFileDate()
    {
        # @return JSON

        # @description
        # <h2>Changing a File's Date</h2>
        # <p>
        #
        # </p>
        # @end

        $calendarDate = (int) $this->get('calendarDate');
        $fileId = (int) $this->get('fileId');

        if (!$calendarDate || !$fileId)
        {
            $this->JSON(-5);
            return;
        }

        $calendars = $this->hCalendarDatabase->getFileCalendars($fileId);

        if ($this->hasAccessToCalendar($calendars, $fileId, 'rw'))
        {
            # Update event date.
            $this->hCalendarDatabase->updateFileDate($fileId, $calendarDate);
            $this->JSON(1);
        }
    }

    public function getEvents()
    {
        # @return JSON

        # @description
        # <h2>Getting Events</h2>
        # <p>
        #
        # </p>
        # @end

        $calendars = $this->get('calendars');
        $calendarDate = (int) $this->get('calendarDate');

        if (!$calendars || !$calendarDate)
        {
            $this->JSON(-5);
            return;
        }
        else if (!is_array($calendars))
        {
            $calendars = array($calendars);
        }

        if ($this->hasAccessToCalendar($calendars, 0))
        {
            # Get events for this month
            $events = array();

            foreach ($calendars as $calendarId)
            {
                $hFiles = $this->hCalendarDatabase->getFiles(
                    $calendarId,
                    0,              # No category
                    0,              # No limit
                    'Month',        # For the given month
                    false,          # Not within time boundaries
                    'ASC',          # Doesn't matter
                    0,              # No File Id
                    $calendarDate  # Date to use for 'Month' preset.
                );

                foreach ($hFiles as $hFile)
                {
                    $events[] = array(
                        'hFileId'             => $hFile['hFileId'],
                        'hFileDescription'    => $hFile['hFileDescription'],
                        'hFileTitle'          => $this->hFileHeadingTitle($hFile['hFileTitle'], $hFile['hFileId']),
                        'hFilePath'           => $hFile['hFilePath'],
                        'hCalendarId'         => $hFile['hCalendarId'],
                        'hCalendarCategoryId' => $hFile['hCalendarCategoryId'],
                        'hCalendarDate'       => $hFile['hCalendarDate']
                    );
                }
            }

            $this->JSON($events);
        }
    }

    public function getSidebarEvents()
    {
        # @return JSON

        # @description
        # <h2>Getting Sidebar Events</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $calendarDate = (int) $this->get('calendarDate');
        $searchCursor = $this->get('searchCursor');

        if (!$calendarDate || !$searchCursor)
        {
            $this->JSON(-5);
            return;
        }

        $this->JSON(
            $this->hCalendar->getEvents($calendarDate)
        );
    }

    public function saveWindowDimensions()
    {
        # @return JSON

        # @description
        # <h2>Remembering Window Dimensions</h2>
        # <p>
        #
        # </p>
        # @end

        $width = (int) $this->get('width');
        $height = (int) $this->get('height');

        if (!$width || !$height)
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $this->user->saveVariable('hCalendarWindowWidth', $width);
        $this->user->saveVariable('hCalendarWindowHeight', $height);

        $this->JSON(1);
    }

    public function saveColumnDimensions()
    {
        # @return JSON

        # @description
        # <h2>Remembering Column Dimensions</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $calendarsWidth = (int) $this->get('calendarsWidth');
        $eventsWidth = (int) $this->get('eventsWidth');

        if ($calendarsWidth)
        {
            $this->user->saveVariable(
                'hCalendarCalendarsColumnWidth',
                $calendarsWidth
            );
        }

        if ($eventsWidth)
        {
            $this->user->saveVariable(
                'hCalendarEventsColumnWidth',
                $eventsWidth
            );
        }

        $this->JSON(1);
    }

    public function RSS()
    {
        # @return JSON

        # @description
        # <h2>Viewing an RSS Feed</h2>
        # <p>
        #
        # </p>
        # @end

        $calendar = $this->get('calendar');

        if (!$calendar)
        {
            $this->JSON(-5);
            return;
        }
        else
        {
            list($fileId, $calendarId, $calendarCategoryId) = explode('/', $calendar);
        }

        if ($this->hCalendarDatabase->hasAccessToCalendar($calendarId, $fileId))
        {
            if (preg_match('#^\d+/\d+/\d+$#', $calendar))
            {
                $this->hRSS = $this->library('hRSS');

                $files = $this->hCalendarDatabase->getFiles(
                    $calendarId,
                    $calendarCategoryId,
                    50,
                    null,
                    true,
                    'DESC'
                );

                $variables = array(
                    'hRSSTitle' => hString::entitiesToUTF8(
                        $this->hRSSTitle(
                            $this->getFileTitle((int) $fileId),
                            $fileId
                        )
                    ),
                    'hRSSLink' => 'http://'.$this->hServerHost,
                    'hRSSDescription' => hString::entitiesToUTF8(
                        $this->getFileDescription((int) $fileId)
                    ),
                    'hRSSEditor' => $this->user->getFullName(
                        $this->getFileOwner($fileId)
                    )
                );

                $lastModified = 0;

                foreach ($files as $i => $file)
                {
                    $lastModified = $file['hFileLastModified'];

                    $files[$i]['hFileOwner'] = $this->user->getUserEmail($file['hUserId']).' ('.$this->user->getFullName($file['hUserId']).')';
                    $files[$i]['hFilePath'] = 'http://'.$this->hServerHost.$file['hFilePath'];
                    $files[$i]['hFileLastModifiedFormatted'] = gmdate(RFC822, $file['hFileLastModified']);

                    if ($lastModified == 0 || $lastModified < $file['hFileLastModified'])
                    {
                        $lastModified = $file['hFileLastModified'];
                    }
                }

                $variables['hFiles'] = $this->hDatabase->getResultsForTemplate($files);

                $variables['hRSSPubDate'] = gmdate(RFC822, $lastModified);
                $variables['hRSSLastBuildDate'] = gmdate(RFC822, $lastModified);

                $this->hRSS->get($variables);
            }
            else
            {
                $this->HTML("Error: unable to retrieve an RSS feed, the 'calendar' argument is not properly formatted.");
            }
        }
        else
        {
            $this->setHTTPAuthentication($this->hFrameworkName.' RSS Feed');
        }
    }
}

?>