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

class hCalendarEventFormDatabase extends hPLugin {

    private $hCalendar;
    private $hCalendarDatabase;
    private $hFile;
    private $hFileDatabase;
    private $hListDatabase;

    private $fileId;

    public function hConstructor($calendar)
    {
        $file = array();

        if (empty($calendar['hCalendarId']))
        {
            $calendar['hCalendarId'] = $this->hCalendarId(1);
        }

        if (isset($calendar['hCalendarId']) && !is_array($calendar['hCalendarId']))
        {
            $calendar['hCalendarId'] = array($calendar['hCalendarId']);
        }

        $this->hCalendarDatabase = $this->database('hCalendar');
        $this->hCalendar = $this->library('hCalendar');

        if ($this->hasAccessToCalendar($calendar['hCalendarId'], (int) $calendar['hFileId']))
        {
            $file['hFileCalendarId'] = $calendar['hCalendarId'];

            $this->hFile = $this->library('hFile');
            $this->hFileDatabase = $this->database('hFile');

            $this->createDefaultFolders();

            # Prepare the file to be saved...

            $calendarId = $file['hFileCalendarId'];

            if (is_array($file['hFileCalendarId']))
            {
                $calendarId = $file['hFileCalendarId'][0];
            }

            $calendarCategoryId = $this->hCalendarCategoryId(3);

            if (isset($calendar['hCalendarCategoryId']))
            {
                $calendarCategoryId = (int) $calendar['hCalendarCategoryId'];
            }

            $file['hFileCalendarCategoryId'] = (int) $calendarCategoryId;

            $this->setProperties($file, $calendar);

            // Updated proper multi-site supporting plugin configuration.
            // Use Configuration/Plugin/hCalendarResource.json
            // And hCalendar/hCalendarResource/hCalendarResource.shell.php

            $calendarResource = $this->hCalendarDatabase->getResource((int) $calendarId, (int) $calendarCategoryId);

            if ($calendarResource === false)
            {
                // Legacy plugin configuration
                $plugin = $this->hCalendar->getEventPlugin((int) $calendarId, (int) $calendarCategoryId);

                foreach ($plugin as $key => $value)
                {
                    $this->console("Calendar Legacy Plugin: {$key} => {$value}");
                }

                $calendarResource = array(
                    'hPlugin' => $plugin['hPlugin']
                );

                if (isset($plugin['hUserPermissionsOwner']))
                {
                    $calendarResource['hUserPermissionsOwner'] = $plugin['hUserPermissionsOwner'];
                }

                if (isset($plugin['hUserPermissionsWorld']))
                {
                    $calendarResource['hUserPermissionsWorld'] = $plugin['hUserPermissionsWorld'];
                }

                if (isset($plugin['hUserPermissionsGroups']))
                {
                    $calendarResource['hUserPermissionsGroups'] = $plugin['hUserPermissionsGroups'];
                }

                if (isset($plugin['hUserPermissionsInherit']))
                {
                    $calendarResource['hUserPermissionsInherit'] = $plugin['hUserPermissionsInherit'];
                }

                if (isset($plugin['hDirectoryPath']))
                {
                    if (!$this->hFile->exists($plugin['hDirectoryPath']))
                    {
                        $calendarResource['hDirectoryId'] = $this->hFile->makePath(
                            $plugin['hDirectoryPath'],
                            array(
                                'hUserId' => (int) $_SESSION['hUserId'],
                                'hUserPermissionsOwner' => 'rw',
                                'hUserPermissionsWorld' => 'r',
                                'hUserPermissionsGroups' => array(
                                    'Website Administrators' => 'rw'
                                )
                            )
                        );
                    }
                    else
                    {
                        $calendarResource['hDirectoryId'] = $this->getDirectoryId($plugin['hDirectoryPath']);
                    }
                }
            }

            foreach ($calendarResource as $key => $value)
            {
                $this->console("Calendar Resource: {$key} => {$value}");
            }

            $this->setDatesAndTimes($file, $calendar);

            if (!empty($calendarResource['hDirectoryId']))
            {
                $file['hDirectoryId'] = (int) $calendarResource['hDirectoryId'];
            }

            $file['hPlugin'] = $calendarResource['hPlugin'];

            foreach ($file as $key => $value)
            {
                $this->console("Calendar File: {$key} => {$value}");
            }

            $this->setDirectory($file, $calendar);

            $fileId = $this->hFileDatabase->save($file);

            $permissions = array();

            if (isset($calendarResource['hUserPermissionsOwner']))
            {
                $permissions['hUserPermissionsOwner'] = $calendarResource['hUserPermissionsOwner'];
            }

            if (isset($calendarResource['hUserPermissionsWorld']))
            {
                $permissions['hUserPermissionsWorld'] = $calendarResource['hUserPermissionsWorld'];
            }

            if (isset($calendarResource['hUserPermissionsGroups']))
            {
                $permissions['hUserPermissionsGroups'] = $calendarResource['hUserPermissionsGroups'];
            }

            if (isset($calendarResource['hUserPermissionsInherit']))
            {
                $permissions['hUserPermissionsInherit'] = $calendarResource['hUserPermissionsInherit'];
            }

            $this->setPermissions($file, $calendar, $fileId, $permissions);

            $this->fileId = (int) $fileId;
        }
        else
        {
            return false;
        }
    }

