<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Panel Plugin
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

class hPreferences extends hPlugin {

    private $hFileIcon;
    private $hPanel;

    public function hConstructor()
    {
        $this->hFileTitleAppend = '';
        $this->hFileTitlePrepend = '';

        $this->plugin('hApplication/hApplicationForm');

        $hPanels = array();

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        $this->loadConfigurationFile(dirname(__FILE__).'/JSON/Preferences');

        $this->plugin('hPanel');
    }
}

?>