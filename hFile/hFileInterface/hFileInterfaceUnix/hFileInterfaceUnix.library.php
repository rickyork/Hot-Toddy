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

class hFileInterfaceUnixLibrary extends hFileInterface {

    private $hFileSpotlightMD;

    public $filterPaths = array();
    public $fileTypes = array();

    private $labels = array(
        'none',
        'gray',
        'green',
        'purple',
        'blue',
        'yellow',
        'red',
        'orange'
    );

    public $methodsWereAdded = false;

    public function hConstructor()
    {

    }

    public function shouldBeCalled()
    {
        return $this->isServerPath;
    }

    public function getMethods()
    {
        // Can't use something nice and simple like get_class_methods(),
        // it returns all methods from all parent objects, and I only want
        // this to return the methods from this object only.
        return array(
            'shouldBeCalled',
            'getMIMEType',
            'getTitle',
            'upload',
            'getSize',
            'getDescription',
            'getMetaData',
            'getLastModified',
            'getCreated',
            'hasChildren',
            'getDirectories',
            'getFiles',
            'getLabel',
            'rename',
            'delete',
            'newDirectory'
        );
    }

    // Create a new interface for Mac using the mdls command
    public function getMIMEType()
    {
        return $this->isDirectory? 'Directory' : $GLOBALS['hFramework']->getMIMEType($this->serverPath);
    }

    public function getTitle()
    {
        return '';
    }

    public function upload($files)
    {
        $response = 1;

        // Path is the directory we're uploading to....
        foreach ($files as $fileCounter => $file)
        {
            // Get rid of nefarious characters
            $file['hFileName'] = str_replace(
                array(
                    "\t", '@', "\n", ':', "\\", "/", '(', ')', '[', ']', '{', '}',
                    '&', '$', '#', '!', '*', '^', '%', '+', '-', '=', '~', '`',
                    '?', '<', '>', '"', '\'', '|', ';'
                ),
                '',
                trim($file['hFileName'])
            );

            // Cut out excessive spacing within the file name
            if (strstr($file['hFileName'], '  '))
            {
                while (strstr($file['hFileName'], '  '))
                {
                    $file['hFileName'] = str_replace('  ', ' ', $file['hFileName']);
                }
            }

            $savePath = $this->getConcatenatedPath(
                $this->serverPath,
                $file['hFileName']
            );

            if (file_exists($savePath))
            {
                $file['hFileId'] = $this->fileId;

                if ($file['hFileReplace'])
                {
                    $GLOBALS['hFramework']->move(
                        $file['hFileTempPath'],
                        $savePath
                    );
                }
                else
                {
                    $response = 0;
                }
            }
            else
            {
                $GLOBALS['hFramework']->move(
                    $file['hFileTempPath'],
                    $savePath
                );
            }
        }

        return $response;
    }

    public function getDescription()
    {
        return $this->command('file -b '.escapeshellarg($this->serverPath));
    }

    public function getMetaData()
    {
        if ($this->hOS == 'Darwin')
        {
            $this->hFileSpotlightMD = $this->library('hFile/hFileSpotlight/hFileSpotlightMD');
            return $this->hFileSpotlightMD->get($this->serverPath);
        }

        return array();
    }

    public function getSize()
    {
        return $this->isDirectory?
                $this->bytes((int) $this->command('du -sx '.escapeshellarg($this->serverPath)))
            :
                $this->bytes(@filesize($this->serverPath));
    }

    public function getLastModified()
    {
        return @filemtime($this->serverPath);
    }

    public function getCreated()
    {
        return @filectime($this->serverPath);
    }

    public function hasChildren($countFiles = false)
    {
        if ($this->exists && $this->isDirectory)
        {
            if (false !== ($dh = @opendir($this->serverPath)))
            {
                while (false !== ($file = readdir($dh)))
                {
                    $server_path = $this->getConcatenatedPath($this->serverPath, $file);

                    $type = @filetype($server_path);

                    if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.' && $type == 'dir' || $countFiles && $type == 'file' && $file != '.DS_Store')
                    {
                        closedir($dh);
                        return true;
                    }
                }

                closedir($dh);
            }
        }

        return false;
    }

