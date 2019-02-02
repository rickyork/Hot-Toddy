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

class hConsoleService extends hService {

    private $hConsoleDatabase;
    private $hSearch;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('Website Administrators'))
        {
            $this->JSON(-1);
            return;
        }
        
        $this->hConsoleDatabase = $this->database('hConsole');
    }
    
    public function truncateErrorLog()
    {
        $this->hFrameworkErrors->truncate();
        $this->JSON(1);
    }

    public function get()
    {
        if (!isset($_GET['hConsoleLog']) || !isset($_GET['hSearchCursor']))
        {
            $this->JSON(-5);
            return;
        }
        
        $this->hSearch = $this->library('hSearch');
        
        $recordCount = !empty($_GET['hConsoleRecordCount'])? (int) $_GET['hConsoleRecordCount'] : (int) $this->hConsoleResultsPerPage(30);

        $this->hSearchResultsPerPage = $recordCount;
        $this->hSearchPagesPerChapter = $this->hConsolePagesPerChapter(7);

        switch ($_GET['hConsoleLog'])
        {
            case 'hFrameworkErrors':
            {
                $data = $this->hConsoleDatabase->getErrors($this->hSearch->getLimit());
                break;
            }
            case 'hFileStatusLog':
            {
                $data = $this->hConsoleDatabase->getStatusCodes($this->hSearch->getLimit());
                break;
            }
            case 'hUserActivity':
            {
                $data = $this->hConsoleDatabase->getActivity($this->hSearch->getLimit());
                break;
            }
            case 'hFileUserStatistics':
            {
                $data = $this->hConsoleDatabase->getDocumentHistory($this->hSearch->getLimit());
                break;
            }
            case 'hUserLog':
            {
                $data = $this->hConsoleDatabase->getUserLog($this->hSearch->getLimit());
                break;
            }
            default:
            {
                $this->JSON(0);
                return;
            }
        }

        $count = $this->hDatabase->getResultCount();

        $this->hSearch->setParameters($count);

        $this->JSON(
            array(
                'log' => $this->getTemplate(
                    $_GET['hConsoleLog'],
                    array(
                        'log' => $this->hConsoleDatabase->getResultsForTemplate($data)
                    )
                ),
                'search' => $this->hSearch->getNavigationHTML('/hConsole/get')
            )
        );
    }

    public function saveWindowDimensions()
    {
        if (!empty($_GET['width']))
        {
            $this->user->saveVariable('hConsoleWindowWidth', (int) $_GET['width']);
        }

        if (!empty($_GET['height']))
        {
            $this->user->saveVariable('hConsoleWindowHeight', (int) $_GET['height']);
        }
        
        $this->JSON(1);
    }
    
    public function saveColumnDimensions()
    {
        if (!empty($_GET['width']))
        {
            $this->user->saveVariable('hConsoleLogsColumnWidth', (int) $_GET['width']);
            $this->JSON(1);
            return;
        }
        
        $this->JSON(-5);
    }
}

?>