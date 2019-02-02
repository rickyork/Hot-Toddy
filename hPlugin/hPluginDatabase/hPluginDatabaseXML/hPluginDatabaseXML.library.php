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
# <h1>Installing Plugins Using XML Configuration Files</h1>
# <p class='hDocumentationWarning'>
#    This method of plugin installation is deprecated!  New plugins must use <b>JSON</b> configuration files!
# </p>
# @end

class hPluginDatabaseXMLLibrary extends hPlugin {

    public function register($xmlPath, $isPrivate, $plugin, $pluginName = null, $pluginPath = null)
    {
        $this->console(
            "Unable to install plugin: The configuration file for {$xmlPath} must be updated to use the Hot Toddy JSON2 configuration file."
        );
    }
}

?>