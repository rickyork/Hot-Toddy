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

interface hContactApplication {
    public function getSearchColumns();
}

class hContactAddressBook extends hPlugin implements hContactApplication {

    private $hContactAddressBookName;
    private $hSpotlightSearch;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Contact Address Book Constructor</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');
        $this->getSearchColumns();

        $this->hSpotlightSearch->setColumnSelected(
            'hContacts',
            'hContactId'
        );
    }

    public function &getAddressBookPlugin($plugin)
    {
        # @return object

        # @description
        # <h2>Getting the Address Book Plugin</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hContactAll = false;

        if (!empty($plugin))
        {
            return $this->plugin($plugin);
        }
        else if ($this->inGroup('Contact Administrators'))
        {
            $this->hContactAll = true;

            // hContactAll is designed to search all address books in the system,
            // not just the specified address book.
            return $this->plugin('hContact/hContactAll');
        }
        else
        {
            // hContactMine is designed to search only the user's address book.
            return $this->plugin('hContact/hContactMine');
        }
    }

    public function &setAddressBookName($addressBookName)
    {
        # @return hContactAddressBook

        # @description
        # <h2>Setting the Address Book Name</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hContactAddressBookName = $addressBookName;

        return $this;
    }

    public function getMenus()
    {
        # @return array

        # @description
        # <h2>Getting the Menu</h2>
        # <p>
        #
        # </p>
        # @end

        return array(
            $this->hContactAddressBookName => array(
                'Preferences',
                '-',
                'Close'
            ),
            'File' => array(
                'New Contact',
                'Open Contact',
                '-',
                'Save',
                'Save As...',
                '-',
                'Import',
                'Export',
                '-',
                'Print'
            ),
            'Edit' => array(
                'Selected Contact',
                'Variables',
                '-',
                'Delete'
            ),
            'View' => array(
                'Contact Information',
                '-',
                'Notes',
                'Variables'
            )
        );
    }

    public function &getSearchColumns()
    {
        # @return hContactAddressBook

        # @description
        # <h2>Getting Search Columns</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hSpotlightSearch
            ->addTable(
                'hContacts',
                'Contact Information'
            )
            ->defineJoinColumns('hContactId')
            ->addAdvancedColumns(
                array(
                    'hContactId' => 'Contact Id',
                    'hUserId' => 'User Id'
                )
            )
            ->addDefaultColumns(
                array(
                    'hContactFirstName' => 'First Name',
                    'hContactLastName' => 'Last Name'
                )
            )
            ->addAdvancedColumns(
                array(
                    'hContactDisplayName' => 'Display Name',
                    'hContactNickName' => 'Nick Name',
                    'hContactWebsite' => 'Website'
                )
            )
            ->addDefaultColumns(
                array(
                    'hContactCompany' => 'Company',
                    'hContactTitle' => 'Title',
                    'hContactDepartment' => 'Department'
                )
            )
            ->addTable(
                'hContactInternetAccounts',
                'Internet Accounts'
            )
            ->addAdvancedColumns(
                array(
                    'hContactId' => 'Contact Id',
                    'hContactInternetAccountId' => 'Internet Account Id',
                    'hContactFieldId' => 'Field Id',
                    'hContactInternetAccount' => 'Online Account (AIM, Yahoo!, Windows Messenger, etc)'
                )
            )
            ->addTable(
                'hContactPhoneNumbers',
                'Phone Numbers'
            )
            ->addAdvancedColumns(
                array(
                    'hContactId' => 'Contact Id',
                    'hContactPhoneNumberId' => 'Phone Number Id',
                    'hContactFieldId' => 'Field Id',
                    'hContactPhoneNumber' => 'Phone Number'
                )
            )
            ->addTable(
                'hContactEmailAddresses',
                'Email Addresses'
            )
            ->addAdvancedColumns(
                array(
                    'hContactId' => 'Contact Id',
                    'hContactEmailAddressId' => 'Email Address Id (Address Book)',
                    'hContactFieldId' => 'Field Id',
                    'hContactEmailAddress' => 'Email Address (Address Book)'
                )
            )
            ->addTable(
                'hContactAddresses',
                'Addresses'
            )
            ->addAdvancedColumns(
                array(
                    'hContactId' => 'Contact Id',
                    'hContactAddressId' => 'Address Id',
                    'hContactFieldId' => 'Field Id',
                    'hContactAddressStreet' => 'Street',
                    'hContactAddressCity' => 'City',
                    'hLocationStateId' => 'State',
                    'hContactAddressPostalCode' => 'Postal Code',
                    'hLocationCountryId' => 'Country',
                    'hContactAddressLatitude' => 'Latitude',
                    'hContactAddressLongitude' => 'Longitude',
                    'hContactAddressIsDefault' => 'Default Address'
                )
            )
            ->addTable(
                'hContactVariables',
                'Contact Variables'
            )
            ->addAdvancedColumns(
                array(
                    'hContactId' => 'Contact Id',
                    'hContactVariable' => 'Variable',
                    'hContactValue' => 'Value'
                )
            );

        return $this;
    }

    public function &query($search, &$where, $time, &$results)
    {
        $this->hSpotlightSearch->query(
            'hContacts',
            $search,
            $where,
            $time,
            '`hContacts`.`hUserId`',
            'hContactId',
            $results
        );

        return $this;
    }

    public function getResultsHTML($results)
    {
        // Take the results and format them like this:
        //
        // <userId>/<contactId>
        // FirstName LastName
        // Title
        // Company
        // email@address.com
        // UserName (if different than email address)
        $html = '';

        if (isset($results['key']))
        {
            unset($results['key']);
        }

        return $this->getTemplate(
            'Results',
            array(
                'results' => $this->hDatabase->getResultsForTemplate($results)
            )
        );
    }
}

?>