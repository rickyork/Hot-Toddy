<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Shell
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

class hShell extends hPlugin {

    public function hConstructor()
    {
        if (!$this->shellArgumentExists('-q', '--quietly'))
        {
            $this->console("Hot Toddy Shell ".$this->hFrameworkVersion."\n");
        }

        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', $this->hFrameworkLogPath.'/PHP.log');
        ini_set('error_reporting', 2147483647);

        //if ($this->shellArgumentExists('hFrameworkSite', '--hFrameworkSite'))
        //{
        //    $this->hFrameworkSite = $this->getShellArgumentValue('hFrameworkSite', '--hFrameworkSite');
        //}
        //
        //if ($this->shellArgumentExists('site', '--site'))
        //{
        //    $this->hFrameworkSite = $this->getShellArgumentValue('site', '--site');
        //}

        switch (true)
        {
            case ($this->shellArgumentExists('-p', 'plugin') || $this->shellArgumentExists('--p', '--plugin')):
            {
                if ($this->shellArgumentExists('install'))
                {
                    $this->shell('hPlugin');
                }
                else
                {
                    $this->shell($this->getShellArgumentValue('-p', 'plugin'));
                }

                break;
            }
            case ($this->shellArgumentExists('-cleanup', 'cleanup')):
            {
                $this->shell('hFramework/hFrameworkCleanup');
                break;
            }
            case ($this->shellArgumentExists('documentation')):
            {
                switch (true)
                {
                    case ($this->shellArgumentExists('tokenize')):
                    {
                        $this->shell('hDocumentation/hDocumentationParser');
                        break;
                    }
                }

                break;
            }
            case ($this->shellArgumentExists('db', '-db')):
            case ($this->shellArgumentExists('database', '-database')):
            {
                switch (true)
                {
                    case ($this->shellArgumentExists('update', '-update')):
                    case ($this->shellArgumentExists('install', '-install')):
                    case ($this->shellArgumentExists('versions', '-versions')):
                    case ($this->shellArgumentExists('revert', '-revert')):
                    {
                        $this->shell('hDatabase/hDatabaseStructure');
                        break;
                    }
                    case ($this->shellArgumentExists('export', '-export')):
                    {
                        $this->shell('hDatabase/hDatabaseExport');
                        break;
                    }
                    case ($this->shellArgumentExists('import', '-import')):
                    {
                        $this->shell('hDatabase/hDatabaseImport');
                        break;
                    }
                }

                break;
            }
            case ($this->shellArgumentExists('-u', 'update')):
            {
                switch (true)
                {
                    case $this->shellArgumentExists('icons', 'icons'):
                    case $this->shellArgumentExists('icns', 'icns'):
                    {
                        $this->shell('hFile/hFileIcon/hFileIconInstall');
                        break;
                    }
                    default:
                    {
                        $this->shell('hFramework/hFrameworkUpdate');
                    }
                }

                break;
            }
            case ($this->shellArgumentExists('-e', 'export')):
            {
                $this->shell('hFramework/hFrameworkExport');
                break;
            }
            case ($this->shellArgumentExists('-m', 'import')):
            {
                $this->shell('hFramework/hFrameworkImport');
                break;
            }
            case ($this->shellArgumentExists('-dbe', 'dbExport')):
            {
                $this->shell('hDatabase/hDatabaseExport');
                break;
            }
            case ($this->shellArgumentExists('-dbi', 'dbImport')):
            {
                $this->shell('hDatabase/hDatabaseImport');
                break;
            }
            case ($this->shellArgumentExists('-i', 'install')):
            {
                switch (true)
                {
                    case $this->shellArgumentExists('icons', '--icons'):
                    case $this->shellArgumentExists('icns', '--icns'):
                    {
                        $this->shell('hFile/hFileIcon/hFileIconInstall');
                        break;
                    }
                    default:
                    {
                        $this->shell('hPlugin');
                    }
                }

                break;
            }
            case ($this->shellArgumentExists('-b', 'backup')):
            {
                $this->shell('hFramework/hFrameworkBackup');
                break;
            }
            case ($this->shellArgumentExists('-tl', 'templateLanguage')):
            {
                $this->shell('hTemplate/hTemplateLanguage');
                break;
            }
            case ($this->shellArgumentExists('-h', 'help')):
            {
                $this->console($this->getTemplateTXT('Commands'));
                break;
            }
            # Experimental web socket support.
            case ($this->shellArgumentExists('-s', 'server')):
            {
                $this->shell('hFramework/hFrameworkServer');
                break;
            }
            case ($this->shellArgumentExists('-tr', 'toRuby')):
            {
                $this->shell('hFramework/hFrameworkToRuby');
                break;
            }
            case ($this->shellArgumentExists('-mail', 'mail')):
            {
                $this->shell('hMail');
                break;
            }
            case ($this->shellArgumentExists('truncate', '-truncate') && $this->shellArgumentExists('cache', '-cache')):
            {
                $this->hFileCache->truncate();
                $this->console("Truncated hFileCache");
                break;
            }
            case ($this->shellArgumentExists('-cache', 'cache')):
            {
                $this->shell('hFramework/hFrameworkCache');
                break;
            }
            case ($this->shellArgumentExists('-sandbox', 'sandbox')):
            {
                $this->shell('hShell/hShellSandbox');
                break;
            }
            default:
            {
                $this->console("Invalid command.\n\n".$this->getTemplateTXT('Commands'));
            }
        }
    }
}

?>