<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy HTTP Library
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
# <h1>HTTP Library</h1>
# <p>
#   Contains methods for sending and receiving HTTP requests.
# </p>
# @end

class hHTTPLibrary extends hPlugin {

    private $headers = array();

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>HTTP Library Constructor</h2>
        # <p>
        #   Sets the MAX_EXECUTION_TIME to 60 seconds to allow HTTP requests more time to
        #   execute.
        # </p>
        # @end

        ini_set('MAX_EXECUTION_TIME', 60);
    }

    public function &setHeaders(array $headers)
    {
        # @return hHTTPLibrary

        # @description
        # <h2>Setting HTTP Headers</h2>
        # <p>
        #   Sets one or more HTTP headers using an array, each header name is
        #   provided in the array as keys and each corresponding value in array values.
        # </p>
        # @end

        foreach ($headers as $header => $value)
        {
            $this->setHeader($header, $value);
        }

        return $this;
    }

    public function &setHeader($header, $value)
    {
        # @return hHTTPLibrary

        # @description
        # <h2>Setting an Individual HTTP Header</h2>
        # <p>
        #   Sets an individual HTTP header.
        # </p>
        # @end

        if (strstr($header, '-'))
        {
            $words = explode("-", strtolower($header));

            foreach ($words as &$word)
            {
                $word = ucwords($word);
            }

            $header = implode('-', $words);
        }
        else if (!in_array($header, array('SOAPAction')))
        {
            $header = ucwords(strtolower($header));
        }

        $this->headers[$header] = $value;

        return $this;
    }

    public function post($url, $data)
    {
        # @return string

        # @description
        # <h2>Creating an HTTP POST Request</h2>
        # <p>
        #   Sends an HTTP POST request.
        # </p>
        # @end

        $debug = $this->hHTTPDebug(false);

        $uri = parse_url($url);

        $ssl = false;

        if (empty($uri['port']))
        {
            $port = 80;

            if ($uri['scheme'] == 'https')
            {
                $port = 443;
                $ssl = true;
            }
        }
        else
        {
            $port = $uri['port'];
        }

        $fp = fsockopen(($ssl? 'ssl://' : '').$uri['host'], $port);

        if (is_resource($fp))
        {
            if ($debug)
            {
                $request = "POST {$uri['path']} HTTP/1.1\n";
            }

            fputs($fp, "POST {$uri['path']} HTTP/1.1\n");
        }

        if (is_resource($fp))
        {
            if ($debug)
            {
                $request .= "Host: {$uri['host']}\n";
            }

            fputs($fp, "Host: {$uri['host']}\n");
        }

        if (!isset($this->headers['Content-Type']) && is_resource($fp))
        {
            if ($debug)
            {
                $request .= "Content-Type: application/x-www-form-urlencoded\n";
            }

            fputs($fp, "Content-Type: application/x-www-form-urlencoded\n");
        }

        if (is_array($data))
        {
            $post = http_build_query($data, null, '&');
        }
        else
        {
            $post = $data;
        }

        if (is_resource($fp))
        {
            if ($debug)
            {
                $request .= "Content-length: ".strlen($post)."\n";
            }

            fputs($fp, "Content-length: ".strlen($post)."\n");
        }

        if (count($this->headers))
        {
            foreach ($this->headers as $header => $value)
            {
                if (is_resource($fp))
                {
                    if ($debug)
                    {
                        $request .= "{$header}: {$value}\n";
                    }

                    fputs($fp, "{$header}: {$value}\n");
                }
            }
        }

        if (!isset($this->headers['Connection']) && is_resource($fp))
        {
            if ($debug)
            {
                $request .= "Connection: close\n";
            }

            fputs($fp, "Connection: close\n");
        }

        if (is_resource($fp))
        {
            # End Headers
            if ($debug)
            {
                $request .= "\n";
            }

            fputs($fp, "\n");
        }

        if (is_resource($fp))
        {
            if ($debug)
            {
                $request .= $post;
            }

            fputs($fp, $post);
        }

        if ($debug)
        {
            $this->console($request);
        }

        $response = '';

        if (is_resource($fp))
        {
            while (!feof($fp))
            {
                if (is_resource($fp))
                {
                    $response .= fgets($fp, 128);
                }
                else
                {
                    break;
                }
            }
        }

        if (is_resource($fp))
        {
            fclose($fp);
        }

        return $response;
    }
}

?>