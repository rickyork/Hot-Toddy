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
#
# hFinderLibrary
#
#   Contains Finder components that can be called via a listener or be called
#   via the plugin.
#
#   Components contained in this library are those that are associated with the
#   hFinder GUI.
#
#   Methods associated with raw file operations (getting, manipulating, deleting, etc),
#   should appear in the hFileLibrary plugin.
#

class hFinderTreeLibrary extends hPlugin {

    private $hFile;
    private $hFinderIcons;

    public function hConstructor()
    {
        $this->hFile        = $this->library('hFile');
        $this->hFinderIcons = $this->library('hFinder/hFinderIcons');

        if (is_array($this->hFinderFilterPaths) && !$this->inGroup('root'))
        {
            $this->hFile->setFilterPaths($this->hFinderFilterPaths);
        }
    }

    public function setFilterPaths(array $paths)
    {
        $hFinderPaths = is_array($this->hFinderFilterPaths)? $this->hFinderFilterPaths : array();

        $this->hFile->setFilterPaths(
            array_merge(
                $hFinderPaths,
                $paths
            )
        );
    }

    public function setFileTypes(array $fileTypes)
    {
        $hFinderFileTypes = is_array($this->hFinderFileTypes)? $this->hFinderFileTypes : array();

        $this->hFile->setFileTypes(
            array_merge(
                $hFinderFileTypes,
                $fileTypes
            )
        );
    }

    /**
    * Returns HTML that can be used for a file tree.
    *
    * @param   (int) $dir The base directory for the tree.
    * @return  (string)
    *
    * @access  public
    * @todo    Need to make this only get top-level directories, then use AJAX to grab
    *          sub-directories since file systems can become large and cumbersome.
    */
    public function getTree($includeEmptyFiles = true)
    {
        $categories = false;

        if (isset($_GET['dialogue']) && $_GET['dialogue'] == 'Directory' && isset($_GET['path']) && ($_GET['path'] == '/Categories' || $this->beginsPath($_GET['path'], '/Categories')) || $this->beginsPath($this->hFinderTreeDefaultPath, '/Categories'))
        {
            if ($this->beginsPath($this->hFinderTreeDefaultPath, '/Categories'))
            {
                $path = $this->hFinderTreeDefaultPath;
            }
            else
            {
                $path = '/Categories';
            }

            $categories = true;
        }
        else if (isset($_GET['path']) && isset($_GET['setDefaultPath']))
        {
            $path = $_GET['path'];
        }
        else
        {
            $path = $this->inGroup('root') && $this->hFinderTreeRootOverrideDefaultPath(true)? '/' : $this->hFinderTreeDefaultPath('/');
        }

        $html = '';

        $this->hFile->query($path);

        if ($this->hFinderTreeHomeDirectory(true))
        {
            $hUserName = $this->user->getUserName();

            $html .= $this->getTemplate(
                'Home Tree',
                array(
                    'hUserName'         => $hUserName,
                    'hDirectoryId'      => $this->getDirectoryId('/Users/'.$hUserName),
                    'hFinderTreeBranch' => $this->getBranch('/Users/'.$hUserName, $includeEmptyFiles)
                )
            );
        }

        if ($this->hFile->isElevatedUser || $this->hFinderTreeRoot(false))
        {
            $html .= $this->getTemplate(
                'Root Tree',
                array(
                    'hFinderTreeClass'  => $categories? ' hFinderTreeCategoriesRoot' : '',
                    'hDirectoryId'      => $categories? 'hCategoryId0' : $this->hFile->directoryId,
                    'hDirectoryPath'    => $path,
                    'hFinderDiskName'   => $categories? $this->hFinderCategoriesDiskName('Categories') : $this->hFinderDiskName($this->hServerHost),
                    'hFinderTreeBranch' => $this->getBranch($path, $includeEmptyFiles)
                )
            );
        }

        return $html;
    }

    public function getBranch($path, $includeEmptyFiles = true)
    {
        $html = '';

        $directories = $this->hFile->getAllDirectories($path);

        if (is_array($directories))
        {
            foreach ($directories as $directory)
            {
                $html .= $this->getTemplate(
                    'Directory',
                    array(
                        'hDirectoryId'                    => $directory['hDirectoryId'],
                        'hDirectoryPath'                  => $this->getEncodedPath($directory['hFilePath']),
                        'hFinderTreeIconClassName'        => ' '.$this->hFinderIcons->getIconClassName($this->hFile->getDirectoryPseudoMIME($directory['hFilePath'], $directory['hFileIconId']), '16x16'),
                        'hFileName'                       => $directory['hFileName'],
                        'hDesktopApplicationStyle'        => $this->hDesktopApplicationStyle(false),
                        'hasChildren'                     => $directory['hDirectoryCount'] > 0 || $this->isServerPath($directory['hFilePath'], true) ||  $this->hFinderTreeDisplayFiles(false) && $directory['hFileCount'] > 0,
                        'isHidden'                        => substr($directory['hFileName'], 0, 1) == '.',
                        'isServer'                        => $this->isServerPath($directory['hFilePath']),
                        'isApplication'                   => !empty($directory['hDirectoryIsApplication']),
                        'hFileMIME'                       => $directory['hFileMIME']
                    )
                );
            }
        }

        if ($this->hFinderTreeDisplayFiles(false))
        {
            $files = $this->hFile->getAllFiles($path);

            foreach ($files as $file)
            {
                if ($includeEmptyFiles || !$includeEmptyFiles && (int) $file['hFileSize'] > 0)
                {
                    $html .= $this->getTemplate(
                        'File',
                        array(
                            'hFileId'              => $file['hFileId'],
                            'hFilePath'            => $this->getEncodedPath($file['hFilePath']),
                            'hFinderTreeIconClass' => ' '.$this->hFinderIcons->getIconClassName($file['hFileMIME'], '16x16', $file['hFileName']),
                            'hFileName'            => $file['hFileName'],
                            'isHidden'             => substr($file['hFileName'], 0, 1) == '.',
                            'isServer'             => $this->isServerPath($file['hFilePath']),
                            'hFileMIME'            => $file['hFileMIME']
                        )
                    );
                }
            }
        }

        return $this->getTemplate(
            'Branch',
            array(
                'hFinderTreeBranch' => $html
            )
        );
    }

    public function getEncodedPath($hFilePath)
    {
        $paths = explode('/', hString::entitiesToUTF8($hFilePath, false));

        foreach ($paths as &$path)
        {
            $path = urlencode($path);
        }

        return implode('/', $paths);
    }
}

?>