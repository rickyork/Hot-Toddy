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
# @description
# <h1>Contact API</h1>
# <p>
#   The <var>hContactLibrary</var> object is made globally available in every Hot Toddy
#   plugin as <var>$this-&gt;hContact</var>, it provides an API to easily store and retrieve
#   data associated with the rolodex.
# </p>
# @end

class hContactLibrary extends hPlugin {

    public $contactId = 0;
    public $userContactId = 0;
    private $userPermissions;

    private $methods = array(
        'getHomeNumber',
        'getMobileNumber',
        'getWorkNumber',
        'getExtensionNumber',
        'getCompanyNumber',
        'getFaxNumber',
        'getPagerNumber',
        'getOtherNumber',
        'getMainNumber',
        'getTollFreeNumber',
        'getAppointmentNumber',
        'getSchedulingNumber',
        'getiPhoneNumber',
        'getHomeFaxNumber',
        'getWorkFaxNumber',
        'getOtherFaxNumber'
    );

    public function __call($method, $arguments)
    {
        if (in_array($method, $this->methods, true))
        {
            $rtn = '';

            $contactFieldId = 0;

            switch ($method)
            {
                case 'getHomeAddress':       $contactFieldId = 1;  break;
                case 'getWorkAddress':       $contactFieldId = 2;  break;
                case 'getOtherAddress':      $contactFieldId = 3;  break;
                case 'getHomeNumber':        $contactFieldId = 4;  break;
                case 'getMobileNumber':      $contactFieldId = 5;  break;
                case 'getWorkNumber':        $contactFieldId = 6;  break;
                case 'getExtensionNumber':   $contactFieldId = 7;  break;
                case 'getCompanyNumber':     $contactFieldId = 8;  break;
                case 'getFaxNumber':         $contactFieldId = 9;  break;
                case 'getPagerNumber':       $contactFieldId = 10; break;
                case 'getOtherNumber':       $contactFieldId = 11; break;
                case 'getMainNumber':        $contactFieldId = 22; break;
                case 'getTollFreeNumber':    $contactFieldId = 23; break;
                case 'getAppointmentNumber': $contactFieldId = 24; break;
                case 'getSchedulingNumber':  $contactFieldId = 47; break;
                case 'getiPhoneNumber':      $contactFieldId = 25; break;
                case 'getHomeFaxNumber':     $contactFieldId = 26; break;
                case 'getWorkFaxNumber':     $contactFieldId = 27; break;
                case 'getOtherFaxNumber':    $contactFieldId = 28; break;
                case 'getPersonalEmail':     $contactFieldId = 19; break;
                case 'getWorkEmail':         $contactFieldId = 20; break;
                case 'getOtherEmail':        $contactFieldId = 21; break;
            }

            if (!empty($contactFieldId))
            {
                $contactId = (
                    !empty($arguments[0]) ? (int) $arguments[0] : $this->user->getContactId()
                );

                $contactAddressId = isset($arguments[1])? (int) $arguments[1] : 0;

                if (!empty($contactId))
                {
                    switch (true)
                    {
                        case stristr($method, 'Number'):
                        {
                            $rtn = $this->getPhoneNumber(
                                $contactId,
                                $contactFieldId,
                                $contactAddressId
                            );

                            break;
                        }
                        case stristr($method, 'Address'):
                        {
                            $rtn = $this->getAddress(
                                $contactId,
                                $contactFieldId
                            );

                            break;
                        }
                        case stristr($method, 'Email'):
                        {
                            $rtn = $this->getEmailAddress(
                                $contactId,
                                $contactFieldId,
                                $contactAddressId
                            );

                            break;
                        }
                        case stristr($method, 'Account'):
                        {
                            $rtn = $this->getInternetAccount(
                                $contactId,
                                $contactFieldId,
                                $contactAddressId
                            );

                            break;
                        }
                    }

                    return $rtn;
                }
                else
                {
                    $this->warning("Unable to '{$method}' because no contactId was provided.", __FILE__, __LINE__);
                }
            }

            return $rtn;
        }
        else
        {
            return parent::__call($method, $arguments);
        }
    }

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Constructor</h2>
        # <p>
        #   Gets the columns for the <var>hContacts</var> table, sets the default
        #   <var>contactId</var>, which operations are carried out in the context of.
        #   The <var>contactId</var> is set to that of the current user, if the current user is
        #   logged in.
        # </p>
        # @end

