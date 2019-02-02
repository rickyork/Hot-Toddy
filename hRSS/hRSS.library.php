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

class hRSSLibrary extends hPlugin {

    public function get($variables)
    {
        if (!isset($variables['hRSSWebMaster']))
        {
            $variables['hRSSWebMaster'] = $this->hRSSWebMaster($this->hFrameworkAdministrator);
        }

        if (!isset($variables['hRSSLanguage']))
        {
            $variables['hRSSLanguage'] = $this->hRSSLanguage($this->hLanguageLocalization('en-us'));
        }

        if (!isset($variables['hRSSCopyright']))
        {
            $variables['hRSSCopyright'] = "Copyright ".date('Y').", ".$this->hFrameworkName.", All Rights Reserved.";
        }

        if (!isset($variables['hRSSTTL']))
        {
            $variables['hRSSTTL'] = 30;
        }

        if (!isset($variables['hRSSStylesheetPath']) && $this->hRSSStylesheetPath(null))
        {
            $variables['hRSSStylesheetPath'] = $this->hRSSStylesheetPath;
        }
        if (!isset($_GET['test']))
        {
            $this->hFileMIME = 'application/rss+xml';
        }
        else
        {
            $this->hFileMIME = 'text/html';
        }

        $this->hTemplatePath = '';
        $this->hFileDocument = $this->getTemplateXML('RSS', $variables);

        if (isset($_GET['test']))
        {
            $this->hFileDocument =
                str_replace(
                    array(
                        '<rss',
                        '</rss>'
                    ),
                    array(
                        '<test',
                        '</test>'
                    ),
                    $this->hFileDocument
                );
        }
    }
}

?>