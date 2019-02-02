<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Account Plugin
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

# This plugin is deprecated and has been replaced with hDashboard/hDashboardAccount
# This plugin will be refactored to be a branded user account plugin for use in
# client sites, as well as to conform to more recent Hot Toddy coding standards.

class hUserAccount extends hPlugin {

    private $hUserDatabase;
    private $hForm;
    private $hSubscription;
    private $hLocation;

    private $hContactTitle;
    private $hContactCompany;
    private $hContactCategory;
    private $hLocationCountryId;
    private $hLocationStateId;
    private $hContactAddressCity;
    private $hContactAddressPostalCode;

    private $isServerLogin = false;

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        $this->hUserDatabase = $this->database('hUser');
        $this->hForm = $this->library('hForm');
        $this->hSubscription = $this->library('hSubscription');
        $this->hLocation = $this->library('hLocation');

        $this->hFileDocumentSelector = '';

        if ($this->isLoggedIn())
        {
            $this->isServerLogin = $this->user->hUserUnixUId(0);

            $this->getPluginFiles();

            $html = '';

            // if ($this->isServerLogin)
            // {
            //     $html .=
            //         "<div id='hUserServerLogin'>\n".
            //         "    Because your account is a network account, modifying your login information is disabled.\n".
            //         "</div>\n";
            // }

            if (!$this->hFileTitle)
            {
                $this->hFileTitle = 'Your Account';
            }

            if ($this->hUserAccountFinder(false))
            {
                $html .=
                    "<div id='hUserAccountIconPanel'>\n".
                    "  <table>\n".
                    "     <tbody>\n".
                    "         <tr>\n".
                    "           <td id='hUserAccountFinder'>\n".
                    "             <img src='/images/icons/48x48/folder.png'".
                                     " alt='Launch Finder'".
                                     " class='hUserAccountFinder'".
                                     " title='Launch Finder'".$this->pngTransparency()." />\n".
                    "             <span>Finder</span>\n".
                    "           </td>\n".
                    "         </tr>\n".
                    "    </tbody>\n".
                    "  </table>\n".
                    "</div>\n";
            }

            $html =
                "<div id='hUserAccountWrapper'>\n".
                $html.
                $this->getLoginPanel().
                $this->getContactPanel();


            if ($this->hUserAccountGroups(true))
            {
                $html .= $this->getGroupsPanel();
            }

            if ($this->hUserAccountSubscriptions(true))
            {
                $html .= $this->getSubscriptionsPanel();
            }

            $html .=
                "</div>\n".
                "<div id='hUserAccountFollow'></div>\n";

            if ($this->hCoreMetricsClientId)
            {
                $html .=
                    $this->getRegistrationTag(
                        array(
                            'hContactId'                => $this->contact->hContactId,
                            'hContactEmailAddress'      => $this->user->getUserEmail(),
                            'hContactAddressCity'       => $this->hContactAddressCity,
                            'hLocationStateCode'        => $this->hLocation->getStateName($this->hLocationStateId),
                            'hContactAddressPostalCode' => $this->hContactAddressPostalCode,
                            'hLocationCountryName'      => $this->hLocation->getCountryName($this->hLocationCountryId),
                            'hContactCategory'            => $this->hContactCategory,
                            'hContactTitle'                => $this->hContactTitle,
                            'hContactCompany'            => $this->hContactCompany,
                            'hContactRegistered'        => true
                        )
                    );
            }

            $this->hFileDocument .= $html;
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    public function getContactPanel()
    {
        $contact = $this->contact->getRecord();

        $html =
            "<div class='hUserAccountPanel' id='hContacts'>\n".
            "<div class='hUserAccountPanelInner'>".
            $this->getPanelHeading($this->translate('Contact Information'), $this->getFilePathByPlugin('hUser/hUserAccount/hUserAccountContactInformation')).
            "<ul class='hUserAccountPanel'>\n".
            "    <li id='hContactName'>\n";

        if (isset($contact['hContactFirstName']) && isset($contact['hContactLastName']))
        {
            $html .= "        {$contact['hContactFirstName']} {$contact['hContactLastName']}\n";
        }

        $html .=
            "    </li>\n".
            "    <li id='hContactTitle'>\n";

        if (isset($contact['hContactTitle']))
        {
            $html .= "        {$contact['hContactTitle']}\n";
        }

        $html .=
            "    </li>\n".
            "    <li id='hContactCompany'>\n";

        if (isset($contact['hContactCompany']))
        {
            $html .= "        {$contact['hContactCompany']}\n";
        }

        $html .=
            "    </li>\n".
            "    <li id='hContactAddresses'>\n";

        if (isset($contact['hContactTitle']))
        {
            $this->hContactTitle    = $contact['hContactTitle'];
        }

        if (isset($contact['hContactCompany']))
        {
            $this->hContactCompany  = $contact['hContactCompany'];
        }

        $this->hContactCategory = $this->getPrivateCategory();

        foreach ($contact['hContactAddresses'] as $hContactAddressId => $address)
        {
            $html .=
                "<ul class='hContactAddress' id='hContactAddress-{$hContactAddressId}'>\n".
                "<li class='hContactAddressStreet'>{$address['hContactAddressStreet']}</li>\n".
                "<li class='hContactAddressLocation'>\n".
                    "<span class='hContactAddressCity'>{$address['hContactAddressCity']}</span>".
                    (!empty($address['hLocationStateId'])?
                            ", <span class='hLocationStateId' id='hLocationStateId-{$address['hLocationStateId']}'>".
                                $this->hLocation->getStateName($address['hLocationStateId']).
                            "</span>\n"
                        :
                            ''
                    ).
                    " <span class='hContactAddressPostalCode'>{$address['hContactAddressPostalCode']}</span>\n".
                "</li>".
                "<li class='hLocationCountryId' id='hLocationCountryId-{$address['hLocationCountryId']}'>".
                    $this->hLocation->getCountryName($address['hLocationCountryId']).
                "</li>\n".
                "</ul>\n";

            if (!empty($address['hContactAddressCity']))
            {
                $this->hContactAddressCity = $address['hContactAddressCity'];
            }

            if (!empty($address['hLocationStateId']))
            {
                $this->hLocationStateId = $address['hLocationStateId'];
            }

            if (!empty($address['hContactAddressPostalCode']))
            {
                $this->hContactAddressPostalCode = $address['hContactAddressPostalCode'];
            }

            $this->hLocationCountryId = $address['hLocationCountryId'];
        }

        $html .=
            "    </li>\n".
            "    <li id='hContactPhoneNumbers'>\n";

        foreach ($contact['hContactPhoneNumbers'] as $hContactPhoneNumberId => $phone)
        {
            $html .=
                "<ul class='hContactPhoneNumber' id='hContactPhoneNumberId-{$hContactPhoneNumberId}'>\n".
                "    <li>\n".
                "        <span class='hContactPhoneNumberField' id='hContactFieldId-{$phone['hContactFieldId']}'>\n".
                "            ".$this->contact->getFieldName($phone['hContactFieldId']).":\n".
                "        </span>\n".
                "        <span class='hContactPhoneNumber'>{$phone['hContactPhoneNumber']}</span>\n".
                "    </li>\n".
                "</ul>\n";
        }

        $html .=
            "    </li>\n".
            "</ul>\n".
            "</div>".
            "</div>\n";

        return $html;
    }

    public function getLoginPanel()
    {
        $disabled = $this->isServerLogin? " disabled='disabled'" : '';
        $html =
            "<div class='hUserAccountPanel' id='hUserLogin'>\n".
            "<div class='hUserAccountPanelInner'>".
            $this->getPanelHeading($this->translate('Login Information'), $this->getFilePathByPlugin('hUser/hUserAccount/hUserAccountLoginInformation')).
            "<ul class='hUserAccountPanel'>\n".
            "  <li><span class='hUserAccountLoginLabel'>Screen Name:</span>".$this->user->getUserName()."</li>\n".
            "  <li id='hUserEmail'><span class='hUserAccountLoginLabel'>Email:</span>".$this->user->getUserEmail()."</li>\n".
            "  <li>\n".
            "    <span class='hUserAccountLoginLabel'>Password:</span>\n".
            "    <span id='hUserAccountPassword'>&middot;&middot;&middot;&middot;&middot;&middot;&middot;</span>\n".
            "  </li>\n".
            "</ul>\n".
            "</div>".
            "</div>\n";

        return $html;
    }

    public function getPanelHeading($label, $link = null, $edit = true)
    {
        return(
            "<h4 class='hUserAccountHeading'>".
                ($edit? "<a href='".($link? $link : '#')."'>Edit</a>" : '').
                "<span>{$label}</span>".
            "</h4>\n"
        );
    }

    public function getSubscriptionsPanel()
    {
        $html = '';

        $subscriptions = $this->hSubscription->getUserSubscriptions();

        if (count($subscriptions))
        {
            $html =
                "<div class='hUserAccountPanel hUserAccountPanelSlim' id='hSubscriptions'>\n".
                "<div class='hUserAccountPanelInner'>".
                $this->getPanelHeading(
                    $this->translate('Your Subscriptions'),
                    $this->getFilePathByPlugin('hUser/hUserAccount/hUserAccountSubscriptions'),
                    count($subscriptions)
                ).
                "<ul class='hUserAccountPanel' id='hUserGroups'>";

            foreach ($subscriptions as $hSubscriptionId => $name)
            {
                if (!empty($name))
                {
                    $html .= "<li class='hUserSubscription' id='hUserSubscription-{$hSubscriptionId}'>{$name}</li>\n";
                }
            }


            $html .=
                "</ul>\n".
                "</div>".
                "</div>\n";
        }

        return $html;
    }

    public function getGroupsPanel()
    {
        $html = '';

        $groups = $this->hUserDatabase->getUserGroups();

        if (count($groups))
        {
            $html =
                "<div class='hUserAccountPanel hUserAccountPanelSlim' id='hUserGroups'>\n".
                "<div class='hUserAccountPanelInner'>".
                $this->getPanelHeading($this->translate('Your Groups'), '', false).
                "<ul class='hUserAccountPanel' id='hUserGroups'>";

            foreach ($groups as $hUserId => $hUserName)
            {
                $html .= "<li class='hUserGroup".($this->isDomainGroup($hUserName)? ' hGroupDomain' : '')."' id='hUserGroup-{$hUserId}'>{$hUserName}</li>";
            }

            $html .=
                "</ul>\n".
                "</div>".
                "</div>\n";
        }

        return $html;
    }
}

?>