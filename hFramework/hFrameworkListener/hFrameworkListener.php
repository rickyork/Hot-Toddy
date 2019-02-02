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

abstract class hListenerPlugin extends hPlugin {

    public function XML($xml, $template = true)
    {
        $this->hFrameworkListener->XML($xml, $template);
    }

    public function HTML($html)
    {
        $this->hFrameworkListener->HTML($html);
    }

    public function JSON($js)
    {
        $this->hFrameworkListener->JSON($js);
    }

    public function response(&$html, &$js, &$xml)
    {
        $this->hFrameworkListener->response($html, $js, $xml);
    }

    public function validation($methods, $method)
    {
        $this->hFrameworkListener->validation($methods, $method);
    }
}

abstract class hListenerApplication extends hFrameworkApplication {

    public function XML($xml, $template = true)
    {
        $this->hFrameworkListener->XML($xml, $template);
    }

    public function HTML($html)
    {
        $this->hFrameworkListener->HTML($html);
    }

    public function JSON($js)
    {
        $this->hFrameworkListener->JSON($js);
    }

    public function response(&$html, &$js, &$xml)
    {
        $this->hFrameworkListener->response($html, $js, $xml);
    }

    public function validation($methods, $method)
    {
        $this->hFrameworkListener->validation($methods, $method);
    }
}

class hFrameworkListener extends hPlugin {

    private $hListener;
    private $responseSet = false;

    public function hConstructor()
    {
        $this->hTemplatePath = '';
        $this->hFileDisableCache = true;
        $this->hFileEnableCache = false;
        $this->hFileDocumentParseEnabled = false;

        if (isset($_GET['hDesktopApplicationStyle']))
        {
            $this->hDesktopApplicationStyle = 1;
        }

        if ($this->hFrameworkListenerMethod)
        {
            $this->hListenerMethod = $this->hFrameworkListenerMethod;
            $this->hPluginListenerMethod = $this->hFrameworkListenerMethod;

            $this->hListener = $this->plugin($this->hFrameworkListenerPlugin);

            if (!method_exists($this->hListener, 'JSON'))
            {
                $this->warning('Listener object '.$name.' does not extend hListenerPlugin or hListenerApplication');
            }

            // Break away if the listener set a response in the constructor.
            if (is_object($this->hListener) && is_object($this->hListener->hFrameworkListener) && $this->hListener->hFrameworkListener->responseSet)
            {
                return;
            }

            $this->hListener->{"{$this->hFrameworkListenerMethod}"}();
        }
    }
}

?>