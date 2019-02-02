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