    public function getFileId()
    {
        return $this->fileId;
    }

    private function createDefaultFolders()
    {
        $folders = array('Events', 'Blog', 'News', 'Jobs');

        foreach ($folders as $folder)
        {
            if (!$this->hFile->exists('/'.$this->hFrameworkSite.'/'.$folder))
            {
                $directoryId = $this->hFile->makePath(
                    '/'.$this->hFrameworkSite.'/'.$folder,
                    array(
                        'hUserId' => (int) $_SESSION['hUserId'],
                        'hUserPermissionsOwner' => 'rw',
                        'hUserPermissionsWorld' => 'r',
                        'hUserPermissionsGroups' => array(
                            'Website Administrators' => 'rw'
                        )
                    )
                );
            }
        }
    }

    private function setPermissions(&$file, &$calendar, $fileId, $permissions)
    {
        $overridePermissions = false;

        if ($this->hCalendarFilePaths(nil))
        {
            foreach ($this->hCalendarFilePaths as $path)
            {
                if (isset($path->hFilePath) && $path->hFilePath == $this->getFilePathByFileId($fileId))
                {
                    $overridePermissions = true;

                    if (isset($path['hUserPermissionsOwner']))
                    {
                        $permissions['hUserPermissionsOwner'] = $path['hUserPermissionsOwner'];
                    }

                    if (isset($path['hUserPermissionsWorld']))
                    {
                        $permissions['hUserPermissionsWorld'] = $path['hUserPermissionsWorld'];
                    }

                    if (isset($path['hUserPermissionsGroups']))
                    {
                        $permissions['hUserPermissionsGroups'] = $path['hUserPermissionsGroups'];
                    }
                }
            }
        }

        if (isset($permissions['hUserPermissionsInherit']) && !empty($permissions['hUserPermissionsInherit']) && !$overridePermissions)
        {
            $calendarId = array_pop($file['hFileCalendarId']);

            $this->hCalendars->inheritPermissionsFrom((int) $calendarId);
            $this->hFiles->savePermissions($fileId);
        }
        else
        {
            if (!isset($permissions['hUserPermissionsGroups']))
            {
                $this->hFiles->setGroup(
                    'Website Administrators',
                    'rw'
                );

                if ($this->hCalendarGroup(nil))
                {
                    $this->hFiles->setGroup($this->hCalendarGroup(nil));
                }

                if ($this->hCalendarEditGroup(nil))
                {
                    $this->hFiles->setGroup(
                        $this->hCalendarEditGroup(nil),
                        'rw'
                    );
                }
            }
            else if (is_array($permissions['hUserPermissionsGroups']))
            {
                foreach ($permissions['hUserPermissionsGroups'] as $group => $level)
                {
                    if (is_array($level))
                    {
                        // $level[0] = $userGroupId
                        // $level[1] = r/rw
                        $level[0] = str_replace('&#92;', '\\\\', $level[0]);
                        $this->hFiles->setGroup($level[0], $level[1]);
                    }
                    else
                    {
                        if (!is_numeric($group))
                        {
                            $group = str_replace('&#92;', '\\\\', $group);
                        }

                        $this->hFiles->setGroup($group, $level);
                    }
                }
            }

            $world = !empty($calendar['hUserPermissionWorldRead'])? 'r' : '';

            if (isset($permissions['hUserPermissionsWorld']))
            {
                $world = $permissions['hUserPermissionsWorld'];
            }

            $owner = 'rw';

            if (isset($permissions['hUserPermissionsOwner']))
            {
                $owner = $permissions['hUserPermissionsOwner'];
            }

            $this->hFiles->savePermissions($fileId, $owner, $world);
        }
    }

