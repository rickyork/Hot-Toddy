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

class hEditorFindAndReplaceService extends hService {

    private $hEditorFindAndReplace;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('root'))
        {
            $this->JSON(-1);
            return;
        }

        if (!isset($_POST['find']))
        {
            $this->JSON(-5);
            return;
        }

        ini_set('max_execution_time', 0);

        $this->hEditorFindAndReplace = $this->library('hEditor/hEditorFindAndReplace', $_POST);
    }

    public function find()
    {
        $this->JSON(
            $this->hEditorFindAndReplace->find(
                hString::decodeEntitiesAndUTF8($_POST['find'])
            )
        );
    }

    public function replace()
    {
        $this->JSON(
            $this->hEditorFindAndReplace->replace(
                hString::decodeEntitiesAndUTF8($_POST['find']),
                hString::decodeEntitiesAndUTF8($_POST['replace'])
            )
        );
    }
}

?>