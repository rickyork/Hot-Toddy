<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Mail Editor Library
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