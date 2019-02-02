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

class hTicket extends hPlugin {

    private $hForm;
    private $hDialogue;
    private $hPagination;

    public function hConstructor()
    {
        $this->plugin('hApplication');

        $this->getPluginFiles();

        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $this->hPagination = $this->library('hPagination');

        $this->hFileDocument = $this->getForm();
    }

    public function getForm()
    {
        $this->hDialogue
            ->setForm($this->hForm)
            ->newDialogue('hTicket');

        $this->hForm
            ->addDiv('hTicketDiv')
            ->addFieldset('Ticket', '100%', '105px,')

            ->addTextInput(
                'hTicketSubject',
                'Subject:',
                '25,255'
            )
            ->addSelectInput(
                'hTicketSeverity',
                'Severity:',
                array(
                    0,
                    1,
                    2,
                    3,
                    4,
                    5,
                    6,
                    7,
                    8,
                    9,
                    10
                )
            )
            ->addSelectInput(
                array(
                    'id' => 'hTicketStatusId',
                    'multiple' => 'multiple'
                ),
                'Status: -L',
                array(

                ),
                5
            )
            ->addTextareaInput(
                'hTicketBody',
                'Body: -L',
                '50,10'
            );

        return $this->hDialogue
            ->addButtons('Save', 'Cancel')
            ->getDialogue(nil, 'Ticket');
    }
}

?>