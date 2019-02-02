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
# <h1>Plugin Installation API</h1>
# <p>
#    This plugin facilitates the installation of plugins by uploading a zip formatted
#    package.  These packages use the ".hot" extension instead of the standard
#    .zip extension to distinguish them from normal zip files.
# </p>
# <p>
#   This functionality makes it possible for users to easily install plugins from
#   third-party developers.  Plugins installed using the package method are always
#   installed to {hFrameworkPath}{hFrameworkPluginRoot}
# </p>
# <p>
#   Installing plugins requires the user to be in the <i>root</i> or
#   <i>Website Adminstrators</i> groups.
# </p>
# @end

class hPluginInstallLibrary extends hPlugin {

    private $hPluginInstallFromZip;
    private $hPluginInstallFromJSON;

    public function hConstructor($arguments)
    {
        if ($arguments['mime'])
        {
            switch (true)
            {
                case substr($arguments['mime'], 0, 5) == 'text/':
                case $arguments['mime'] == 'application/json':
                case $arguments['mime'] == 'text/plain':
                case $arguments['mime'] == 'application/octet-stream':
                {
                    $this->hPluginInstallFromJSON = $this->library(
                        'hPlugin/hPluginInstall/hPluginInstallFromJSON',
                        $arguments
                    );

                    break;
                }
                default:
                {
                    $this->hPluginInstallFromZip = $this->library(
                        'hPlugin/hPluginInstall/hPluginInstallFromZip',
                        $arguments
                    );
                }
            }
        }
    }
}

?>