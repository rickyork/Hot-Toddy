<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy HTTP
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
# <h1>HTTP API</h1>
# <p>
#   This object contains methods that modify outgoing HTTP headers as required for
#   Hot Toddy's operation.
# </p>
# @end

class hHTTP extends hFrameworkResources {

    public function &setDownload($mime, $name = null)
    {
        # @return void

        # @description
        # <h2>Setting Download Parameters</h2>
        # <p>
        #   Sets HTTP variables required to trigger a forced download. The MIME type is provided
        #   in the <var>$mime</var> argument. Optionally, the file name can be provided in the
        #   <var>$name</var> argument. If no <var>$name</var> is provided the current value of
        #   <var>hFileName</var> is used as the file name. If <var>$name</var> is provided, then
        #   <var>$name</var> overwrites the current value of <var>hFileName</var>.
        # </p>
        # @end

        $this->hFileMIME        = $mime;
        $this->hTemplatePath    = '';
        $this->hFileHTMLHeaders = false;
        $this->hFileDownload    = true;

        if (!empty($name))
        {
            $this->hFileName = $name;
        }
    }

    public function range()
    {
        # @return void

        # @description
        # <h2>Defining an HTTP Range</h2>
        # <p>
        #
        # </p>
        # @end

        # The HTTP_RANGE header is used is pausable / resumable downloads
        # as well as with video streaming.
        if (isset($_SERVER['HTTP_RANGE']))
        {
            # The value of HTTP_RANGE looks like: bytes=2908234-2340983234
            $this->hFileRange = substr($_SERVER['HTTP_RANGE'], 6);

            $bits = explode('-', $this->hFileRange);

            if ($bits[0] > 0)
            {
                $this->hFileRangeStart = (int) $bits[0];
            }

            $this->hFileRangeEnd = $bits[1] > 0? (int) $bits[1] : -1;

            if ($this->hFileRangeEnd < $this->hFileRangeStart)
            {
                $this->hFileRangeEnd = $this->hFileSize - 1;
            }
        }
        else if (isset($_GET['start']))
        {
            $this->hFileRange = 1;
            $this->hFileRangeStart = (int) $_GET['start'];
        }
    }

    protected function setHTTPHeaders()
    {
        # @return void

        # @description
        # <h2>Setting HTTP Headers</h2>
        # <p>
        #   Sets the outgoing HTTP headers based on file variables and properties set up
        #   to the point of calling this method.
        # </p>
        # @end

        $mime = $this->hFileMIME('text/html');

        $movie = $this->isMovie($this->hFileName, $mime);
        $audio = $this->isAudio($this->hFileName, $mime);

        if ($movie || $audio)
        {
            header('Pragma: public');
        }

        header('Accept-Ranges: bytes');
        header('X-UA-Compatible: IE=Edge');
        header('X-Powered-By: Hot Toddy/'.$this->hFrameworkVersion.', PHP/'.phpversion());

        if ($this->hFileRange(false))
        {
            header('HTTP/1.0 206 Partial Content');
            header('Status: 206 Partial Content');
            header('Content-Range: bytes '.$this->hFileRangeStart(0).'-'.$this->hFileRangeEnd.'/'.$this->hFileSize);
        }
        else
        {
            header("HTTP/1.0 200 OK");
        }

        if ($this->hFileContentLength(0))
        {
            header("Content-Length: ".$this->hFileContentLength);
        }

        $mime = $this->hFileMIME('text/html');

        switch ($mime)
        {
            case 'text/html':
            case 'application/xml':
            case 'application/xhtml+xml':
            case 'text/plain':
            case 'text/css':
            case 'text/xml':
            case 'text/javascript':
            case 'application/json':
            case 'application/javascript':
            case 'application/x-javascript':
            case 'application/opensearchdescription+xml':
            case 'application/rss+xml':
            case 'image/svg+xml':
            {
                header('Content-type: '.$mime.'; charset=UTF-8', true);
                break;
            }
            case 'image/png':
            case 'image/x-png':
            case 'image/pjpeg':
            case 'image/jpeg':
            case 'image/gif':
            case 'application/pdf':
            case 'application/x-shockwave-flash':
            case 'video/x-flv':
            case 'video/x-m4v':
            case 'video/mp4':
            {
                header('Content-type: '.$mime, true);
                header('Content-Transfer-Encoding: binary');
                break;
            }
            default:
            {
                header('Content-type: '.$mime, true);

                // Need to write a ditty to auto-detect binary.
                if (!stristr($mime, 'text/'))
                {
                    header('Content-Transfer-Encoding: binary');
                }

                $ext = $this->getExtension($this->hFileName);

                $download = true;

                if (!$movie && !$audio && $download)
                {
                    // The catch all will force a download, inline
                    // types have to be explicitly defined.
                    $this->hFileDownload = true;
                }
            }
        }

        if (isset($_GET['hFileForceDownload']))
        {
            $this->hFileDownload = true;
        }

        if ($mime == 'text/html')
        {
            $this->hFileEnableCache = false;
            $this->hFileDisableCache = true;
        }

        # Explicitly say when the cache expires
        if ($this->hFileEnableCache(true))
        {
            header('Expires: '.gmdate('D, d M Y H:i:s', ($this->hFileCacheExpires(time() + 86400))).' GMT', true);
            header('Cache-Control: public', true);
            header('Pragma: public', true);
            header('Date: '.gmdate('D, d M Y H:i:s', time()).' GMT', true);
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->hFileLastModified).' GMT', true);
        }

        if ($this->hFileDisableCache(false))
        {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT', true);
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', time() - 86400).' GMT', true);
            header('Cache-Control: private', true);
        }

        # The file name must be enclosed in quotes if there are spaces.

        header(
            'Content-Disposition: '.($this->hFileDownload(false)? 'attachment' : 'inline').'; '.
            'Filename="'.$this->hFileName.'"', true
        );
    }

    public function setCookie($name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $http_only = false)
    {
        # @return void

        # @description
        # <h2>Setting a Cookie</h2>
        # <p>
        #   Sets a cookie.
        # </p>
        # @end

        header(
            'Set-Cookie: '.rawurlencode($name) . '=' . rawurlencode($value) .
            (empty($expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s \\G\\M\\T', $expires)).
            (empty($path)    ? '' : '; path=' . $path).
            (empty($domain)  ? '' : '; domain=' . $domain).
            (!$secure        ? '' : '; secure').
            (!$http_only    ? '' : '; HttpOnly'),
            false
        );
    }

    public function getCookieDomain()
    {
        # @reteurn string

        # @description
        # <h2>Getting the Cookie Domain</h2>
        # <p>
        #   Returns the current domain used for cookies.
        # </p>
        # @end

        # Wildcard "www." to all subdomains.
         return (substr($this->hServerHost, 0, 4) == 'www.')? substr($this->hServerHost, 3) : $this->hServerHost;
    }
}

?>