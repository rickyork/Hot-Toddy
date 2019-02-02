<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Application
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
# <h1>Hot Toddy Framework Application API</h1>
# <p>
#
# </p>
#
#
# @end

abstract class hFrameworkApplication extends hPluginLibrary {

    protected $hFramework;
    protected $hPluginPath;
    protected $hDatabase;

    protected $hServerDocumentRoot;
    protected $hDB;
    public $hFrameworkListener;
    public $hFrameworkService;

    public function __construct($pluginPath, $pluginArguments = array())
    {
        #  @return void

        # @description
        # <h2>Application Constructor</h2>
        # <p>
        #
        # </p>
        # @end

        if (isset($GLOBALS['hDB']))
        {
            $this->hDB = $GLOBALS['hDB'];
        }

        if (isset($GLOBALS['hDatabase']))
        {
            $this->hDatabase = &$GLOBALS['hDatabase'];
        }

        $this->hPluginPath = $pluginPath;
        $this->hServerDocumentRoot = $GLOBALS['hFramework']->hServerDocumentRoot;

        $isListenerPath = (
            $GLOBALS['hFramework']->hFileListenerPath &&
            !strstr($pluginPath, 'hFrameworkListenerAPI') &&
            strstr($pluginPath, '.listener.php')
        );

        if ($isListenerPath)
        {
            $this->hFrameworkListener = $this->plugin('hFramework/hFrameworkListener/hFrameworkListenerAPI');
        }

        $isServicePath = (
            $GLOBALS['hFramework']->hFileServicePath &&
            !strstr($pluginPath, 'hFrameworkServiceAPI') &&
            strstr($pluginPath, '.service.php')
        );

        if ($isServicePath)
        {
            $this->hFrameworkService = $this->plugin('hFramework/hFrameworkService/hFrameworkServiceAPI');
        }

        if (method_exists($this, 'hConstructor'))
        {
            $this->hConstructor($pluginArguments);
        }
    }

    public function &getPluginCSS($files = nil, $path = nil)
    {
        # @return hFrameworkApplication

        # @description
        # <h2>Adding CSS to hFileCSS</h2>
        # <p>
        #
        # </p>
        # @end

        if (is_array($files))
        {
            foreach ($files as $file)
            {
                $GLOBALS['hFramework']->setHeaders(
                    $this->hPluginPath,
                    'css',
                    $file,
                    $path
                );
            }
        }
        else
        {
            $GLOBALS['hFramework']->setHeaders(
                $this->hPluginPath,
                'css',
                $files,
                $path
            );
        }

        return $this;
    }

    public function &addPluginCSS($file = nil, $path = nil)
    {
        # @return hFrameworkApplication

        # @description
        # <h2>Adding CSS to CSS Headers</h2>
        # <p>
        #   Alias for <a href='#getPluginCSS' class='code'>getPluginCSS()</a>
        # </p>
        # @end

        return $this->getPluginCSS($file, $path);
    }

    public function &hFileCSS($file = nil, $path = nil)
    {
        # @return hFrameworkApplication

        # @description
        # <h2>Adding CSS to CSS Headers</h2>
        #// <p>
        #   Deprecated.
        #   Use <a href='#getPluginCSS' class='code'>getPluginCSS()</a> or
        #   <a href='#addPluginCSS' class='code'>addPluginCSS()</a>
        # </p>
        # @end

        return $this->getPluginCSS($file, $path);
    }

    public function &getPluginJavaScript($files = nil, $path = nil)
    {
        if (is_array($files))
        {
            foreach ($files as $file)
            {
                $GLOBALS['hFramework']->setHeaders(
                    $this->hPluginPath,
                    'js',
                    $file,
                    $path
                );
            }
        }
        else
        {
            $GLOBALS['hFramework']->setHeaders(
                $this->hPluginPath,
                'js',
                $files,
                $path
            );
        }

        return $this;
    }

    public function &hFileJavaScript($file = nil, $path = nil)
    {
        return $this->getPluginJavaScript($file, $path);
    }

    public function &getPluginJS($file = nil, $path = nil)
    {
        return $this->getPluginJavaScript($file, $path);
    }

    public function &addPluginJS($file = nil, $path = nil)
    {
        return $this->getPluginJavaScript($file, $path);
    }

    public function &getPluginFiles($files = nil, $path = nil)
    {
        if (is_array($files))
        {
            foreach ($files as $file)
            {
                $GLOBALS['hFramework']->setHeaders(
                    $this->hPluginPath, 'both', $file, $path
                );
            }
        }
        else
        {
            $GLOBALS['hFramework']->setHeaders(
                $this->hPluginPath, 'both', $files, $path
            );
        }

        return $this;
    }

    public function &addPluginFiles($file = nil, $path = nil)
    {
        return $this->getPluginFiles($file, $path);
    }

    public function &hFileHeaders($file = nil, $path = nil)
    {
        return $this->getPluginFiles($file, $path);
    }
}

?>