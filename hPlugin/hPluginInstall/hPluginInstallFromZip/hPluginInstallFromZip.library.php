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

class hPluginInstallFromZipLibrary extends hPlugin {

    private $unzip;

    public function hConstructor($arguments)
    {
        $this->unzip = $this->hFrameworkPathToUnzip('/usr/bin/unzip');

        $this->installPackage(
            $arguments['path'],
            $arguments['name'],
            $arguments['mime']
        );
    }

    public function installPackage($path, $name, $mime)
    {
        if ($this->inGroup('Website Administrators'))
        {
            $temporary = $this->hFrameworkTemporaryPath; // I need a temporary secretary!

            // Delete the temporary folder.
            //`rm -rf "{$temporary}"`;
            if (!file_exists($temporary))
            {
                if (is_writable(dirname($temporary)))
                {
                    $this->mkdir($temporary);
                    $this->console("Creating temporary directory: '{$temporary}'");

                    $this->chmod($temporary, 775, true);
                    $this->console("Permissions of temporary directory set to 775.");
                }
                else
                {
                    $this->warning(
                        "Plugin installation failed: Unable to make a temporary folder at '".dirName($temporary)."'. ",
                        __FILE__,
                        __LINE__
                    );
                }
            }

            if (is_writable($temporary))
            {
                $name = str_replace('.hot', '.zip', $name);

                $bits = explode('.', $name);

                $plugin = array_shift($bits);

                $this->copy($path, $temporary.'/'.$name, true);
                $this->console("Copying installation package from {$path} to {$temporary}/{$name}");

                $this->chmod(775, $temporary.'/'.$name, true);
                $this->console("Chmod of installation package set to 775");

                // Let's see if it be a zip archive...
                if (is_executable($this->unzip))
                {
                    //$result = `/usr/bin/unzip "{$temporary}/{$name}"`;

                    // The extraction directory must be explicitly set
                    $rtn = $this->pipeCommand(
                        $this->unzip,
                        '-o '.escapeShellArg($temporary.'/'.$name).' -d '.escapeShellArg($temporary),
                        0
                    );

                    $this->console("Unzipping installation package with command: {$this->unzip}.");

                    if ($rtn > 0)
                    {
                        $this->warning(
                            "Plugin installation failed: unzip command exited with status '{$rtn}'.",
                            __FILE__,
                            __LINE__
                        );
                    }

                    if (file_exists($temporary.'/'.$plugin))
                    {
                        $applications = $this->hFrameworkPath.$this->hFrameworkApplicationRoot('/Applications');

                        if (!file_exists($applications.'/'.$plugin))
                        {
                            $this->move(
                                $applications.'/'.$plugin,
                                $temporary.'/'.$plugin
                            );

                            $this->console(
                                "Moving decompressed installation folder from temporary folder to ".
                                "'".$this->hFrameworkApplicationRoot('/Applications')."'"
                            );

                            $this->console(
                                "mv '{$temporary}/{$plugin}' '{$applications}/{$plugin}'"
                            );
                        }

                        // Install the plugin!
                        // See if thar be a manifest, yarrr!
                        $this->console("Installing plugin: '{$plugin}'");

                        $this->console(
                            $this->hot('install '.escapeShellArg($pluginName))
                        );

                        $this->console("Looking inside of the plugin folder for subplugins...");

                        $this->iteratePlugin(
                            $applications.'/'.$plugin,
                            $plugin,
                            $plugin
                        );

                        // There is the possibility that the plugin folder contains multiple plugins,
                        // which will need to be iterated over and all plugins installed.
                    }
                    else
                    {
                        $this->warning(
                            "Plugin installation failed: The plugin folder doesn't appear to exist at '{$temporary}/{$plugin}'",
                            __FILE__,
                            __LINE__
                        );
                    }
                }
                else
                {
                    $this->warning(
                        "Plugin installation failed: '{$this->unzip}' does not appear to be executable.",
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->warning(
                    "Plugin installation failed: Temporary folder '{$temporary}' is not writable.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    private function iteratePlugin($path, $plugin, $subPlugin)
    {
        if (file_exists($path))
        {
            if (is_dir($path))
            {
                $files = scanDir($path);

                foreach ($files as $file)
                {
                    if (subStr($file, 0, 1) != '.' && is_dir($path.'/'.$file) && strStr($file, $plugin))
                    {
                        switch ($file)
                        {
                            case 'Database':
                            case 'Pictures':
                            case 'SQL':
                            case 'HTML':
                            case 'XML':
                            case 'JSON':
                            case 'CSS':
                            case 'JS':
                            case 'PHP':
                            case 'TXT':
                            case 'Templates':
                            {
                                 break;
                            }
                            default:
                            {
                                $pluginPath = $subPlugin.'/'.$file;

                                $this->console("Installing sub plugin: '{$pluginPath}'");

                                $this->console(
                                    $this->hot('install '.escapeShellArg($pluginPath))
                                );

                                $this->iteratePlugin(
                                    $path.'/'.$file,
                                    $plugin,
                                    $pluginPath
                                );
                            }
                        }
                    }
                }
            }
            else
            {
                $this->warning(
                    "Unable to iterate plugin directory because path, '{$path}', is not a directory.",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->warning(
                "Unable to iterate plugin directory because path, '{$path}', does not exist.",
                __FILE__,
                __LINE__
            );
        }
    }
}

?>