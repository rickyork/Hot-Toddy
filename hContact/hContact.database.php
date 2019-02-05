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
# <h1>Contact Database API</h1>
# <p>
#   <var>hContactDatabase</var> provides methods for creating or updating
#   data in the following database tables:
# </p>
# <table>
#   <tbody>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactAddressBooks/hContactAddressBooks.sql' class='code'>hContactAddressBooks</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactAddresses/hContactAddresses.sql' class='code'>hContactAddresses</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactAddressTemplates/hContactAddressTemplates.sql' class='code'>hContactAddressTemplates</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactEmailAddresses/hContactEmailAddresses.sql' class='code'>hContactEmailAddresses</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactFields/hContactFields.sql' class='code'>hContactFields</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactInternetAccounts/hContactInternetAccounts.sql' class='code'>hContactInternetAccounts</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactPhoneNumbers/hContactPhoneNumbers.sql' class='code'>hContactPhoneNumbers</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContacts/hContacts.sql' class='code'>hContacts</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactUsers/hContactUsers.sql' class='code'>hContactUsers</a></td>
#       </tr>
#       <tr>
#           <td><a href='/System/Framework/Hot Toddy/hContact/Database/hContactVariables/hContactVariables.sql' class='code'>hContactVariables</a></td>
#       </tr>
#   </tbody>
# </table>
# @end

class hContactDatabase extends hPlugin {

    private $hContactValidation;
    private $hForm;
    private $hContactEmailAddress;
    private $hContactDirectory;
    private $hFrameworkResource;

    private $hMap;

    private $contactId = 0;
    private $contactAddressId = 0;
    private $duplicateFields = true;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Constructor</h2>
        # <p>
        #   Loads:
        # </p>
        # <ul>
        #   <li><a href='/Hot Toddy/Documentation?hContact/hContactValidation/hContactValidation.library.php' class='code'>hContactValidation</a></li>
        #   <li><a href='/Hot Toddy/Documentation?hMap/hMap.library.php' class='code'>hMap</a>.</li>
        #   <li>
        #       <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #       if <var>hContactDirectoryEnabled</var> is <var>true</var> (it's <var>false</var>, by
        #       default), and <var>hContactDatabaseSyncDirectory</var> is <var>true</var> (it's <var>true</var>, by
        #       default)
        #   </li>
        # </ul>
        # @end

        $this->hContactValidation = $this->library('hContact/hContactValidation');
        $this->hMap = $this->library('hMap');

