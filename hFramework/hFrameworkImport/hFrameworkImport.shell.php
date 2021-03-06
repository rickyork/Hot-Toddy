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

class hFrameworkImportShell extends hShell {

    private $hFrameworkImport;
    private $hJSON;

    public function hConstructor()
    {
        ini_set('memory_limit', -1);

        $this->console("Preparing to import framework data\n");
        $this->console("MAKE SURE BOTH EXPORTING AND IMPORTING INSTALLATIONS ARE FULLY UP-TO-DATE!\n");
        $this->console("IMPORTING FRAMEWORK DATA CANNOT BE UNDONE\n");
        $this->console("MAKE SURE YOUR DATABASE IS BACKED-UP!  PRESS CTRL+C TO CANCEL");

/*
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".";
        sleep(1);
        echo ".\n";
*/

        $this->console("Proceeding with importation of data");

        if ($this->shellArgumentExists('from', '--from'))
        {
            $from = $this->getShellArgumentValue('from', '--from');
        }
        else
        {
            $this->fatal("Cannot import data 'from' argument was not specified or did not contain a value.", __FILE__, __LINE__);
        }

        $this->console("Importing from {$from}");

        $columns = array();

        // hUserId:tmpTrainingSignedOffBy,tmpTrainer|hFileId:someFileId,someOther
        if ($this->shellArgumentExists('include', '--include'))
        {
            $include = $this->getArgumentValue('include', '--include');
            $lists = explode('|', $include);

            foreach ($lists as $list)
            {
                $tokens = explode(':', $list);
                $columns[$tokens[0]] = explode(',', $tokens[1]);
            }
        }

        if (file_exists($from))
        {
            if (!class_exists('hJSONLibrary'))
            {
                include $this->hServerDocumentRoot.'/hJSON/hJSON.library.php';
            }

            $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');

            $json = $this->hJSON->getJSON($from, false);

            $this->hFrameworkImport = $this->library('hFramework/hFrameworkImport');

            $this->console("Running import script");
            $this->hFrameworkImport->fromJSON($json, $columns);
        }
        else
        {
            $this->fatal("Path specified in 'from' does not exist.", __FILE__, __LINE__);
        }
    }
}

?>