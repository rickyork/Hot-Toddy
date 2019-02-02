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

class hPluginInstallFromJSONLibrary extends hPlugin {

    private $hPluginDatabase;

    public function hConstructor($arguments)
    {
        $this->install(
            array(
                'path' => $arguments['path']
            )
        );
    }

    public function install($arguments)
    {
        $object = 'hPluginDatabaseJSON2Library';

        $pluginDatabasePath = '/hPlugin/hPluginDatabase/hPluginDatabaseJSON2/hPluginDatabaseJSON2.library.php';

        if (!class_exists($object))
        {
            require $this->hServerDocumentRoot.$pluginDatabasePath;
        }

        $this->hPluginDatabase = new $object($pluginDatabasePath);

        $this->hPluginDatabase->register(
            $arguments['path']
        );
    }
}

?>