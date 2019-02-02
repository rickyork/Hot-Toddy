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

class hDatabaseExportLibrary extends hShell {

    public function hConstructor()
    {
        if (!file_exists($this->hFrameworkPath.'/SQL'))
        {
            $this->mkdir($this->hFrameworkPath.'/SQL');
        }
    }

    public function execute($to = null)
    {
        $pathToMySQLDump = '/usr/bin/mysqldump';

        if (!$this->hDatabaseExportPathToMySQLDump)
        {
            if (!is_executable($pathToMySQLDump))
            {
                $pathToMySQLDump = '/usr/local/mysql/bin/mysqldump';
            }

            if (!is_executable($pathToMySQLDump))
            {
                $this->fatal(
                    "Unable to determine the location of 'mysqldump'.  Please specify the absolute path ".
                    "to this command via the framework variable 'hDatabaseExportPathToMySQLDump'"
                );
            }
        }
        else if (!file_exists($this->hDatabaseExportPathToMySQLDump))
        {
            $this->fatal("The command specified in hDatabaseExportPathToMySQLDump does not exist.", __FILE__, __LINE__);
        }
        else if (!is_executable($this->hDatabaseExportPathToMySQLDump))
        {
            $this->fatal("The command specified in hDatabaseExportPathToMySQLDump is not executable.", __FILE__, __LINE__);
        }

        $pathToBackup = empty($to)? $this->hFrameworkPath.'/SQL/'.$this->hDatabaseInitial.'.sql' : $to;

        if (!is_writable(dirname($pathToBackup)))
        {
            $this->fatal("Could not export the database, the folder '".dirname($pathToBackup)."' is not writable. ", __FILE__, __LINE__);
        }

        if (file_exists($pathToBackup))
        {
            $this->rm($pathToBackup);
        }

        $this->console("Exporting '{$this->hDatabaseInitial}' to '{$pathToBackup}' using 'mysqldump'");

        $this->pipeCommand(
            $this->hDatabaseExportPathToMySQLDump($pathToMySQLDump),
            '-u '.escapeshellarg($this->hDatabaseUser).' '.
            '--opt --default-character-set=utf8 '.
            escapeshellarg($this->hDatabaseInitial).
            ' -p'.escapeshellarg($this->hDatabasePassword).' > '.
            escapeshellarg($pathToBackup)
        );
    }
}

?>