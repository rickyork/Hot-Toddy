<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Update
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

class hFrameworkUpdateShell extends hShell {

    private $hFrameworkVariables;
    private $hDatabaseStructure;

    public function hConstructor()
    {
        if ($this->shellArgumentExists('test', '--test'))
        {
            $test = $this->getShellArgumentValue('test', '--test');

            if (!empty($test))
            {
                $file = dirname(__FILE__).'/hFrameworkUpdates/hFrameworkUpdate-'.$test.'.php';

                if (file_exists($file))
                {
                    include $file;

                    # $test = 1.0.4-1.0.5

                    $obj = 'hFrameworkUpdate_'.str_replace(array('.', '-'), array('', 'To'), $test);

                    new $obj($file, array('test' => true));
                }
            }

            return;
        }

        $this->hFrameworkVariables = $this->library('hFramework/hFrameworkVariables');

        $this->hFrameworkVersion = $this->hFrameworkVariables->get('hFrameworkVersion');

        $this->console("Hot Toddy Software Update");
        $this->console("Framework version is set to ".$this->hFrameworkVersion);

        if (!$this->hFrameworkVersion)
        {
            $this->setVersion('0.9.0');
            $this->hFrameworkVersion = '0.9.0';
        }

        $hServerDocumentRoot = $this->hServerDocumentRoot;
        $hServerDocumentRootBase = $this->hFrameworkPath;

        $this->console("Updating Hot Toddy from Subversion");

        $frameworkPath = '';

        if (file_exists($hServerDocumentRootBase.'/Hot Toddy/.svn'))
        {
            $frameworkPath = escapeShellArg("{$hServerDocumentRootBase}/Hot Toddy");
            echo `svn update {$frameworkPath}`;
        }
        else if (file_exists($hServerDocumentRootBase.'/www/.svn'))
        {
            $frameworkPath = escapeShellArg("{$hServerDocumentRootBase}/www");
            echo `svn update {$frameworkPath}`;
        }

        if (file_exists($this->hFrameworkLibraryPath.'/.svn'))
        {
            $libraryPath = escapeShellArg($this->hFrameworkLibraryPath);

            echo `svn update {$libraryPath}`;
        }

        if (file_exists($this->hFrameworkIconPath.'/512x512/.svn'))
        {
            $iconPath = escapeShellArg($this->hFrameworkIconPath.'/512x512');
            echo `svn update {$iconPath}`;
        }

        if (file_exists($this->hFrameworkIconPath.'/128x128/.svn'))
        {
            $iconPath = escapeShellArg($this->hFrameworkIconPath.'/128x128');
            echo `svn update {$iconPath}`;
        }

        $this->console("Updating Hot Toddy Database");

        $shellPath = escapeShellArg($this->hServerDocumentRoot.'/hFramework/hFramework.shell.php');

        $this->hDatabaseStructure = $this->library('hDatabase/hDatabaseStructure');

        $this->hDatabaseStructure->install();
        $this->hDatabaseStructure->update();

        $this->hDatabase->refresh();

        $installedVersion = $this->hFrameworkVersion(null);

        $this->console("Installed framework version is {$installedVersion}");

        // Get a list of updates
        $folder = dirname(__FILE__).'/hFrameworkUpdates';

        $directory = opendir($folder);

        $this->console("Reading updates in folder {$folder}");

        if ($directory)
        {
            while (false !== ($file = readdir($directory)))
            {
                if ($file != '.' && $file != '..' && !is_dir($folder.'/'.$file))
                {
                    // Parse versions...
                    $matches = array();

                    $this->console("Evaluating {$folder}/{$file}");

                    preg_match_all("/\d{1,}\.\d{1,}\.\d{1,}/", $file, $matches);

                    if (isset($matches[0][0]) && isset($matches[0][1]))
                    {
                        $this->console("Found update from {$matches[0][0]} to {$matches[0][1]}");

                        if ($matches[0][0] == $installedVersion)
                        {
                            $this->console("{$matches[0][0]} == {$installedVersion}");

                            $this->console("Updating to version {$matches[0][1]}");

                            // Install the new version.
                            include $folder.'/'.$file;

                            $obj = "hFrameworkUpdate_".str_replace('.', '', $matches[0][0])."To".str_replace('.', '', $matches[0][1]);

                            new $obj($folder.'/'.$file);

                            $this->setVersion($matches[0][1]);
                        }
                        else
                        {
                            $this->console("{$matches[0][0]} != {$installedVersion}");
                        }
                    }
                }
            }

            closedir($directory);
        }
    }

    private function setVersion($version)
    {
        $this->hFrameworkVariables->save('hFrameworkVersion', $version);
    }
}

?>