<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar Event Form Service
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\|
#//\\\\\  ----  \@@@@| Use and redistribution are subject to the terms of the license.
#//@@@@@\       \@@@@| http://www.hframework.com/license
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hCalendarEventFormService extends hService {

    private $hCalendar;
    private $hCalendarDatabase;
    private $hFile;
    private $hFileDatabase;
    private $hListDatabase;
    private $hTidy;

    public function hConstructor()
    {
        $this->hCalendar = $this->library('hCalendar');
        $this->hCalendarDatabase = $this->database('hCalendar');
    }

    public function hasAccessToCalendar($calendarId = 0, $fileId = 0, $level = 'r')
    {
        if ($this->hCalendarDatabase->hasAccessToCalendar($calendarId, $fileId, $level))
        {
            return true;
        }

        $this->JSON(-1);
        return false;
    }

    public function save()
    {
        $fileId = $this->database('hCalendar/hCalendarEventForm', $_POST)->getFileId();

        if (false === $fileId)
        {
            $this->JSON(-1);
            return;
        }

        $this->JSON($fileId);
    }

    public function getAttachedDocument()
    {
        $fileId = (int) $this->get('fileId');

        if (!$fileId)
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->hFiles->hasPermission($fileId, 'rw'))
        {
            $this->JSON(-1);
            return;
        }

        $this->JSON(
            $this->hCalendarDatabase->getEvent($fileId, false)
        );
    }

    public function getImportedDocument()
    {
        $fileId = (int) $this->get('fileId');

        if (!$fileId)
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->hFiles->hasPermission($fileId))
        {
            $this->JSON(-1);
            return;
        }

        $this->hFileDatabase = $this->database('hFile');

        $this->hFileDatabase->setFileId($fileId);

        $permissions = $this->hFiles->getPermissions($fileId);

        $this->JSON(
            array(
                'hFileDocument'            => $this->hFileDatabase->hFileDocument,
                'hFileDescription'         => $this->hFileDatabase->hFileDescription,
                'hFileTitle'               => $this->hFileDatabase->hFileTitle,
                'hCalendarLink'            => $this->hFileDatabase->hCalendarLink,
                'hUserName'                => $this->user->getUserName($this->hFileDatabase->hUserId),
                'hFileName'                => $this->hFileDatabase->hFileName,
                'hDirectoryPath'           => $this->getDirectoryPath($this->hFileDatabase->hDirectoryId),
                'hFileId'                  => $this->hFileDatabase->hFileId,
                'hUserPermissionWorldRead' => strstr($permissions['hUserPermissionsWorld'], 'r')? 1 : 0
            )
        );
    }

    public function getEvent()
    {
        $fileId = (int) $this->get('fileId');

        if (!$fileId)
        {
            $this->JSON(-5);
            return;
        }

        $calendars = $this->hCalendarDatabase->getFileCalendars($fileId);

        if ($this->hasAccessToCalendar($calendars, $fileId))
        {
            $event = $this->hCalendarDatabase->getEvent($fileId);

            if ($this->hTidyEnabled(false))
            {
                $this->hTidy = $this->library('hTidy');
                $event['hFileDocument'] = $this->hTidy->getHTML($event['hFileDocument']);
            }

            $this->JSON(
                array_merge(
                    array(
                        'hFileHasWriteAccess' => $this->hasAccessToCalendar($calendars, $fileId, 'rw')
                    ),
                    $event
                )
            );
        }
    }
}

?>