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

class hPluginLibrary {

    private $hPluginDatabase;
    private $hJSON;

    private $dontLoadConfigurationPaths = array();

    public function &hPlugin($plugin, $arguments = array(), $fuse = false, $instantiate = true, $include = true)
    {
        # @return object

        # @deprecated

        # @description
        # <h2>Including a Plugin</h2>
        # <p>
        #   Deprecated, see <a href='#plugin' class='code'>plugin()</a>
        # </p>
        # @end

        return $this->plugin(
            $plugin,
            $arguments,
            $fuse,
            $instantiate,
            $include
        );
    }

    public function &plugin($plugin, $arguments = array(), $fuse = false, $instantiate = true, $include = true)
    {
        # @return object

        # @description
        # <h2>Including a Plugin</h2>
        # <p>
        #   Includes a plugin.
        # </p>
        # @end

        if (!empty($plugin))
        {
            $plugin = $this->queryPlugin(
                $plugin,
                nil,
                false
            );

            if (!isset($GLOBALS['hPlugins'][$plugin['basePath']]))
            {
                $confFolder = $GLOBALS['hFramework']->hFrameworkConfigurationPath;

                $folders = explode('/', $plugin['basePath']);

                if ($GLOBALS['hFramework']->hPluginConfigurations(null) && is_array($GLOBALS['hFramework']->hPluginConfigurations))
                {
                    foreach ($GLOBALS['hFramework']->hPluginConfigurations as $pluginRoot)
                    {
                        foreach ($folders as $folder)
                        {
                            $path = $confFolder.'/'.$folder;

                            // Commented out only loading *root* plugin configuration.
                            // /*$folder == $pluginRoot &&*/
                            if (!in_array($path, $GLOBALS['hPluginConfiguration'], true))
                            {
                                if (false !== $this->loadConfigurationFile($path))
                                {
                                    array_push(
                                        $GLOBALS['hPluginConfiguration'],
                                        $path
                                    );
                                }
                            }
                        }
                    }
                }

                $path = $GLOBALS['hFramework']->getIncludePath(
                    $this->hServerDocumentRoot.$plugin['path']
                );

                if (is_object($GLOBALS['hFramework']->ua))
                {
                    $path = $GLOBALS['hFramework']->insertSubExtension(
                        $path,
                        'mobile',
                        $GLOBALS['hFramework']->userAgent->interfaceIdiomIsPhone
                    );
                }

                if ($path === false)
                {
                    $GLOBALS['hFramework']->warning(
                        "Load plugin failed. Plugin located at {$path} does not exist. ".
                        "Current framework path: {$this->hFilePath}",
                        __FILE__,
                        __LINE__
                    );

                    $return = false;

                    return $return;
                }

                if ($include && !class_exists($plugin['name']))
                {
                    //$GLOBALS['hFramework']->addLoadedPath('Plugin: '.$path);
                    hFrameworkInclude($path);
                }

                if ($fuse)
                {
                    $this->fusePlugins($plugin['basePath']);
                }

                if (class_exists($plugin['baseName'].'Plugin'))
                {
                    $plugin['name'] .= 'Plugin';
                }

                if ($include)
                {
                    if (class_exists($plugin['name']))
                    {
                        if ($instantiate)
                        {
                            // $data is passed by reference to the plugin constructor.
                            $GLOBALS['hPlugins'][$plugin['basePath']] = new $plugin['name'](
                                $plugin['path'],
                                $arguments
                            );

                            return $GLOBALS['hPlugins'][$plugin['basePath']];
                        }
                    }
                    else
                    {
                        $GLOBALS['hFramework']->warning(
                            "Load plugin failed. Class '{$plugin['name']}' does not exist.",
                            __FILE__, __LINE__
                        );
                    }
                }
            }
            else
            {
                return $GLOBALS['hPlugins'][$plugin['basePath']];
            }
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                'Unable to get plugin because the plugin path supplied was empty.',
                __FILE__,
                __LINE__
            );
        }