    public function getDirectories()
    {
        $directories = array();

        if ($this->exists && $this->isDirectory)
        {
            if (false !== ($dh = @opendir($this->serverPath)))
            {
                while (false !== ($file = readdir($dh)))
                {
                    $serverPath = $this->getConcatenatedPath($this->serverPath, $file);

                    $type = @filetype($serverPath);

                    if ($file != '.' && $file != '..' && ($type == 'dir' || $type == 'link') && $file != '.DS_Store')
                    {
                        $virtualPath = $this->getVirtualFileSystemPath($serverPath);

                        $properties = array(
                            'hFileInterfaceObjectId'  => $serverPath,
                            'hFileName'               => $file,
                            'hFilePath'               => $virtualPath,
                            'hFileIsServer'           => true,
                            'hDirectoryId'            => str_replace('=', '', base64_encode($serverPath)).'s',
                            'hDirectoryIsApplication' => false,
                            'hFileIconId'             => 0,
                            'hFileCreated'            => @filectime($serverPath),
                            'hFileLastModified'       => @filemtime($serverPath),
                            'hFileDescription'        => '',
                            'hFileMIME'               => 'directory',
                            'hFileCount'              => $this->hasChildren(true),
                            'hDirectoryCount'         => $this->hasChildren()
                        );

                        if ($this->hOS == 'Darwin')
                        {
                            $meta = $this->hFile->getMetaData($virtualPath);

                            $label = 0;

                            if (isset($meta['kMDItemFSLabel']))
                            {
                                $label = (int) $meta['kMDItemFSLabel'];
                            }
                            else if (isset($meta['FSLabel']))
                            {
                                $label = (int) $meta['FSLabel'];
                            }

                            $properties['hFileLabel'] = $this->labels[$label];
                        }

                        $directories[$file] = $properties;
                    }
                }

                closedir($dh);
            }
            else
            {
                return 403;
            }
        }

        return $directories;
    }

    public function getFiles()
    {
        $files = array();

        if ($this->exists($this->filePath))
        {
            if (false !== ($dh = @opendir($this->serverPath)))
            {
                while (false !== ($file = readdir($dh)))
                {
                    $serverPath = $this->getConcatenatedPath($this->serverPath, $file);

                    if ($file != '.' && $file != '..' && @filetype($serverPath) == 'file' && $file != '.DS_Store')
                    {
                        $virtualPath = $this->getVirtualFileSystemPath($serverPath);

                        $properties = $this->getFileProperties($virtualPath);

                        if ($this->hOS == 'Darwin')
                        {
                            $meta = $this->hFile->getMetaData($virtualPath);

                            $label = 0;

                            if (isset($meta['kMDItemFSLabel']))
                            {
                                $label = (int) $meta['kMDItemFSLabel'];
                            }
                            else if (isset($meta['FSLabel']))
                            {
                                $label = (int) $meta['FSLabel'];
                            }

                            $properties['hFileInterfaceObjectId'] = $serverPath;
                            $properties['hFileLabel'] = $this->labels[$label];
                        }

                        $files[$file] = $properties;
                    }
                }

                closedir($dh);
            }
            else
            {
                return 403;
            }
        }

        return $files;
    }

    public function getLabel()
    {
        if ($this->hOS == 'Darwin')
        {
            $meta = $this->getMetaData();

            $label = 0;

            if (isset($meta['kMDItemFSLabel']))
            {
                $label = (int) $meta['kMDItemFSLabel'];
            }
            else if (isset($meta['FSLabel']))
            {
                $label = (int) $meta['FSLabel'];
            }

            return $this->labels[$label];
        }

        return 'none';
    }

    public function rename($newName)
    {
        // Remember the old path
        $newPath = $this->getConcatenatedPath($this->parentDirectoryPath, $newName);

        // Next step is see if the "renamed" direcory or file already exists.
        // Circumvent namespace clashing in the VFS
        // See if a directory exists, if directory
        // See if a file exists, if file
        if ($this->exists($newPath))
        {
            $rtn = -3;
        }
        else
        {
            $GLOBALS['hFramework']->move($this->serverPath, $this->getServerFileSystemPath($newPath));
            $rtn = 1;
        }

        return $rtn;
    }

    public function delete()
    {
        return $this->rm($this->serverPath, !$this->isDirectory);
    }

    public function newDirectory($newDirectoryName, $hUserId = 0)
    {
        $path = $this->getConcatenatedPath($this->serverPath, $newDirectoryName);

        if (!file_exists($path))
        {
            mkdir($path);
            return str_replace('=', '', base64_encode($path)).'s';
        }
        else
        {
            return -3;
        }
    }
}

?>