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

class hLanguageText extends hPlugin {

    private $hDialogue;
    private $hForm;

    public function hConstructor()
    {
        $this->hFileCSS = '';

        $this->hDialogue = $this->library('hDialogue');
        $this->getPluginFiles();

        $this->hDialogue->newDialogue('hFrameworkUpdate');

        $this->hDialogueFullScreen = true;
        $this->hDialogueAutoTabs   = false;
        $this->hDialogueAction     = $this->hFilePath;

        $this->hForm = $this->library('hForm');

        $this->hForm->addDiv('hLanguages');

        $this->hForm->addFieldset('Translations', '100%', '150px,');

        $query = $this->hDatabase->select(
            array(
                'hLanguageId',
                'hLanguageName'
            ),
            'hLanguages'
        );

        foreach ($query as $data)
        {
            $this->hForm->addTextareaInput('hLanguageId-'.$data['hLanguageId'], $data['hLanguageName'].': -L', '50,3', '');
        }

        $this->hForm->addTableCell('');
        $this->hForm->addSubmitButton('hLanguageTextSave', 'Save');

        $this->hForm->addTableCell('');
        $this->hForm->addSubmitButton('hLanguageTextNew', 'New');

        $this->hDialogue->setForm($this->hForm);

        $this->hFileDocument =
            $this->hDialogue->getDialogue();
    }
}

?>