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
# @description
# <h1>Console Application</h1>
# <p>
#    Hot Toddy's Console application provides a console for viewing framework 
#    error messages, file status code errors, user activity logs, user document 
#    viewing history, and user account activity.
# </p>
# @end

class hConsole extends hPlugin {

    private $hSearch;

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        $this->hFileCSS = '';
        $this->hFileJavaScript = '';
        
        $this->hEditorTemplateEnabled = false;

        $this->hSearch = $this->library('hSearch');
        $this->hSearch->getCSS();

        $this->getPluginFiles();
        
        $this->hTemplatePath = '/hConsole/hConsole.template.php';

        $this->hFileDocument = $this->getTemplate('Console');
    }
}

?>