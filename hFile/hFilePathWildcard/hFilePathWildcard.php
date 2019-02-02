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
# @description
# <h1>File Path Wildcard API</h1>
# @end

class hFilePathWildcard extends hPlugin {

    public function hConstructor()
    {
        if (!$this->hFilePath && isset($GLOBALS['uri']['path']))
        {
            $this->setPath($GLOBALS['uri']['path']);

            $query = $this->hFilePathWildcards->select(
                array(
                    'hFilePathWildcard',
                    'hFileId'
                )
            );

            foreach ($query as $data)
            {
                if ($this->beginsPath($GLOBALS['uri']['path'], $data['hFilePathWildcard']))
                {
                    $this->hFileWildcardPath = $GLOBALS['uri']['path'];

                    $filePath = $this->getFilePathByFileId($data['hFileId']);

                    $this->setPath($filePath);

                    $this->fire->setFileWildcardPath($filePath);

                    return;
                }
            }

            if ($this->isServerPath($GLOBALS['uri']['path']))
            {
                // Allow files that don't have extensions, if they're elsewhere on the server.
                $this->hFileServerPath = $GLOBALS['uri']['path'];

                $filePath = $this->getFilePathByPlugin('hFile/hFileServer');

                $this->setPath($filePath);

                $this->fire->setFileWildcardPath($filePath);

                return;
            }
            else if ($this->isServicePath($GLOBALS['uri']['path']) && !strstr($GLOBALS['uri']['path'], '.sql'))
            {
                $this->hFileServicePath = $GLOBALS['uri']['path'];
                $this->hServerOutputBuffer = true;

                $filePath = $this->getFilePathByPlugin('hFramework/hFrameworkService');

                $this->setPath($filePath);

                $this->fire->setFileWildcardPath($filePath);

                return;
            }
            else if ($this->isListenerPath($GLOBALS['uri']['path']) && !strstr($GLOBALS['uri']['path'], '.sql'))
            {
                $this->hFileListenerPath = $GLOBALS['uri']['path'];
                $this->hServerOutputBuffer = true;

                $filePath = $this->getFilePathByPlugin('hFramework/hFrameworkListener');

                $this->setPath($filePath);

                $this->fire->setFileWildcardPath($filePath);

                return;
            }
        }
    }
}

?>