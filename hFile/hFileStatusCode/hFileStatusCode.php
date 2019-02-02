<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Status Code
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

class hFileStatusCode extends hPlugin {

    public function hConstructor()
    {
        if ($this->hCoreMetricsClientId)
        {
            $this->hFileDocument .= $this->getTemplate(
                'Core Metrics',
                array(
                    'hFileStatusCode' => $this->hFileStatusCode,
                    'hServerRequestURI' => $this->hServerRequestURI
                )
            );
        }

        $this->getPluginCSS();

        if (!$this->hFileTitle || $this->hFileTitle == 'Status Code')
        {
            $this->hFileTitle = 'File Not Found';
        }

        if (!$this->hFileHeadingTitle)
        {
            $this->hFileHeadingTitle = 'File Not Found';
        }

        if (!$this->hFileStatusCodeText)
        {
            $this->hFileStatusCodeText = '';
        }

        if (!$this->hFileDocument)
        {
            $this->hFileDocument = $this->getTemplate(
                "404",
                array(
                    'hServerRequestURI' => $this->hServerRequestURI
                )
            );
        }
    }
}

?>