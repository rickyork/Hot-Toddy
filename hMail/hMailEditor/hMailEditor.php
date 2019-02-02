<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Mail Editor
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

class hMailEditor extends hPlugin {

    private $hForm;
    private $hDialogue;
    private $hMailEditor;

    public function hConstructor()
    {
        $this->plugin('hApplication/hApplicationForm');

        $this->getPluginFiles();

        $this->hMailEditor = $this->library('hMail/hMailEditor');

        $this->hFileDocument = $this->getTemplate(
            'Editor',
            array(
                'mailTemplates' => $this->hMailEditor->getMailers(),
                'form' => $this->getForm()
            )
        );
    }

    public function getForm()
    {
        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $this->hForm->addDiv('hMailEditorDocumentMailer', 'Mailer');

        $this->hForm->addFieldset('Mailer', '100%', '100px,');

        $this->hForm->addTextInput('hMailTo', 'To:');
        $this->hForm->addTextInput('hMailCc', 'Cc:');
        $this->hForm->addTextInput('hMailBcc', 'Bcc:');
        $this->hForm->addTextInput('hMailFrom', 'From:');
        $this->hForm->addTextInput('hMailReplyTo', 'Reply-To:');
        $this->hForm->addTextInput('hMailSubject', 'Subject:');

        $this->hForm->addDiv('hMailEditorDocumentProperties', 'Properties');
        $this->hForm->addFieldset('Mailer', '100%', '100px,');

        $this->hForm->addData('hMailTemplateId', 'Mailer Id:', '');

        $this->hForm->addTextInput('hMailTemplateName', 'Name:');
        $this->hForm->addTextInput('hMailTemplateDescription', 'Label:');


        $this->hDialogue->newDialogue('hMailEditor');
        $this->hDialogue->setForm($this->hForm);

        $this->hDialogueDisableFocus = true;
        $this->hDialogueShadow = false;
        $this->hDialogueTitlebar = false;

        $this->hDialogueContentAppend = $this->getTemplate('Document Frame');

        return $this->hDialogue->getDialogue();
    }
}

?>