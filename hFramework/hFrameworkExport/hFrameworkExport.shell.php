<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Export Shell
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
#
# Makes it possible to export data from one framework installation and install that
# data into another framework installation.
#

class hFrameworkExportShell extends hShell {

    private $hFrameworkExport;

    public function hConstructor()
    {
        $this->console("Preparing to export framework data");
        $this->console("MAKE SURE BOTH EXPORTING AND IMPORTING INSTALLATIONS ARE FULLY UP-TO-DATE!");

        if ($this->shellArgumentExists('to', '--to'))
        {
            $path = $this->getShellArgumentValue('to', '--to');
        }
        else
        {
            $path = $this->hFrameworkTemporaryPath.'/hFrameworkExport.json';
        }

        $this->console("Exporting to {$path}");

        $this->hFrameworkExport = $this->library('hFramework/hFrameworkExport');

        $include = array();

        if ($this->shellArgumentExists('include', '--include'))
        {
            $tables = explode(
                ',',
                $this->getShellArgumentValue(
                    'include',
                    '--include'
                )
            );

            foreach ($tables as $i => $table)
            {
                $include[$i] = trim($table);
            }
        }

        $result = file_put_contents(
            $path,
            $this->hFrameworkExport->getJSON($include)
        );

        if ($result === false)
        {
            $this->console(
                "Export of framework data to {$path} failed, check that you have permission ".
                "to write to the destination folder and try again"
            );
        }
        else
        {
            $this->console("Export of framework data to {$path} was successful!");
        }
    }
}

?>