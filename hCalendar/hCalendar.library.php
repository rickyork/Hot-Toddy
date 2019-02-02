<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar Library
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

class hCalendarLibrary extends hPlugin {

    # This is used to make sure that the ids are unique,
    # even when there are multiple calendars present in a
    # single document.
    private $hFileIcon;
    private $hCalendarDatabase;
    private $hSearch;

    public function hConstructor()
    {
        $this->hFileIcon = $this->library('hFile/hFileIcon');
        $this->hCalendarDatabase = $this->database('hCalendar');
    }

    public function getEventPath(&$directoryPath, $calendarId, $calendarCategoryId)
    {
        # @return string

        # @description
        # <h2>Getting the Path to an Event</h2>
        # <p>
        #
        #
        #
        # </p>
        # @end

        if (empty($directoryPath))
        {
            if (is_array($this->hCalendarPath))
            {
                if (is_array($this->hCalendarPath))
                {
                    foreach ($this->hCalendarPath as $calendarPath)
                    {
                        $directoryPath = $this->getCalendarPath(
                            $calendarPath,
                            $calendarId,
                            $calendarCategoryId
                        );

                        if (!empty($directoryPath))
                        {
                            break;
                        }
                    }
                }
                else
                {
                    $directoryPath = $this->getCalendarPath(
                        $this->hCalendarPath,
                        $calendarId,
                        $calendarCategoryId
                    );
                }
            }
            else
            {
                if (empty($calendarCategoryId))
                {
                    $calendarCategoryId = $this->hCalendarCategoryId(3);
                }

                switch ($calendarCategoryId)
                {
                    case 1:
                    {
                        $folder = 'News';
                        break;
                    }
                    case 2:
                    {
                        $folder = 'Events';
                        break;
                    }
                    case 3:
                    {
                        $folder = 'Blog';
                        break;
                    }
                    case 6:
                    {
                        $folder = 'Jobs';
                        break;
                    }
                    default:
                    {
                        $folder = 'Events';
                        break;
                    }
                }

                $directoryPath = '/'.$this->hFrameworkSite.'/'.$folder;
            }
        }

        return $directoryPath;
    }

    public function getEventFileName(&$fileName)
    {
        # @return string

        # @description
        # <h2>Getting an Event's File Name</h2>
        # <p>
        #
        # </p>
        # @end

        if (!empty($fileName))
        {
            if (!strstr($fileName, '.') && !strstr($fileName, '.html'))
            {
                $fileName .= '.html';
            }
        }
        else
        {
            $fileName = date('YmdHis').'.html';
        }

        return $fileName;
    }

    public function getCalendarPath($calendarPath, $calendarId, $calendarCategoryId)
    {
        # @return string

        # @description
        # <h2>Getting a Calendar's Save Path </h2>
        # @end

        if (is_object($calendarPath))
        {
            $this->verbose(
                "hCalendarId: {$calendarPath->hCalendarId}\n".
                "hCalendarCategoryId: {$calendarPath->hCalendarCategoryId}\n".
                "hDirectoryPath: {$calendarPath->hDirectoryPath}"
            );

            switch (true)
            {
                case (isset($calendarPath->hCalendarId, $calendarPath->hCalendarCategoryId, $calendarPath->hDirectoryPath)):
                {
                    if (in_array($calendarPath->hCalendarId, $calendarId) && $calendarPath->hCalendarCategoryId == $calendarCategoryId)
                    {
                        $directoryPath = $calendarPath->hDirectoryPath;
                    }

                    break;
                }
                case (isset($calendarPath->hDirectoryPath, $calendarPath->hCalendarId)):
                {
                    if (in_array($calendarPath->hCalendarId, $calendarId))
                    {
                        $directoryPath = $calendarPath->hDirectoryPath;
                    }

                    break;
                }
                case (isset($calendarPath->hDirectoryPath, $calendarPath->hCalendarCategoryId)):
                {
                    if ($calendarPath->hCalendarCategoryId == $calendarCategoryId)
                    {
                        $directoryPath = $calendarPath->hDirectoryPath;
                    }

                    break;
                }
                case (isset($calendarPath->hDirectoryPath)):
                {
                    $directoryPath = $calendarPath->hDirectoryPath;
                    break;
                }
                default:
                {
                    $this->warning('A calendar plugin definition is missing required information.', __FILE__, __LINE__);
                }
            }
        }
        else
        {
            $this->warning('Calendar plugin definition is not an object.', __FILE__, __LINE__);
        }

        if (isset($directoryPath))
        {
            $directoryId = $this->getDirectoryId($directoryPath);

            if (empty($directoryId))
            {
                $this->warning('Calendar path '.$directoryPath.' does not exist.', __FILE__, __LINE__);
            }

            return $directoryPath;
        }
    }

