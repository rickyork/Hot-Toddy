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

class hCoreMetricsTag extends hPlugin {

    public function hConstructor()
    {
        if (!$this->hFileParentId)
        {
            $hFileParentId = ($this->hFileId == 1)? '' : 1;
        }
        else
        {
            $hFileParentId = (int) $this->hFileParentId;
        }

        $this->hFileJavaScript .=
            "    <script type='text/javascript' src='/Library/CoreMetrics/eluminate.js'></script>\n".
            "    <script type='text/javascript' src='/Library/CoreMetrics/cmdatatagutils.js'></script>\n".
            "    <script type='text/javascript'>\n";

        if ($this->hServerIsProduction)
        {
            $this->hFileJavaScript .=
                "      cmSetProduction();\n";
        }

        $searchTerms   = ($this->hSearchTerms)?   "'{$this->hSearchTerms}'" : 'null';
        $searchResults = ($this->hSearchResults)? (int) $this->hSearchResults : 'null';

        if ($this->hCoreMetricsPageView(true))
        {
            $this->hFileJavaScript .=
                "      cmCreatePageviewTag('{$this->hFilePath}', ". (int) $hFileParentId .", {$searchTerms}, {$searchResults});\n";
        }

        $this->hFileJavaScript .=
            "    </script>\n";
    }
}

?>