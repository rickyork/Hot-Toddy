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

class hMailEditorLibrary extends hPlugin {

    public function hConstructor()
    {

    }

    public function getMailers()
    {
        return $this->hMailTemplates->selectForTemplate(
            array(
                'hMailTemplateId',
                'hMailTemplateName',
                'hMailTemplateDescription'
            ),
            array(),
            'AND',
            'hMailTemplateDescription'
        );
    }

    public function getMailer($mailTemplateId)
    {
        $data = $this->hMailTemplates->selectAssociative(
            array(
                'hMailTemplateId',
                'hMailTemplateName',
                'hMailTemplateDescription',
                'hMailSubject',
                'hMailTo',
                'hMailCc',
                'hMailBcc',
                'hMailFrom',
                'hMailReplyTo',
                'hMailHTML',
                'hMailText'
            ),
            (int) $mailTemplateId
        );

        foreach ($data as $key => &$value)
        {
            $value = hString::entitiesToUTF8($value, false);
        }

        return $data;
    }

    public function getHTMLBody($mailTemplateId)
    {
        return hString::decodeHTML(
            $this->hMailTemplates->selectColumn(
                'hMailHTML',
                (int) $mailTemplateId
            )
        );
    }
}

?>