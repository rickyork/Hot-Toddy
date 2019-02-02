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

class hPluginDatabase extends hPlugin {

    private $hFileUtilities;

    private $files = array();

    public function hConstructor()
    {

    }

    public function install($path = nil)
    {
        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'includeFileTypes' => array(
                    'json'
                ),
                'excludeFolders' => array(
                    'JSON',
                    'HTML',
                    'CSS',
                    'XML',
                    'JS',
                    'SQL',
                    'XHTML',
                    'TXT',
                    'PHP',
                    'Database'
                ),
                'excludeFiles' => array(
                    'mail.json'
                ),
                'autoScanEnabled' => false,
                'scanFolder' => $path
            )
        );

        $this->files = $this->hFileUtilities->getFiles();

        $object = 'hPluginDatabaseJSON2Library';

        $pluginDatabasePath = '/hPlugin/hPluginDatabase/hPluginDatabaseJSON2/hPluginDatabaseJSON2.library.php';

        if (!class_exists($object))
        {
            require $this->hServerDocumentRoot.$pluginDatabasePath;
        }

        $this->hPluginDatabase = new $object($pluginDatabasePath);

        array_shift($this->files);

        foreach ($this->files as $file)
        {
            $this->hPluginDatabase->register($file);
        }
    }

    public function getPluginName($plugin)
    {
        switch (true)
        {
            case strstr($plugin, '.library.'):
            case strstr($plugin, '.database.'):
            case strstr($plugin, '.daemon.'):
            case strstr($plugin, '.shell.'):
            case strstr($plugin, '.listener.'):
            case strstr($plugin, '.service.'):
            {
                return basename(dirname($plugin));
            }
            default:
            {
                return basename($plugin);
            }
        }
    }

    public function getPluginPath($plugin)
    {
        switch (true)
        {
            case strstr($plugin, '.library.'):
            case strstr($plugin, '.database.'):
            case strstr($plugin, '.daemon.'):
            case strstr($plugin, '.shell.'):
            case strstr($plugin, '.listener.'):
            case strstr($plugin, '.service.'):
            {
                return dirname($plugin);
            }
            default:
            {
                return $plugin;
            }
        }
    }

    public function &register($plugin, $pluginName = null, $pluginPath = null)
    {
        # @return

        # @description
        # <h2>Registering a Plugin</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($pluginName) && empty($pluginPath))
        {
            $pluginName = $this->getPluginName($plugin);
            $pluginPath = $this->getPluginPath($plugin);
        }

        $isPrivate = (substr($pluginName, 0, 1) !== 'h');

        $path = $this->getIncludePath(
            $this->hServerDocumentRoot.'/'.$pluginPath.'/'.$pluginName.'.json'
        );

        $xmlPath = $this->getIncludePath(
            $this->hServerDocumentRoot.'/'.$pluginPath.'/'.$pluginName.'.xml'
        );

        if (file_exists($path))
        {
            $this->console('Found a JSON configuration file at '.$path);
            $format = 'JSON';
        }
        else if (file_exists($xmlPath))
        {
            // Legacy XML format
            $path = $xmlPath;
            $this->console('Found an XML configuration file at '.$path);
            $format = 'XML';

            if (!file_exists($path))
            {
                $this->warning(
                    "Installation of plugin {$path} failed. Unable to locate a suitable plugin configuration file.",
                    __FILE__,
                    __LINE__
                );
            }
        }

        if (isset($format))
        {
            $this->console("Is this a private plugin? ".((int) $isPrivate == 1? 'Yes' : 'No'));

            $object = "hPluginDatabase{$format}Library";
            $pluginDatabasePath = '/hPlugin/hPluginDatabase/hPluginDatabase'.$format.'/hPluginDatabase'.$format.'.library.php';

            if (!class_exists($object))
            {
                require $this->hServerDocumentRoot.$pluginDatabasePath;
            }

            $this->hPluginDatabase = new $object($pluginDatabasePath);

            $this->hPluginDatabase->register(
                $path,
                $isPrivate,
                $plugin,
                $pluginName,
                $pluginPath
            );

            if ($format == 'JSON')
            {
                $object = "hPluginDatabaseJSON2Library";
                $pluginDatabasePath = '/hPlugin/hPluginDatabase/hPluginDatabaseJSON2/hPluginDatabaseJSON2.library.php';

                if (!class_exists($object))
                {
                    require $this->hServerDocumentRoot.$pluginDatabasePath;
                }

                $this->hPluginDatabase = new $object($pluginDatabasePath);

                $this->hPluginDatabase->register(
                    $path,
                    $plugin,
                    $pluginName,
                    $pluginPath
                );
            }
        }

        return $this;
    }
}

?>