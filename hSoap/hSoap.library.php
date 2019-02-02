<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy SOAP Library
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

class hSoapLibrary extends hPlugin {

    private $hHTTP;
    private $version = 1.2;
    private $uri;

    public function hConstructor()
    {
        $this->hHTTP = $this->library('hHTTP');
    }

    public function setURI($uri)
    {
        $this->uri = $uri;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function request($xml, $method)
    {
        $start = hFrameworkBenchmarkMicrotime();

        $headers = array();
        $contentType = '';

        if ($this->version == 1.2)
        {
            $headers['Content-Type'] = 'application/soap+xml; charset=utf-8; action='.dirname($this->TireServiceURI).'/'.$method;
        }
        else if ($this->version == 1.1)
        {
            $headers['Content-Type'] = 'text/xml; charset=utf-8';
            $headers['SOAPAction'] = '"'.dirname($this->TireServiceURI)."/{$method}\"";

            $xml = str_replace(
                array(
                    'soap12:',
                    'soap12='
                ),
                array(
                    'soap:',
                    'soap='
                ),
                $xml
            );
        }

        $this->hHTTP->setHeaders($headers);

        if ($this->hSoapDebug(false))
        {
            $this->hHTTPDebug = true;
        }

        $response = $this->hHTTP->post($this->uri, $xml);

        if (strstr($response, "\r\n"))
        {
            $response = str_replace(
                "\r\n",
                "\n",
                $response
            );
        }

        $response = trim(
            substr(
                $response,
                strpos(
                    $response,
                    "\n\n"
                )
            )
        );

        $response = str_replace(
            array(
                '<soap:',
                '</soap:',
                '<soap12:',
                '</soap12:',
                '?'.'>',
                '><'
            ),
            array(
                '<',
                '</',
                '<',
                '</',
                '?'.">\n",
                ">\n<"
            ),
            $response
        );

        if ($this->hSoapDebug(false))
        {
            $this->console("\n\n".$response."\n\n");
        }

        $xml = simplexml_load_string($response);

        if ($this->hSoapDebug(false))
        {
            var_dump($xml);
        }

        if (isset($xml->Body->Fault) && isset($xml->Body->Fault->Reason->Text))
        {
            $this->warning(
                $xml->Body->Fault->Reason->Text,
                __FILE__,
                __LINE__
            );
        }

        if (isset($xml->Body->Fault->faultstring))
        {
            $this->warning(
                $xml->Body->Fault->faultstring,
                __FILE__,
                __LINE__
            );
        }

        if (isset($xml->Body->{"{$method}Response"}->{"{$method}Result"}))
        {
            return $xml->Body->{"{$method}Response"}->{"{$method}Result"};
        }

        if (isset($xml->Body))
        {
            return $xml->Body;
        }

        $benchmark = (round((hFrameworkBenchmarkMicrotime() - $start), 3) * 1000);

        $this->console('SOAP Benchmark: '.$benchmark.' Milliseconds');

        return $xml;
    }

    public function getBoolean($value)
    {
        return $value? 'true' : 'false';
    }
}

?>