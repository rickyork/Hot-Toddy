<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Forum Response
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

class hForumResponse extends hPlugin {

    private $hApplicationForm;

    public function hConstructor()
    {
        $this->plugin('hApplication/hApplicationForm');

        $frameworkResourceName = $this->getResourceName(
            $_GET['hFrameworkResource'],
            $_GET['hFrameworkResourceKey']
        );

        if (isset($_GET['hSubscription']))
        {
            $this->hFileDocument = $this->getTemplate(
                'Subscription',
                array(
                    'hFrameworkResourceName' => $frameworkResourceName,
                    'hUserEmail' => $this->user->getUserEmail(),
                    'subscribed' => (int) $_GET['hSubscriptionStatus']
                )
            );
        }

        if (isset($_GET['togglePostAttribute']))
        {
            switch ($_GET['togglePostAttribute'])
            {
                case 'hForumPostIsSticky':
                case 'hForumPostIsApproved':
                case 'hForumPostIsLocked':
                {
                    $this->hFileDocument = $this->getTemplate(
                        $_GET['togglePostAttribute'],
                        array(
                            'hFrameworkResourceName' => $frameworkResourceName,
                            'is' => (int) $_GET['is']
                        )
                    );
                    break;
                }
            }
        }

        if (isset($_GET['delete']))
        {
            $this->hFileDocument = $this->getTemplate(
                'Deletion',
                array(
                    'hFrameworkResourceName' => $frameworkResourceName
                )
            );
        }
    }
}

?>