        return $plugin;
    }

    public function &hLibrary($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Library Plugin</h2>
        # <p>
        #    This method is deprecated use <a href='#library'>library()</a> instead.
        # </p>
        # @end

        return $this->library(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function &library($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Library Plugin</h2>
        # <p>
        #    Includes and returns a Library Plugin.  Libraries contain reusable APIs.
        # </p>
        # @end

        $plugin .= '/'.(!strstr($plugin, '/')? $plugin : basename($plugin)).'.library.php';

        return $this->plugin(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function &database($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Database Plugin</h2>
        # <p>
        #    Includes and returns a Database Plugin.  Database plugins contain reusable APIs,
        #    providing in/out for databases.
        # </p>
        # @end

        $plugin .= '/'.(!strstr($plugin, '/')? $plugin : basename($plugin)).'.database.php';

        return $this->plugin(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function &hDaemon($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Daemon Plugin</h2>
        # <p>
        #    This method is deprecated use <a href='#daemon'>daemon()</a> instead.
        # </p>
        # @end

        return $this->daemon(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function daemon($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Daemon Plugin</h2>
        # <p>
        #    Includes and returns a CLI plugin.  Daemon plugins differ from shell plugins
        #    in that daemon plugins are attached to a scheduled or reoccuring process.
        # </p>
        # @end

        $plugin .= '/'.(!strstr($plugin, '/')? $plugin : basename($plugin)).'.daemon.php';

        return $this->plugin(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function &hShell($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Shell Plugin</h2>
        # <p>
        #    This method is deprecated use <a href='#shell'>shell()</a> instead.
        # </p>
        # @end

        return $this->shell(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function shell($plugin, $arguments = array(), $fuse = false)
    {
        # @return object

        # @description
        # <h2>Including a Shell Plugin</h2>
        # <p>
        #    Includes and returns a CLI plugin.  Daemon plugins differ from shell plugins
        #    in that daemon plugins are attached to a scheduled or reoccuring process.
        # </p>
        # @end

        if (!class_exists('hShell'))
        {
            $this->plugin('hShell');
        }

        $plugin .= '/'.(!strstr($plugin, '/')? $plugin : basename($plugin)).'.shell.php';

        return $this->plugin(
            $plugin,
            $arguments,
            $fuse
        );
    }

    public function &hInclude($plugin, $arguments = array())
    {
        # @return object

        # @description
        # <h2>Including a Plugin Without Instantiation</h2>
        # <p>
        #   Deprecated, use <a href='#plugin' class='code'>plugin()</a>
        #   instead.
        # </p>
        # @end

        return $this->plugin(
            $plugin,
            $arguments,
            false,
            false
        );
    }

    public function &includePlugin($plugin, $arguments = array())
    {
        # @return object

        # @description
        # <h2>Including a Plugin Without Instantiation</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->plugin(
            $plugin,
            $arguments,
            false,
            false
        );
    }

    public function &dontLoadConfigurationForFile($path)
    {
        # @return hPluginLibrary

        # @description
        # <h2>Preventing a Plugin Configuration File From Loading</h2>
        # <p>
        #
        # </p>
        # @end

        array_push(
            $this->dontLoadConfigurationPaths,
            $path
        );

        return $this;
    }

    public function &allowConfigurationForFile($path)
    {
        # @return hPluginLibrary

        # @description
        # <h2>Allowing a Plugin Configuration File to Load</h2>
        # <p>
        #
        # </p>
        # @end

        if (in_array($this->dontLoadConfigurationPaths, $path))
        {
            if (false !== ($key = array_search($this->dontLoadConfigurationPaths, $path)))
            {
                unset($this->dontLoadConfigurationPaths[$key]);
            }
        }

        return $this;
    }

    public function loadConfigurationFile($path)
    {
        # @return boolean

        # @description
        # <h2>Loading a Configuration File for a Plugin</h2>
        # <p>
        #
        # </p>
        # @end

        if (in_array($path, $this->dontLoadConfigurationPaths))
        {
            return false;
        }

        if (is_object($GLOBALS['hFramework']->ua))
        {
            $path = $GLOBALS['hFramework']->insertSubExtension(
                $path,
                'mobile',
                $GLOBALS['hFramework']->userAgent->interfaceIdiomIsPhone
            );
        }

        if (file_exists($path.'.json'))
        {
            if (!$this->hJSON)
            {
                if (!class_exists('hJSONLibrary'))
                {
                    include $this->hServerDocumentRoot.'/hJSON/hJSON.library.php';
                }

                $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');
            }

            $GLOBALS['hFramework']->addLoadedPath(
                'Plugin Configuration: '.$path.'.json'
            );

            $configurationVariables = $this->hJSON->getJSON($path.'.json');

            $GLOBALS['hFramework']->setVariables($configurationVariables);
            return true;
        }
        else if (file_exists($path.'.conf'))
        {
            $GLOBALS['hFramework']->addLoadedPath(
                'Plugin Configuration: '.$path.'.conf'
            );

            $GLOBALS['hFramework']->setVariables(
                parse_ini_file($path.'.conf')
            );

            return true;
        }

        return false;
    }

    public function queryPlugin($plugin, $arguments = false, $getId = true)
    {
         # @return array

         # @description
         # <h2>Querying a Plugin</h2>
         # <p>
         #    Returns an array of information about a plugin, such as:
         # </p>
         # <table>
         #    <tbody>
         #        <tr>
         #            <td class='code'>isPrivate</td>
         #            <td>
         #                Whether or not the plugin is private.  Private plugin names
         #                start with any letter except lowercase <var>h</var>, which
         #                is reserved for built-in plugins.
         #            </td>
         #        </tr>
         #        <tr>
         #            <td class='code'>isListener</td>
         #            <td>Whether or not a plugin is a listener.</td>
         #        </tr>
         #        <tr>
         #            <td class='code'>isService</td>
         #            <td>Whether or not a plugin is a service.</td>
         #        </tr>
         #        <tr>
         #            <td class='code'>isLibrary</td>
         #            <td>Whether or not a plugin is a library.</td>
         #        </tr>
         #        <tr>
         #            <td class='code'>isDatabase</td>
         #            <td>Whether or not a plugin is a database API.</td>
         #        </tr>
         #        <tr>
         #            <td class='code'>isDaemon</td>
         #            <td>Whether or not a plugin is a command line interface daemon (called upon by a scheduled or reoccuring process)</td>
         #        </tr>
         #        <tr>
         #            <td class='code'>isShell</td>
         #            <td>Whether or not a plugin is a command line interface.</td>
         #        </tr>
         #        <tr>
         #            <td class='code'>baseName</td>
         #            <td>
         #                The basename of a plugin.  The basename of hCalendar/hCalendar.database.php is <i>hCalendar</i>.
         #                The basename of hFile/hFile.library.php is <i>hFile</i>.
         #            </td>
         #        </tr>
         #        <tr>
         #            <td class='code'>name</td>
         #            <td>
         #                The name of the plugin.  The name of hCalendar/hCalendar.database.php is <i>hCalendarDatabase</i>
         #                The name of hFile/hFile.library.php is <i>hFileLibrary</i>.  This is the name of the object itself.
         #            </td>
         #        </tr>
         #        <tr>
         #            <td class='code'>basePath</td>
         #            <td>
         #                The simple path to the plugin, also what's stored in the <var>hPlugins</var> and <var>hPluginPrivate</var>
         #                database tables under <var>hPluginPath</var>.  Some examples: <i>hCalendar</i> is the plugin path for
         #                <i>hCalendar/hCalendar.php</i>.  <i>hForm/hForm.library.php</i> is a plugin path for <i>hFormLibrary</i>.
         #            </td>
         #        </tr>
         #        <tr>
         #            <td class='code'>path</td>
         #            <td>
         #                The full path to a plugin file from the root of the plugin folder. i.e. <var>/hCalendar/hCalendar.php</var>.
         #                This path can then be used to include a plugin's source code when sent to <a href='/Hot Toddy/Documentation?hFile/hFilePath#getIncludePath'>getIncludePath()</a> like
         #                this:
         #                <code><a href='/Hot Toddy/Documentation?hFile/hFilePath#getIncludePath'>$this-&gt;getIncludePath($this-&gt;hServerDocumentRoot.$plugin['path']);</a></code>
         #            </td>
         #        </tr>
         #    </tbody>
         # </table>
         # @end

        if (!empty($plugin) && isset($GLOBALS['hPluginCache'][$plugin]))
        {
            return $GLOBALS['hPluginCache'][$plugin];
        }

        $table = $this->getPluginDatabaseTable($plugin);

//        if (is_numeric($plugin) || $getId)
//        {
//            $pg = $this->getPlugin($plugin);
//        }

        $basePlugin = $plugin;

        if (substr($basePlugin, -1) == ';')
        {
            $basePlugin = substr($basePlugin, 0, -1);
        }

        $baseName = strstr($basePlugin, '/')? basename($basePlugin) : $basePlugin;

        $path = '/'.$basePlugin;

        $isListener = (bool) strstr(
            $path,
            '.listener.'
        );

        $isService = (bool) strstr(
            $path,
            '.service.'
        );

        $isDaemon = (bool) strstr(
            $path,
            '.daemon.'
        );

        $isShell = (bool) strstr(
            $path,
            '.shell.'
        );

        $isLibrary = (bool) strstr(
            $path,
            '.library.'
        );

        $isDatabase = (bool) strstr(
            $path,
            '.database.'
        );

        if ($isListener || $isService || $isDaemon || $isShell || $isLibrary || $isDatabase)
        {
            $pluginPath = $path;

            $bits = explode('.', $baseName);
            $baseName = $bits[0];
        }
        else
        {
            $pluginPath = $path.'/'.basename($path).'.php';
        }

        $name = '';

        switch (true)
        {
            case $isLibrary:
            {
                $name = 'Library';
                break;
            }
            case $isDatabase:
            {
                $name = 'Database';
                break;
            }
            case $isListener:
            {
                $name = 'Listener';
                break;
            }
            case $isService:
            {
                $name = 'Service';
                break;
            }
            case $isDaemon:
            {
                $name = 'Daemon';
                break;
            }
            case $isShell:
            {
                $name = 'Shell';
                break;
            }
        }

        $basePluginName = $baseName.$name;

        $version = 1;

        if ($GLOBALS['hFramework']->hPluginVersions && is_object($GLOBALS['hFramework']->hPluginVersions) && isset($GLOBALS['hFramework']->hPluginVersions->$basePluginName))
        {
            $version = (int) $GLOBALS['hFramework']->hPluginVersions->$basePluginName;

            $pluginPath = str_replace(
                '.'.strtolower($name).'.php',
                '.'.($version).'.'.strtolower($name).'.php',
                $pluginPath
            );
        }

        $GLOBALS['hPluginCache'][$plugin] = array(
            'table'         => $table,
            'isPrivate'     => $table == 'hPluginPrivate',
            'isApplication' => $table == 'hPluginApplication',
            'isListener'    => $isListener,
            'isService'     => $isService,
            'isLibrary'     => $isLibrary,
            'isDaemon'      => $isDaemon,
            'isDatabase'    => $isDatabase,
            'isShell'       => $isShell,
            'baseName'      => $baseName,
            'name'          => $basePluginName,
            'path'          => $pluginPath,
            'basePath'      => $basePlugin,
            'version'       => $version
        );

        return $GLOBALS['hPluginCache'][$plugin];
    }

    public function fusePlugins($plugin)
    {
        # @return hPluginLibrary

        # @description
        # <h2>Fusing Plugins</h2>
        # <p>
        #    A plugin is considered <i>fused</i> when the API of that plugin joins the base API of the
        #    entire framework, and the methods of that plugin can be called along with any other
        #    framework method.
        # </p>
        # @end

        if (!in_array($plugin, $this->fusePlugins))
        {
            array_push(
                $this->fusePlugins,
                $plugin
            );
        }

        #return $this;
    }

    public function hasPlugin($fileId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a File Has a Plugin</h2>
        # <p>
        #    Queries the provided <var>$fileId</var> and determines if that file has a plugin.
        # </p>
        # @end

        return $this->hFiles->selectExists(
            'hPlugin',
            (int) $fileId
        );
    }

    public function isPrivatePlugin(&$plugin, &$isPrivate)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Plugin Is Private</h2>
        # <p>
        #    Determines whetehr the provided plugin path is private.  A private
        #    plugin begins with any letter other than a lowercase <var>h</var>.
        # </p>
        # @end

        if (!is_numeric($plugin))
        {
            $isPrivate = (substr($plugin, 0, 1) !== 'h');
        }
        else
        {
            //debug_print_backtrace();
            //exit;

            $this->warning(
                "Plugin privacy cannot be determined because the numeric id '{$plugin}' was passed. ".
                "Plugin privacy can only be determined from the plugin's name, ".
                "which will be determined private if the first letter of the plugin name ".
                "or path is any character other than lowercase 'h'.",
                __FILE__,
                __LINE__
            );

            return false;
        }

        return $isPrivate;
    }

    public function getPluginDatabaseTable($plugin)
    {
        if (file_exists($GLOBALS['hFramework']->hFrameworkPath.$GLOBALS['hFramework']->hFrameworkRoot('/Hot Toddy').'/'.$plugin))
        {
            return 'hPlugins';
        }
        else if (file_exists($GLOBALS['hFramework']->hFrameworkPluginPath.'/'.$plugin))
        {
            return 'hPluginPrivate';
        }
        else if (file_exists($GLOBALS['hFramework']->hFrameworkApplicationPath.'/'.$plugin))
        {
            return 'hPluginApplication';
        }
    }

    public function getPluginPathInFramework($path)
    {
        # @return string

        # @description
        # <h2>Getting a Plugin's Path Relative to the Framework's Installed Location</h2>
        # <p>
        #    Returns a plugin's path relative to the framework's installed location.  A
        #    plugin's full path may look like this:
        # </p>
        # <code>/Websites/www.example.com/Hot Toddy/hFile/hFile.php</code>
        # <p>
        #    Relative to just the framework's installed location would be this path:
        # </p>
        # <code>/hFile/hFile.php</code>
        # @end

        $frameworkPath =
            $GLOBALS['hFramework']->hFrameworkPath.
            $GLOBALS['hFramework']->hFrameworkRoot;

        $pluginPath =
            $GLOBALS['hFramework']->hFrameworkPluginPath;

        $applicationPath =
            $GLOBALS['hFramework']->hFrameworkApplicationPath;

        if ($this->beginsPath($path, $frameworkPath))
        {
            return $this->getEndOfPath(
                $path,
                $frameworkPath
            );
        }
        else if ($this->beginsPath($path, $pluginPath))
        {
            return $this->getEndOfPath(
                $path,
                $pluginPath
            );
        }
        else if ($this->beginsPath($path, $applicationPath))
        {
            return $this->getEndOfPath(
                $path,
                $applicationPath
            );
        }

        return '';
    }

    public function getBasePath($path)
    {
        # @return string

        # @description
        # <h2>Getting a Plugin's Base Path</h2>
        # <p>
        #    Returns a plugin's base path, or in the context of the <var>hPlugins</var>
        #    and <var>hPlugin9Private</var> database tables, the path stored in
        #    <var>hPluginPath</var>.  This is a simplified, shortened version of the
        #    plugin's path, that contains just enough information to locate the plugin.
        #    For example, if you provide the following full path:
        # </p>
        # <code>/Websites/www.example.com/Hot Toddy/hFile/hFile.php</code>
        # <p>
        #    The plugin's base path is:
        # </p>
        # <code>hFile</code>
        # @end

        $path = $this->getPluginPathInFramework($path);

        $fileName = basename($path);

        $extension = strtolower(
            $this->getExtension($fileName)
        );

        if ($extension == 'php' && !strstr($fileName, '.template.php'))
        {
            $path = substr($path, 1);

            switch (true)
            {
                case strstr($path, '.library.php'):
                case strstr($path, '.database.php'):
                case strstr($path, '.service.php'):
                case strstr($path, '.listener.php'):
                case strstr($path, '.shell.php'):
                case strstr($path, '.daemon.php'):
                {
                    return $path;
                }
                default:
                {
                    $bits = explode('/', $path);

                    array_pop($bits);

                    return implode('/', $bits);
                }
            }
        }

        return '';
    }

    public function getBaseObjectName($path)
    {
        # @return string

        # @description
        # <h2>Getting a Plugin's Base Name From Its Path</h2>
        # <p>
        #
        # </p>
        # @end

        $name = basename($path);

        $pluginTypes = array(
            '.library.php'  => 'Library',
            '.database.php' => 'Database',
            '.service.php'  => 'Service',
            '.listener.php' => 'Listener',
            '.shell.php'    => 'Shell',
            '.daemon.php'   => 'Daemon'
        );

        foreach ($pluginTypes as $pluginFile => $pluginType)
        {
            $name = str_replace(
                $pluginFile,
                $pluginType,
                $name
            );
        }

        return $name;

        # switch (true)
        # {
        #     case strstr($path, '.library.php'):
        #     {
        #         return str_replace('.library.php', 'Library', $name);
        #     }
        #     case strstr($path, '.database.php'):
        #     {
        #         return str_replace('.database.php', 'Database', $name);
        #     }
        #     case strstr($path, '.service.php'):
        #     {
        #         return str_replace('.service.php', 'Service', $name);
        #     }
        #     case strstr($path, '.listener.php'):
        #     {
        #         return str_replace('.listener.php', 'Listener', $name);
        #     }
        #     case strstr($path, '.shell.php'):
        #     {
        #         return str_replace('.shell.php', 'Shell', $name);
        #     }
        #     case strstr($path, '.daemon.php'):
        #     {
        #         return str_replace('.daemon.php', 'Daemon', $name);
        #     }
        #     default:
        #     {
        #         return $name;
        #     }
        # }
    }

    public function getBaseName($path)
    {
        # @return string

        # @description
        # <h2>Getting a Plugin's Base Name</h2>
        # <p>
        #    Takes a plugin's full path and returns the plugin's base name.
        #    For example:
        # </p>
        # <code>/Websites/www.example.com/Hot Toddy/hFile/hFile.php</code>
        # <p>
        #    This returns the base name <var>hFile</var>
        # </p>
        # <p>
        #    A more complicated example:
        # </p>
        # <code>/Websites/www.example.com/Hot Toddy/hFile/hFileBreadcrumbs/hFileBreadcrumbs.library.php</code>
        # <p>
        #    Based on the preceding path, the base name is <var>hFile</var>
        # </p>
        # @end

        $path = $this->getPluginPathInFramework($path);

        $bits = explode('/', $path);

        foreach ($bits as $bit)
        {
            if (!empty($bit))
            {
                if (strstr($bit, '.'))
                {
                    $pieces = explode('.', $bit);

                    return array_shift($pieces);
                }
                else
                {
                    return $bit;
                }
            }
        }
    }

    public function isServiceMethod($plugin, $pluginServiceMethod)
    {
        # @return boolean

        # @description
        # <h2>Determining a Service Method</h2>
        # <p>
        #    Determines if the provided service method is registered with the database.
        #    <i>Services</i> are the new name for <i>Listeners</i>, <i>Services</i> better
        #    describes the purpose of the plugin.  Service methods must be registered
        #    with the database to prevent arbitrary method execution on service plugins.
        #    Only methods explicitly granted permission by listing the method in the
        #    plugin's json configuraiton file can be accessed via a URI.
        # </p>

        return $this->hPluginServices->selectExists(
            'hPlugin',
            array(
                'hPluginServiceMethod' => $pluginServiceMethod,
                'hPlugin' => $plugin
            )
        );
    }

    public function registerPlugin($plugin)
    {
        # @return void

        # @description
        # <h2>Registering Plugins</h2>
        # <p>
        #    This method registers and installs plugins and associated files.  Service
        #    (formerly Listener) plugins are registered in the database to lock down
        #    method execution by URI to explicit, granted methods.  Plugin registration
        #    in the database in general is done for association of plugins with files and
        #    documentation.
        # </p>
        # @end

        if (strstr($plugin, 'hJSON'))
        {
            return;
        }

        $pluginPath = '/hPlugin/hPlugin.database.php';

        if (!class_exists('hPluginDatabase'))
        {
            require $this->hServerDocumentRoot.$pluginPath;
        }

        $this->hPluginDatabase = new hPluginDatabase($pluginPath);

        $this->hPluginDatabase->hDatabase = $GLOBALS['hDatabase'];

        $this->hPluginDatabase->register($plugin);

        $GLOBALS['hPluginData'] = array();
        $GLOBALS['hPluginCache'] = array();
    }
}

?>