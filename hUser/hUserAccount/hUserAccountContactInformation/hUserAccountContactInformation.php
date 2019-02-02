<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Account Contact Information
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

class hUserAccountContactInformation extends hPlugin {

    private $hForm;
    private $hContactForm;
    private $hUserValidation;

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        $this->hFileDocumentSelector = '';

        if ($this->isLoggedIn())
        {
            $this->hForm = $this->library('hForm');

            $this->hUserValidation = $this->library('hUser/hUserValidation');
            $this->hContactForm    = $this->library('hContact/hContactForm');
            $this->hContactForm->setForm($this->hForm);

            $this->getPrivateForm();

            $this->contactForm();

            if ($this->hForm->renderMode('verify'))
            {
                $this->hContactForm->setDuplicateFields(false);
                $this->hContactForm->saveContactForm();

                if ($this->hUserAccountContactInformationRedirect(null))
                {
                    header('Location: '.$this->hUserAccountContactInformationRedirect);
                }
                else
                {
                    header('Location: '.$this->href($this->getFilePathByPlugin('hUser/hUserAccount')));
                }
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function contactForm()
    {
        if (!isset($_POST['hUserAccountContactInformation']))
        {
            $this->hContactForm->getContactData(true);
        }

        $this->hForm->addDiv('hUserAccountContactInformationDiv');

        $this->hContactForm->addContactForm();

        $this->hForm->addTableCell('');
        $this->hForm->addSubmitButton('hUserAccountContactInformationSubmit', $this->hUserAccountContactInformationButtonLabel('Save'));

        if ($this->hUserAccountContactInformationCallback(null))
        {
            call_user_func($this->hUserAccountContactInformationCallback, $this->hForm);
        }

        $this->hFileDocument = $this->hForm->getForm('hUserAccountContactInformation');
    }
}

?>