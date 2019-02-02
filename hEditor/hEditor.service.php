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

class hEditorService extends hService {

    private $hFileDatabase;

    public function saveColumnDimensions()
    {
        $width = (int) $this->get('width');

        if (empty($width))
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $this->user->saveVariable(
            'hEditorTreeWidth',
            $width
        );

        $this->JSON(1);
    }

    public function saveWindowDimensions()
    {
        $width = (int) $this->get('width');
        $height = (int) $this->get('height');


        if (empty($width) || empty($height))
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $this->user->saveVariable(
            'hEditorWindowWidth',
            $width
        );

        $this->user->saveVariable(
            'hEditorWindowHeight',
            $height
        );

        $this->JSON(1);
    }

    public function savePreferences()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (isset($_GET['backwards']))
        {
            $this->user->saveVariable(
                'hEditorFindReplaceBackwards',
                (int) $_GET['backwards']
            );
        }

        if (isset($_GET['wrap']))
        {
            $this->user->saveVariable(
                'hEditorFindReplaceWrap',
                (int) $_GET['wrap']
            );
        }

        if (isset($_GET['caseSensitive']))
        {
            $this->user->saveVariable(
                'hEditorFindReplaceCaseSensitive',
                (int) $_GET['caseSensitive']
            );
        }

        if (isset($_GET['wholeWord']))
        {
            $this->user->saveVariable(
                'hEditorFindReplaceWholeWord',
                (int) $_GET['wholeWord']
            );
        }

        if (isset($_GET['regExp']))
        {
            $this->user->saveVariable(
                'hEditorFindReplaceRegularExpression',
                (int) $_GET['regExp']
            );
        }

        $this->JSON(1);
    }

    public function save()
    {
        if (!isset($_POST['hFileId']))
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->hFiles->hasPermission((int) $_POST['hFileId'], 'rw'))
        {
            $this->JSON(-1);
            return;
        }

        if (strtolower($_POST['hFileDocument']) == 'undefined' || strtolower($_POST['hFileDocument']) == 'null')
        {
            $this->JSON(0);
            return;
        }

        $this->console($_POST['hFileDocument']);

        if (strtolower($_POST['hFileTitle']) == 'undefined')
        {
            unset($_POST['hFileTitle']);
        }

        if (strtolower($_POST['hFileKeywords']) == 'undefined')
        {
            unset($_POST['hFileKeywords']);
        }

        if (strtolower($_POST['hFileDescription']) == 'undefined')
        {
            unset($_POST['hFileDescription']);
        }

        $this->database('hFile')->save($_POST, false);

        $this->JSON(1);
    }
}


?>