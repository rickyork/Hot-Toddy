<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Plugin Installation From JSON Library
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