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

class hDatabaseImportLibrary extends hShell {

    public function hConstructor()
    {

    }

    public function execute($from = null)
    {
        $pathToMySQL = '/usr/bin/mysql';

        if (!$this->hDatabaseImportPathToMySQL)
        {
            if (!is_executable($pathToMySQL))
            {
                $pathToMySQL = '/usr/local/mysql/bin/mysql';
            }

            if (!is_executable($pathToMySQL))
            {
                $this->fatal(
                    "Unable to determine the location of 'mysql'.  Please specify the absolute path ".
                    "to this command via the framework variable 'hDatabaseImportPathToMySQL'"
                );
            }
        }
        else if (!file_exists($this->hDatabaseImportPathToMySQL))
        {
            $this->fatal("The command specified in hDatabaseImportPathToMySQL does not exist.", __FILE__, __LINE__);
        }
        else if (!is_executable($this->hDatabaseImportPathToMySQL))
        {
            $this->fatal("The command specified in hDatabaseImportPathToMySQL is not executable.", __FILE__, __LINE__);
        }

        $pathToDatabase = empty($from)? $this->hFrameworkPath.'/SQL/'.$this->hDatabaseInitial.'.sql' : $from;

        if (!is_readable($pathToDatabase))
        {
            $this->fatal("Could not import the database, the file '{$pathToDatabase}' is not readable.", __FILE__, __LINE__);
        }

        $this->console("Importing '{$this->hDatabaseInitial}' from '{$pathToDatabase}' using 'mysql'");

        $this->pipeCommand(
            $this->hDatabaseImportPathToMySQL($pathToMySQL),
            '-u '.escapeshellarg($this->hDatabaseUser).' '.
            ' --default-character-set=utf8 '.
            escapeshellarg($this->hDatabaseInitial).
            ' -p'.escapeshellarg($this->hDatabasePassword).' < '.
            escapeshellarg($pathToDatabase)
        );
    }
}

?>