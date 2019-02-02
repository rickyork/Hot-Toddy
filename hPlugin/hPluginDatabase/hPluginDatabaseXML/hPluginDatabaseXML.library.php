<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Plugin Database
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