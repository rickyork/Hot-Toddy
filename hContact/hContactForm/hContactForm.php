<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Form
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

class hContactForm extends hPlugin {

    private $hForm;
    private $hFormHumanVerification;
    private $hContactForm;
    private $contactFormPlugin;

    public function hConstructor()
    {
        $this->hForm = $this->library('hForm');
        $this->hFormHumanVerification = $this->library('hForm/hFormHumanVerification');
        $this->hContactForm = $this->library('hContact/hContactForm');

        if ($this->hContactFormPlugin(nil))
        {
            $this->contactFormPlugin = $this->library($this->hContactFormPlugin);

            if (method_exists($this->contactFormPlugin, 'getForm'))
            {
                $this->hContactFormDocument = $this->contactFormPlugin->getForm();
            }
            else
            {
                $this->warning('hContactFormPlugin was specified, but it does not have the required getForm() method.');
            }
        }
        else
        {
            $this->hContactFormDocument = $this->getForm();
        }

        if ($this->hForm->passedValidation())
        {
            $this->hContactForm->saveContactForm(1, $this->hContactFormAddressBookId(2), true);

            // {$hFrameworkName} <{$hFrameworkAdministrator}>

            $contact = $this->hContactForm->getContactData();

            $this->sendMail(
                'hContactForm',
                array_merge(
                    $contact,
                    array(
                        'hContactFormMailerSubject' => $this->hContactFormMailerSubject("[{$this->hFrameworkName}] Contact Form"),
                        'hContactFormTo' => $this->hContactFormTo("{$this->hFrameworkName} <{$this->hFrameworkAdministrator}>"),
                        'hContactFormCc' => $this->hContactFormCc(nil),
                        'hContactFormBcc' => $this->hContactFormBcc(nil),
                        'hContactFormFrom' => $this->hContactFormFrom("{$contact['hContactDisplayName']} <{$contact['hContactEmailAddress']}>"),
                        'hContactFormReplyTo' => $this->hContatFormReplyTo("{$contact['hContactDisplayName']} <{$contact['hContactEmailAddress']}>"),
                        'hContactFormComments' => nl2br($_POST['hContactFormComments']),
                        'hContactFormWhy' => isset($_POST['hContactFormWhy'])? $_POST['hContactFormWhy'] : ''
                    )
                )
            );

            $this->hContactFormDocument = $this->getTemplate('hContactFormThankYouTemplate:Thank-You');
        }

        $this->hFileDocument = $this->parseTemplateMarkup($this->hFileDocument);
    }

    private function getForm()
    {
        $this->hForm->addDiv('hContactFormDiv');

        $this->hContactForm->setLayout(
            array(
                'hContactFirstName',
                'hContactLastName',
                'hContactEmailAddress' => array(
                    'enabled' => true
                ),
                'hContactFormComments' => array(
                    $this, 'getComments'
                )
            )
        );

        $this->hContactForm->getForm($this->hContactFormFieldsetLabel('Contact Us'));

        return $this->hForm->getForm('hContactForm');
    }

    public function getComments()
    {
        if ($this->hContactFormWhy && is_array($this->hContactFormWhy))
        {
            $why = array();

            $why[0] = $this->hContactFormWhyFirstOption('Tell us why...');

            foreach ($this->hContactFormWhy as $value)
            {
                $why[$value] = $value;
            }

            $this->hForm
                ->addRequiredField('You did not indicate why you were writing in.')
                ->addSelectInput(
                    'hContactFormWhy',
                    $this->hContactFormWhyLabel('&nbsp;'),
                    $why
                );
        }

        $this->hForm
            ->addRequiredField('You did not provide any comments.')
            ->addTextareaInput(
                'hContactFormComments',
                'Comments: -L',
                '30,20'
            );

        $this->hFormHumanVerification->add();

        $this->hForm
            ->addTableCell('')
            ->addSubmitButton(
                'hContactFormSubmit',
                'Submit'
            );
    }
}

?>