        if ($this->hContactDirectoryEnabled(false) && $this->hContactDatabaseSyncDirectory(true))
        {
            $this->hContactDirectory = $this->library('hContact/hContactDirectory');
        }
    }
    
    public function &frameworkResource()
    {
        # @return hFrameworkResourceLibrary
        
        # @description
        # <h2>Using the Framework Resource API</h2>
        # <p>
        #   Intializes the <a href='/Hot Toddy/Documentation?hFrameworkResource.library.php' class='code'>hFrameworkResourceLibrary</a> 
        #   object the first time it's used, and then it returns the 
        #   <a href='/Hot Toddy/Documentation?hFrameworkResource.library.php' class='code'>hFrameworkResourceLibrary</a> 
        #   object.
        # </p>
        # @end

        if (!is_object($this->hFrameworkResource))
        {
            $this->hFrameworkResource = $this->library('hFramework/hFrameworkResource');
        }

        return $this->hFrameworkResource;
    }

    public function getAddressBookId($addressBook)
    {
        # @return integer

        # @description
        # <p>
        #    Returns an <var>hContactAddressBookId</var> for the provided <var>$addressBook</var>
        # </p>
        # @end

        return $this->hContactAddressBooks->selectColumn(
            'hContactAddressBookId',
            array(
                'hContactAddressBookName' => $addressBook
            )
        );
    }

    public function addressBookExists($addressBook)
    {
        # @return boolean

        # @description
        # <p>
        #    Returns whether or not an address books with the provided <var>$addressBook</var> name.
        # </p>
        # @end

        return $this->hContactAddressBooks->selectExists(
            'hContactAddressBookId',
            array(
                'hContactAddressBookName' => $addressBook
            )
        );
    }

    public function &setForm(hFormLibrary &$form)
    {
        # @return hContactDatabase

        # @description
        # <p>
        #   This method is not presently used.
        # </p>
        # @end

        $this->hForm = &$form;

        return $this;
    }

    public function &setContactId($contactId)
    {
        # @return hContactDatabase

        # @description
        # <h2>Setting the Contact Id</h2>
        # <p>
        #   Sets the <var>$contactId</var> used for database interaction with
        #   contact data.
        # </p>
        # @end

        $this->contactId = (int) $contactId;
        return $this;
    }

    public function &setContactAddressId($contactAddressId)
    {
        # @return hContactDatabase

        # @description
        # <h2>Setting the Contact Address Id</h2>
        # <p>
        #   Sets the <var>$contactAddressId</var> used for database interaction with
        #   contact data.
        # </p>
        # @end

        $this->contactAddressId = (int) $contactAddressId;
        return $this;
    }

    public function getContactId()
    {
        # @return integer

        # @description
        # <h2>Retrieving the Current Contact Id</h2>
        # <p>
        #   Returns the <var>contactId</var> presently used for the internal
        #   <var>$this-&gt;contactId</var> property.
        # </p>
        # @end

        return $this->contactId;
    }

    public function getContactAddressId()
    {
        # @return integer

        # @description
        # <h2>Retrieving the Current Contact Address Id</h2>
        # <p>
        #   Returns the <var>contactAddressId</var> presently used for the internal
        #   <var>$this-&gt;contactAddressId</var> property.
        # </p>
        # @end

        return $this->contactAddressId;
    }

    public function saveContact($columns, $contactAddressBookId, $userId, $contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Contact</h2>
        # <p>
        #   This method is an alias of <a href='#save' class='code'>save()</a>.
        #   See <a href='#save' class='code'>save()</a> for more information.
        # </p>
        # @end

        return $this->save(
            $columns,
            $contactAddressBookId,
            $userId,
            $contactId
        );
    }

    public function save($columns, $contactAddressBookId, $userId, $contactId = 0)
    {
        # @return integer
        # <p>
        #   The <var>contactId</var> updated or inserted.
        # </p>
        # @end

        # @description
        # <h2>Saving a Contact</h2>
        # <p>
        #   Saves a contact in the
        #   <a href='/System/Framework/Hot Toddy/hContact/Database/hContacts/hContacts.sql' class='code'>hContacts</a>
        #   table.
        # </p>
        # <p>
        #   The following fields are provided in the <var>$columns</var> array:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hContactFirstName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactMiddleName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactLastName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactDisplayName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactNickName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactWebsite</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactCompany</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactTitle</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactDepartment</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactGender</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactDateOfBirth</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p>
        #   The address book the contact information is saved to is provided in the
        #   <var>$contactAddressBookId</var>.
        # </p>
        # <p>
        #   The <var>$userId</var> of the owner of the record is provided in
        #   the <var>$userId</var> argument.  In the case of contactAddressBookId
        #   #1, the <var>$userId</var> will be the <var>userId</var> for the user
        #   account.
        # </p>
        # <p>
        #   Finally, if modifying an existing contact, the <var>$contactId</var> is
        #   provided in the <var>$contactId</var> argument.
        # </p>
        # <h3>Directory Syncing</h3>
        # <p>
        #   If a directory (Open Directory, Active Directory, or local Mac OS X user syncing)
        #   is enabled and configured, address data is also synced with the network or local server using the
        #   <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   library plugin.  Data synced to the network or local server is determined by
        #   how <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   is configured, as well as what fields the provided network user is allowed to modify.
        # </p>
        # @end

        $this->checkArgument($columns, 'is_array')
             ->checkArgument($contactAddressBookId, '!empty')
             ->checkArgument($userId, '!empty');

        $this->hContactAddressBooks->modifyResource($contactAddressBookId);

        if (!isset($columns['hContactDisplayName']) && !empty($columns['hContactFirstName']) && !empty($columns['hContactLastName']))
        {
            $columns['hContactDisplayName'] =
                $columns['hContactFirstName'].' '.$columns['hContactLastName'];
        }

        if (empty($contactId))
        {
            $columns['hContactCreated'] = time();
        }

        $this->contactId = $this->hContacts->save(
            array_merge(
                $columns,
                array(
                    'hContactAddressBookId' => (int) $contactAddressBookId,
                    'hContactId' => $contactId,
                    'hUserId' => (int) $userId,
                    'hContactLastModified'  => time()
                )
            )
        );

        if (is_object($this->hContactDirectory) && $contactAddressBookId == 1)
        {
            $this->hContactDirectory->setUser(
                $this->user->getUserName($userId)
            );

            $this->hContactDirectory->save($columns);
        }

        if (!empty($this->contactId))
        {
            $this->modifyAddressBookByContactId($this->contactId);
        }

        $this->contact->setId($this->contactId);

        $this->hContacts->modifyResource($this->contactId);

        return $this->contactId;
    }

    public function &setDuplicateFields($duplicateFields)
    {
        # @return void

        # @description
        # <h2>Preventing Duplicate Data</h2>
        # <p>
        #   This method accepts a booean argument in <var>$duplicateFields</var>.
        #   If <var>$duplicateFields</var> is <var>true</var>, duplicate records
        #   will be allowed in the same contact's record.  For example, the same
        #   email address can be saved multiple times in different email address
        #   records.  The same phone number can be saved multiple times in different
        #   phone number records.  The same adderss can be saved multiple times
        #   in different records.  Setting <var>$duplicateFields</var> to false
        #   will cause attempts to save duplicate information to be ignored.
        # </p>
        # @end

        $this->duplicateFields = $duplicateFields;

        return $this;
    }

    public function saveAddress($columns, $contactFieldId = 2, $contactAddressId = 0, $contactId = 0)
    {
        # @return integer
        # <p>
        #   The <var>hContactAddressId</var> created or updated.
        # </p>
        # @end

        # @description
        # <h2>Saving an Address</h2>
        # <p>
        #   Saves an address to the <var>contactId</var> specified by either
        #   providing a <var>contactId</var> to the <var>$contactId</var>
        #   argument or by using the
        #   <a href='#setContactId' class='code'>setContactId()</a> method,
        #   or by saving a contact using either the
        #   <a href='#save' class='code'>save()</a> or
        #   <a href='#saveContact' class='code'>saveContact()</a> methods,
        #   which automatically set a <var>contactId</var> when they are
        #   used.
        # </p>
        # <p>
        #   See also the
        #   <a href='/System/Framework/Hot Toddy/hContact/Database/hContactAddresses/hContactAddresses.sql' class='code'>hContactAddresses</a>
        #   table.
        # </p>
        # <p>
        #   If the <var>$contactId</var> argument is provided, it sets the value
        #   of the internal <var>$this-&gt;contactId</var> property, making it
        #   uneccesary to provide on subsequent method calls that affect the same
        #   contact record.
        # <p>
        #   The <var>$columns</var> argument provides an array of fields containing
        #   address information.
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hContactAddressStreet</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressCity</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hLocationStateId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressPostalCode</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hLocationCountyId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hLocationCountryId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressLatitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressLongitude</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressOperatingHours</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressIsDefault</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h3>Directory Syncing</h3>
        # <p>
        #   If a directory (Open Directory, Active Directory, or local Mac OS X user syncing)
        #   is enabled and configured, address data is also synced with the network or local server using the
        #   <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   library plugin.  Data synced to the network or local server is determined by
        #   how <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   is configured, as well as what fields the provided network user is allowed to modify.
        # </p>
        # <h3>Geocoding</h3>
        # <p>
        #   If the framework variable <var>hMapEnableGeocode</var>
        #   is <var>true</var> (it is <var>true</var>, by default), Hot Toddy
        #   will attempt to automatically query Google (or the default map geocoding service) for the
        #   <var>hContactAddressLatitude</var> and <var>hContactAddressLongitude</var>
        #   information for an address.
        # </p>
        # <h3>Fields</h3>
        # <p>
        #   Address fields:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</th>
        #           <th>hContactField</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>1</td>
        #           <td>Home</td>
        #       </tr>
        #       <tr>
        #           <td>2</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>3</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($contactId))
        {
            $this->setContactId($contactId);
        }

        if (!empty($this->contactId))
        {
            $this->modifyAddressBookByContactId($this->contactId);
        }

        $query = $this->hContactAddresses->selectQuery(
            'hContactAddressId',
            array_merge(
                array(
                    'hContactId' => (int) $this->contactId,
                    'hContactFieldId' => (int) $contactFieldId
                ),
                $this->duplicateFields?
                    array(
                        'hContactAddressStreet' => trim($columns['hContactAddressStreet']),
                        'hContactAddressCity' => trim($columns['hContactAddressCity']),
                        'hLocationStateId' => (int) $columns['hLocationStateId'],
                        'hContactAddressPostalCode' => trim($columns['hContactAddressPostalCode']),
                        'hLocationCountryId' => (int) $columns['hLocationCountryId']
                    ) :
                    array()
            )
        );

        $this->getMergedColumns(
            $columns,
            'hContactAddresses',
            $query,
            $contactAddressId,
            $contactFieldId
        );

        if (is_object($this->hContactDirectory) && $this->hContactDirectory->userSet())
        {
            $this->hContactDirectory->saveAddress(
                $columns,
                $contactFieldId
            );
        }

        $contactAddressId = $this->hContactAddresses->save($columns);

        if ($this->hMapEnableGeocode(true))
        {
            $this->hMap->getAddressCoordinates($contactAddressId);
        }

        $this->hContactAddresses->modifyResource($contactAddressId);
        $this->hContacts->modifyResource($this->contactId);

        $this->setContactAddressId($contactAddressId);

        return $contactAddressId;
    }

    public function saveHomeAddress($columns, $contactAddressId = 0, $contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Home Address</h2>
        # <p>
        #   Saves an address as a home address, <var>hContactFieldId = 1</var>.
        # </p>
        # @end

        return $this->saveAddress($columns, 1, $contactAddressId, $contactId);
    }

    public function saveWorkAddress($columns, $contactAddressId = 0, $contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Work Address</h2>
        # <p>
        #   Saves an address as a work address, <var>hContactFieldId = 2</var>.
        # </p>
        # @end

        return $this->saveAddress($columns, 2, $contactAddressId, $contactId);
    }

    public function saveOtherAddress($columns, $contactAddressId = 0, $contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Other Address</h2>
        # <p>
        #   Saves an address as an other address, <var>hContactFieldId = 3</var>.
        # </p>
        # @end

        return $this->saveAddress($columns, 3, $contactAddressId, $contactId);
    }

    public function deleteAddress($contactAddressId)
    {
        # @return integer
        # <p>
        #   If deletion was successful, the number of addresses deleted is returned.
        # </p>
        # @end

        # @description
        # <h2>Deleting an Address</h2>
        # <p>
        #   Deletes the specified address using the provided <var>contactAddressId</var>,
        #   which is the unique <var>hContactAddressId</var> of the record to be
        #   deleted.
        # </p>
        # @end
        $contactId = $this->hContactAddresses->selectColumn(
            'hContactId',
            $contactAddressId
        );

        $contactAddressBookId = $this->hContacts->selectColumn(
            'hContactAddressBookId',
            $contactId
        );

        $this->hContactAddressBooks->modifyResource($contactAddressBookId);
        $this->hContacts->modifyResource($contactId);
        $this->hContactAddresses->modifyResource();

        return $this->hContactAddresses->delete('hContactAddressId', $contactAddressId);
    }

    public function &deleteAddresses($contactId)
    {
        # @return hContactDatabase

        # @description
        # <h2>Deleting All Addresses Associated With a Contact</h2>
        # <p>
        #   Deletes all addresses associated with a <var>$contactId</var>.
        # </p>
        # @end

        $this->hContactAddresses->delete('hContactId', $contactId);
        return $this;
    }

    public function savePhoneNumber($columns, $contactFieldId = 6, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer
        # <p>
        #   The <var>hContactPhoneNumberId</var> created or updated.
        # </p>
        # @end

        # @description
        # <h2>Saving a Phone Number</h2>
        # <p>
        #   Saves a phone number to the <var>contactId</var> specified by either
        #   providing a <var>contactId</var> to the <var>$contactId</var>
        #   argument or by using the
        #   <a href='#setContactId' class='code'>setContactId()</a> method,
        #   or by saving a contact using either the
        #   <a href='#save' class='code'>save()</a> or
        #   <a href='#saveContact' class='code'>saveContact()</a> methods,
        #   which automatically set a <var>contactId</var> when they are
        #   used.
        # </p>
        # <p>
        #   See also the
        #   <a href='/System/Framework/Hot Toddy/hContact/Database/hContactPhoneNumbers/hContactPhoneNumbers.sql' class='code'>hContactPhoneNumbers</a>
        #   table.
        # </p>
        # <p>
        #   If the <var>$contactId</var> argument is provided, it sets the value
        #   of the internal <var>$this-&gt;contactId</var> property, making it
        #   uneccesary to provide on subsequent method calls that affect the same
        #   contact record.
        # <p>
        #   <var>$columns</var> can be provided either as an array, or
        #   a string directly containing the value of a phone number.
        #   If an array is provided it should look like this:
        # </p>
        # <code>
        #   array(
        #       'hContactPhoneNumber' => '(317) 555-1212'
        #   )
        # </code>
        # <h3>Directory Syncing</h3>
        # <p>
        #   If a directory (Open Directory, Active Directory, or local Mac OS X user syncing)
        #   is enabled and configured, address data is also synced with the network or local server using the
        #   <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   library plugin.  Data synced to the network or local server is determined by
        #   how <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   is configured, as well as what fields the provided network user is allowed to modify.
        # </p>
        # <h3>Fields</h3>
        # <p>
        #   Phone number fields:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</th>
        #           <th>hContactField</th>
        #           <th>Flat Record Index</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>4</td>
        #           <td>Home</td>
        #           <td>hContactPhoneNumberHome</td>
        #       </tr>
        #       <tr>
        #           <td>5</td>
        #           <td>Mobile</td>
        #           <td>hContactPhoneNumberMobile</td>
        #       </tr>
        #       <tr>
        #           <td>6</td>
        #           <td>Work</td>
        #           <td>hContactPhoneNumberWork</td>
        #       </tr>
        #       <tr>
        #           <td>7</td>
        #           <td>Extension</td>
        #           <td>hContactPhoneNumberExtension</td>
        #       </tr>
        #       <tr>
        #           <td>8</td>
        #           <td>Company</td>
        #           <td>hContactPhoneNumberCompany</td>
        #       </tr>
        #       <tr>
        #           <td>9</td>
        #           <td>Fax</td>
        #           <td>hContactPhoneNumberFax</td>
        #       </tr>
        #       <tr>
        #           <td>10</td>
        #           <td>Pager</td>
        #           <td>hContactPhoneNumberPager</td>
        #       </tr>
        #       <tr>
        #           <td>11</td>
        #           <td>Other</td>
        #           <td>hContactPhoneNumberOther</td>
        #       </tr>
        #       <tr>
        #           <td>22</td>
        #           <td>Main</td>
        #           <td>hContactPhoneNumberMain</td>
        #       </tr>
        #       <tr>
        #           <td>23</td>
        #           <td>Toll-Free</td>
        #           <td>hContactPhoneNumberTollFree</td>
        #       </tr>
        #       <tr>
        #           <td>24</td>
        #           <td>Appointments</td>
        #           <td>hContactPhoneNumberAppointment</td>
        #       </tr>
        #       <tr>
        #            <td>47</td>
        #            <td>Scheduling</td>
        #            <td>hContactPhoneNumberScheduling</td>
        #       </tr>
        #       <tr>
        #           <td>25</td>
        #           <td>iPhone</td>
        #           <td>hContactPhoneNumberiPhone</td>
        #       </tr>
        #       <tr>
        #           <td>26</td>
        #           <td>Home Fax</td>
        #           <td>hContactPhoneNumberHomeFax</td>
        #       </tr>
        #       <tr>
        #           <td>27</td>
        #           <td>Work Fax</td>
        #           <td>hContactPhoneNumberWorkFax</td>
        #       </tr>
        #       <tr>
        #           <td>28</td>
        #           <td>Other Fax</td>
        #           <td>hContactPhoneNumberOtherFax</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($contactId))
        {
            $this->setContactId($contactId);
        }

        if (!empty($contactAddressId))
        {
            $this->setContactAddressId($contactAddressId);
        }

        if (!empty($this->contactId))
        {
            $this->modifyAddressBookByContactId($this->contactId);
        }

        if (!is_array($columns))
        {
            $columns = array(
                'hContactPhoneNumber' => trim($columns)
            );
        }

        $query = $this->queryFieldId(
            'hContactPhoneNumbers',
            $columns['hContactPhoneNumber'],
            $contactFieldId
        );

        $this->getMergedColumns(
            $columns,
            'hContactPhoneNumbers',
            $query,
            $contactPhoneNumberId,
            $contactFieldId
        );

        if (is_object($this->hContactDirectory) && $this->hContactDirectory->userSet())
        {
            $this->hContactDirectory->savePhoneNumber(
                $columns['hContactPhoneNumber'],
                $contactFieldId
            );
        }

        $contactPhoneNumberId = $this->hContactPhoneNumbers->save($columns);

        $this->hContactPhoneNumbers->modifyResource($contactPhoneNumberId);
        $this->hContacts->modifyResource($this->contactId);

        return $contactPhoneNumberId;
    }

    public function saveHomeNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Home Phone Number</h2>
        # <p>
        #   Saves a phone number as a home phone number, <var>hContactFieldId = 4</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            4,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveMobileNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Mobile Phone Number</h2>
        # <p>
        #   Saves a phone number as a mobile phone number, <var>hContactFieldId = 5</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            5,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveWorkNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Work Phone Number</h2>
        # <p>
        #   Saves a phone number as a work phone number, <var>hContactFieldId = 6</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            6,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveExtensionNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Extension Phone Number</h2>
        # <p>
        #   Saves a phone number as an extension phone number, <var>hContactFieldId = 7</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            7,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveCompanyNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Company Phone Number</h2>
        # <p>
        #   Saves a phone number as a company phone number, <var>hContactFieldId = 8</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            8,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveFaxNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Fax Phone Number</h2>
        # <p>
        #   Saves a phone number as a fax phone number, <var>hContactFieldId = 9</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            9,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function savePagerNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Pager Phone Number</h2>
        # <p>
        #   Saves a phone number as a pager phone number, <var>hContactFieldId = 10</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            10,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveMainNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Main Phone Number</h2>
        # <p>
        #   Saves a phone number as a main phone number, <var>hContactFieldId = 22</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            22,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveTollFreeNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Toll-Free Phone Number</h2>
        # <p>
        #   Saves a phone number as a toll-free phone number, <var>hContactFieldId = 23</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            23,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveAppointmentsNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Appointment Phone Number</h2>
        # <p>
        #   Saves a phone number as a appointment phone number, <var>hContactFieldId = 24</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            24,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveSchedulingNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Scheduling Phone Number</h2>
        # <p>
        #   Saves a phone number as a scheduling phone number, <var>hContactFieldId = 47</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            47,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveiPhoneNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an iPhone Number</h2>
        # <p>
        #   Saves a phone number as an iPhone number, <var>hContactFieldId = 25</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            25,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveHomeFaxNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Home Fax Phone Number</h2>
        # <p>
        #   Saves a phone number as a home fax phone number, <var>hContactFieldId = 26</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            26,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveWorkFaxNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Work Fax Phone Number</h2>
        # <p>
        #   Saves a phone number as a work fax phone number, <var>hContactFieldId = 27</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            27,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveOtherFaxNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Other Fax Phone Number</h2>
        # <p>
        #   Saves a phone number as an other fax phone number, <var>hContactFieldId = 28</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            28,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function saveOtherNumber($contactPhoneNumber, $contactPhoneNumberId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Other Phone Number</h2>
        # <p>
        #   Saves a phone number as an other phone number, <var>hContactFieldId = 11</var>.
        # </p>
        # @end

        return $this->savePhoneNumber(
            $contactPhoneNumber,
            11,
            (int) $contactPhoneNumberId,
            (int) $contactId,
            (int) $contactAddressId
        );
    }

    public function deletePhoneNumber($contactPhoneNumberId)
    {
        # @return integer
        # <p>
        #   The number of phone numbers deleted.
        # </p>
        # @end

        # @description
        # <h2>Deleting a Phone Number</h2>
        # <p>
        #   Deletes the specified <var>$contactPhoneNumberId</var>, which is the
        #   unique <var>hContactPhoneNumberId</var> of the record to be deleted.
        # </p>
        # @end
        $contactId = $this->hContactPhoneNumbers->selectColumn(
            'hContactId',
            $contactPhoneNumberId
        );

        $contactAddressBookId = $this->hContacts->selectColumn(
            'hContactAddressBookId',
            $contactId
        );

        $this->hContactAddressBooks->modifyResource($contactAddressBookId);
        $this->hContacts->modifyResource($contactId);
        $this->hContactPhoneNumbers->modifyResource();

        return $this->hContactPhoneNumbers->delete(
            'hContactPhoneNumberId',
            $contactPhoneNumberId
        );
    }

    public function &deletePhoneNumbers($contactId)
    {
        # @return hContactDatabase

        # @description
        # <h2>Deleting All Phone Numbers Associated With a Contact</h2>
        # <p>
        #   Deletes all phone numbers associated with the specified
        #   <var>$contactId</var>.
        # </p>
        # @end

        $this->hContactPhoneNumbers->delete(
            'hContactId',
            $contactId
        );

        return $this;
    }

    public function saveInternetAccount($columns, $contactFieldId, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer
        # <p>
        #   The <var>hContactInternetAccountId</var> created or updated.
        # </p>
        # @end

        # @description
        # <h2>Saving an Internet Account</h2>
        # <p>
        #   Saves an internet account to the <var>contactId</var> specified by either
        #   providing a <var>contactId</var> to the <var>$contactId</var>
        #   argument or by using the
        #   <a href='#setContactId' class='code'>setContactId()</a> method,
        #   or by saving a contact using either the
        #   <a href='#save' class='code'>save()</a> or
        #   <a href='#saveContact' class='code'>saveContact()</a> methods,
        #   which automatically set a <var>contactId</var> when they are
        #   used.
        # </p>
        # <p>
        #   See also the
        #   <a href='/System/Framework/Hot Toddy/hContact/Database/hContactInternetAccounts/hContactInternetAccounts.sql' class='code'>hContactInternetAccounts</a>
        #   table.
        # </p>
        # <p>
        #   Internet account in this context refers to a user or screen name
        #   used for IM, chat, or whatever else could easily fit under the
        #   'Internet Account' paradigm.
        # </p>
        # <p>
        #   If the <var>$contactId</var> argument is provided, it sets the value
        #   of the internal <var>$this-&gt;contactId</var> property, making it
        #   uneccesary to provide on subsequent method calls that affect the same
        #   contact record.
        # <p>
        #   <var>$columns</var> can be provided either as an array, or
        #   a string directly containing the value of an internet account.
        #   If an array is provided it should look like this:
        # </p>
        # <code>
        #   array(
        #       'hContactInternetAccount' => 'johnny1975'
        #   )
        # </code>
        # <h3>Directory Syncing</h3>
        # <p>
        #   If a directory (Open Directory, Active Directory, or local Mac OS X user syncing)
        #   is enabled and configured, address data is also synced with the network or local server using the
        #   <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   library plugin.  Data synced to the network or local server is determined by
        #   how <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   is configured, as well as what fields the provided network user is allowed to modify.
        # </p>
        # <h3>Fields</h3>
        # <p>
        #   Internet account fields:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</th>
        #           <th>hContactField</th>
        #           <th>Flat Record Index</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>12</td>
        #           <td>Aol</td>
        #           <td>hContactInternetAccountAol</td>
        #       </tr>
        #       <tr>
        #           <td>13</td>
        #           <td>Yahoo!</td>
        #           <td>hContactInternetAccountYahoo</td>
        #       </tr>
        #       <tr>
        #           <td>15</td>
        #           <td>ICQ</td>
        #           <td>hContactInternetAccountICQ</td>
        #       </tr>
        #       <tr>
        #           <td>16</td>
        #           <td>iChat</td>
        #           <td>hContactInternetAccountiChat</td>
        #       </tr>
        #       <tr>
        #           <td>17</td>
        #           <td>Jabber</td>
        #           <td>hContactInternetAccountJabber</td>
        #       </tr>
        #       <tr>
        #           <td>18</td>
        #           <td>Other</td>
        #           <td>hContactInternetAccountOther</td>
        #       </tr>
        #       <tr>
        #           <td>29</td>
        #           <td>Facebook</td>
        #           <td>hContactInternetAccountFacebook</td>
        #       </tr>
        #       <tr>
        #           <td>30</td>
        #           <td>Apple Id</td>
        #           <td>hContactInternetAccountAppleId</td>
        #       </tr>
        #       <tr>
        #           <td>31</td>
        #           <td>iMessages</td>
        #           <td>hContactInternetAccountiMessages</td>
        #       </tr>
        #       <tr>
        #           <td>32</td>
        #           <td>iCloud</td>
        #           <td>hContactInternetAccountiCloud</td>
        #       </tr>
        #       <tr>
        #           <td>33</td>
        #           <td>Game Center</td>
        #           <td>hContactInternetAccountGameCenter</td>
        #       </tr>
        #       <tr>
        #           <td>34</td>
        #           <td>iTunes</td>
        #           <td>hContactInternetAccountiTunes</td>
        #       </tr>
        #       <tr>
        #           <td>35</td>
        #           <td>Mac App Store</td>
        #           <td>hContactInternetAccountMacAppStore</td>
        #       </tr>
        #       <tr>
        #           <td>36</td>
        #           <td>Windows Live</td>
        #           <td>hContactInternetAccountWindowsLive</td>
        #       </tr>
        #       <tr>
        #           <td>42</td>
        #           <td>Google</td>
        #           <td>hContactInternetAccountGoogle</td>
        #       </tr>
        #       <tr>
        #           <td>45</td>
        #           <td>Playstation Network</td>
        #           <td>hContactInternetAccountPSN</td>
        #       </tr>
        #       <tr>
        #           <td>46</td>
        #           <td>Xbox Live</td>
        #           <td>hContactInternetAccountXbox</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($contactId))
        {
            $this->setContactId($contactId);
        }

        if (!empty($contactAddressId))
        {
            $this->setContactAddressId($contactAddressId);
        }

        if (!empty($this->contactId))
        {
            $this->modifyAddressBookByContactId($this->contactId);
        }

        if (!is_array($columns))
        {
            $columns = array(
                'hContactInternetAccount' => trim($columns)
            );
        }

        $query = $this->queryFieldId(
            'hContactInternetAccounts',
            $columns['hContactInternetAccount'],
            $contactFieldId
        );

        $this->getMergedColumns(
            $columns,
            'hContactInternetAccounts',
            $query,
            $contactInternetAccountId,
            $contactFieldId
        );

        $contactInternetAccountId = $this->hContactInternetAccounts->save($columns);

        $this->hContactInternetAccounts->modifyResource($contactInternetAccountId);
        $this->hContacts->modifyResource($this->contactId);

        return $contactInternetAccountId;
    }

    public function saveAppleIdAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Apple Id Internet Account</h2>
        # <p>
        #   Saves an internet account as an Apple Id internet account, <var>hContactFieldId = 30</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            30,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveiMessagesAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an iMessages Internet Account</h2>
        # <p>
        #   Saves an internet account as an iMessages internet account, <var>hContactFieldId = 31</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            31,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveiCloudAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an iCloud Internet Account</h2>
        # <p>
        #   Saves an internet account as an iCloud internet account, <var>hContactFieldId = 32</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            32,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveGameCenterAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Game Center Internet Account</h2>
        # <p>
        #   Saves an internet account as a Game Center internet account, <var>hContactFieldId = 33</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            33,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveiTunesAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an iTunes Internet Account</h2>
        # <p>
        #   Saves an internet account as an iTunes internet account, <var>hContactFieldId = 34</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            34,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveMacAppStoreAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Mac App Store Internet Account</h2>
        # <p>
        #   Saves an internet account as a Mac App Store internet account, <var>hContactFieldId = 35</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            35,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveFacebookAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Facebook Internet Account</h2>
        # <p>
        #   Saves an internet account as a Facebook internet account, <var>hContactFieldId = 29</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            29,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveWindowsLiveAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Windows Live Internet Account</h2>
        # <p>
        #   Saves an internet account as a Windows Live internet account, <var>hContactFieldId = 36</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            36,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveWindowsAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving Windows Internet Account</h2>
        # <p>
        #   Saves an internet account as an Windows internet account, <var>hContactFieldId = 36</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            36,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveGoogleAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Google Internet Account</h2>
        # <p>
        #   Saves an internet account as a Google internet account, <var>hContactFieldId = 42</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            42,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveAolAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an AOL Internet Account</h2>
        # <p>
        #   Saves an internet account as an AOL internet account, <var>hContactFieldId = 12</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            12,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function savePlaystationAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Playstation Internet Account</h2>
        # <p>
        #   Saves an internet account as a Playstation internet account, <var>hContactFieldId = 45</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            45,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveXboxAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Xbox Internet Account</h2>
        # <p>
        #   Saves an internet account as an Xbox internet account, <var>hContactFieldId = 46</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            46,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveYahooAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Yahoo Internet Account</h2>
        # <p>
        #   Saves an internet account as a Yahoo internet account, <var>hContactFieldId = 13</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            13,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveICQAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an ICQ Internet Account</h2>
        # <p>
        #   Saves an internet account as an ICQ internet account, <var>hContactFieldId = 15</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            15,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveiChatAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an iChat Internet Account</h2>
        # <p>
        #   Saves an internet account as an iChat internet account, <var>hContactFieldId = 16</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            16,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveJabberAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Jabber Internet Account</h2>
        # <p>
        #   Saves an internet account as a Jabber internet account, <var>hContactFieldId = 17</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            17,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveOtherAccount($contactInternetAccount, $contactInternetAccountId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Other Internet Account</h2>
        # <p>
        #   Saves an internet account as an Other internet account, <var>hContactFieldId = 18</var>.
        # </p>
        # @end

        return $this->saveInternetAccount(
            $contactInternetAccount,
            18,
            $contactInternetAccountId,
            $contactId,
            $contactAddressId
        );
    }

    public function deleteInternetAccount($contactInternetAccountId)
    {
        # @return integer

        # @description
        # <h2>Deleting an Internet Account</h2>
        # <p>
        #   Deletes the internet account specified in <var>$contactInternetAccountId</var>,
        #   if successful, the number of records deleted is returned, if not successful,
        #   the method returns <var>0</var>.
        # </p>
        # @end
        $contactId = $this->hContactInternetAccounts->selectColumn(
            'hContactId',
            $contactInternetAccountId
        );

        $contactAddressBookId = $this->hContacts->selectColumn(
            'hContactAddressBookId',
            $contactId
        );

        $this->hContactAddressBooks->modifyResource($contactAddressBookId);
        $this->hContacts->modifyResource($contactId);
        $this->hContactInternetAccounts->modifyResource();

        return $this->hContactInternetAccounts->delete(
            'hContactInternetAccountId',
            $contactInternetAccountId
        );
    }

    public function &deleteInternetAccounts($contactId)
    {
        # @return hContactDatabase

        # @description
        # <h2>Deleting All Internet Accounts Associated With a Contact</h2>
        # <p>
        #   Deletes all internet accounts associated with <var>$contactId</var>
        # </p>
        # @end

        $this->hContactInternetAccounts->delete('hContactId', $contactId);
        return $this;
    }

    public function saveEmailAddress($columns, $contactFieldId = 19, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer
        # <p>
        #   The <var>hContactEmailAccountId</var> created or updated.
        # </p>
        # @end

        # @description
        # <h2>Saving an Email Address</h2>
        # <p>
        #   Saves an email address to the <var>contactId</var> specified by either
        #   providing a <var>contactId</var> to the <var>$contactId</var>
        #   argument or by using the
        #   <a href='#setContactId' class='code'>setContactId()</a> method,
        #   or by saving a contact using either the
        #   <a href='#save' class='code'>save()</a> or
        #   <a href='#saveContact' class='code'>saveContact()</a> methods,
        #   which automatically set a <var>contactId</var> when they are
        #   used.
        # </p>
        # <p>
        #   See also the
        #   <a href='/System/Framework/Hot Toddy/hContact/Database/hContactEmailAddresses/hContactEmailAddresses.sql' class='code'>hContactEmailAddresses</a>
        #   table.
        # </p>
        # <p>
        #   If the <var>$contactId</var> argument is provided, it sets the value
        #   of the internal <var>$this-&gt;contactId</var> property, making it
        #   uneccesary to provide on subsequent method calls that affect the same
        #   contact record.
        # <p>
        #   <var>$columns</var> can be provided either as an array, or
        #   a string directly containing the value of an email address.
        #   If an array is provided it should look like this:
        # </p>
        # <code>
        #   array(
        #       'hContactEmailAddress' => 'john@example.com'
        #   )
        # </code>
        # <h3>Directory Syncing</h3>
        # <p>
        #   If a directory (Open Directory, Active Directory, or local Mac OS X user syncing)
        #   is enabled and configured, address data is also synced with the network or local server using the
        #   <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   library plugin.  Data synced to the network or local server is determined by
        #   how <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php' class='code'>hContactDirectory</a>
        #   is configured, as well as what fields the provided network user is allowed to modify.
        # </p>
        # <h3>Fields</h3>
        # <p>
        #   Email address fields:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</th>
        #           <th>hContactField</th>
        #           <th>Flat Record Index</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>19</td>
        #           <td>Personal</td>
        #           <td>hContactEmailAddressPersonal</td>
        #       </tr>
        #       <tr>
        #           <td>20</td>
        #           <td>Work</td>
        #           <td>hContactEmailAddressWork</td>
        #       </tr>
        #       <tr>
        #           <td>21</td>
        #           <td>Other</td>
        #           <td>hContactEmailAddressOther</td>
        #       </tr>
        #       <tr>
        #           <td>37</td>
        #           <td>Facebook</td>
        #           <td>hContactEmailAddressFacebook</td>
        #       </tr>
        #       <tr>
        #           <td>38</td>
        #           <td>Gmail</td>
        #           <td>hContactEmailAddressGmail</td>
        #       </tr>
        #       <tr>
        #           <td>39</td>
        #           <td>Microsoft Hotmail</td>
        #           <td>hContactEmailAddressMicrosoftHotmail</td>
        #       </tr>
        #       <tr>
        #           <td>40</td>
        #           <td>Windows Live</td>
        #           <td>hContactEmailAddressWindowsLive</td>
        #       </tr>
        #       <tr>
        #           <td>41</td>
        #           <td>iCloud</td>
        #           <td>hContactEmailAddressiCloud</td>
        #       </tr>
        #       <tr>
        #           <td>43</td>
        #           <td>Microsoft Exchange</td>
        #           <td>hContactEmailAddressMicrosoftExchange</td>
        #       </tr>
        #       <tr>
        #           <td>44</td>
        #           <td>Aol</td>
        #           <td>hContactEmailAddressAol</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if (!empty($contactId))
        {
            $this->setContactId($contactId);
        }

        if (!empty($contactAddressId))
        {
            $this->setContactAddressId($contactAddressId);
        }

        if (!empty($this->contactId))
        {
            $this->modifyAddressBookByContactId($this->contactId);
        }

        if (!is_array($columns))
        {
            $columns = array(
                'hContactEmailAddress' => trim($columns)
            );
        }

        $query = $this->queryFieldId(
            'hContactEmailAddresses',
            $columns['hContactEmailAddress'],
            $contactFieldId
        );

        $this->getMergedColumns(
            $columns,
            'hContactEmailAddresses',
            $query,
            $contactEmailAddressId,
            $contactFieldId
        );

        if (is_object($this->hContactDirectory) && $this->hContactDirectory->userSet())
        {
            $this->hContactDirectory->saveEmailAddress(
                $columns['hContactEmailAddress'],
                $contactFieldId
            );
        }

        $contactEmailAddressId = $this->hContactEmailAddresses->save($columns);

        $this->hContactEmailAddresses->modifyResource($contactEmailAddressId);

        $this->hContacts->modifyResource($this->contactId);

        return $contactEmailAddressId;
    }

    public function savePersonalEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Personal Email Address</h2>
        # <p>
        #   Saves an email address as a personal email address, <var>hContactFieldId = 19</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            19,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveWorkEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Work Email Address</h2>
        # <p>
        #   Saves an email address as a work email address, <var>hContactFieldId = 20</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            20,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveFacebookEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Facebook Email Address</h2>
        # <p>
        #   Saves an email address as a Facebook email address, <var>hContactFieldId = 37</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            37,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveGmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Gmail Email Address</h2>
        # <p>
        #   Saves an email address as a Gmail email address, <var>hContactFieldId = 38</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            38,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveHotmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Hotmail Email Address</h2>
        # <p>
        #   Saves an email address as a hotmail email address, <var>hContactFieldId = 39</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            39,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveWindowsLiveEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Windows Live Email Address</h2>
        # <p>
        #   Saves an email address as a Windows Live email address, <var>hContactFieldId = 40</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            40,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveiCloudEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an iCloud Email Address</h2>
        # <p>
        #   Saves an email address as an iCloud email address, <var>hContactFieldId = 41</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            41,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveMicrosoftExchangeEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Microsoft Exchange Email Address</h2>
        # <p>
        #   Saves an email address as a Microsoft Exchange email address, <var>hContactFieldId = 43</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            43,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveAolEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an AOL Email Address</h2>
        # <p>
        #   Saves an email address as an AOL email address, <var>hContactFieldId = 44</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            44,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function saveOtherEmail($contactEmailAddress, $contactEmailAddressId = 0, $contactId = 0, $contactAddressId = 0)
    {
        # @return integer

        # @description
        # <h2>Saving an Other Email Address</h2>
        # <p>
        #   Saves an email address as an other email address, <var>hContactFieldId = 21</var>.
        # </p>
        # @end

        return $this->saveEmailAddress(
            $contactEmailAddress,
            21,
            $contactEmailAddressId,
            $contactId,
            $contactAddressId
        );
    }

    public function deleteEmailAddress($contactEmailAddressId)
    {
        # @return integer

        # @description
        # <h2>Deleting an Email Address</h2>
        # <p>
        #   Deletes the email address specified in <var>$contactEmailAddressId</var>,
        #   if successful, the number of records deleted is returned, if not successful,
        #   the method returns <var>0</var>.
        # </p>
        # @end
        $contactId = $this->hContactEmailAddresses->selectColumn(
            'hContactId',
            $contactEmailAddressId
        );

        $contactAddressBookId = $this->hContacts->selectColumn(
            'hContactAddressBookId',
            $contactId
        );

        $this->hContactAddressBooks->modifyResource($contactAddressBookId);
        $this->hContacts->modifyResource($contactId);
        $this->hContactEmailAddresses->modifyResource();

        return $this->hContactEmailAddresses->delete(
            'hContactEmailAddressId',
            $contactEmailAddressId
        );
    }

    public function &deleteEmailAddresses($contactId)
    {
        # @return hContactDatabase

        # @description
        # <h2>Deleting All Email Addresses Assocaited With a Contact</h2>
        # <p>
        #   Deletes all email addresses associated with the specitied <var>$contactId</var>.
        # </p>
        # @end

        $this->hContactEmailAddresses->delete(
            'hContactId',
            $contactId
        );

        return $this;
    }

    private function &getMergedColumns(&$columns, $table, $query, $contactColumnId, $contactFieldId)
    {
        # @return void

        # @description
        # <h2>Merging Columns in Preparation for a Database Query</h2>
        # <p>
        #   This method assembles the necessary columns to save an address, phone number,
        #   email address, or internet account.
        # </p>
        # @end

        if (empty($contactColumnId))
        {
            $contactColumnId = $this->hDatabase->getColumn($query);
        }

        $this->validateFieldId($table, $contactFieldId);

        $columns['hContactId'] = $this->contactId;

        if ($table != 'hContactAddresses')
        {
            $columns['hContactAddressId'] = $this->contactAddressId;
        }

        $columns['hContactFieldId'] = $contactFieldId;
        $columns[preg_replace('/s$|es$/', 'Id', $table)] = (int) $contactColumnId;

        return $this;
    }

    private function queryFieldId($table, $data, $contactFieldId)
    {
        # @return integer

        # @description
        # <h2>Getting the Unique Id for Data</h2>
        # <p>
        #   Used for finding a unique id.  More specifically: <var>hContactPhoneNumberId</var>,
        #   <var>hContactEmailAddressId</var>, or <var>hContactInternetAccountId</var>.
        # </p>
        # <p>
        #   <var>$table</var> is one of <var>hContactPhoneNumbers</var>, <var>hContactEmailAddresses</var>,
        #   or <var>hContactInternetAccounts</var>.
        # </p>
        # <p>
        #   <var>$data</var> refers to a phone number, email address, or internet account.
        # </p>
        # <p>
        #   <var>$fieldId</var> is an <var>hContactFieldId</var>.
        # </p>
        # <p>
        #   This method is used to assist in the prevent duplicate data functionality
        #   associated with <a href='#setDuplicateFields'>setDuplicateFields()</a>, it returns
        #   an existing <var>hContactPhoneNumberId</var>,
        #   <var>hContactEmailAddressId</var>, or <var>hContactInternetAccountId</var> if data
        #   is found to already exist in the database and <var>0</var> if nothing is found.
        # </p>
        # @end

        $fields = array();

        if ($this->duplicateFields)
        {
            $fields[preg_replace('/s$|es$/', '', $table)] = $data;
        }

        if (!empty($this->contactAddressId))
        {
            $fields['hContactAddressId'] = $this->contactAddressId;
        }

        return $this->$table->selectQuery(
            preg_replace('/s$|es$/', '', $table).'Id',
            array_merge(
                array(
                    'hContactId' => (int) $this->contactId,
                    'hContactFieldId' => (int) $contactFieldId
                ),
                $fields
            )
        );
    }

    public function &validateFieldId($table, $contactFieldId)
    {
        # @return void

        # @description
        # <h2>Validating a Field Id</h2>
        # <p>
        #   Determines if the supplied <var>$contactFieldId</var> is valid for the
        #   provided <var>$table</var>.  If the field id is not valid, an error is logged to
        #   the error console.
        # </p>
        # @end

        // Don't forget that getResourceId passes by reference, and assigns the Id to the argument!
        if (!$this->hContactValidation->isFieldId($contactFieldId, $this->getResourceId($table)))
        {
            $this->warning('Field Id, '.$contactFieldId.', is not valid for resource, '.$table.'.', __FILE__, __LINE__);
        }

        return $this;
    }

    public function saveAddressBook($columns)
    {
        # @return integer

        # @description
        # <h2>Saving an Address Book</h2>
        # <p>
        #   An address book is saved by specifying <var>$columns</var>
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hContactAddressBookId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserId</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressBookName</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hPlugin</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactAddressBookIsDefault</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p>
        #   If a creating a new address book, <var>hContactAddressBookId</var> should
        #   be <var>null</var> or <var>0</var>.
        # </p>
        # <p>
        #   <var>hUserId</var> is the <var>userId</var> that will own the address book
        #   from the vantage point of permissions.
        # </p>
        # <p>
        #   If a user has multiple address books, <var>hContactAddressBookIsDefault</var>
        #   is used to flag one of them as the default address book, it should be set
        #   to <var>true</var> or <var>false</var> or <var>0</var> or <var>1</var>.
        # </p>
        # @end

        $userId = 0;

        if (isset($columns['hUserId']))
        {
            $userId = (int) $columns['hUserId'];
        }

        $contactAddressBookId = 0;

        if (isset($columns['hContactAddressBookId']))
        {
            $contactAddressBookId = (int) $columns['hContactAddressBookId'];
        }

        if (empty($contactAddressBookId) && empty($userId))
        {
            $userId = $this->isLoggedIn()? (int) $_SESSION['hUserId'] : 1;
        }

        $data = array('hContactAddressBookId' => $contactAddressBookId);

        if (isset($columns['hContactAddressBookName']))
        {
            $data['hContactAddressBookName'] = $columns['hContactAddressBookName'];
        }

        if (!empty($userId))
        {
            $data['hUserId'] = $userId;
        }

        if (isset($columns['hPlugin']))
        {
            $data['hPlugin'] = $columns['hPlugin'];
        }

        if (isset($columns['hContactAddressBookIsDefault']))
        {
            $data['hContactAddressBookIsDefault'] = (int) $columns['hContactAddressBookIsDefault'];
        }

        if (empty($contactAddressBookId))
        {
            $columns['hContactAddressBookCreated'] = time();
        }

        $contactAddressBookId = $this->hContactAddressBooks->save($data);

        $this->hContactAddressBooks->modifyResource($contactAddressBookId);

        return $contactAddressBookId;
    }

    public function getAddressBook($contactAddressBookId)
    {
        # @return array

        # @description
        # <h2>Fetching Address Book Information</h2>
        # <p>
        #   Returns <var>hContactAddressBookName</var>, and <var>hPlugin</var>
        #   as an associative array for the supplied <var>$contactAddressBookId</var>.
        # </p>
        # @end

        return $this->hContactAddressBooks->selectAssociative(
            array(
                'hContactAddressBookName',
                'hPlugin'
            ),
            (int) $contactAddressBookId
        );
    }

    public function &saveVariable($contactId, $contactVariable, $contactValue)
    {
        # @return hContactDatabase

        # @description
        # <h2>Saving a Contact Variable</h2>
        # <p>
        #   Like many other things in Hot Toddy, arbitrary key, value pairs can
        #   be stored for contacts.  Variables can be set for contacts using
        #   this method.
        # </p>
        # @end

        $this->modifyAddressBookByContactId($contactId);

        $this->hContacts->modifyResource($contactId);

        $this->hContactVariables->delete(
            array(
                'hContactId' => (int) $contactId,
                'hContactVariable' => $contactVariable
            )
        );

        $this->hContactVariables->insert(
            array(
                'hContactId' => (int) $contactId,
                'hContactVariable' => $contactVariable,
                'hContactValue' => $contactValue
            )
        );

        return $this;
    }

    public function getContactIdByEmailAddress($contactEmailAddress, $contactAddressBookId = 1)
    {
        # @return integer

        # @description
        # <h2>Getting a Contact Id By Email Address</h2>
        # <p>
        #   Returns a <var>contactId</var> for the supplied <var>$contactEmailAddress</var>,
        #   if one is found.  If no record is found, the method returns zero.
        # </p>
        # @end

        return $this->hDatabase->selectColumn(
            array(
                'hContacts' => 'hContactId'
            ),
            array(
                'hContacts',
                'hContactAddressBooks',
                'hContactEmailAddresses'
            ),
            array(
                'hContacts.hContactAddressBookId' => 'hContactAddressBooks.hContactAddressBookId',
                'hContactAddressBooks.hContactAddressBookId' => (int) $contactAddressBookId,
                'hContacts.hContactId' => 'hContactEmailAddresses.hContactId',
                'hContactEmailAddresses.hContactEmailAddress' => $contactEmailAddress
            )
        );
    }

    public function getContactIdByUserId($userId = 0, $contactAddressBookId = 1)
    {
        # @return integer

        # @description
        # <h2>Getting a Contact Id By User Id</h2>
        # <p>
        #   Returns a <var>contactId</var> for the supplied <var>$userId</var>
        #   and <var>$contactAddressBookId</var>.  The default address book is
        #   <var>1</var>, <i>Website Registrations</i>.
        # </p>
        # @end

        $this->user
             ->setNumericUserId($userId)
             ->whichUserId($userId);

        return $this->hContacts->selectColumn(
            'hContactId',
            array(
                'hContactAddressBookId' => (int) $contactAddressBookId,
                'hUserId' => (int) $userId
            )
        );
    }

    public function getContactsByState($locationStateId, $contactAddressBookId = 1)
    {
        # @return array

        # @description
        # <h2>Getting Contacts By State</h2>
        # <p>
        #   Returns a list of contacts by the supplied <var>$locationStateId</var>,
        #   and <var>$contactAddressBookId</var>.  The default address book is
        #   <var>1</var>, <i>Website Registrations</i>.
        # </p>
        # @end

        return $this->hDatabase->selectResults(
            array(
                'DISTINCT',
                'hContacts' => 'hContactId'
            ),
            array(
                'hContactAddresses',
                'hContacts'
            ),
            array(
                'hContactAddresses.hContactId' => 'hContacts.hContactId',
                'hContacts.hContactAddressBookId' => (int) $contactAddressBookId,
                'hContactAddresses.hLocationStateId' => (int) $locationStateId
            )
        );
    }

    public function chown($userId = 0, $contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Changing the Owner of a Contact</h2>
        # <p>
        #   Changes the owner of the supplied <var>$contactId</var> to the supplied
        #   <var>$userId</var>.  If successful, this method returns the number of
        #   records affected.
        # </p>
        # @end

        $this->user
             ->setNumericUserId($userId)
             ->whichUserId($userId);

        $this->whichContactId($contactId)->modifyAddressBookByContactId($contactId);
        $this->hContacts->modifyResource($contactId);

        return $this->hContacts->update(
            array('hUserId' => (int) $userId),
            (int) $contactId
        );
    }

    public function &modifyAddressBookByContactId($contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Setting the Address Book Modified Time</h2>
        # <p>
        #   Updates the last modified time of the address book that the <var>$contactId</var>
        #   exists within.  Setting a modified time for the address book can be used to
        #   control the updating of cached contact information data.
        # </p>
        # @end

        $this->whichContactId($contactId)
            ->hContactAddressBooks
            ->modifyResource(
                $this->hContacts->selectColumn(
                    'hContactAddressBookId',
                    (int) $contactId
                )
            );

        return $this;
    }

    public function &saveFile($fileId, $contactId = 0, $fileCategoryId = 1, $isProfilePhoto = true, $isDefaultProfilePhoto = true)
    {
        # @return hContactDatabase

        # @description
        # <h2>Saving a Contact File</h2>
        # <p>
        #   Contact files are used for things like associated a profile photo with a contact.
        #   The contact file API can associate one or more files with a contactId for
        #   any purpose. The default purpose is for a profile photo.
        # </p>
        # @end

        $this->whichContactId($contactId)
             ->modifyAddressBookByContactId($contactId);

        $this->hContacts->modifyResource($contactId);

        $exists = $this->hContactFiles->selectExists(
            'hFileId',
            array(
                'hContactId' => (int) $contactId,
                'hFileId' => (int) $fileId
            )
        );

        if ($exists)
        {
            $this->hContactFiles->update(
                array(
                    'hContactFileCategoryId' => (int) $fileCategoryId,
                    'hContactIsProfilePhoto' => (int) $isProfilePhoto,
                    'hContactIsDefaultProfilePhoto' => (int) $isDefaultProfilePhoto
                ),
                array(
                    'hContactId' => (int) $contactId,
                    'hFileId' => (int) $fileId
                )
            );
        }
        else
        {
            $this->hContactFiles->insert(
                array(
                    'hContactId' => (int) $contactId,
                    'hFileId' => (int) $fileId,
                    'hContactFileCategoryId' => (int) $fileCategoryId,
                    'hContactIsProfilePhoto' => (int) $isProfilePhoto,
                    'hContactIsDefaultProfilePhoto' => (int) $isDefaultProfilePhoto
                )
            );
        }

        return $this;
    }

    public function &delete($contactId = 0)
    {
        # @return integer

        # @description
        # <h2>Deleting a Contact</h2>
        # <p>
        #   This method completely and permanently deletes the provided
        #   <var>$contactId</var>.
        # </p>
        # <p>
        #   Information is removed from the following tables, in this order:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hContactAddresses</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactEmailAddresses</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactInternetAccounts</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactPhoneNumbers</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactUsers</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContactVariables</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hContacts</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this
            ->whichContactId($contactId)
            ->modifyAddressBookByContactId($contactId)
            ->hDatabase
            ->delete(
                array(
                    'hContactFiles',
                    'hContactAddresses',
                    'hContactEmailAddresses',
                    'hContactInternetAccounts',
                    'hContactPhoneNumbers',
                    'hContactUsers',
                    'hContactVariables',
                    'hContacts'
                ),
                'hContactId',
                $contactId
            );

        return $this;
    }

    public function getFieldLabel($contactFieldId)
    {
        # @return string

        # @description
        # <h2>Returning a Field Label</h2>
        # <p>
        #   Returns a label for the provided <var>$contactFieldId</var>.
        #   For example, providing a <var>$contactFieldId</var> of <var>7</var>
        #   returns the label <i>Extension</i>.
        # </p>
        # @end

        return $this->hContactFields->selectColumn(
            'hContactField',
            (int) $contactFieldId
        );
    }

    public function getFieldsForTemplate($frameworkResourceId, $contactFieldId = 0)
    {
        # @return array

        # @description
        # <h2>Returning Fields for Use in a Template</h2>
        # <p>
        #   Returns the collection of field labels and ids for the provided
        #   <var>$frameworkResourceId</var>.  For example, Providing a <var>$frameworkResourceId</var> of
        #   <var>11</var> returns all <var>hContactFields</var> for <var>hContactPhoneNumbers</var>,
        #   as an array suitable for inclusion in a template.  The template array sets the following
        #   fields <var>hContactFieldId</var>, <var>hContactFieldLabel</var>, and
        #   <var>hContactFieldIsSelected</var>.  Whether or not <var>hContactFieldIsSelected</var>
        #   is <var>true</var> is based on the value provided in the <var>$contactFieldId</var>
        #   argument.
        # </p>
        # @end

        $this->frameworkResource()->numericResourceId($frameworkResourceId);

        $query = $this->hContactFields->select(
            array(
                'hContactFieldId',
                'hContactField'
            ),
            array(
                'hFrameworkResourceId' => (int) $frameworkResourceId
            ),
            'AND',
           'hContactFieldSortIndex'
        );

        $results = array();

        foreach ($query as $data)
        {
            $results['hContactFieldId'][] = $data['hContactFieldId'];
            $results['hContactFieldLabel'][] = $data['hContactField'];
            $results['hContactFieldIsSelected'][] = ($contactFieldId == $data['hContactFieldId']);
        }

        return $results;
    }

    public function getFields($frameworkResourceId)
    {
        # @return array

        # @description
        # <h2>Getting Contact Databases Field Types</h2>
        # <p>
        #   Returns an array of <var>hContactFieldId</var>
        #   and <var>hContactField</var> values for the specified
        #   <var>$frameworkResource</var> or <var>$frameworkResourceId</var>
        # </p>
        # <p>
        #   Returned Data:
        # </p>
        # <h4>hContactAddresses</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>1</td>
        #           <td>Home</td>
        #       </tr>
        #       <tr>
        #           <td>2</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>3</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>hContactEmailAddresses</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>19</td>
        #           <td>Personal</td>
        #       </tr>
        #       <tr>
        #           <td>20</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>37</td>
        #           <td>Facebook</td>
        #       </tr>
        #       <tr>
        #           <td>38</td>
        #           <td>Gmail</td>
        #       </tr>
        #       <tr>
        #           <td>39</td>
        #           <td>Microsoft Hotmail</td>
        #       </tr>
        #       <tr>
        #           <td>40</td>
        #           <td>Windows Live</td>
        #       </tr>
        #       <tr>
        #           <td>41</td>
        #           <td>iCloud</td>
        #       </tr>
        #       <tr>
        #           <td>43</td>
        #           <td>Microsoft Exchange</td>
        #       </tr>
        #       <tr>
        #           <td>44</td>
        #           <td>Aol.</td>
        #       </tr>
        #       <tr>
        #           <td>21</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>hContactInternetAccounts</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>30</td>
        #           <td>Apple Id</td>
        #       </tr>
        #       <tr>
        #           <td>31</td>
        #           <td>iMessages</td>
        #       </tr>
        #       <tr>
        #           <td>32</td>
        #           <td>iCloud</td>
        #       </tr>
        #       <tr>
        #           <td>33</td>
        #           <td>Game Center</td>
        #       </tr>
        #       <tr>
        #           <td>34</td>
        #           <td>iTunes</td>
        #       </tr>
        #       <tr>
        #           <td>35</td>
        #           <td>Mac App Store</td>
        #       </tr>
        #       <tr>
        #           <td>29</td>
        #           <td>Facebook</td>
        #       </tr>
        #       <tr>
        #           <td>36</td>
        #           <td>Windows Live</td>
        #       </tr>
        #       <tr>
        #           <td>42</td>
        #           <td>Google</td>
        #       </tr>
        #       <tr>
        #           <td>12</td>
        #           <td>Aol.</td>
        #       </tr>
        #       <tr>
        #           <td>45</td>
        #           <td>Playstation Network</td>
        #       </tr>
        #       <tr>
        #           <td>46</td>
        #           <td>Xbox Live</td>
        #       </tr>
        #       <tr>
        #           <td>13</td>
        #           <td>Yahoo!</td>
        #       </tr>
        #       <tr>
        #           <td>15</td>
        #           <td>ICQ</td>
        #       </tr>
        #       <tr>
        #           <td>16</td>
        #           <td>iChat</td>
        #       </tr>
        #       <tr>
        #           <td>17</td>
        #           <td>Jabber</td>
        #       </tr>
        #       <tr>
        #           <td>18</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>hContactPhoneNumbers</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>4</td>
        #           <td>Home</td>
        #       </tr>
        #       <tr>
        #           <td>5</td>
        #           <td>Mobile</td>
        #       </tr>
        #       <tr>
        #           <td>6</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>7</td>
        #           <td>Extension</td>
        #       </tr>
        #       <tr>
        #           <td>8</td>
        #           <td>Company</td>
        #       </tr>
        #       <tr>
        #           <td>9</td>
        #           <td>Fax</td>
        #       </tr>
        #       <tr>
        #           <td>10</td>
        #           <td>Pager</td>
        #       </tr>
        #       <tr>
        #           <td>22</td>
        #           <td>Main</td>
        #       </tr>
        #       <tr>
        #           <td>23</td>
        #           <td>Toll-Free</td>
        #       </tr>
        #       <tr>
        #           <td>24</td>
        #           <td>Appointments</td>
        #       </tr>
        #       <tr>
        #           <td>47</td>
        #           <td>Scheduling</td>
        #       </tr>
        #       <tr>
        #           <td>25</td>
        #           <td>iPhone</td>
        #       </tr>
        #       <tr>
        #           <td>26</td>
        #           <td>Home Fax</td>
        #       </tr>
        #       <tr>
        #           <td>27</td>
        #           <td>Work Fax</td>
        #       </tr>
        #       <tr>
        #           <td>28</td>
        #           <td>Other Fax</td>
        #       </tr>
        #       <tr>
        #           <td>11</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->frameworkResource()->numericResourceId($frameworkResourceId);

        return $this->hContactFields->selectColumnsAsKeyValue(
            array(
                'hContactFieldId',
                'hContactField'
            ),
            array(
                'hFrameworkResourceId' => (int) $frameworkResourceId
            ),
            'AND',
            'hContactFieldSortIndex'
        );
    }

    public function getAddressesNearZipCode($locationZipCode, $radius = 10, $contactAddressBookId = 1, $limit = 3)
    {
        # @return array

        # @description
        # <h2>Getting All Addresses Near a Zip Code</h2>
        # <p>
        #   Returns all addresses in the specified <var>$contactAddressBookId</var> within
        #   the specified mile <var>$radius</var> of <var>$locationZipCode</var>. Results
        #   are limited to <var>$limit</var>.
        # </p>
        # <p>
        #   Proximity search requires that the <var>hLocationZipCodes</var> table
        #   in the database be populated with zip codes and latitude and longitude
        #   data. An older copy of zip code latitude and longitude data is provided
        #   in the installation.
        # </p>
        # @end

        $fence = $this->hMap->getZipCodeGeofence($locationZipCode);

        # Calculate lon and lat to create a rectangle.
        # This rectangle is used to optimize the query. It
        # limits the possible latitude and longitude results so
        # that the query reads only a fraction of possible
        # latitude and longitude hits, rather than the whole table.
        # this works well for a low volume of possible hits, but
        # for high volume the MySQL spatial extension should offer
        # the best possible performance.
        return array(
            'contacts' => $this->hDatabase->getResults(
                $this->getTemplateSQL(
                    array_merge(
                        $fence,
                        array(
                            'contactAddressBookId' => $contactAddressBookId,
                            'limit' => $limit
                        )
                    )
                )
            ),
            'latitude' => $fence['latitude'],
            'longitude' => $fence['longitude']
        );
    }

    public function &whichContactId(&$contactId)
    {
        # @return void

        # @description
        # <h2>Determining Which Contact Id to Use</h2>
        # <p>
        #   Determines whether or not to use the <var>$contactId</var> argument
        #   passed to a method, or, if <var>$contactId</var> is empty, to instead use the value
        #   of the internal <var>$this-&gt;contactId</var> property.
        # </p>
        # @end

        $contactId = !empty($contactId)? $contactId : $this->contactId;
        return $this;
    }
}

?>