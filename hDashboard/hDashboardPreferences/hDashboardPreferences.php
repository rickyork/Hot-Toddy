<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Preferences Plugin
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

class hDashboardPreferences extends hPlugin {

    public function hConstructor()
    {
        $this->hFileCSS = '';
        $this->hFileJavaScript = '';

        $this->getPluginCSS('hTemplate/hTemplateDefault');

        $this->getPluginFiles();

        $this->hTemplatePath = '/hTemplate/hTemplateDefault/hTemplateDefault.template.php';

        $this->HotToddySideBoxHeading = "Preferences";

        $this->HotToddySideBoxContent = "";

        $this->hFileDocument = $this->getTemplate('Preferences');
    }
}

?>