    private function setProperties(&$file, &$calendar)
    {
        $fields = array(
            'hFileTitle',
            'hFileDocument',
            'hFileDescription',
            'hFileId'
        );

        foreach ($fields as $field)
        {
            $file[$field] = $calendar[$field];
        }

        $file['hFileId'] = (int) $calendar['hFileId'];

        if (!empty($calendar['hFileHeadingTitle']))
        {
            $file['hFileHeadingTitle'] = $calendar['hFileHeadingTitle'];
        }

        // Set file owner or author
        if ($this->hCalendarUserNameEnabled(true) || isset($calendar['hUserName']))
        {
            $file['hUserId'] = (int) $_SESSION['hUserId'];

            if (!empty($calendar['hUserName']))
            {
                $this->user->getUserId($calendar['hUserName']);
            }
        }

        // If categories or tags are enabled, create an array to contain them.
        if ((int) $this->hCalendarFileCategoryId(-1) >= 0 || (int) $this->hCalendarTagCategoryId(-1) >= 0)
        {
            $file['hCategories'] = array();
        }

        // Add categories, if any are specified
        if (isset($calendar['hCalendarFileCategories']) && is_array($calendar['hCalendarFileCategories']))
        {
            $file['hCategories'] = array_merge($file['hCategories'], $calendar['hCalendarFileCategories']);
        }

        // Tags are categories as well, add those, if any are specified
        if (isset($calendar['hCalendarTagCategories']) && is_array($calendar['hCalendarTagCategories']))
        {
            $file['hCategories'] = array_merge($file['hCategories'], $calendar['hCalendarTagCategories']);
        }

        // Add a third-party script that deals with Flash
        if ($this->hCalendarSWFObject(nil) && !empty($calendar['hFileMovieId']))
        {
            if (isset($file['hFileJavaScript']))
            {
                // Add a new JavaScript file to the JavaScript container
                $file['hFileJavaScript'] .= $this->getTemplate('SWF Object');
            }
            else
            {
                $file['hFileJavaScript'] = $this->getTemplate('SWF Object');
            }
        }

        // Videos can be attached using the list API
        if (isset($calendar['hFileMovieId']))
        {
            $this->hListDatabase = $this->database('hList');

            // Create the Movies list if it doesn't exist
            if (!$this->hListDatabase->listExists('Movies'))
            {
                // Get the list id for the new Movies list
                $listId = $this->hListDatabase->save(0, 'Movies');
            }
            else
            {
                // Get the list id for the existing Movies list
                $listId = $this->hListDatabase->getListId('Movies');
            }

            // Create a new list association
            $file['hLists'][] = $listId;

            if (!empty($calendar['hFileMovieId']))
            {
                if (is_array($calendar['hFileMovieId']))
                {
                    foreach ($calendar['hFileMovieId'] as $fileMovieId)
                    {
                        $file['hListFiles'][(int) $fileMovieId] = $listId;
                    }
                }
                else
                {
                    $file['hListFiles'][(int) $calendar['hFileMovieId']] = $listId;
                }
            }
        }

        // Set File Variables
        // Enable variables
        $file['hFileVariables'] = 1;

        // Set post flag
        $file['hCalendarPost'] = 1;

        // Enable or disable comments
        $file['hFileCommentsEnabled'] = (int) !empty($calendar['hFileComments']);

        // This variable replaces the body of the post with a link, the link
        // can be to a third-party website, or elsewhere within this same website.
        if (isset($calendar['hCalendarLink']))
        {
            $file['hCalendarLink'] = $calendar['hCalendarLink'];
        }

        // Set the fileId of the thumbnail image
        if (isset($calendar['hCalendarFileThumbnailId']))
        {
            $file['hCalendarFileThumbnailId'] = (int) $calendar['hCalendarFileThumbnailId'];
        }

        // For job postings
        if (isset($calendar['hCalendarJobCompany']))
        {
            $file['hCalendarJobCompany'] = $calendar['hCalendarJobCompany'];
        }

        if (isset($calendar['hCalendarJobLocation']))
        {
            $file['hCalendarJobLocation'] = $calendar['hCalendarJobLocation'];
        }

        switch ($file['hFileCalendarCategoryId'])
        {
            case 2:
            {
                $file['hCalendarEventPost'] = 1;
                break;
            }
            case 3:
            {
                $file['hCalendarBlogPost'] = 1;
                break;
            }
            case 1:
            case 5:
            {
                $file['hCalendarNewsPost'] = 1;
                break;
            }
            default:
            {
                $file['hCalendarGenericPost'] = 1;
            }
        }
    }

