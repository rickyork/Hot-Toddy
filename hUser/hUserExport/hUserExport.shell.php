<?php

//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy User Export Shell
//\\ @@@@  @@@@\\\\\|
//\\\@@@@| @@@@\\\\\|
//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
//\\\\  \\_   \\\\\\|
//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
//\\\\\  ----  \@@@@| http://www.hframework.com/license
//@@@@@\       \@@@@|
//@@@@@@\     \@@@@@|
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hUserExportShell extends hShell {

    private $hUserExport;

    public function hConstructor()
    {
        $this->hUserExport = $this->library('hUser/hUserExport');

        if (!function_exists('json_encode'))
        {
           // PHP4, yuk!
            $this->setToPHP4();
            include_once 'Services/JSON.php';
        }

        if (!$this->shellArgumentExists('to', '--to'))
        {
            $this->fatal('Export users failed because the save path was not specified.', __FILE__, __LINE__);
        }

        $to = $this->getShellArgumentValue('to', '--to');

        if (file_exists($to))
        {
            $this->fatal('Export users failed because the save path already exists.', __FILE__, __LINE__);
        }

        if ($this->shellArgumentExists('with', '--with'))
        {
            $with = $this->getShellArgumentValue('with', '--with');

            if (file_exists($with))
            {
                include $with;

                if (!isset($export) || !is_array($export))
                {
                    $this->fatal('Export users failed because the with path does not contain a valid $export variable, which must exist and be an array.', __FILE__, __LINE__);
                }
            }
            else
            {
                $this->fatal('Export users failed because the with path does not exist.', __FILE__, __LINE__);
            }
        }
        else
        {
            $export = array();
        }

        file_put_contents(
            $to,
            json_encode($this->hUserExport->getUsers($export))
        );

        $this->console('Exported users to '.$to);
    }
}

?>