        $this->hContacts->getColumns();
        $this->setContactId();
    }

    public function &setContactId($userId = 0)
    {
        # @return hContactLibrary

        # @description
        # <h2>Setting the Contact Id</h2>
        # <p>
        #   Sets the <var>contactId</var> that the <var>hContact</var> library object uses internally,
        #   by default the <var>contactId</var> of the current user is set, if the user is logged in.
        # </p>
        # @end

        if (empty($userId) && $this->isLoggedIn())
        {
            $userId = (int) $_SESSION['hUserId'];
        }

        if (!empty($userId))
        {
            $this->hContactId = $this->user->getContactId($userId);
        }

        return $this;
    }

    public function getRecordsByEmailAddress($contactEmailAddress = '')
    {
        # @return array

        # @description
        # <h2>Getting Contacts By Email Address</h2>
        # <p>
        #   Returns contacts, regardless of the address book they are located in,
        #   by the email address you specified in <var>$contactEmailAddress</var>.
        # </p>
        # <p>
        #   See <a href='#getRecords' class='code'>getRecords()</a> for a detailed
        #   explanation of the information  returned.
        # </p>
        # @end

        return $this->getRecords(
            $this->hContactEmailAddresses->select(
                'hContactId',
                array(
                    'hContactEmailAddress' => $contactEmailAddress
                )
            )
        );
    }

    public function getRecord($contactId = 0, $userId = 0)
    {
        # @return array

        # @description
        # <h2>Getting a Contact</h2>
        # <p>
        #   Returns contacts, regardless of the address book they are located in,
        #   by the email address you specified in <var>$contactEmailAddress</var>.
        # </p>
        # <p>
        #   See <a href='#getRecords' class='code'>getRecords()</a> for a detailed
        #   explanation of the information returned.
        # </p>

        if (empty($userId))
        {
            if (empty($contactId))
            {
                $this->setContactId();
                $contactId = $this->hContactId;

                if (empty($contactId))
                {
                    $this->notice('No contact Id could be set.', __FILE__, __LINE__);
                    return $this->getBlankContact();
                }
            }
        }
        else
        {
            $this->setContactId($userId);
            $contactId = $this->hContactId;
        }

        $record = $this->getRecords(array($contactId));
        return $record[$contactId];
    }

    public function userIdToContactId($userId = 0)
    {
        # @return integer

        # @description
        # <h2>Getting a contactId For a userId</h2>
        # <p>
        #   Returns the <var>contactId</var> for the supplied <var>userId</var>.
        #   If no user is specified in the <var>$userId</var> argument,
        #   the current user is assumed.
        # </p>
        # <p>
        #   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user
            ->whichUserId($userId)
            ->setNumericUserId($userId);

        return (int) $this->hContacts->selectColumn(
            'hContactId',
            array(
                'hContactAddressBookId' => 1,
                'hUserId' => $userId
            )
        );
    }

    public function getFlatRecord($contactId = 0, $unset = true)
    {
        $this->whichContactId($contactId);

        # @return array

        # @description
        # <h2>Returning a Flat Record of Contact Information</h2>
        # <p>
        #    Returns a flat record of contact information, where the returned array has no indices that
        #    are themselves arrays.
        # </p>
        # <ul>
        #    <li class='code'>hContactAddressBookId</li>
        #    <li class='code'>hContactId</li>
        #    <li class='code'>hUserId</li>
        #    <li class='code'>hContactFirstName</li>
        #    <li class='code'>hContactMiddleName</li>
        #    <li class='code'>hContactLastName</li>
        #    <li class='code'>hContactDisplayName</li>
        #    <li class='code'>hContactNickName</li>
        #    <li class='code'>hContactWebsite</li>
        #    <li class='code'>hContactCompany</li>
        #    <li class='code'>hContactTitle</li>
        #    <li class='code'>hContactDepartment</li>
        #    <li class='code'>hContactGender</li>
        #    <li class='code'>hContactDateOfBirth</li>
        #    <li class='code'>hContactCreated</li>
        #    <li class='code'>hContactLastModified</li>
        #    <li class='code'>hContactDateOfBirthFormatted</li>
        #    <li class='code'>hContactAddressId</li>
        #    <li class='code'>hContactFieldId</li>
        #    <li class='code'>hContactAddressStreet</li>
        #    <li class='code'>hContactAddressCity</li>
        #    <li class='code'>hLocationStateId</li>
        #    <li class='code'>hContactAddressPostalCode</li>
        #    <li class='code'>hLocationCountyId</li>
        #    <li class='code'>hLocationCountryId</li>
        #    <li class='code'>hContactAddressLatitude</li>
        #    <li class='code'>hContactAddressLongitude</li>
        #    <li class='code'>hLocationCountryName</li>
        #    <li class='code'>hLocationCountryISO2</li>
        #    <li class='code'>hLocationCountryISO3</li>
        #    <li class='code'>hContactAddressTemplateId</li>
        #    <li class='code'>hLocationStateLabel</li>
        #    <li class='code'>hLocationUseStateCode</li>
        #    <li class='code'>hLocationCountyName</li>
        #    <li class='code'>hContactAddressTemplate</li>
        #    <li class='code'>hLocationStateCode</li>
        #    <li class='code'>hLocationStateName</li>
        #    <li class='code'>hContactFieldName</li>
        #    <li class='code'>hLocationCity</li>
        #    <li class='code'>hLocationCounty</li>
        #    <li class='code'>hLocationSequenceNumber</li>
        #    <li class='code'>hLocationAcceptable</li>
        #    <li class='code'>hContactAddressBookId</li>
        #    <li class='code'>hContactAddressBookName</li>
        #    <li class='code'>hContactAddressBookIsDefault</li>
        #    <li class='code'>hContactEmailAddressPersonal <span>*</span></li>
        #    <li class='code'>hContactEmailAddressWork <span>*</span></li>
        #    <li class='code'>hContactEmailAddressFacebook <span>*</span></li>
        #    <li class='code'>hContactEmailAddressGmail <span>*</span></li>
        #    <li class='code'>hContactEmailAddressMicrosoftHotmail <span>*</span></li>
        #    <li class='code'>hContactEmailAddressWindowsLive <span>*</span></li>
        #    <li class='code'>hContactEmailAddressiCloud <span>*</span></li>
        #    <li class='code'>hContactEmailAddressMicrosoftExchange <span>*</span></li>
        #    <li class='code'>hContactEmailAddressAol <span>*</span></li>
        #    <li class='code'>hContactEmailAddressOther <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberHome <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberMobile <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberWork <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberExtension <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberCompany <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberFax <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberPager <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberOther <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberMain <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberTollFree <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberAppointment <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberScheduling <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberiPhone <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberHomeFax <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberWorkFax <span>*</span></li>
        #    <li class='code'>hContactPhoneNumberOtherFax <span>*</span></li>
        #    <li class='code'>hContactInternetAccountAppleId <span>*</span></li>
        #    <li class='code'>hContactInternetAccountiMessages <span>*</span></li>
        #    <li class='code'>hContactInternetAccountiCloud <span>*</span></li>
        #    <li class='code'>hContactInternetAccountGameCenter <span>*</span></li>
        #    <li class='code'>hContactInternetAccountiTunes <span>*</span></li>
        #    <li class='code'>hContactInternetAccountMacAppStore <span>*</span></li>
        #    <li class='code'>hContactInternetAccountFacebook <span>*</span></li>
        #    <li class='code'>hContactInternetAccountWindowsLive <span>*</span></li>
        #    <li class='code'>hContactInternetAccountGoogle <span>*</span></li>
        #    <li class='code'>hContactInternetAccountAol <span>*</span></li>
        #    <li class='code'>hContactInternetAccountPSN <span>*</span></li>
        #    <li class='code'>hContactInternetAccountXbox <span>*</span></li>
        #    <li class='code'>hContactInternetAccountYahoo <span>*</span></li>
        #    <li class='code'>hContactInternetAccountICQ <span>*</span></li>
        #    <li class='code'>hContactInternetAccountiChat <span>*</span></li>
        #    <li class='code'>hContactInternetAccountJabber <span>*</span></li>
        #    <li class='code'>hContactInternetAccountOther <span>*</span></li>
        #    <li class='code'>hContactVariableName <span>**</span></li>
        # </ul>
        # <p>
        #    <i>* Only indexes that exist in the database are created.</i>
        # </p>
        # <p>
        #    <i>** Variables are merged into the flat array with the variable name as the
        #    key for each indice, and the variable value is assigned to each corresponding
        #    variable key.</i>
        # </p>
        # <p>
        #    With regards to the address, only the default address is returned as part of the
        #    array.  If there is no default address, then the first address in the address
        #    collection is used.
        # </p>
        # @end

        $contact = $this->getRecord($contactId);

        if (is_array($contact['hContactAddresses']) && count($contact['hContactAddresses']))
        {
            $contactAddressId = 0;

            foreach ($contact['hContactAddresses'] as $addressId => $address)
            {
                if (!empty($address['hContactAddressIsDefault']))
                {
                     $contactAddressId = $addressId;
                     break;
                }
            }

            if (!empty($contactAddressId))
            {
                $contact = $contact['hContactAddresses'][$contactAddressId];
            }
            else
            {
                $contact = array_merge(
                    $contact,
                    array_shift($contact['hContactAddresses'])
                );
            }
        }

        $contact = array_merge($contact, $contact['hContactAddressBook']);

        unset($contact['hContactAddressBook']);

        unset($contact['hContactAddresses']);

        $this->setPhoneNumbers($contact, $unset);
        $this->setEmailAddresses($contact, $unset);
        $this->setInternetAccounts($contact, $unset);

        $variables = $this->getVariables($contactId);

        foreach ($variables as $variable)
        {
            $contact[$variable['hContactVariable']] = $variable['hContactValue'];
        }

        return $contact;
    }

    public function &setPhoneNumbers(&$contact, $unset = true)
    {
        # @return hContactLibrary

        # @description
        # <h2>Setting Phone Numbers</h2>
        # <code>
        #    $phoneNumbers = array(
        #        1234 => array(
        #            'hContactPhoneNumberId' => 1234,
        #            'hContactFieldId' => 4,
        #            'hContactFieldName' => 'Home',
        #            'hContactPhoneNumber' => '(555) 555-1212'
        #        )
        #    );
        # </code>
        # <p>
        #    This method takes the preceding array and creates the following in its place:
        # </p>
        # <code>
        #    $phoneNumbers = array(
        #        'hContactPhoneNumberHome' => '(555) 555-1212'
        #    );
        # </code>
        # @end

        $this->setFieldValuesForArray(
            $contact,
            'hContactPhoneNumber',
            'hContactPhoneNumbers',
            $unset
        );

        return $this;
    }

    public function &setEmailAddresses(&$contact, $unset = true)
    {
        # @description
        # <h2>Setting Email Addresses</h2>
        # <code>
        #    $emailAddresses = array(
        #        1234 => array(
        #            'hContactEmailAddressId' => 1234,
        #            'hContactFieldId' => 20,
        #            'hContactFieldName' => 'Work',
        #            'hContactPhoneNumber' => 'johndoe@example.com'
        #        )
        #    );
        # </code>
        # <p>
        #    This method takes the preceding array and creates the following in its place:
        # </p>
        # <code>
        #    $emailAddresses = array(
        #        'hContactEmailAddressWork' => 'johndoe@example.com'
        #    );
        # </code>
        # @end

        $this->setFieldValuesForArray(
            $contact,
            'hContactEmailAddress',
            'hContactEmailAddresses',
            $unset
        );

        return $this;
    }

    public function &setInternetAccounts(&$contact, $unset = true)
    {
        # @description
        # <h2>Setting Internet Accounts</h2>
        # <code>
        #    $internetAccounts = array(
        #        1234 => array(
        #            'hContactInternetAccountId' => 1234,
        #            'hContactFieldId' => 30,
        #            'hContactFieldName' => 'Apple Id',
        #            'hContactPhoneNumber' => 'johndoe@example.com'
        #        )
        #    );
        # </code>
        # <p>
        #    This method takes the preceding array and creates the following in its place:
        # </p>
        # <code>
        #    $internetAccounts = array(
        #        'hContactInternetAccountAppleId' => 'johndoe@example.com'
        #    );
        # </code>
        # @end

        $this->setFieldValuesForArray(
            $contact,
            'hContactInternetAccount',
            'hContactInternetAccounts',
            $unset
        );

        return $this;
    }

    private function &setFieldValuesForArray(&$contact, $singular, $plural, $unset = true)
    {
        # @return hContactLibrary

        # @description
        # <h2>Setting Contact Field in a Flat Associative Array</h2>
        # <p>
        #    Takes an array of phone numbers, email addresses, or internet accounts, then creates new
        #    records in the array for each bit of information, and finally unsets the original more complex
        #    array.
        # </p>
        # <p>
        #
        # </p>
        # @end

        if (isset($contact[$plural]) && is_array($contact[$plural]))
        {
            foreach ($contact[$plural] as $field)
            {
                $item = $field[$singular];

                if (!isset($contact[$singular]))
                {
                    $contact[$singular] = $item;
                }

                $contact[$this->getFieldLabelForArray($field['hContactFieldId'])] = $item;
            }

            if ($unset)
            {
                unset($contact[$plural]);
            }
        }

        return $this;
    }

    public function getFieldLabelForArray($contactFieldId)
    {
        # @return string

        # @description
        # <h2>Getting a Field's Name</h2>
        # <p>
        #    Returns the name used for a field when building, for example, a
        #    <a href='#getFlatRecord'>flat record</a>.  Passing a <var>$contactFieldId</var> of
        #    <var>8</var> (a <var>contactFieldId</var> for a phone number) returns
        #    the value <var>hContactPhoneNumberCompany</var>.  This label can then be used to
        #    flatten values in a contact record to a single dimension associative array.
        # </p>
        # @end

        return (
            $this->hFrameworkResources->selectColumn(
                'hFrameworkResourceNameColumn',
                $this->hContactFields->selectColumn(
                    'hFrameworkResourceId',
                    $contactFieldId
                )
            ).
            $this->hContactFields->selectColumn(
                'hContactFieldName',
                $contactFieldId
            )
        );
    }

    public function getBlankContact()
    {
        # @return array

        # @description
        # <h2>Getting a Blank Contact</h2>
        # <p>
        #   This method returns a structure identical to <a href='#getRecord' class='code'>getRecord()</a>,
        #   except without any contact data.  This is useful when you want to fill in the data yourself,
        #   or if retrieving a contact fails because that contact has no record, a blank record can be returned
        #   instead to ensure consistent data.
        # </p>
        # @end

        return array(
            'hContactAddressBookId' => 0,
            'hContactId' => 0,
            'hUserId' => 0,
            'hContactFirstName' => '',
            'hContactMiddleName' => '',
            'hContactLastName' => '',
            'hContactDisplayName' => '',
            'hContactNickName' => '',
            'hContactWebsite' => '',
            'hContactCompany' => '',
            'hContactTitle' => '',
            'hContactDepartment' => '',
            'hContactGender' => '',
            'hContactDateOfBirth' => nil,
            'hContactCreated' => nil,
            'hContactLastModified' => nil,
            'hContactDateOfBirthFormatted'  => nil,
            'hContactAddresses' => array(),
            'hContactAddressBook' => array(),
            'hContactEmailAddresses' => array(),
            'hContactPhoneNumbers' => array(),
            'hContactInternetAccounts' => array(),
            'hContactVariables' => array()
        );
    }

    public function getRecords(array $contactIds = array())
    {
        # @return integer

        # @description
        # <h2>Getting Contact Records</var>
        # <p>
        #  The following information is returned in an <var>array</var> for every <var>contactId</var>
        #  passed in the <var>$contactIds</var> argument, which requires an array.
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code' colspan='2'>hContactAddressBookId</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactId</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hUserId</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactFirstName</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactMiddleName</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactLastName</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactDisplayName</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactNickName</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactWebsite</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactCompany</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactTitle</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactDepartment</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactGender</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactDateOfBirth</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactCreated</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactLastModified</td>
        #     </tr>
        #     <tr>
        #       <td class='code' colspan='2'>hContactDateOfBirthFormatted</td>
        #     </tr>
        #     <tr>
        #       <td class='code' rowspan='25'>hContactAddresses</td>
        #       <td class='code'>hContactAddressId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressStreet</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressCity</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressPostalCode</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountyId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressLatitude</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressLongitude</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryISO2</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountryISO3</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressTemplateId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateLabel</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationUseStateCode</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCountyName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressTemplate</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateCode</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationStateName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCity</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationCounty</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationSequenceNumber</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hLocationAcceptable</td>
        #     </tr>
        #     <tr>
        #       <td rowspan='3' class='code'>hContactAddressBook</td>
        #       <td class='code'>hContactAddressBookId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressBookName</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactAddressBookIsDefault</td>
        #     </tr>
        #     <tr>
        #       <td rowspan='4' class='code'>hContactEmailAddresses</td>
        #       <td class='code'>hContactEmailAddressId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactEmailAddress</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldName</td>
        #     </tr>
        #     <tr>
        #       <td rowspan='4' class='code'>hContactPhoneNumbers</td>
        #       <td class='code'>hContactPhoneNumberId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactPhoneNumber</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldName</td>
        #     </tr>
        #     <tr>
        #       <td rowspan='4' class='code'>hContactInternetAccounts</td>
        #       <td class='code'>hContactInternetAccountId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactInternetAccount</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldName</td>
        #     </tr>
        #     <tr>
        #       <td rowspan='2' class='code'>hContactVariables</td>
        #       <td class='code'>hContactVariable</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactValue</td>
        #     </tr>
        #   </tbody>
        # </table>
        # @end

        if (!count($contactIds))
        {
            $contactIds = array($this->hContactId);
        }

        $records = array();

        foreach ($contactIds as $contactId)
        {
            // Array merge does not preserve numberic indexes, nor merge
            // duplicate numerical indexes.  WTF? :-/  I guess I do it the hard way.
            $contact = $this->getContact($contactId);

            $contact[$contactId]['hContactEmailAddresses'] = $this->getEmailAddresses($contactId);
            $contact[$contactId]['hContactPhoneNumbers'] = $this->getPhoneNumbers($contactId);
            $contact[$contactId]['hContactInternetAccounts'] = $this->getInternetAccounts($contactId);
            $contact[$contactId]['hContactAddresses'] = $this->getAddresses($contactId);
            $contact[$contactId]['hContactAddressBook'] = $this->getAddressBook($contactId);

            $contact[$contactId]['hContactVariables'] = $this->hContactVariables->select(
                array(
                    'hContactVariable',
                    'hContactValue'
                ),
                $contactId
            );

            $records[$contactId] = $contact[$contactId];
        }

        return $records;
    }

    public function getUserAddressBookId($userId = 0)
    {
        # @return integer

        # @description
        # <h2>Get a User's Address Book Id</h2>
        # <p>
        # Returns the <var>contactAddressBookId</var> for the specified <var>$userId</var>, this
        # would be the address book owned by the user, that would be used by the user to store
        # whatever contacts the user needs to store.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        # If the user does not have an address book, one is automatically created for them,
        # and the <var>contactAddressBookId</var> of the new address book is returned.
        # </p>
        # @end

        $this->user
            ->whichUserId($userId)
            ->setNumericUserId($userId);

        if (!empty($userId))
        {
            $contactAddressBookId = $this->hContactAddressBooks->selectColumn(
                'hContactAddressBookId',
                array(
                    'hUserId' => (int) $userId,
                    'hContactAddressBookIsDefault' => 1
                )
            );

            if (!empty($contactAddressBookId))
            {
                return (int) $contactAddressBookId;
            }
            else
            {
                // Automatically create one.
                $contactAddressBookId = $this->hContactAddressBooks->insert(
                    array(
                        'hContactAddressBookId' => nil,
                        'hUserId'=> (int) $userId,
                        'hContactAddressBookName' => $this->user->getFullName().'&039;s Address Book',
                        'hPlugin' => '',
                        'hContactAddressBookIsDefault' => 1
                    )
                );

                $this->hUserPermission = $this->library('hUser/hUserPermission');

                // Give the owner rw access to their new address book.
                $this->hUserPermission->save(
                    'hContactAddressBooks',
                    $contactAddressBookId
                );
            }
        }
        else
        {
            return 0;
        }
    }

    public function getAddressBook($contactId = 0)
    {
        $this->whichContactId($contactId);

        # @return array

        # @description
        # <h2>Getting an Address Book</h2>
        # <p>
        #   Returns the address book associated with the specified <var>$contactId</var>.
        # </p>
        # <p>
        #   The following information is returned:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hContactAddressBookId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressBookName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressBookIsDefault</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        return $this->hContactAddressBooks->selectAssociative(
            array(
                'hContactAddressBookId',
                'hContactAddressBookName',
                'hContactAddressBookIsDefault'
            ),
            (int) $this->getAddressBookId($contactId)
        );
    }

    public function getAddressBookId($contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Getting an Address Book Id for a Contact</h2>
        # <p>
        #   Returns a <var>contactAddressBookId</var> for the specified <var>$contactId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # @end

        $this->whichContactId($contactId);

        return (int) $this->hContacts->selectColumn(
            'hContactAddressBookId',
            (int) $contactId
        );
    }

    public function getUserId($contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Getting a User Id</h2>
        # <p>
        #   Returns the <var>userId</var> associated with the specified <var>$contactId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # @end

        $this->whichContactId($contactId);

        return (int) $this->hContacts->selectColumn(
            'hUserId',
            (int) $contactId
        );
    }

    public function getContact($contactId = 0)
    {
        # @return array

        # @description
        # <h2>Get Contact</h2>
        # <p>
        #   Returns the contact information associated with the specified <var>$contactId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        #   The following information is returned:
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hContactAddressBookId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactFirstName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactMiddleName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactLastName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactDisplayName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactNickName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactWebsite</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactCompany</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactTitle</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactDepartment</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactGender</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactDateOfBirth</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactCreated</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactLastModified</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactDateOfBirthFormatted</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        $this->whichContactId($contactId);

        $this->hDatabase->setResultIndex('hContactId');

        $contacts = $this->hContacts->selectResults(
            array(
                'hContactAddressBookId',
                'hContactId',
                'hUserId',
                'hContactFirstName',
                'hContactMiddleName',
                'hContactLastName',
                'hContactDisplayName',
                'hContactNickName',
                'hContactWebsite',
                'hContactCompany',
                'hContactTitle',
                'hContactDepartment',
                'hContactGender',
                'hContactDateOfBirth',
                'hContactCreated',
                'hContactLastModified'
            ),
            (int) $contactId
        );

        foreach ($contacts as $i => &$contact)
        {
            $contact['hContactFirstName'] = hString::decodeHTML($contact['hContactFirstName']);
            $contact['hContactMiddleName'] = hString::decodeHTML($contact['hContactMiddleName']);
            $contact['hContactLastName'] = hString::decodeHTML($contact['hContactLastName']);
            $contact['hContactDisplayName'] = hString::decodeHTML($contact['hContactDisplayName']);
            $contact['hContactNickName'] = hString::decodeHTML($contact['hContactNickName']);
            $contact['hContactCompany'] = hString::decodeHTML($contact['hContactCompany']);
            $contact['hContactTitle'] = hString::decodeHTML($contact['hContactTitle']);
            $contact['hContactDepartment'] = hString::decodeHTML($contact['hContactDepartment']);
            $contact['hContactGender'] = (int) $contact['hContactGender'];
            $contact['hContactDateOfBirthFormatted'] = date('m/d/Y', $contact['hContactDateOfBirth']);
            $contact['hContactCreatedFormatted'] = date('m/d/Y h:i a', $contact['hContactCreated']);
            $contact['hContactLastModifiedFormatted'] = date('m/d/Y h:i a', $contact['hContactLastModified']);

            $gender = '';

            switch ((int) $contact['hContactGender'])
            {
                case -1:
                {
                    $gender = 'Not Specified';
                    break;
                }
                case 0:
                {
                    $gender = 'Female';
                    break;
                }
                case 1:
                {
                    $gender = 'Male';
                    break;
                }
            }

            $contact['hContactGenderLabel'] = $gender;
        }

        return $contacts;
    }

    public function getAddresses($contactId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Addresses</h2>
        # <p>
        #   Returns one or more addresses for the specified <var>$contactId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        #   The following information is returned in each array:
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hContactAddressId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactFieldId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressStreet</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressCity</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationStateId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressPostalCode</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCountyId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCountryId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressLatitude</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressLongitude</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCountryName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCountryISO2</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCountryISO3</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressTemplateId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationStateLabel</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationUseStateCode</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCountyName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactAddressTemplate</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationStateCode</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationStateName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactFieldName</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCity</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationCounty</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationSequenceNumber</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hLocationAcceptable</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        $this->whichContactId($contactId);

        $sql = $this->getTemplateSQL(
            array(
                'contactId' => (int) $contactId
            )
        );

        return $this->hDatabase->getResults($sql, 'hContactAddressId');
    }

    public function getAddress($contactId = 0, $contactFieldId = 2)
    {
        $this->whichContactId($contactId);

        $sql = $this->getTemplateSQL(
            'getAddresses',
            array(
                'contactId' => $contactId,
                'contactFieldId' => $contactFieldId
            )
        );

        return $this->hDatabase->getAssociativeResults($sql);
    }

    public function getEmailAddresses($contactId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Email Addresses</h2>
        # <p>
        #   Returns one or more email addresses for the specified <var>$contactId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        #   If no email addresses are found for the contact, and the address book is
        #   address book '1' (Website Registrations), then the method returns the
        #   user's account email address as their <i>Work</i> email address.
        # </p>
        # <p>
        #   The following information is returned for each email address:
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hContactEmailAddressId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactEmailAddress</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactFieldId</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hContactFieldName</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        $this->whichContactId($contactId);

        $data = $this->getData($contactId, 'hContactEmailAddress', 'es');

        if ($this->getAddressBookId($contactId) == 1 && !count($data))
        {
            $userId = $this->getUserId($contactId);

            $data[0] = array(
                'hContactEmailAddressId' => -1,
                'hContactFieldId' => 20,
                'hContactFieldName' => 'Work',
                'hContactEmailAddress' => $this->user->getUserEmail($userId)
            );
        }

        return $data;
    }

    public function getEmailAddress($contactId, $contactFieldId = 2, $contactAddressId = 0)
    {
        # @return false, string

        # @description
        # <h2>Getting an Email Address</h2>
        # <p>
        #   Returns the email address for the specified <var>$contactId</var>
        #   and <var>$contactFieldId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        #   If <var>$contactAddressId</var> is specified, the phone number is returned for the
        #   specified contact address id.
        # </p>
        # <p>

        # </p>
        # @end

        return $this->getSingleDataFromArray(
            'hContactEmailAddress',
            'es',
            $contactId,
            $contactFieldId,
            $contactAddressId
        );
    }

    public function getPhoneNumbers($contactId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Phone Numbers</h2>
        # <p>
        #   Returns one or more phone numbers for the specified <var>$contactId</var>.
        #   If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        #   <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        #   The following information is returned for each phone number:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hContactPhoneNumberId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactPhoneNumber</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactFieldId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactFieldName</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->whichContactId($contactId);

        return $this->getData(
            $contactId,
            'hContactPhoneNumber'
        );
    }

    public function getPhoneNumber($contactId = 0, $contactFieldId = 6, $contactAddressId = 0)
    {
        $this->whichContactId($contactId);

        # @return string

        # @description
        # <h2>Getting a Phone Number</h2>
        # <p>
        # Returns the phone number for the specified <var>$contactId</var>
        # and <var>$contactFieldId</var>.
        # If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        # <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        # If <var>$contactAddressId</var> is specified, the phone number is returned for the
        # specified contact address id.
        # </p>
        # @end

        return $this->getSingleDataFromArray(
            'hContactPhoneNumber',
            's',
            $contactId,
            $contactFieldId,
            $contactAddressId
        );
    }

    public function getInternetAccounts($contactId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Internet Accounts</h2>
        # <p>
        # Returns one or more internet accounts for the specified <var>$contactId</var>.
        # If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        # <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        # The following information is returned for each internet account:
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>hContactInternetAccountId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactInternetAccount</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldId</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hContactFieldName</td>
        #     </tr>
        #   </tbody>
        # </table>
        # @end

        $this->whichContactId($contactId);

        return $this->getData(
            $contactId,
            'hContactInternetAccount'
        );
    }

    public function getInternetAccount($contactId = 0, $contactFieldId = 18, $contactAddressId = 0)
    {
        $this->whichContactId($contactId);

        # @return string

        # @description
        # <h2>Getting an Internet Account</h2>
        # <p>
        # Returns an internet account for the specified <var>$contactId</var>
        # and <var>$contactFieldId</var>.
        # If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        # <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # <p>
        # If <var>$contactAddressId</var> is specified, the account is returned for the
        # specified contact address id.
        # </p>
        # @end

        return $this->getSingleDataFromArray(
            'hContactInternetAccount',
            's',
            $contactId,
            $contactFieldId,
            $contactAddressId
        );
    }

    private function getData($contactId = 0, $field = '', $plural = 's')
    {
        # @return array

        # @description
        # <h2>Getting Generic Contact Data</h2>
        # <p>
        # Returns data from the <var>hContactEmailAddresses</var>, <var>hContactPhoneNumbers</var>,
        # or <var>hContactInternetAccounts</var> tables for the specified <var>$contactId</var>.
        # If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        # <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # @end

        $this->whichContactId($contactId);
        $this->hDatabase->setResultIndex($field.'Id');

        $data = $this->hDatabase->selectResults(
            array(
                $field.'Id',
                'hContactFieldId',
                $field
            ),
            $field.$plural,
            array(
                'hContactId' => (int) $contactId
            ),
            'AND',
            'hContactFieldId'
        );

        foreach ($data as $id => $record)
        {
            $data[$id]['hContactFieldName'] = $this->getFieldName($data[$id]['hContactFieldId']);
        }

        return $data;
    }

    private function getSingleDataFromArray($field, $plural, $contactId, $contactFieldId, $contactAddressId)
    {
        $table = $field.$plural;

        if (is_array($contactId))
        {
            if (isset($contactId[$table]) && is_array($contactId[$table]))
            {
                foreach ($contactId[$table] as $data)
                {
                    $condition = ((
                        empty($contactFieldId) ||
                        !isset($contactId['hContactFieldId']) ||
                        isset($contactId['hContactFieldId']) && $contactFieldId == $contactId['hContactFieldId']
                    ) && (
                        empty($contactAddressId) ||
                        !isset($contactId['hContactAddressId']) ||
                        isset($contactId['hContactAddressId']) && $contactAddressId == $contactId['hContactAddressId']
                    ));

                    if ($condition)
                    {
                        return $data[$field];
                    }
                }
            }

            return nil;
        }
        else
        {
            $where = array(
                'hContactId' => $contactId,
                'hContactFieldId' => $contactFieldId
            );

            if (!empty($contactAddressId))
            {
                $where['hContactAddressId'] = $contactAddressId;
            }

            return $this->$table->selectColumn($field, $where);
        }
    }

    public function getFieldName($contactFieldId)
    {
        # @return array

        # @description
        # <h2>Getting a Field Name</h2>
        # <p>
        # Returns the field name for the speficied <var>$contactFieldId</var>.
        # </p>
        # @end

        return $this->hContactFields->selectColumn(
            'hContactField',
            (int) $contactFieldId
        );
    }

    public function &setId($contactId)
    {
        # @return hContactLibrary

        # @description
        # <h2>Setting the contactId</h2>
        # <p>
        # Sets the internal <var>contactId</var> to the value specified in
        # <var>$contactId</var>.
        # </p>
        # @end

        $this->hContactId = $contactId;

        return $this;
    }

    public function getVariable($contactVariable, $default = nil, $contactId = 0)
    {
        # @return mixed

        # @description
        # <h2>Contact Variables</h2>
        # <p>
        # Returns the variable specified in <var>$contactVariable</var>. If the variable
        # does not exist, the value specified in <var>$default</var> is returned instead.
        # </p>
        # <p>
        # The variable is returned for the specified <var>$contactId</var>.
        # If no <var>$contactId</var> is specified, the <var>contactId</var> set by
        # <a href='#setContactId' class='code'>setContactId()</a> is assumed.
        # </p>
        # @end

        $this->whichContactId($contactId);
        $this->hDatabase->setDefaultResult($default);

        return $this->hContactVariables->selectColumn(
            'hContactValue',
            array(
                'hContactId' => (int) $contactId,
                'hContactVariable' => $contactVariable
            )
        );
    }

    public function getVariables($contactId = 0)
    {
        # @return array

        # @description
        # <h2>Getting All Contact Variables</h2>
        # <p>
        #    Returns all contact variables associated with a user rolodex.
        # </p>
        # @end

        $this->whichContactId($contactId);

        return $this->hContactVariables->select(
            array(
                'hContactVariable',
                'hContactValue'
            ),
            array(
                'hContactId' => (int) $contactId
            )
        );
    }

    public function &whichContactId(&$contactId)
    {
        # @return hContactLibrary

        # @description
        # <h2>Setting the contactId Argument</h2>
        # <p>
        # Determines if the <var>$contactId</var> parameter passed to a function contains
        # a value, if it does not, the value of the internal <var>contactId</var> is
        # used instead.
        # </p>
        # @end

        $contactId = empty($contactId) ? $this->hContactId : (int) $contactId;
        return $this;
    }
}

?>