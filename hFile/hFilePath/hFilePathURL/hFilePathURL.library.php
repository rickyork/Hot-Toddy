<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Path URL Library
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
# <h1>File Path URL API</h1>
# @end

class hFilePathURLLibrary extends hPlugin {

    public function absolutePathToSelf($SSL = true)
    {
        # @return string

        # @description
        # <p>
        #   Alias of: <a href='#getURL' class='code'>getURL()</a>
        # </p>
        # @end

        return $this->getURL($SSL);
    }

    public function getURL($SSL = true)
    {
        # @return string

        # @description
        # <h2>Getting a URL for the Current File</h2>
        # <p>
        #   Returns a URL to the current file, if SSL is enabled via <var>hFileSSLEnabled</var>,
        #   the URL is <var>https://</var>.
        # </p>
        # @end

        if ($this->hFileSSLEnabled(false) && $SSL)
        {
            # The value of hServerHost should be the SSL domain
            $url = 'https://'.$this->hFrameworkSite;
        }
        else
        {
            # No SSL, keep with the current host if there is one, or fall back on hServerHost.
            $url = 'http://'.(!empty($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST'] : $this->hServerHost);
        }

        $path = $this->hFileWildcardPath($this->hFilePath);

        return $url.$this->cloakSitesPath($path).(strlen($_SERVER['QUERY_STRING']) > 0? '?'.$_SERVER['QUERY_STRING'] : '');
    }

    public function getURLByFileId($fileId = 0, $SSL = true)
    {
        # @return string

        # @description
        # <h2>Getting a URL for a File by File Id</h2>
        # <p>
        #   Returns a URL to the file specified in <var>$fileId</var>, if SSL is enabled via
        #   <var>hFileSSLEnabled</var>, the URL is <var>https://</var>.
        # </p>
        # @end

        if (empty($fileId))
        {
            $fileId = $this->hFileId;
        }

        if ($this->hFileSSLEnabled(false) && $SSL)
        {
            # The value of hServerHost should be the SSL domain
            $url = 'https://'.$this->hFrameworkSite;
        }
        else
        {
            # No SSL, keep with the current host if there is one, or fall back on hServerHost.
            $url = 'http://'.(!empty($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST'] : $this->hServerHost);
        }

        if (empty($fileId))
        {
            $fileId = $this->hFileId;
        }

        $path = $this->getFilePathByFileId($fileId);

        return $url.$this->cloakSitesPath($path);
    }
}

?>