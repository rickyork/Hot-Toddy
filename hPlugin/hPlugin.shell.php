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

class hPluginShell extends hShell {

    private $hPluginDatabase;
    private $hPrivatePlugin;
    private $hTemplate;
    private $hPluginTemplate;

    public function hConstructor()
    {
        $force = $this->shellArgumentExists('force');

        if ($this->shellArgumentExists('install'))
        {
            $plugin = $this->getShellArgumentValue('install');

            $this->console(
                "Hot Toddy Plugin Installer\n\n".
                "Installing plugin: {$plugin}\n"
            );

            if (strstr($plugin, '.hot'))
            {
                $object = 'hPluginDatabaseJSON2Library';

                $pluginDatabasePath = '/hPlugin/hPluginDatabase/hPluginDatabaseJSON2/hPluginDatabaseJSON2.library.php';

                if (!class_exists($object))
                {
                    require $this->hServerDocumentRoot.$pluginDatabasePath;
                }

                $this->hPluginDatabase = new $object($pluginDatabasePath);

                $this->hPluginDatabase->register(
                    $plugin
                );
            }
            else
            {
                $this->hPluginInstallFiles = true;

                $this->hPluginDatabase = $this->database('hPlugin');

                $this->hPluginDatabase->register(
                    $plugin,
                    nil,
                    nil
                );
            }
        }

        if ($this->shellArgumentExists('private'))
        {
            $this->console("Installing a new private plugin framework");

            // Installs a new private framework to the private folder...
            $this->hPrivatePlugin = $this->getShellArgumentValue('private');

            $php = $this->getTemplatePHP(
                'hPrivatePlugin',
                array(
                    'hPrivatePlugin' => $this->hPrivatePlugin
                )
            );

            $xml = $this->getTemplateXML(
                'hPrivatePlugin',
                array(
                    'hPrivatePlugin' => $this->hPrivatePlugin
                )
            );

            $savePath = $this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').'/'.$this->hPrivatePlugin;

            $this->console("Private framework save path is {$savePath}");

            if (is_writable(dirname($savePath)))
            {
                if (!file_exists($savePath) || $force)
                {
                    $this->console(
                        "Making a new private plugin directory at {$savePath}"
                    );

                    `mkdir {$savePath}`;
                }

                if (!file_exists($savePath.'/'.$this->hPrivatePlugin.'.php') || $force)
                {
                    $this->console(
                        "Saving a private plugin PHP file to {$savePath}/{$this->hPrivatePlugin}.php"
                    );

                    file_put_contents(
                        $savePath.'/'.$this->hPrivatePlugin.'.php',
                        $php
                    );
                }

                if (!file_exists($savePath.'/'.$this->hPrivatePlugin.'.xml') || $force)
                {
                    $this->console(
                        "Saving a private plugin XML configuration file to {$savePath}/{$this->hPrivatePlugin}.xml"
                    );

                    file_put_contents(
                        $savePath.'/'.$this->hPrivatePlugin.'.xml',
                        $xml
                    );
                }

                if (!file_exists($savePath.'/'.$this->hPrivatePlugin.'.css') || $force)
                {
                    $this->console(
                        "Saving a private plugin CSS file to: {$savePath}/{$this->hPrivatePlugin}.css"
                    );

                    file_put_contents(
                        $savePath.'/'.$this->hPrivatePlugin.'.css',
                        ''
                    );
                }

                if (!file_exists($savePath.'/'.$this->hPrivatePlugin.'.js') || $force)
                {
                    $this->console(
                        "Saving a private plugin JavaScript file to: {$savePath}/{$this->hPrivatePlugin}.js"
                    );

                    file_put_contents(
                        $savePath.'/'.$this->hPrivatePlugin.'.js',
                        ''
                    );
                }

                if ($this->shellArgumentExists('-h', 'hostname'))
                {
                    $hostname = $this->getShellArgumentValue('-h', 'hostname');
                }
                else
                {
                    $hostname = $this->hServerHost;
                }

                $confPath = $this->hFrameworkConfigurationPath.'/'.$hostname.'.json';

                if (is_writable($confPath))
                {
                    // Update conf file...
                    $this->console(
                        "Reading your Hot Toddy configuration file from: {$confPath}"
                    );

                    $conf = file_get_contents($confPath);

                    $this->console(
                        "Updating framework variable hPrivatePlugin with the plugin path ".
                        "of your new private plugin framework"
                    );

                    $conf = preg_replace_callback(
                        "/(hPrivatePlugin\s{0,}\:\s{0,}\")(.*)(\")/U",
                        array(
                            $this,
                            'updateConf'
                        ),
                        $conf
                    );

                    $this->console("Saving revised Hot Toddy JSON configuration file");

                    file_put_contents($confPath, $conf);

                    $this->console(
                        "Private plugin framework: {$this->hPrivatePlugin} was successfully ".
                        "installed"
                    );
                }
                else
                {
                    $this->console(
                        "The Hot Toddy configuration file located at: {$confPath} is not ".
                        "readable"
                    );

                    $this->console(
                        "Hot Toddy was unable to automatically configure {$hostname}.json ".
                        "with the plugin path of your new private plugin framework"
                    );
                }
            }
            else
            {
                $this->console(
                    "The private plugin directory: ".dirname($savePath)." is not writable"
                );

                $this->console(
                    "Because the private plugin directory is unwritable, Hot Toddy is ".
                    "unable to scaffold a new private plugin framework"
                );
            }
        }

        if ($this->shellArgumentExists('-s', '--scaffold'))
        {
            /**
            * TODO: Write some scaffolding presets
            *
            * Identify redundancy in web development and design and make tools that fit the development and design scenarios for
            * most small sites.
            *
            *   ContactForm      : Scaffold a contact form with mailer and thank-you page.
            *
            *   CurrentEvents    : Scaffold a plugin with calendar database pulling out the last 3 events, with HTML and CSS
            *
            *   Blog             : Create a plugin wrapper around the hCalendarBlog plugin and create hCalendar.conf with some
            *                      typical preset configurations
            *
            *   List             : Scaffold a plugin where there is a list of lists under a header on one side of the document
            *
            *   PrivateFramework : Create a new private plugin framework, a new template, and a suite of plugins.
            *                      Basically make it possible to stub out an entire site.
            *
            *                        * Feed in an XML file defining a site map and basic functionality.
            *
            *   Navigation       : Generate code for top navigation, adding HTML and CSS for "You are Here" markers
            *
            *   CustomBorders    : Generate HTML and CSS for custom borders, using transparent PNG for all browsers and GIF for
            *                      IE6.
            *
            *   TemplateBody     : Generate HTML and CSS for the typical web page body, i.e., a header, a body, a footer, with
            *                      a logo, some navigation at the top, a footer.  Nine slice body design.
            */

            // Scaffold a new plugin
            //
            // ./hot -p hPlugin -s
            $this->hPluginTemplate = $this->library('hPlugin/hPluginTemplate');

            $confPath = $this->hFrameworkConfigurationPath.'/hPluginScaffold.conf';

            if (file_exists($confPath))
            {
                $this->console('Reading scaffold configuration file: '.$confPath);

                $conf = parse_ini_file($confPath);

                if (isset($conf['hPluginMethods']))
                {
                    $conf['hPluginMethods'] = explode(
                        ',',
                        $conf['hPluginMethods']
                    );
                }

                if (isset($conf['hPluginLibraries']))
                {
                    $conf['hPluginLibraries'] = explode(
                        ',',
                        $conf['hPluginLibraries']
                    );
                }

                if (isset($conf['hPluginPrivateLibraries']))
                {
                    $conf['hPluginPrivateLibraries'] = explode(
                        ',',
                        $conf['hPluginPrivateLibraries']
                    );
                }

                $this->hPluginTemplate->scaffold($conf);
            }
            else
            {
                $this->console('The scaffold configuration file located at: '.$confPath.' does not exist.');
                $this->console('Attempting to automatically copy the scaffold configuration file.');

                if (is_writable(dirname($confPath)))
                {
                    file_put_contents(
                        $confPath,
                        file_get_contents(
                            dirname(__FILE__).'/hPluginTemplate/hPluginTemplate.conf'
                        )
                    );

                    $this->console('A new scaffold configuration file has been created at: '.$confPath);
                    $this->console('Configure this file to scaffold a plugin and rerun this command.');
                }
                else
                {
                    $this->console('Unable to automatically copy the scaffold configuration file.');
                    $this->console('The conf folder: '.dirname($confPath).' is not writable');
                }
            }
        }
    }

    public function updateConf($matches)
    {
        return $matches[1].$this->hPrivatePlugin.$matches[3];
    }
}

?>