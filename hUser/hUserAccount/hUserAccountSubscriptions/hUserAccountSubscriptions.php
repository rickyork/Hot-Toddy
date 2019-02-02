<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Account Subscriptions
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

class hUserAccountSubscriptions extends hPlugin {

    private $hForm;
    private $hSubscription;

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        $this->hFileDocumentSelector = '';

        if ($this->isLoggedIn())
        {
            $this->hForm = $this->library('hForm');
            $this->hSubscription = $this->library('hSubscription');
            $this->getPrivateForm();

            if (isset($_POST['hSubscriptionId']) && is_array($_POST['hSubscriptionId']))
            {
                 foreach ($_POST['hSubscriptionId'] as $hSubscriptionId => $value)
                 {
                     $this->hSubscription->unsubscribeById((int) $hSubscriptionId);
                 }
            }

            $this->form();
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function form()
    {
        $form = $this->hForm;
        $form->addDiv('hUserAccountSubscriptionsDiv');
        $form->addFieldset('Subscriptions', '100%', '35%,auto');

        $subscriptions = $this->hSubscription->getUserSubscriptions();

        if (count($subscriptions))
        {
            $form->addTableCell(
                "<p>".
                    "Select each topic you'd like to unsubscribe from.".
                "</p>",
                2
            );

            foreach ($subscriptions as $hSubscriptionId => $name)
            {
                if (!empty($name))
                {
                    $form->addCheckboxInput('hSubscriptionId['.$hSubscriptionId.']', $name, 0);
                }

             //   $html .= "<li class='hUserSubscription' id='hUserSubscription-{$hSubscriptionId}'>{$name}</li>\n";
            }

            $form->addSubmitButton('hUserAccountSubscriptionsSubmit', 'Unsubscribe', 2);
        }
        else
        {
            $form->addTableCell(
                "<p>".
                    "You currently have no active subscriptions.".
                "</p>",
                2
            );
        }

        $this->hFileDocument .= $form->getForm();

    }
}

?>