    public function getEventPlugin($calendarId = 0, $calendarCategoryId = 0)
    {
        # @return array

        # @description
        # <h2>Getting the Plugin for an Event</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($calendarId))
        {
            $calendarId = $this->hCalendarId(1);
        }

        if (empty($calendarCategoryId))
        {
            $calendarCategoryId = $this->hCalendarCategoryId(3);
        }

        $file['hPlugin'] = '';

        switch (true)
        {
            case $this->hCalendarPlugins(null):
            {
                if (is_array($this->hCalendarPlugins))
                {
                    foreach ($this->hCalendarPlugins as $calendarPlugin)
                    {
                        $file = $this->getCalendarPlugin(
                            $calendarPlugin,
                            $calendarId,
                            $calendarCategoryId
                        );

                        if (!empty($file['hPlugin']))
                        {
                            break;
                        }
                    }
                }
                else
                {
                    $file = $this->getCalendarPlugin(
                        $this->hCalendarPlugins,
                        $calendarId,
                        $calendarCategoryId
                    );
                }

                break;
            }
            case $this->hCalendarPlugin(null):
            {
                $file = $this->getCalendarPlugin(
                    $this->hCalendarPlugin,
                    $calendarId,
                    $calendarCategoryId
                );

                break;
            }
            # The following are legacy plugin configurations, use the new JSON configuration file format instead.
            case ($calendarCategoryId == 2 && $this->hCalendarFileEventPlugin('hCalendar/hCalendarEvents')):
            {
                $plugin = $this->hCalendarFileEventPlugin('hCalendar/hCalendarEvents');
                $file['hPlugin'] = $plugin;
                break;
            }
            case ($this->hCalendarFilePlugin('hCalendar/hCalendarBlog')):
            {
                $plugin = $this->hCalendarFilePlugin('hCalendar/hCalendarBlog');
                $file['hPlugin'] = $plugin;
                break;
            }
            default:
            {
                $file['hPlugin'] = '';
            }
        }

        return $file;
    }

