<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Language Text
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