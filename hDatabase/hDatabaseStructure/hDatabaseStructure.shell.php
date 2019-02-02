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

class hDatabaseStructureShell extends hShell {

    private $hDatabaseStructure;

    public function hConstructor()
    {
        $this->console("Hot Toddy Database Management");

        $this->hDatabaseStructure = $this->library('hDatabase/hDatabaseStructure');

        if ($this->shellArgumentExists('-versions', 'versions'))
        {
            $versions = $this->getShellArgumentValue('-versions', 'versions');
            $this->console("Creating version file(s)/Logging database table version(s) for ".$this->getStatement($versions));
            $this->hDatabaseStructure->versions($versions == 'all'? null : $versions);
        }

        if ($this->shellArgumentExists('-install', 'install'))
        {
            $install = $this->getShellArgumentValue('-install', 'install');
            $this->console("Installing ".($install == 'all'? ' all uninstalled database tables' : $install));
            $this->hDatabaseStructure->install($install == 'all'? null : $install);
        }

        if ($this->shellArgumentExists('-update', 'update'))
        {
            $update = $this->getShellArgumentValue('-update', 'update');
            $this->console("Updating ".$this->getStatement($update));
            $this->hDatabaseStructure->update($update == 'all'? null : $update);
        }

        if ($this->shellArgumentExists('-revert', 'revert'))
        {
            $revert = $this->getShellArgumentValue('-revert', 'revert');
            $this->console("Reverting ".$this->getStatement($revert));
            $this->hDatabaseStructure->revert($revert == 'all'? null : $revert);
        }
    }

    private function getStatement($table)
    {
        return ($table == 'all'? 'all database tables' : $table);
    }

}

?>