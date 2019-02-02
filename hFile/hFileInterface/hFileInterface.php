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

abstract class hFileInterface extends hPlugin {

    protected $hFile;

    public function __construct($hPluginPath)
    {
        parent::__construct($hPluginPath);
    }

    public function getFileObject()
    {
        if (!is_object($this->hFile))
        {
            $this->hFile = $this->library('hFile');
        }
    }

    public function &__get($key)
    {
        $this->getFileObject();

        if (in_array($key, $this->hFile->variables))
        {
            return $this->hFile->__get($key);
        }
        else
        {
            return parent::__get($key);
        }
    }

    public function __set($key, $value)
    {
        $this->getFileObject();

        if (in_array($key, $this->hFile->variables))
        {
            $this->hFile->__set($key, $value);
        }
        else
        {
            parent::__set($key, $value);
        }
    }

    public function __call($method, $arguments)
    {
        $this->getFileObject();

        if (method_exists($this->hFile, $method))
        {
            return call_user_func_array(
                array(
                    $this->hFile,
                    $method
                ),
                $arguments
            );
        }
        else
        {
            return $this->hFile->__call(
                $method,
                $arguments
            );
        }
    }

    abstract public function getMIMEType();
    // {}

    abstract public function getTitle();
    // {}

    abstract public function getDescription();
    // {}

    abstract public function getSize();
    // {}

    abstract public function getLastModified();
    // {}

    abstract public function getCreated();
    // {}

    abstract public function hasChildren($countFiles = false);
    // {}

    abstract public function getDirectories();
    // {}

    abstract public function getFiles();
    // {}

    abstract public function rename($newName);
    // {}

    abstract public function delete();
    // {}

    abstract public function newDirectory($newDirectoryName, $hUserId = 0);
    // {}
}

?>