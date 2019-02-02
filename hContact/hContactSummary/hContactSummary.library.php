<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Summary Library
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

class hContactSummaryLibrary extends hPlugin {

    private $hContactDatabase;
    private $hContactTitle;
    private $hLocation;

    public function hConstructor()
    {
        $this->getPluginFiles();
        $this->hContactDatabase = $this->database('hContact');
        $this->hLocation = $this->library('hLocation');
    }

    public function get($contactId)
    {
        $contact = array();

        if (!empty($contactId))
        {
            $contact = $this->contact->getRecord($contactId);
        }

        if (!isset($contact['hContactAddressBookId']))
        {
            $contact['hContactAddressBookId'] = (int) $_GET['hContactAddressBookId'];
        }

        $userId = 0;

        if (empty($contact['hUserId']))
        {
            $userId = $contact['hContactAddressBookId'] > 1? (int) $_SESSION['hUserId'] : 0;
        }
        else
        {
            $userId = $contact['hUserId'];
        }

        $contact['hContactId'] = $contactId;
        $contact['hUserId'] = $userId;

        $contact['hContactEmailAddresses'] = $this->getEmailAddresses(
            isset($contact['hContactEmailAddresses']) ? $contact['hContactEmailAddresses'] : array()
        );

        $contact['hContactPhoneNumbers'] = $this->getPhoneNumbers(
            isset($contact['hContactPhoneNumbers']) ? $contact['hContactPhoneNumbers'] : array()
        );

        $contact['hContactAddresses'] = $this->getAddresses(
            isset($contact['hContactAddresses']) ? $contact['hContactAddresses'] : array()
        );

        $contact['hContactTitleSelect'] = '';

        if ($this->hContactTitlePlugin)
        {
            $this->hContactTitle = $this->plugin($this->hContactTitlePlugin);

            $contact['hContactTitleSelect'] = $this->hContactTitle->get(
                isset($contact['hContactTitle']) ? $contact['hContactTitle'] : ''
            );
        }

        $fields = array(
            'hContactFirstName',
            'hContactLastName',
            'hContactTitle',
            'hContactDepartment',
            'hContactCompany',
            'hContactWebsite'
        );

        foreach ($fields as $field)
        {
            if (!isset($contact[$field]))
            {
                $contact[$field] = '';
            }
        }

        $contact['hUserName'] = nil;

        if ($contact['hContactAddressBookId'] == 1)
        {
            $contact['hUserName'] = urlencode(
                $this->user->getUserName($contact['hUserId'])
            );
        }

        $template = $this->getTemplate('Summary', $contact);

        return $template;
    }

    private function getPhoneNumbers($phoneNumbers)
    {
        return $this->getData(
            $phoneNumbers,
            'PhoneNumber',
            's',
            $this->hContactSummaryDefaultPhoneNumberField(6),
            11,
            'Phone Numbers',
            'No phone numbers entered.'
        );
    }

    private function getEmailAddresses($emailAddresses)
    {
        return $this->getData(
            $emailAddresses,
            'EmailAddress',
            'es',
            $this->hContactSummaryDefaultEmailAddressField(20),
            9,
            'Email Addresses',
            'No email addresses entered.'
        );
    }

    private function getData($results, $contactDataTable, $contactDataPlural, $contactFieldId, $frameworkResourceId, $contactModuleHeading, $contactNoData)
    {
        $data = array();

        if (count($results))
        {
            foreach ($results as $result)
            {
                $data['hContactData'][] = $result['hContact'.$contactDataTable];
                $data['hContactDataId'][] = $result['hContact'.$contactDataTable.'Id'];
                $data['hContactSummaryEdit'][] = $this->hContactSummaryEdit(true);
                $data['hContactFieldLabel'][] = $this->hContactDatabase->getFieldLabel($result['hContactFieldId']);
                $data['hContactFieldId'][]  = $result['hContactFieldId'];

                $data['hContactFieldSelect'][] = $this->getSelectField(
                    $frameworkResourceId,
                    $result['hContactFieldId'],
                    'hContactField'
                );
            }
        }

        return $this->getTemplate(
            'Data',
            array(
                'hContactModuleHeading' => $contactModuleHeading,
                'hContactDataTable' => $contactDataTable,
                'hContactDataPlural' => $contactDataPlural,
                'hContactData' => $data,
                'hContactNoData' => $contactNoData,
                'hContactSummaryEdit' => $this->hContactSummaryEdit(true),
                'hContactFieldLabel' => $this->hContactDatabase->getFieldLabel($contactFieldId),
                'hContactFieldId' => $contactFieldId,
                'hContactFieldSelect' => $this->getSelectField(
                    $frameworkResourceId,
                    $contactFieldId,
                    'hContactField'
                )
            )
        );
    }

