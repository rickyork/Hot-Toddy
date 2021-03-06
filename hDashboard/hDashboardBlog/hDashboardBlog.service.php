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

class hDashboardBlogService extends hService {

    private $hCalendarDatabase;
    private $hFile;
    private $hFileDatabase;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('Website Administrators') && !$this->inGroup('Calendar Administrators'))
        {
            $this->JSON(-1);
            return;
        }

        if (!isset($_GET['fileId']))
        {
            $this->JSON(-5);
            return;
        }

        $this->hCalendarDatabase = $this->database('hCalendar');
    }

    public function get()
    {
        $fileId = (int) $_GET['fileId'];

        $this->JSON(
            $this->hCalendarDatabase->getEvent($fileId)
        );
    }

    public function save()
    {
        $fileId = (int) $_GET['fileId'];

        $this->hFile = $this->library('hFile');
        $this->hFileDatabase = $this->database('hFile');

        if (empty($_POST['hCalendarDate']))
        {
            $_POST['hCalendarDate'] = date('m/d/Y');
        }

        $fileName = '';

        if (!empty($fileId))
        {
            $fileName = $this->getFileName($fileId);
        }
        else
        {
            $timestamp = strtotime($_POST['hCalendarDate']);

            $date = date('Y-m-d', $timestamp);

            if (!$this->hFile->exists('/'.$this->hFrameworkSite.'/News/'.$date.'.html'))
            {
                $fileName = $date.'.html';
            }
            else
            {
                $name = $date;

                for ($i = 1; $this->hFile->exists('/'.$this->hFrameworkSite.'/News/'.$name.'.html'); $i++)
                {
                    $name = $date.' '.$i;
                    $fileName = $name.'.html';
                }
            }
        }

        $fileId = $this->hFileDatabase->save(
            array(
                'hFileTitle'                => $_POST['hFileTitle'],
                'hDirectoryPath'            => '/'.$this->hFrameworkSite.'/News',
                'hFileName'                 => $fileName,
                'hFileDocument'             => $_POST['hFileDocument'],
                'hFileCalendarId'           => $this->hCalendarId(1),
                'hFileCalendarCategoryId'   => $this->hCalendarCategoryId(3),
                'hFileCalendarDate'         => $_POST['hCalendarDate'],
                'hUserPermissions'          => true,
                'hUserPermissionsOwner'     => 'rw',
                'hUserPermissionsWorld'     => $_POST['hUserPermissionsWorld'],
                'hUserPermissionsGroups'    => array(
                    'Calendar Administrators'   => 'rw',
                    'Website Administrators'    => 'rw',
                    'AHNI\\myahni_marketing'    => 'rw'
                )
            )
        );

        $this->JSON(
            array(
                'fileId' => $fileId
            )
        );
    }

    public function delete()
    {
        if (empty($_GET['fileId']))
        {
            $this->JSON(-5);
            return;
        }

        $fileId = (int) $_GET['fileId'];

        $this->hCalendarDatabase->delete(
            $this->hCalendarId(1),
            $this->hCalendarCategoryId(3),
            $fileId
        );

        $this->hFile = $this->library('hFile');

        $this->hFile->delete($this->getFilePathByFileId($fileId));

        $this->JSON(1);
    }
}

?>