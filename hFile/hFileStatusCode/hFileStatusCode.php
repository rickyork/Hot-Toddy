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