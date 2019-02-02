<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy List
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
# <h1>List API</h1>
# <p>
#    The List API is used to provide navigation in the framework.  Lists are
#    created as simply lists of links that can be styled however you like. 
#    Lists are created by associating one or more files in HtFS with any other file.
#    The List API is disabled by default.  Once enabled, the methods in this object
#    are fused with the core Hot Toddy API and are available globally, but only when
#    an HTML templated document is requested.
# </p>
# @end

class hList extends hPlugin {

    public function getListId($listName)
    {
        return (int) $this->hLists->selectColumn(
            'hListId',
             array(
                 'hListName' => $listName
            )
        );
    }
    
    public function getListName($listId)
    {
        return $this->hLists->selectColumn('hListName', (int) $listId);
    }

    public function listHasFiles($listName, $fileId = 0)
    {
        return $this->hListFiles->selectExists(
            'hListFileId',
            array(
                'hFileId' => (empty($fileId)? (int) $this->hFileId : (int) $fileId),
                'hListId' => $this->getListId($listName)
            )
        );
    }

    public function getListFiles($listName, $fileId = 0)
    {
        return $this->hListFiles->select(
            'hListFileId',
            array(
                'hFileId' => (empty($fileId)? (int) $this->hFileId : (int) $fileId),
                'hListId' => $this->getListId($listName)
            ),
            'AND',
            'hListFileSortIndex'
        );
    }

    public function getList($listId, $getIcons = true, $getHTMLIcon = false)
    {
        $iconResolution = $this->hFileIconResolution('32x32');
        
        $this->hFileIconResolution = '16x16';
    
        $html = '';

        (int) $fileId = $this->hFileSymbolicLinkTo($this->hFileId);

        $listFiles = $this->hDatabase->selectForTemplate(
            array(
                'hFilePath',
                'hListFiles' => array(
                    'hFileId',
                    'hListFileId'
                ),
                'hFileDocuments' => 'hFileTitle',
                'hFiles' => 'hFileName'
            ),
            array(
                'hListFiles',
                'hFileDocuments',
                'hFiles'
            ),
            array(
                'hListFiles.hFileId' => (int) $fileId,
                'hListFiles.hListId' => (int) $listId,
                'hListFiles.hListFileId' => array(
                    array('=', 'hFileDocuments.hFileId'),
                    array('=', 'hFiles.hFileId')
                )
            ),
            'AND',
            'hListFileSortIndex'
        );
        
        $this->hFileIconResolution = $iconResolution;
        
        $html = $this->getTemplate(
            'List Files',
            array(
                'hListFiles' => $listFiles,
                'prefix'     => $this->hListClassIdPrefix('')
            )
        );
        
        $listName = $this->getListName($listId);

        return $this->getListTemplate($html, $listName, strtolower(str_replace(' ', '', $listName)));
    }

    public function getListTemplate($listFiles, $listName = null, $id = null, $ul = true)
    {
        return $this->getTemplate(
            'List Template',
            array(
                'hListUL'    => $ul,
                'hListId'    => $id,
                'hListName'  => $listName,
                'hListFiles' => $listFiles,
                'prefix'     => $this->hListClassIdPrefix('')
            )
        );
    }
}

?>