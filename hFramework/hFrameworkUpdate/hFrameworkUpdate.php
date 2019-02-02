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

class hFrameworkUpdate extends hPlugin {

    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        if ($this->loggedIn())
        {
            if ($this->inGroup('root'))
            {
                if (!isset($_POST['hFrameworkUpdatePlugin']))
                {
                    $this->hDialogue = $this->library('hDialogue');

                    $this->hDialogue->newDialogue('hFrameworkUpdate');
                    $this->hDialogueFullScreen = true;
                    $this->hDialogueAutoTabs   = false;
                    $this->hDialogueAction     = $this->hFilePath;

                    $this->hForm = $this->library('hForm');

                    $dh = opendir(dirname(__FILE__));

                    $updates = array();

                    while (false !== ($file = readdir($dh)))
                    {
                        $path = $this->getConcatenatedPath(dirname(__FILE__), $file);

                        if ($file != '.' && $file != '..' && filetype($path) == 'dir' && substr($file, 0, 1) != '.')
                        {
                            $updates[$file] = $file;
                        }
                    }

                    closedir($dh);

                    $this->hForm->addDiv('hFrameworkUpdate');
                    $this->hForm->addFieldset('Select an Update', '100%', '150px,200px,');

                    $this->hForm->addSelectInput('hFrameworkUpdatePlugin', 'U:Update:', $updates, 1);
                    $this->hForm->addSubmitButton('hFrameworkUpdateSubmit', 'Submit');

                    $this->hDialogue->setForm($this->hForm);
                    $this->hFileDocument = $this->hDialogue->getDialogue();
                }
                else
                {
                    $plugin = $_POST['hFrameworkUpdatePlugin'];

                    $path = dirname(__FILE__)."/{$plugin}/{$plugin}.php";

                    if (file_exists($path) && !class_exists($plugin))
                    {
                        include_once($path);
                        new $plugin('hFramework/hFrameworkUpdate/'.$plugin);
                    }
                    else
                    {
                        $this->warning("Update Failed: Plugin path, {$path}, does not exist.", __FILE__, __LINE__);
                    }
                }

                $this->getPluginCSS();
            }
            else
            {
                $this->notAuthorized();
            }
        }
    }
}

?>