    public function getCalendarPlugin($calendarPlugin, $calendarId, $calendarCategoryId)
    {
        # @return array

        # @description
        # <h2>Getting the Plugin for a Calendar</h2>
        # <p>
        #
        # </p>
        # @end

        $file = array();

        if (is_object($calendarPlugin))
        {
            switch (true)
            {
                case (isset($calendarPlugin->hCalendarId, $calendarPlugin->hCalendarCategoryId, $calendarPlugin->hPluginPath)):
                {
                    # Assign a plugin based on calendar and category
                    if (in_array($calendarPlugin->hCalendarId, $calendarId) && $calendarPlugin->hCalendarCategoryId == $calendarCategoryId)
                    {
                        $file['hPlugin'] = $calendarPlugin->hPluginPath;
                    }

                    break;
                }
                case (isset($calendarPlugin->hPluginPath) && isset($calendarPlugin->hCalendarId)):
                {
                    # Assign a plugin based on calendar
                    if (in_array($calendarPlugin->hCalendarId, $calendarId))
                    {
                        $file['hPlugin'] = $calendarPlugin->hPluginPath;
                    }

                    break;
                }
                case (isset($calendarPlugin->hPluginPath, $calendarPlugin->hCalendarCategoryId)):
                {
                    # Assign a plugin based on category
                    if ($calendarPlugin->hCalendarCategoryId == $calendarCategoryId)
                    {
                        $file['hPlugin'] = $calendarPlugin->hPluginPath;
                    }

                    break;
                }
                case (isset($calendarPlugin->hPluginPath)):
                {
                    # Just assign a plugin
                    $file['hPlugin'] = $calendarPlugin->hPluginPath;
                    break;
                }
                default:
                {
                    $this->warning('A calendar plugin definition is missing required information.', __FILE__, __LINE__);
                }
            }
        }
        else
        {
            $this->warning('Calendar plugin definition is not an object.', __FILE__, __LINE__);
        }

        if (isset($calendarPlugin->hDirectoryPath))
        {
            $file['hDirectoryPath'] = $calendarPlugin->hDirectoryPath;
        }

        if (isset($calendarPlugin->hUserPermissionsOwner))
        {
            $file['hUserPermissionsOwner'] = $calendarPlugin->hUserPermissionsOwner;
        }

        if (isset($calendarPlugin->hUserPermssionsWorld))
        {
            $file['hUserPermissionsWorld'] = $calendarPlugin->hUserPermssionsWorld;
        }

        if (isset($calendarPlugin->hUserPermissionsGroups))
        {
            $file['hUserPermissionsGroups'] = $calendarPlugin->hUserPermissionsGroups;
        }

        if (isset($calendarPlugin->hUserPermissionsInherit))
        {
            $file['hUserPermissionsInherit'] = $calendarPlugin->hUserPermissionsInherit;
        }

        return $file;
    }

    public function padMinutes($item)
    {
        # @return string

        # @description
        # <h2>Padding Minutes</h2>
        # @end

        return str_pad($item, 2, '0', STR_PAD_LEFT);
    }

    public function getEnabledCalendars()
    {
        # @return array

        # @description
        # <h2>Finding Enabled Calendars</h2>
        # <p>
        #
        # </p>
        # @end

        $calendars = array();

        $calendarIds = $this->hCalendars->select('hCalendarId');

        foreach ($calendarIds as $calendarId)
        {
            $isChecked = (int) $this->user->getVariable('hCalendarToggleState-'.$calendarId, 1);

            if ($isChecked)
            {
                array_push($calendars, (int) $calendarId);
            }
        }

        return $calendars;
    }

    public function getEvents($date = null)
    {
        # @return array

        # @description
        # <h2>Getting Events</h2>
        # <p>
        #
        # </p>
        # @end

        $limit = $this->hCalendarEventsResultsPerPage(15);

        $paging = false;

        if ($this->hCalendarEnableEventPaging(true))
        {
            $paging = true;

            $this->hSearchResultsPerPage = $this->hCalendarEventsResultsPerPage(15);
            $this->hSearchPagesPerChapter = $this->hCalendarEventsPagesPerChapter(3);

            $this->hSearch = $this->library('hSearch');

            $limit = $this->hSearch->getLimit();
        }

        $calendars = $this->hCalendarDatabase->getCalendars('rw');

        $calendarIds = array();

        foreach ($calendars as $calendarId => $calendarName)
        {
            if ((int) $this->user->getVariable('hCalendarToggleState-'.$calendarId, 1))
            {
                array_push($calendarIds, $calendarId);
            }
        }

        $this->hCalendarDatabase->setFileCalendars($calendarIds);

        $files = $this->hCalendarDatabase->getFilesForTemplate(
            array(
                'hCalendarDate' => 'F jS'
            ),
            $this->getEnabledCalendars(),
            0,
            $limit,
            'News',
            false,
            'ASC',
            0,
            (int) $date
        );

        $pagingNavigation = '';

        if ($paging)
        {
            $count = $this->hCalendarDatabase->getResultCount();
            $this->hSearch->setParameters($count);
            $pagingNavigation = $this->hSearch->getNavigationHTML();
        }

        return array(
            'events' => $this->getTemplate(
                'Event',
                array(
                    'hFiles' => $files,
                    'hCalendarRecentEvents' => $this->hCalendarRecentEvents(false),

                )
            ),
            'navigation' => $pagingNavigation
        );
    }
}

?>