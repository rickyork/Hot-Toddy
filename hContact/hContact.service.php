<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Contact Service
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

class hContactService extends hService {

    private $hContactAddressBookId;
    private $hContactAddressBook;
    private $hContactApplication;
    private $hContactAddressBookName;
    private $hContactSummary;
    private $hSpotlightSearch;
    private $hContactDatabase;

    private $searchColumns = array();
    private $timeColumns = array();

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Contact Service Constructor</h2>
        # <p>
        #
        # </p>
        # @end

        switch ($this->hFrameworkServiceMethod)
        {
            case 'saveSize':
            case 'saveInstructionsDefault':
            {
                break;
            }
            default:
            {
                $contactConf = $this->get('contactConf');

                if (!empty($contactConf))
                {
                    $this->loadConfigurationFile(
                        $this->hFrameworkConfigurationPath.'/hContact '.hString::scrubString($contactConf)
                    );
                }

                $this->hContactAddressBookId = $this->get('contactAddressBookId', 1);
                $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');

                $data = $this->hContactAddressBooks->selectAssociative(
                    array(
                        'hContactAddressBookName',
                        'hPlugin'
                    ),
                    (int) $this->hContactAddressBookId
                );

                if (count($data))
                {
                    $this->hContactAddressBook = $this->plugin('hContact/hContactAddressBook');
                    $this->hContactAddressBookName = $data['hContactAddressBookName'];

                    $this->hContactAddressBook->setAddressBookName($data['hContactAddressBookName']);

                    $this->hContactApplication = $this->hContactAddressBook->getAddressBookPlugin($data['hPlugin']);
                }

                // Is the user logged in?
                if ($this->isLoggedIn())
                {
                    $inGroup = $this->inAnyOfTheFollowingGroups(
                        array(
                            'User Administrators',
                            'Website Administrators',
                            'Contact Administrators'
                        )
                    );

                    if ($this->hContactAddressBookId == 1 && $inGroup)
                    {
                        return;
                    }

                    // Does this user have permission to look at this address book?
                    if (!$this->hContactAddressBooks->hasPermission($this->hContactAddressBookId, 'r'))
                    {
                        // If not, is there a specific contact specified, and does the user have permission to look
                        // at that contact?
                        if (isset($_GET['hContactId']))
                        {
                            if (!$this->hContacts->hasPermission((int) $_GET['hContactId'], 'r'))
                            {
                               $this->JSON(-1);
                            }
                        }
                        else
                        {
                            $this->JSON(-1);
                        }
                    }
                }
                else
                {
                    $this->JSON(-6);
                }
            }
        }
    }

    private function &setDefaultColumns($search)
    {
        # @return hContactService

        # @description
        # <h2>Setting Default Columns</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($_POST['hSpotlightSearchColumns']) && strstr($search, ' '))
        {
            $bits = explode(' ', $search);

            $this->hSpotlightSearch->addWhereAddendum(
                'OR',
                " `hContacts`.`hContactFirstName` = '".array_shift($bits)."'".
                " AND `hContacts`.`hContactLastName` = '".array_pop($bits)."'"
            );
        }

        return $this;
    }

    public function query()
    {
        # @return JSON

        # @description
        # <h2>Querying Contacts</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($_POST['hSpotlightSearchColumns']) || !isset($_POST['hSpotlightSearchQuery']))
        {
            $this->JSON(-5);
            return;
        }

        $search = $_POST['hSpotlightSearchQuery'];

        $this->setDefaultColumns($search);

        $columns = array();
        $validation = false;
        $time = array();
        $location = array();
        $sort = '';
        $sortOrientation = '';

        $this->hSpotlightSearch->setColumns(
            $columns,
            $validation,
            $time,
            $location,
            $sort,
            $sortOrientation
        );

        if ($validation)
        {
            $results = array();
            $mainQuery = nil;

            if (count($location))
            {
                if (method_exists($this->hContactApplication, 'queryLocation'))
                {
                    $this->hContactApplication->queryLocation(
                        $search,
                        $location,
                        $columns,
                        $time,
                        $sort,
                        $sortOrientation,
                        $results
                    );
                }
            }
            else
            {
                if ($this->hContactApplication && method_exists($this->hContactApplication, 'query'))
                {
                    // Allow address books to provide custom query interfaces.
                    $this->hContactApplication->query(
                        $search,
                        $columns,
                        $time,
                        $sort,
                        $sortOrientation,
                        $results
                    );
                }
                else
                {
                    $this->hContactAddressBook->query(
                        $search,
                        $columns,
                        $time,
                        $sort,
                        $sortOrientation,
                        $results
                    );
                }
            }

            $this->sendResults(
                $results,
                $search,
                $mainQuery
            );
        }
        else
        {
            $this->JSON(0);
        }
    }

    public function queryLocation()
    {

    }

    public function queryGroup()
    {
        # @return JSON

        # @description
        # <h2>Querying Groups</h2>
        # <p>
        #
        # </p>
        # @end

        $contactGroupId = (int) $this->get('contactGroupId');

        if (!$contactGroupId)
        {
            $this->JSON(-5);
            return;
        }

        $results = array();

        $sort = '';
        $sortOrientation = '';

        $this->hSpotlightSearch->setSort($sort, $sortOrientation);

        if ($this->hContactApplication && method_exists($this->hContactApplication, 'queryGroup'))
        {
            $this->hContactApplication->queryGroup(
                $this->hContactAddressBookId,
                $contactGroupId,
                $sort,
                $sortOrientation,
                $results
            );
        }

        $this->sendResults(
            $results,
            $this->user->getUserName($contactGroupId)
        );
    }

    private function sendResults(array &$results, $search, $mainQuery = nil)
    {
        # @return JSON

        # @description
        # <h2>Sending Results</h2>
        # <p>
        #
        # </p>
        # @end

        $html = '';

        if ($this->hContactApplication && method_exists($this->hContactApplication, 'getResultsHTML'))
        {
            $html = $this->hContactApplication->getResultsHTML($results);
        }
        else
        {
            $html = $this->hContactAddressBook->getResultsHTML($results);
        }

        if (empty($html))
        {
            $html =
                "<div class='hContactNoResults'>".
                "    There are no results for <i>{$search}</i>.".
                "</div>";
        }

        $this->JSON(
            $html.
            "<div class='hContactBenchmark'>".
                ($mainQuery? 'Query: '.$mainQuery.' Milliseconds, ' : '').
                'Total: '.$this->getBenchmark().' Milliseconds'.
            "</div>"
        );
    }

    public function getRecord()
    {
        # @return JSON

        # @description
        # <h2>Getting a Contact Record</h2>
        # <p>
        #
        # </p>
        # @end

        $contactId = (int) $this->get('contactId');

        if (!$contactId)
        {
            $this->JSON(-5);
             return;
        }

        $this->hContactSummary = $this->library(
            $this->hContactSummaryPlugin('hContact/hContactSummary')
        );

        $this->JSON(
            $this->hContactSummary->get($contactId)
        );
    }

    public function newRecord()
    {
        # @return JSON

        # @description
        # <h2>Creating a New Contact Record</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hContactSummary = $this->library(
            $this->hContactSummaryPlugin('hContact/hContactSummary')
        );

        $this->JSON(
            $this->hContactSummary->get(0)
        );
    }

    private function hasWriteAccess($contactId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a User Has Write Access to a Contact</h2>
        # <p>
        #
        # </p>
        # @end

        $inGroup = $this->inAnyOfTheFollowingGroups(
            array(
                'User Administrators',
                'Website Administrators',
                'Contact Administrators'
            )
        );

        if ($this->hContactAddressBookId == 1 && $inGroup)
        {
            return true;
        }

        // Does the user have write access to the address book?
        if (!$this->hContactAddressBooks->hasPermission($this->hContactAddressBookId, 'rw'))
        {
            // If the user does not have write access to the address book, does the user have write access
            // to the contact?
            if (!empty($contactId) && !$this->hContacts->hasPermission($contactId, 'rw'))
            {
                $this->JSON(-1);
                return false;
            }
        }

        return true;
    }

    public function save()
    {
        # @return JSON

        # @description
        # <h2>Saving a Contact</h2>
        # <p>
        #
        # </p>
        # @end

        $contactId = (int) $this->post('contactId');

        if (!$contactId)
        {
            $this->JSON(-5);
            return;
        }

        if (!$this->hasWriteAccess($contactId))
        {
            return;
        }

        $userId = (int) $this->post('userId', 0);

        if (empty($contactId))
        {
            $userId = $this->hContactAddressBookId == 1? 0 : (int) $_SESSION['hUserId'];
        }

        if ($this->hContactApplication && method_exists($this->hContactApplication, 'save'))
        {
            $response = 1;
            $this->hContactApplication->save($response, $contactId);

            if ($response <= 0)
            {
                $this->JSON($response);
                return;
            }
        }

        $this->hContactDatabase = $this->database('hContact');
        $this->hContactDatabase->setDuplicateFields(true);

        $firstName = $this->post('contactFirstName');
        $lastName = $this->post('contactLastName');

        $contact = array(
            'hContactFirstName'     => $firstName,
            'hContactMiddleName'    => $this->post('contactMiddleName'),
            'hContactLastName'      => $lastName,
            'hContactDisplayName'   => "{$firstName} {$lastName}",
            'hContactWebsite'       => $this->post('contactWebsite'),
            'hContactCompany'       => $this->post('contactCompany'),
            'hContactTitle'         => $this->post('contactTitle'),
            'hContactDepartment'    => $this->post('contactDepartment'),
            'hContactLastModified'  => time()
        );

        foreach ($contact as $key => $value)
        {
            if ($value == 'undefined')
            {
                $contact[$key] = '';
            }
        }

        if (empty($contactId))
        {
            $contact['hContactCreated'] = time();
        }

        $contactId = $this->hContactDatabase->saveContact(
            $contact,
            $this->hContactAddressBookId,
            $userId,
            $contactId
        );

        $this->saveData(
            'hContactPhoneNumber',
            's',
            $this->post('contactPhoneNumbers', array()),
            'savePhoneNumber'
        );

        $this->saveData(
            'hContactEmailAddress',
            'es',
            $this->post('contactEmailAddresses', array()),
            'saveEmailAddress'
        );

        $this->saveData(
            'hContactAddress',
            'es',
            $this->post('contactAddresses', array()),
            'saveAddress'
        );

        $this->JSON(
            array(
                'hContactId' => $contactId,
                'hUserId' => $userId
            )
        );
    }

    private function &saveData($field, $plural, &$data, $method)
    {
        # @return hContactService

        # @description
        # <h2>Saving Contact Data</h2>
        # <p>
        #
        # </p>
        # @end

        $plural = $field.$plural;

        $ids = array();

        if (isset($data) && is_array($data))
        {
            foreach ($data as $columns)
            {
                if ($columns[$field.'Id'] < 0)
                {
                    $columns[$field.'Id'] = 0;
                }

                if ($field == 'hContactAddress')
                {
                    $array = array(
                        'hContactAddressStreet' => trim($columns['hContactAddressStreet']),
                        'hContactAddressCity' => trim($columns['hContactAddressCity']),
                        'hLocationStateId' => (int) $columns['hLocationStateId'],
                        'hContactAddressPostalCode' => trim($columns['hContactAddressPostalCode']),
                        'hLocationCountryId' => (int) $columns['hLocationCountryId']
                    );

                    if (empty($array['hContactAddressStreet']) && empty($array['hContactAddressCity']) && empty($array['hContactAddressPostalCode']) && empty($array['hLocationStateId']))
                    {
                        continue;
                    }
                }

                $columns[$field.'Id'] = $this->hContactDatabase->$method(
                    isset($array) && count($array) ? $array : array($field => trim($columns[$field])),
                    (int) $columns['hContactFieldId'],
                    (int) $columns[$field.'Id']
                );

                array_push($ids, (int) $columns[$field.'Id']);
            }
        }

        $records = $this->hDatabase->select(
            $field.'Id',
            $plural,
            array(
                'hContactId' => (int) $_POST['hContactId']
            )
        );

        if (count($records))
        {
            foreach ($records as $record)
            {
                if (!in_array((int) $record, $ids, true))
                {
                    $this->hDatabase->delete($plural, $field.'Id', (int) $record);
                }
            }
        }

        return $this;
    }

    public function delete()
    {
        # @return JSON

        # @description
        # <h2>Deleting a Contact</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hContactDatabase = $this->database('hContact');

        if (empty($_GET['hContactId']))
        {
            if (isset($_GET['hUserId']))
            {
                $_GET['hContactId'] = $this->hContactDatabase->getContactIdByUserId(
                    (int) $_GET['hUserId'],
                    (int) $_GET['hContactAddressBookId']
                );
            }

            if (!isset($_GET['hContactId']))
            {
                $this->JSON(-5);
                return;
            }
        }

        $contactId = (int) $_GET['hContactId'];

        if (!$this->hasWriteAccess($contactId))
        {
            return;
        }

        if ($this->hContactApplication && method_exists($this->hContactApplication, 'delete'))
        {
            $response = 1;
            $this->hContactApplication->delete($response);

            if ($response <= 0)
            {
                $this->JSON($response);
                return;
            }
        }

        $this->hContactDatabase->delete($contactId);

        $this->JSON(1);
    }

    public function deleteData()
    {
        # @return JSON

        # @description
        # <h2>Deleting Contact Data</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($_GET['hContactId']) || !isset($_GET['data']) || !isset($_GET['dataId']))
        {
            $this->JSON(-5);
            return;
        }

        $contactId = (int) $_GET['hContactId'];

        if (!$this->hasWriteAccess($contactId))
        {
            return;
        }

        $this->hContactDatabase = $this->database('hContact');

        $type = str_replace(' ', '', $_GET['data']);

        switch ($type)
        {
            case 'Address':
            case 'PhoneNumber':
            case 'EmailAddress':
            case 'InternetAccount':
            {
                $this->hContactDatabase->{"delete{$type}"}((int) $_GET['dataId']);
                $this->JSON(1);
                return;
            }
            default:
            {
                $this->JSON(0);
                return;
            }
        }
    }

    public function getContactIdByUserId()
    {
        # @return JSON

        # @description
        # <h2>Getting Contact Id By User Id</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($_GET['hUserId']))
        {
            $this->JSON(-5);
            return;
        }

        $this->hContactDatabase = $this->database('hContact');

        $this->JSON(
            $this->hContactDatabase->getContactIdByUserId(
                (int) $_GET['hUserId'],
                isset($_GET['hContactAddressBookId']) ? (int) $_GET['hContactAddressBookId'] : 1
            )
        );
    }

    public function saveColumnDimensions()
    {
        # @return JSON

        # @description
        # <h2>Saving Column Dimensions</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!empty($_GET['groupWidth']))
        {
            $this->user->saveVariable(
                'hContactGroupColumnWidth',
                (int) $_GET['groupWidth']
            );
        }

        if (!empty($_GET['resultsWidth']))
        {
            $this->user->saveVariable(
                'hContactResultsColumnWidth',
                (int) $_GET['resultsWidth']
            );
        }

        $this->JSON(1);
    }

    public function saveInstructionsDefault()
    {
        # @return JSON

        # @description
        # <h2>Saving Instructions Default</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $this->user->saveVariable(
            'hContactInstructionsDefault',
            1
        );

        $this->JSON(1);
    }

    public function saveWindowDimensions()
    {
        # @return JSON
        # @description
        # <h2>Saving Window Dimensions</h2>
        # <p>
        #
        # </p>
        # @end

        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        $this->user
            ->saveVariable(
                'hContactWindowWidth',
                $_GET['width']
            )
            ->saveVariable(
                'hContactWindowHeight',
                $_GET['height']
            );

        $this->JSON(1);
    }
}

?>