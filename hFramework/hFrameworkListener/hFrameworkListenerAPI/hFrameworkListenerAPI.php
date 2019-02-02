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

class hFrameworkListenerAPI extends hPlugin {

    public $responseSet = false;

    public function hConstructor()
    {

    }

    public function methodSetResponse()
    {
        return $this->responseSet;
    }

    public function XML($xml, $template = true)
    {
        $this->responseSet = true;

        $GLOBALS['hFramework']->hTemplatePath = '';
        $GLOBALS['hFramework']->hFileMIME     = 'application/xml';
        $GLOBALS['hFramework']->hFileName     = 'plugin.xml';

        $GLOBALS['hFramework']->hFileDocument = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?".">\n";

        if ($template)
        {
            $GLOBALS['hFramework']->hFileDocument .= "<response>{$xml}</response>";
        }
        else
        {
            $GLOBALS['hFramework']->hFileDocument = $xml;
        }
    }

    public function HTML($html)
    {
        $this->responseSet = true;

        $GLOBALS['hFramework']->hTemplatePath = '';
        $GLOBALS['hFramework']->hFileMIME     = 'text/html';
        $GLOBALS['hFramework']->hFileName = 'plugin.html';
        $GLOBALS['hFramework']->hFileDocument = $html;
    }

    public function JSON($js)
    {
        // This hack for negative numbers is sometimes necessary.
        if (is_numeric($js) && $js < 0)
        {
            $js = (string) $js;
        }

        if (!function_exists('json_encode'))
        {
            // PHP4, yuk!
            $this->setToPHP4();
            include_once 'Services/JSON.php';
        }

        $this->responseSet = true;

        $GLOBALS['hFramework']->hTemplatePath = '';
        $GLOBALS['hFramework']->hFileMIME = isset($_GET['debug'])? 'application/javascript' : 'application/json';
        $GLOBALS['hFramework']->hFileName     = 'plugin.json';
        $GLOBALS['hFramework']->hFileDocument = trim(json_encode($js));

        $this->setToDefault();
    }

    public function response(&$html, &$js, &$xml)
    {
        $this->responseSet = true;

        switch (true)
        {
            case (isset($html)):
            {
                $this->HTML($html);
                break;
            }
            case (isset($js)):
            {
                $this->JSON($js);
                break;
            }
            case (isset($xml)):
            {
                $this->XML($xml);
                break;
            }
            default:
            {
                $GLOBALS['hFramework']->warning('No HTML, JS, or XML was passed, cannot output a value.', __FILE__, __LINE__);
            }
        }
    }

    public function validation($methods, $method)
    {
        hString::scrubArray($_GET);

        $xml = 1;

        if ($xml > 0)
        {
            switch (true)
            {
                case (!$this->inGroup('root')):
                {
                    $xml = -1;
                    break;
                }
                case isset($methods[$method]['isset']):
                {
                    $variables = array('_GET', '_POST', '_COOKIE');

                    foreach ($variables as $variable)
                    {
                        if (isset($methods[$method]['isset'][$variable]))
                        {
                            foreach ($methods[$method]['isset'][$variable] as $key)
                            {
                                if (!isset($_GET[$key]))
                                {
                                    $xml = -5;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $xml;
    }
}

?>