    private function setDatesAndTimes(&$file, &$calendar)
    {
        $file['hFileCalendarDate']  = !empty($calendar['hCalendarDate'])?  $calendar['hCalendarDate']  : date('m/d/y');
        $file['hFileCalendarBegin'] = !empty($calendar['hCalendarBegin'])? $calendar['hCalendarBegin'] : '';
        $file['hFileCalendarEnd']   = !empty($calendar['hCalendarEnd'])?   $calendar['hCalendarEnd'].' 11:59PM' : '';

        if (!empty($calendar['hCalendarBeginTime']))
        {
            $file['hFileCalendarBeginTime'] =
                $calendar['hCalendarBeginTime'].' '.
                $calendar['hCalendarBeginTimeHour'].':'.
                $this->hCalendar->padMinutes($calendar['hCalendarBeginTimeMinute']).' '.
                $calendar['hCalendarBeginTimeMeridiem'];

            $calendar['hCalendarEndTime'] = $calendar['hCalendarBeginTime'];
        }
        else
        {
            $file['hFileCalendarBeginTime'] = '';
        }

        if (!empty($calendar['hCalendarEndTime']))
        {
            $file['hFileCalendarEndTime'] =
                $calendar['hCalendarEndTime'].' '.
                $calendar['hCalendarEndTimeHour'].':'.
                $this->hCalendar->padMinutes($calendar['hCalendarEndTimeMinute']).' '.
                $calendar['hCalendarEndTimeMeridiem'];
        }
        else
        {
            $file['hFileCalendarEndTime'] = '';
        }
    }

    private function setDirectory(&$file, &$calendar)
    {
        if (empty($file['hFileId']))
        {
            $file['hFileName'] = $this->hCalendar->getEventFileName($calendar['hFileName']);

            if (empty($file['hDirectoryId']))
            {
                if (isset($calendar['hDirectoryPath']) && !empty($calendar['hDirectoryPath']))
                {
                    hString::safelyDecodeURL($calendar['hDirectoryPath']);
                }

                $directoryPath = $this->hCalendar->getEventPath(
                    $calendar['hDirectoryPath'],
                    $file['hFileCalendarId'],
                    $file['hFileCalendarCategoryId']
                );

                if (!empty($directoryPath))
                {
                    $file['hDirectoryId'] = $this->getDirectoryId($directoryPath);
                }
                else
                {
                    $this->warning('Unable to get a directory path for event.', __FILE__, __LINE__);
                }
            }
        }
    }

    private function hasAccessToCalendar($calendarId = 0, $fileId = 0, $level = 'rw')
    {
        if ($this->hCalendarDatabase->hasAccessToCalendar($calendarId, $fileId, $level))
        {
            return true;
        }

        return false;
    }
}

?>