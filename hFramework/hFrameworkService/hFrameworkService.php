<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Service
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

abstract class hService extends hPlugin {

    public function XML($xml, $template = true)
    {
        $this->hFrameworkService->XML($xml, $template);
    }

    public function HTML($html)
    {
        $this->hFrameworkService->HTML($html);
    }

    public function JSON($js)
    {
        $this->hFrameworkService->JSON($js);
    }

    public function response(&$html, &$js, &$xml)
    {
        $this->hFrameworkService->response($html, $js, $xml);
    }

    public function validation($methods, $method)
    {
        $this->hFrameworkService->validation($methods, $method);
    }
}

abstract class hServiceApplication extends hFrameworkApplication {

    public function XML($xml, $template = true)
    {
        $this->hFrameworkService->XML($xml, $template);
    }

    public function HTML($html)
    {
        $this->hFrameworkService->HTML($html);
    }

    public function JSON($js)
    {
        $this->hFrameworkService->JSON($js);
    }

    public function response(&$html, &$js, &$xml)
    {
        $this->hFrameworkService->response($html, $js, $xml);
    }

    public function validation($methods, $method)
    {
        $this->hFrameworkService->validation($methods, $method);
    }
}

class hFrameworkService extends hPlugin {

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

        if ($this->hFrameworkServiceMethod)
        {
            $this->hServiceMethod = $this->hFrameworkServiceMethod;

            $service = $this->plugin($this->hFrameworkServicePlugin);

            if (!method_exists($service, 'JSON'))
            {
                $this->warning(
                    'Service object '.$name.' does not extend hService or hServiceApplication',
                    __FILE__,
                    __LINE__
                );
            }

            // Break away if the listener set a response in the constructor.
            if (is_object($service) && is_object($service->hFrameworkService) && $service->hFrameworkService->responseSet)
            {
                return;
            }

            $service->{"{$this->hFrameworkServiceMethod}"}();
        }
    }
}

?>