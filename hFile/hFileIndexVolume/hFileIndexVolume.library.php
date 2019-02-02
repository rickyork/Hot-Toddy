<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Index Volume Library
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

class hFileIndexVolumeLibrary extends hPlugin {

    private $hFile;
    private $hFileDatabase;
    private $hFileConvert;

    private $frameworkVolumePath;
    private $files = array();

    public function hConstructor()
    {
        $this->hFileDatabase = $this->database('hFile');
        $this->hFile = $this->library('hFile');
        $this->hFileConvert = $this->library('hFile/hFileConvert');

        if (!$this->hFile->exists('/Volumes'))
        {
            $directoryId = $this->hFile->newDirectory('/', 'Volumes', 1);

            $this->setDirectoryProperties(
                $directoryId,
                'directory/volumes'
            );
        }
    }

    public function setVolumeName($name)
    {
        if (!$this->hFile->exists('/Volumes/'.$name))
        {
            $directoryId = $this->hFile->newDirectory('/Volumes', $name, 1);

            $this->setDirectoryProperties(
                $directoryId,
                'directory/sharepoint'
            );
        }

        $this->frameworkVolumePath = '/Volumes/'.$name;
    }

    private function setDirectoryProperties($directoryId, $mime)
    {
        $this->hDirectoryProperties->insert(
            array(
                'hDirectoryId' => $directoryId,
                'hFileIconId' => $this->hFileIcons->selectColumn(
                    'hFileIconId',
                    array(
                        'hFileMIME' => $mime
                    )
                ),
                'hDirectoryIsApplication' => 0,
                'hDirectoryLabel' => ''
            )
        );
    }

    public function index($volume)
    {
        $timer = hFrameworkBenchmarkMicrotime();

        // Oh, dear, this could get crazy.
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);

        $this->files = array();

        $this->getFileIndex($volume);

        $frameworkPaths = array(
            $this->frameworkVolumePath
        );

        foreach ($this->files as $file)
        {
            $path = $file['hFilePath'];

            $basePath = $this->getEndOfPath($path, $volume);

            $frameworkPath = hString::escapeAndEncode(
                $this->getConcatenatedPath(
                    $this->frameworkVolumePath,
                    $basePath
                )
            );

            if ($file['isDirectory'])
            {
                if (!$this->hFile->exists($frameworkPath))
                {
                    $this->hFile->newDirectory(
                        dirname($frameworkPath),
                        basename($frameworkPath),
                        1
                    );
                }
            }
            else
            {
                $bits = explode(';', $file['hFileMIME']);
                $mime = trim($bits[0]);

                $bits = explode('=', $bits[1]);
                $charset = trim($bits[1]);

                $hFile = array(
                    'hUserId' => 1,
                    'hFileName' => basename($frameworkPath),
                    'hFileCreated' => (int) $file['hFileCreated'],
                    'hFileLastModified' => (int) $file['hFileLastModified'],
                    'hFileMIME' => $mime,
                    'hFileSystemPath' => $volume,
                    'hFileDocument' => hString::escapeAndEncode(
                        trim(
                            $this->hFileConvert->getPlainText(
                                $path,
                                $mime,
                                $charset
                            )
                        )
                    ),
                    'hDirectoryId' => $this->getDirectoryId(
                        dirname($frameworkPath)
                    ),
                    'hUserPermissions' => true,
                    'hUserPermissionsOwner' => 'rw',
                    'hUserPermissionsWorld' => ''
                );

                $this->hFileDatabase->save($hFile);
            }

            array_push(
                $frameworkPaths,
                $frameworkPath
            );

            echo "Original Path: ".$path."\n";
            echo "Base Path: ".$basePath."\n";
            echo "Framework Path: ".$frameworkPath."\n\n";
        }

        // Verify folders
        $directories = $this->hDirectories->select(
            'hDirectoryPath',
            array(
                'hDirectoryPath' => array(
                    array(
                        '=',
                        $this->frameworkVolumePath
                    ),
                    array(
                        'LIKE',
                        $this->frameworkVolumePath.'/%'
                    )
                )
            ),
            'OR'
        );

        foreach ($directories as $directoryPath)
        {
            if (!in_array($directoryPath, $frameworkPaths))
            {
                $this->hFile->delete($directoryPath);
                $this->console("Removed outdated folder {$frameworkPath}");
            }
        }

        $hFiles = $this->hFiles->select(
            array(
                'hFilePath'
            ),
            array(
                'hDirectoryId' => $this->getDirectoryId($this->frameworkVolumePath)
            )
        );

        foreach ($files as $filePath)
        {
            if (!in_array($filePath, $frameworkPaths))
            {
                $this->hFile->delete($filePath);
                $this->console("Removed outdated file {$filePath}");
            }
        }

        $this->console("Index Time: ".round((hFrameworkBenchmarkMicrotime() - $timer), 3)." Seconds");
    }

    /**
      Base path: /Users/richy

      Volume Path: /Volumes/Richard

      Documents: /Users/richy/Documents -> /Volumes/Richard/Documents

      File System Path: /Users/richy
    **/
    public function getFileIndex($path)
    {
        $files = scandir($path);

        foreach ($files as $file)
        {
            $node = $path.'/'.$file;

            if (substr($file, 0, 1) != '.' && $file != '$RECYCLE.BIN')
            {
                if (is_dir($node))
                {
                    $this->files[] = array(
                        'hFilePath' => $node,
                        'isDirectory' => true,
                        'hFileCreated' => filectime($node),
                        'hFileLastModified' => filemtime($node)
                    );

                    $this->getFileIndex($node);
                }
                else
                {
                    $this->files[] = array(
                        'hFilePath' => $node,
                        'isDirectory' => false,
                        'hFileMIME' => trim(`file -Ib "{$node}"`),
                        'hFileCreated' => filectime($node),
                        'hFileLastModified' => filemtime($node)
                    );
                }
            }
        }
    }
}

?>