    private function getAddresses($addresses)
    {
        $contactAddresses = '';

        if (is_array($addresses))
        {
            foreach ($addresses as $address)
            {
                $contactAddresses .= $this->getAddressTemplate($address);
            }
        }

        return $this->getTemplate(
            'Addresses',
            array(
                'hContactAddresses' => $contactAddresses,
                'hContactAddressTemplate' => $this->getAddressTemplate(
                    array(
                        'hContactAddressId' => 0,
                        'hLocationCountryISO2' => 'US',
                        'hLocationCountryISO3' => 'USA',
                        'hContactFieldId' => $this->hContactSummaryDefaultAddressField(2),
                        'hLocationCountryId' => 223,
                        'hLocationCountryName' => 'United States',
                        'hLocationStateId' => 0,
                        'hContactAddressTemplate' => $this->hLocation->getAddressTemplateByCountry(223),
                        'hLocationStateLabel' => 'State'
                    )
                ),
                'hContactSummaryEdit' => $this->hContactSummaryEdit(true)
            )
        );
    }

    private function getAddressTemplate($address)
    {
        return $this->getTemplate(
            'Address',
            array(
                'hContactAddressId' => $address['hContactAddressId'],
                'hContactSummaryEdit' => $this->hContactSummaryEdit(true),
                'hLocationCountryISO2' => strtolower($address['hLocationCountryISO2']),
                'hContactFieldId' => $address['hContactFieldId'],
                'hContactFieldLabel' => $this->hContactDatabase->getFieldLabel($address['hContactFieldId']),
                'hContactFieldSelect' => $this->hContactSummaryEdit(true) ? $this->getSelectField(8, $address['hContactFieldId'], 'hContactField') : '',
                'hContactAddress' => str_replace(
                    array(
                        '|',
                        '{$street}',
                        '{$city}',
                        '{$state}',
                        '{$postalCode}',
                        '{$country}'
                    ),
                    array(
                        $this->getTemplate('Address Separator'),
                        $this->getTemplate(
                            'Street',
                            array(
                                'hContactAddressStreet' => !empty($address['hContactAddressStreet']) ? trim($address['hContactAddressStreet']) : ''
                            )
                        ),
                        $this->getTemplate(
                            'City',
                            array(
                                'hContactAddressCity' => !empty($address['hContactAddressCity']) ? trim($address['hContactAddressCity']) : ''
                            )
                        ),
                        $this->getTemplate(
                            'State',
                            array(
                                'hLocationStateId' => $address['hLocationStateId'],
                                'hLocationStateLabel' => empty($address['hLocationStateId'])? $address['hLocationStateLabel'] : trim($address['hLocationState'.(!empty($address['hLocationUseStateCode'])? 'Code' : 'Name')]),
                                'hContactSelectState' => $this->hContactSummaryEdit(true)? $this->getSelectState($address['hLocationCountryId'], $address['hLocationStateId']) : ''
                            )
                        ),
                        $this->getTemplate(
                            'Postal Code',
                            array(
                                'hContactAddressPostalCode' => !empty($address['hContactAddressPostalCode'])? trim($address['hContactAddressPostalCode']) : ''
                            )
                        ),
                        $this->getTemplate(
                            'Country',
                            array(
                                'hLocationCountryName' => !empty($address['hLocationCountryName'])? $address['hLocationCountryName'] : '',
                                'hContactSelectCountry' => $this->getSelectCountry($address['hLocationCountryId'])
                            )
                        )
                    ),
                    $address['hContactAddressTemplate']
                )
            )
        );
    }

    private function getSelectState($locationCountryId, $locationStateId)
    {
        return $this->getTemplate(
            'Select State',
            array(
                'hLocationStates' => $this->hLocation->getStatesForTemplate(
                    $locationCountryId,
                    $locationStateId
                )
            )
        );
    }

    private function getSelectCountry($locationCountryId)
    {
        return $this->getTemplate(
            'Select Country',
            array(
                'hLocationCountries' => $this->hLocation->getCountriesForTemplate($locationCountryId)
            )
        );
    }

    private function getSelectField($frameworkResourceId, $contactFieldId, $class)
    {
        return $this->getTemplate(
            'Select Field',
            array(
                'hContactFieldSelectClass' => $class,
                'hContactFields' => $this->hContactDatabase->getFieldsForTemplate(
                    $frameworkResourceId,
                    $contactFieldId
                )
            )
        );
    }
}

?>