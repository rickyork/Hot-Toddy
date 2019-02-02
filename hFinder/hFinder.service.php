<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Listener
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
#
# A URL-callable API that performs various file system tasks
# such as create directory, rename file, etc.  These methods are
# callable through the hFrameworkListener plugin.
#
# Each URL-callable method must be registered in the appropriate hPluginListeners table.
#
# Response codes:
#   All failures or errors should be zero or negative numbers,
#   this allows a successful response to be 1 or greater, whereas
#   an inserted id, or some other (positive) value evaluating to
#   true can also be returned.
#
# (see hHTTP.js for a list of response codes)

class hFinderService extends hService {

    private $methods = array(
        'upload' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_POST' => array(
                    'meta_title',
                    'meta_description',
                    'force_download',
                    'replace_file',
                    'world_read'
                )
            )
        ),
        'getDirectory' => array(
            'authenticate' => 'r'
        ),
        'getBranch' => array(
            'authenticate' => 'r'
        ),
        'getColumnFileProperties' => array(
            'authenticate' => 'r'
        )
    );

    private $hFinder;
    private $hFinderTree;
    private $hFile;
    private $hFileIcon;

    public function hConstructor()
    {
        $this->hFinder = $this->library('hFinder');
        $this->hFinderTree = $this->library('hFinder/hFinderTree');
        $this->hFile = $this->library('hFile');

        hString::safelyDecodeURL($_GET['path']);
        hString::safelyDecodeURL($_POST['path']);

        if (is_array($this->hFinderFilterPaths) && !$this->inGroup('root'))
        {
            $this->hFile->setFilterPaths($this->hFinderFilterPaths);
        }

        if (array_key_exists($this->hServiceMethod, $this->methods))
        {
            if (($json = $this->hFile->listenerValidation($this->methods, $this->hServiceMethod)) <= 0)
            {
                $this->JSON($json);
            }
        }
    }

    public function getDirectory()
    {
        $this->HTML(
            $this->hFinder->getDirectory(
                $this->hFile->filePath,
                $_GET['view'],
                isset($_GET['sortBy'])? $_GET['sortBy'] : 'name'
            )
        );
    }

    public function search()
    {
        $fileSearchTerms = $this->get('fileSearchTerms');

        if (empty($fileSearchTerms))
        {
            $this->JSON(-5);
            return;
        }

        $this->HTML(
            $this->hFinder->search($fileSearchTerms)
        );
    }

    public function searchByPreset()
    {
        $fileSearchPreset = $this->get('fileSearchPreset');

        if (empty($fileSearchPreset))
        {
            $this->JSON(-5);
            return;
        }

        $fileSearchTime = $this->get('fileSearchTime', array());

        $this->HTML(
            $this->hFinder->searchByPreset(
                $fileSearchPreset,
                $fileSearchTime
            )
        );
    }

    public function getColumnFileProperties()
    {
        $meta = $this->hFile->getMetaData();

        if (isset($meta['PixelWidth']))
        {
            # Should work on this and get a properly resized thumbnail.
            $hFilePreviewPath = $_GET['path'];
        }
        else
        {
            $this->hFileIcon = $this->library('hFile/hFileIcon');

            if (!empty($meta['hFileIconId']))
            {
                $hFilePreviewPath = $this->hFileIcon->getIconPathById(
                    $meta['hFileIconId'],
                    '128x128'
                );
            }
            else
            {
                $hFilePreviewPath = $this->hFileIcon->getIconPath(
                    $this->hFile->getMIMEType(),
                    $this->hFile->fileName,
                    '128x128'
                );
            }
        }

        if (!empty($meta['hDirectoryIsApplication']))
        {
            $meta['Kind'] = 'Application';
        }

        $path = $this->get('path');

        $this->HTML(
            $this->getTemplate(
                'Columns Properties',
                array(
                    'hFilePath'        => $path,
                    'hFilePreviewPath' => $hFilePreviewPath,
                    'hFileAccessCount' => $this->hFileStatistics->selectColumn(
                        'hFileAccessCount',
                        array(
                            'hFileId' => $this->hFile->fileId
                        )
                    ),
                    'hFileName'             => isset($meta['DisplayName'])? $meta['DisplayName'] : '',
                    'hFileKind'             => isset($meta['Kind'])? $meta['Kind'] : '',
                    'hFileSize'             => isset($meta['FSSize'])? $meta['FSSize'] : '',
                    'hFileCreatedDate'      => isset($meta['ContentCreationDate'])? date('n/j/y g:i A', $meta['ContentCreationDate']) : '',
                    'hFileLastModifiedDate' => isset($meta['ContentModificationDate'])? date('n/j/y g:i A', $meta['ContentModificationDate']) : '',
                    'hFileLastAccessedDate' => !empty($meta['ContentAccessedDate'])? date('n/j/y g:i A', $meta['ContentAccessedDate']) : 0,
                    'hFileDimensions'       => isset($meta['PixelHeight'])? $meta['PixelWidth'].' x '.$meta['PixelHeight'] : ''
                )
            )
        );
    }

    public function getBranch()
    {
        $displayFiles = $this->get('displayFiles');

        if (!empty($displayFiles))
        {
            $this->hFinderTreeDisplayFiles = true;
        }

        $this->HTML(
            $this->hFinderTree->getBranch(
                $this->hFile->filePath
            )
        );
    }

    # Remember what size the user set the width of the tree view and the margin of the main document window.
    public function saveSize()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $width = (int) $this->get('width');

        $this->user->saveVariable(
            'hFinderSideColumnWidth',
            $width
        );

        $this->JSON(1);
    }

    # Remember the default directory view selected by the user.
    public function setDefaultView()
    {
        $view = $this->get('view');

        $this->hFinder->validateView($view);

        if ($this->isLoggedIn())
        {
            $this->user->saveVariable(
                'hFinderView',
                $view
            );

            $this->JSON(1);
        }
        else
        {
            $this->JSON(-6);
        }
    }
}

?>