<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy List Service
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

class hListService extends hService {
    
    private $hFileIcon;
    
    public function hConstructor()
    {
        $this->hFileIcon = $this->library('hFile/hFileIcon');
    }

    public function getListFilesAsList()
    {
        if (!isset($_GET['hListId']) || !isset($_GET['hFileId']))
        {
            $this->JSON(-5);
            return;
        }

        $hListFileIds = $this->hListFiles->select(
            'hListFileId',
            array(
                'hFileId' => (int) $_GET['hFileId'],
                'hListId' => (int) $_GET['hListId']
            ),
            'AND',
           'hListFileSortIndex'
        );

        $hListFiles = '';

        foreach ($hListFileIds as $hListFileId)
        {
            $hListFiles .= $this->getListHTML($hListFileId);
        }

        $this->HTML(
            $this->getTemplate(
                'List Files',
                array(
                    'hListId' => (int) $_GET['hListId'],
                    'hListFiles' => $hListFiles
                )  
            )
        );
    }

    public function getListFileHTML()
    {
        if (isset($_GET['hFileId']) && is_numeric($_GET['hFileId']))
        {
            $this->HTML($this->getListHTML((int) $_GET['hFileId']));
        }
        else
        {
            $this->JSON(-5);
        }
    }
    
    public function getMultipleListFilesHTML()
    {
        if (isset($_GET['hFileIds']))
        {
            $hFileIds = explode(',', $_GET['hFileIds']);
            
            $html = '';
            
            $hFileIds = array_unique($hFileIds);

            foreach ($hFileIds as $hFileId)
            {
                if (!empty($hFileId))
                {
                    $html .= $this->getListHTML($hFileId);
                }
            }

            $this->HTML($html);
        }
        else
        {
            $this->JSON(-5);
        }
    }
    
    private function getListHTML($hFileId)
    {
        return $this->getTemplate(
            'List',
            array(
                'hFileId'        => $hFileId,
                'hListFileIcon'  => $this->hFileIcon->getFileIconPath($hFileId),
                'hListFileTitle' => $this->getFileTitle($hFileId),
                'hFilePath'      => $this->getFilePathByFileId($hFileId)
            )
        );
    }
}

?>