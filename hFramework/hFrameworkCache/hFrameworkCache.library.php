<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Cache Library
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

class hFrameworkCacheLibrary extends hPlugin {

    private $hFile;
    private $hFileUtilities;
    private $hFileCSSCompress;
    private $hFileJSCompress;

    private $documentRoot;

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');

        $this->hFileCSSCompress = $this->library('hFile/hFileCSSCompress');
        $this->hFileJSCompress = $this->library('hFile/hFileJSCompress');

        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'autoScanEnabled' => false,
                'includeFileTypes' => array(
                    #'ico', 'png', 'jpg', 'jpe', 'gif', 'tif', 'tiff', 'svg',
                    #'xml',
                    #'swf', 'flv', 'mpg', 'mpeg', 'mp4', 'mpv', 'avi'
                    #'mp3', 'wav',
                    #'doc', 'xls', 'docx', 'xlsx', 'ppt', 'pptx', 'dot',
                    #'zip',
                    #'pages', 'numbers', 'keynote',
                    #'pdf'
                ),
                'excludeFileTypes' => array(
                    'sql',
                    'json',
                    'csv',
                    'php',
                    'rb',
                    'conf'
                ),
                'excludeFolders' => array(
                    'HTML',
                    'SQL',
                    'Cache'
                ),
                'scanTextFiles' => false
            )
        );

        $this->documentRoot = $this->hFrameworkPath.$this->hServerDocumentRootName('/www');
    }

    public function delete()
    {
        $this->rm($this->documentRoot.'/Template');

        # A previously cached to document root folder or file could be deleted from its original
        # location.  If deleted, there is no way to know the file was removed so that the
        # correpsonding file can be removed from document root.  So to get around this, a listing
        # of every file and folder is kept in the database.
        $query = $this->hServerDocumentRootFiles->select(
            array(
                'hServerDocumentRootFilePath',
                'hServerDocumentRootFileIsFolder'
            ),
            array(),
            'AND',
            array(
                'hServerDocumentRootFilePath',
                'DESC'
            )
        );

        foreach ($query as $data)
        {
            if ((int) $data['hServerDocumentRootFileIsFolder'])
            {
                $this->rm($data['hServerDocumentRootFilePath']);
            }
            else
            {
                $this->rm($data['hServerDocumentRootFilePath'], true);
            }
        }

        $this->hServerDocumentRootFiles->truncate();
    }

    public function go()
    {
        # Make copies of various files in DOCUMENT_ROOT, this makes Hot Toddy much more
        # efficient, since it reduces load on the database and cpu by routing less through
        # Hot Toddy, when possible.
        $this->delete();

        $templatePictures = $this->hFrameworkPicturesPath($this->hFrameworkPath.'/Pictures');

        $this->makePath('/Template/Pictures');

        $this->copy(
            $templatePictures,
            $this->documentRoot.'/Template',
            true
        );

        # Hot Toddy
        $hotToddy = $this->hFrameworkPath.'/Hot Toddy';

        $this->hFileUtilities->scanFiles($hotToddy);

        $files = $this->hFileUtilities->getFiles();
        $folders = $this->hFileUtilities->getFolders();

        $this->copyFolders(
            $hotToddy,
            $folders
        );

        $this->copyFiles(
            $hotToddy,
            $files,
            false
        );

        $this->hFileUtilities->resetFilesAndFolders();

        # Plugins
        $privatePlugins = $this->hFrameworkPluginRoots(
            array("/Plugins")
        );

        foreach ($privatePlugins as $privatePlugin)
        {
            $privatePluginPath = $this->hFrameworkPath.$privatePlugin;

            $this->console("Private plugin path '{$privatePluginPath}'");

            $this->hFileUtilities->scanFiles($privatePluginPath);

            $files = $this->hFileUtilities->getFiles();
            $folders = $this->hFileUtilities->getFolders();

            $this->copyFolders(
                $privatePluginPath,
                $folders
            );

            $this->copyFiles(
                $privatePluginPath,
                $files,
                false
            );

            $this->hFileUtilities->resetFilesAndFolders();
        }

        # Library
        $library = $this->hFrameworkLibraryPath;

        $this->hFileUtilities->setExcludeFolders(
            array(
                'Arc90_Service_Twitter',
                'bin',
                'CoreImageTool',
                'Packer',
                'PEAR',
                'phpFlickr',
                'qlpreview',
                'Tidy',
                'toSqlite'
            )
        );

        $this->hFileUtilities->scanFiles($library);

        $files = $this->hFileUtilities->getFiles();
        $folders = $this->hFileUtilities->getFolders();

        $this->copyFolders($this->hFrameworkPath, $folders);
        $this->copyFiles($this->hFrameworkPath, $files);

        $this->hFileUtilities->resetFilesAndFolders();
    }

    public function copyFolders($basePath, $folders)
    {
        foreach ($folders as $folder)
        {
            $folderPath = $this->getEndOfPath(
                $folder,
                $basePath
            );

            $this->hFile->makeServerPath($this->documentRoot.$folderPath);

            $this->hServerDocumentRootFiles->insert(
                array(
                    'hServerDocumentRootFilePath' => $this->documentRoot.$folderPath,
                    'hServerDocumentRootFileIsFolder' => 1
                )
            );
        }
    }

    public function copyFiles($basePath, $files, $copyCSS = false)
    {
        $this->console("Caching to document root with base path '{$basePath}'");

        foreach ($files as $file)
        {
            $filePath = $this->getEndOfPath($file, $basePath);

            $extension = $this->getExtension($file);

            switch ($extension)
            {
                case 'css':
                {
                    if ($copyCSS)
                    {
                        $document = $this->hFileCSSCompress->get($file);
                        file_put_contents($this->documentRoot.$filePath, $document);
                    }

                    break;
                }
                case 'js':
                {
                    $document = $this->hFileJSCompress->get($file);

                    file_put_contents(
                        $this->documentRoot.$filePath,
                        $document
                    );

                    break;
                }
                default:
                {
                    $this->copy(
                        $file,
                        $this->documentRoot.$filePath
                    );
                }
            }

            $this->console('Cached to document root: '.$this->documentRoot.$filePath);

            $this->hServerDocumentRootFiles->insert(
                array(
                    'hServerDocumentRootFilePath' => $this->documentRoot.$filePath,
                    'hServerDocumentRootFileIsFolder' => 0
                )
            );
        }
    }

    public function makePath($path)
    {
        $this->hFile->makeServerPath($this->documentRoot.$path);
    }
}

?>