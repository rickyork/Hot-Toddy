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

class hMailEditorDocument extends hPlugin {

    private $hMailEditor;
    private $body;

    public function hConstructor()
    {
        $this->hFileCSS = '';
        $this->hFileJavaScript = '';

        $this->getPluginFiles();

        if (!isset($_GET['mailTemplateId']))
        {
            $this->fatal('No mailTemplateId specified.');
        }

        $mailTemplateId = (int) $_GET['mailTemplateId'];

        $this->hTemplatePath = '';

        $this->hMailEditor = $this->library('hMail/hMailEditor');

        $this->hEditorTemplateEnabled = true;
        $this->hEditorTemplateIsEmbedded = true;
        $this->hEditorTemplateForcePermission = true;
        $this->hFileDocumentSelector = 'div#hMailHTML';
        $this->hFileHTMLHeaders = true;

        $body = $this->hMailEditor->getHTMLBody($mailTemplateId);

        preg_replace_callback('/<body[^>]*?>(.*?)<\/body>/si', array($this, 'matchBody'), $body);

        $this->hFileDocument = $this->getTemplate(
            'Document',
            array(
                'body' => $this->body
            )
        );
    }

    public function matchBody($matches)
    {
        $this->body = $matches[1];
    }
}

?>