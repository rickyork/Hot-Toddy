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
# <h1>User Address Book Plugin</h1>
# <p>
#
# </p>
# @end

class hUser extends hPlugin implements hContactApplication {

    private $hSpotlightSearch;
    private $tables = array();

    private $hUserDatabase;
    private $hUserLogin;
    private $hUserPrivate;
    private $hUserSave;
    private $hContactDatabase;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>User Plugin Constructor</h2>
        # <p>
        #
        # </p>
        # @end

        if (rand(1, 100) == 1)
        {
            // Clean-up your mess!
            $contacts = $this->hContacts->select(
                array(
                    'hContactId',
                    'hUserId'
                ),
                array(
                    'hContactAddressBookId' => 1
                )
            );

            $this->hContactDatabase = $this->database('hContact');

            foreach ($contacts as $contact)
            {
                $exists = $this->hUsers->selectExists(
                    'hUserId',
                    $contact['hUserId']
                );

                if (!$exists)
                {
                    $this->hContactDatabase->delete(
                        (int) $contact['hContactId']
                    );
                }
            }
        }

        $this->getPluginFiles();
        $this->getPluginCSS('hSearch');

        $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');
        $this->getSearchColumns();

        $this->hUserDatabase = $this->database('hUser');

        $this->hSpotlightSearch->addWhereCondition('`hContacts`.`hContactAddressBookId` = 1');
        $this->hSpotlightSearch->addWhereCondition('`hUsers`.`hUserId` > 0');
    }

    public function &getSearchColumns()
    {
        # @return hUser

        # @description
        # <h2>Setting up Search Fields</h2>
        # <p>
        #   Sets up the search and advanced search panels, defining what and how a user can search user
        #   contacts within the Contacts application.
        # </p>
        # @end

        $this->hSpotlightSearch
            ->addTable(
                'hUsers',
                'Login Information'
            )
            ->defineJoinColumns(
                'hUserId',
                'hUserGroupId',
                'hUserAccountLastModifiedBy',
                'hUserGroupOwner'
            )
            ->addDefaultColumns(
                array(
                    'hUserId' => 'User Id',
                    'hUserName' => 'User Name',
                    'hUserEmail' => 'Email Address'
                )
            )
            ->addTable(
                'hUserVariables',
                'Login Variables'
            )
            ->addAdvancedColumns(
                array(
                    'hUserId' => 'User Id',
                    'hUserVariable' => 'Variable',
                    'hUserValue' => 'Value'
                )
            )
            ->addTable(
                'hUserLog',
                'User Activity Log'
            )
            ->addAdvancedColumn(
                'hUserId',
                'User Id'
            )
            ->addSortableColumn(
                'hUserLoginCount',
                'Login Count'
            )
            ->addAdvancedColumn(
                'hUserAccountLastModifiedBy',
                'Last Modified By'
            )
            ->addTimeColumns(
                array(
                    'hUserAccountCreated' => 'Account Created',
                    'hUserAccountLastLogin' => 'Last Login',
                    'hUserAccountLastModified' => 'Last Modified'
                )
            )
            ->addTable(
                'hUserGroups',
                'Groups'
            )
            ->addAdvancedColumns(
                array(
                    'hUserGroupId' => 'Group Id',
                    'hUserId' => 'User Id'
                )
            )
            ->addTable(
                'hUserGroupProperties',
                'Group Properties'
            )
            ->addAdvancedColumns(
                array(
                    'hUserId' => 'User Id',
                    'hUserGroupOwner' => 'Group Owner',
                    'hUserGroupIsElevated' => 'Elevated Group',
                    'hUserGroupLoginEnabled' => 'Login Enabled'
                )
            )
            ->addTable(
                'hUserUnixProperties',
                'Unix Login Properties'
            )
            ->addAdvancedColumns(
                array(
                    'hUserUnixUId' => 'Unix UId',
                    'hUserUnixGId' => 'Unix GId',
                    'hUserUnixHome' => 'Unix Home Path',
                    'hUserUnixShell' => 'Unix Default Shell'
                )
            );

        return $this;
    }

    public function isAuthorized()
    {
        # @return boolean

        # @description
        # <h2>Determining if a User is Authorized</h2>
        # <p>
        #   Determines if a user is authorized to access the address book.
        # </p>
        # @end

        return ($this->isLoggedIn() && $this->inGroup('User Administrators'));
    }

    public function &query($search, &$where, $time, $sort, $sortOrientation, &$results)
    {
        # @return hUser

        # @descrpition
        # <h2>Executing a Query of Users</h2>
        # <p>
        #   Optional method for making a custom query.
        # </p>
        # @end

        $this->hSpotlightSearch
            ->setColumnSelected('hContact', 'hUserId')
            ->query(
                '',
                $search,
                $where,
                $time,
                $sort,
                $sortOrientation,
                '`hUsers`.`hUserId`',
                $results
            );

        return $this;
    }

    public function &queryGroup($addressBookId, $groupId, $sort, $sortOrientation, &$results)
    {
        $this->hSpotlightSearch
            ->setColumnSelected('hContact', 'hUserId')
            ->setColumnSelected('hContact', 'hContactId');

        $select = $this->hSpotlightSearch->getSelectColumns();

        if (!empty($sort))
        {
            $bits = explode('.', $sort);
            $bit  = array_shift($bits);

            $this->hSpotlightSearch->addTableToQuery($bit);
        }

        $this->hSpotlightSearch->addTableToQuery("`hUserGroups`");

        $from = $this->hSpotlightSearch->getTablesInQuery();

        if (is_array($from) && is_array($select))
        {
            $query = $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'select' => implode(',', $select),
                        'includeUserLog' => in_array("`hUserLog`", $from),
                        'groupId' => (int) $groupId,
                        'sort' => $sort,
                        'sortOrientation' => $sortOrientation
                    )
                )
            );

            while ($data = $this->hDatabase->getAssociativeResults($query))
            {
                $results[$data['hUserId']] = $data;
            }

            $this->hDatabase->closeResults($query);
        }

        return $this;
    }

    public function &queryLocation($search, $location, $where, $time, $sort, $sortOrientation, &$results)
    {
        $this->hSpotlightSearch
            ->setColumnSelected('hContact', 'hUserId')
            ->setColumnSelected('hContact', 'hContactId');

        if (empty($where))
        {
            $where = $this->hSpotlightSearch->getDefaultColumns(null);
        }

        if (!empty($sort))
        {
            $bits = explode('.', $sort);
            $this->hSpotlightSearch->addTableToQuery(array_shift($bits));
        }

        $select = $this->hSpotlightSearch->getSelectColumns();

        $constrainTime = (count($time)? $this->hSpotlightSearch->getTime($time, $join) : '');

        if (false !== ($where = $this->hSpotlightSearch->getWhereClause($where, $search)))
        {
            $this->hSpotlightSearch->addTableToQuery("`hContactAddresses`");

            if (!empty($location['county']))
            {
                $this->hSpotlightSearch->addTableToQuery("`hLocationCounties`");
            }

            $from = $this->hSpotlightSearch->getTablesInQuery();

            $query = $this->hDatabase->query(
                $this->getTemplateSQL(
                    array_merge(
                        array(
                            'select' => implode(',', $select),
                            'from' => implode(',', $from),
                            'includeUserLog' => in_array(
                                "`hUserLog`",
                                $from
                            ),
                            'where' => $where,
                            'constrainTime' => $constrainTime,
                            'sort' => $sort,
                            'sortOrientation' => $sortOrientation
                        ),
                        $location
                    )
                )
            );

            if ($this->hDatabase->resultsExist($query))
            {
                while ($data = $this->hDatabase->getAssociativeResults($query))
                {
                    $results[$data['hUserId']] = $data;
                }
            }

            $this->hDatabase->closeResults($query);
        }

        return $this;
    }

    public function ammendResults($results)
    {
        return $this->hSpotlightSearch->ammendResults(
            $results,
            'hUsers'
        );
    }

    public function getGroups()
    {
        $groups = $this->hUserDatabase->getGroups(
            $this->hContactUserGroups(0),
            false,
            '',
            $this->hContactGroupsSort('Alphabetically')
        );

        foreach ($groups as $userId => $userName)
        {
            $this->fixGroupContactRecords($userId);
        }

        $return = array();

        foreach ($groups as $userId => $userName)
        {
            $contactId = $this->user->getContactId($userId);

            $addresses = $this->contact->getAddresses($contactId);

            if (count($addresses))
            {
                $address = array_shift($addresses);

                if (empty($address['hContactAddressStreet']) && empty($address['hLocationAddressCity']) && empty($address['hContactAddressPostalCode']))
                {
                    $address = array(
                        'hContactAddress' => false
                    );
                }
                else
                {
                    $state = $address['hLocationStateName'];

                    if (!empty($address['hLocationUseStateCode']))
                    {
                        $state = $address['hLocationStateCode'];
                    }

                    $address['hContactAddress'] = str_replace(
                        array(
                            '|',
                            '{$street}',
                            '{$city}',
                            '{$state}',
                            '{$postalCode}',
                            '{$country}'
                        ),
                        array(
                            '</li><li>',
                            $address['hContactAddressStreet'],
                            $address['hContactAddressCity'],
                            $state,
                            $address['hContactAddressPostalCode'],
                            $address['hLocationCountryName']
                        ),
                        $address['hContactAddressTemplate']
                    );
                }
            }
            else
            {
                $address = array(
                    'hContactAddress' => false
                );
            }

            $return[$userName] = array_merge(
                array(
                    'hUserId' => $userId,
                    'hUserName' => $userName,
                    'hContactId' => $contactId
                ),
                $address
            );
        }

        ksort($return);

        return $this->hDatabase->getResultsForTemplate($return);
    }

    public function getContactForm(&$hForm)
    {
        if (!$this->inAnyOfTheFollowingGroups(array('Website Administrators', 'Contact Administrators')))
        {
            return;
        }

        //$hForm->addDiv('hUserNewDiv', 'Login Info');
        $hForm
            ->addFieldset(
                'Login',
                '400px',
                '150px,',
                'hUserRegistrationSetId'
            )

            ->addTextInput(
                'hUserName',
                'Username:',
                20
            )
            ->addTextInput(
                'hUserEmail',
                'Email Address:',
                25
            )

            ->addPasswordInput(
                'hUserPassword',
                'Password:',
                20
            )
            ->addPasswordInput(
                'hUserPasswordConfirm',
                'Confirm Password:',
                20
            );

        if ($this->hUserEnableGroupProperties(true))
        {
            $hForm
                ->addTextInput(
                    'hUserGroupOwner',
                    'Group Owner:',
                    20
                )
                ->addPasswordInput(
                    'hUserGroupPassword',
                    'Group Password:',
                    20
                )
                ->addPasswordInput(
                    'hUserGroupConfirmPassword',
                    'Group Confirm Password:',
                    20
                )
                ->addTableCell('')
                ->addCheckboxInput(
                    'hUserGroupIsElevated',
                    'Elevated?'
                )
                ->addTableCell('')
                ->addCheckboxInput(
                    'hUserGroupLoginEnabled',
                    'Enable Login?'
                );
        }
        else
        {
            $hForm
                ->addHiddenInput('hUserGroupOwner')
                ->addHiddenInput('hUserGroupIsElevated')
                ->addHiddenInput('hUserGroupPassword')
                ->addHiddenInput('hUserGroupLoginEnabled');
        }

        if (!$this->hUserEnableGroupSelection(true))
        {
            $this->hFileCSS .= $this->getTemplate('Hide Group Selection');
        }

        $hForm
            ->addFieldset(
                'Groups',
                '400px',
                '150px,',
                'hUserGroupSelection'
            )
            ->addSelectInput(
                array(
                    'id' => 'hUserMemberGroups',
                    'multiple' => 'multiple'
                ),
                'Member of Groups: -L',
                array(),
                4
            )

            ->addTableCell('')
            ->addTableCell(
                "<input type='submit' id='hUserGroupAdd' value='&uarr;' />".
                "<input type='submit' id='hUserGroupRemove' value='&darr;' />"
            )
            ->addSelectInput(
                array(
                    'id' => 'hUserGroups',
                    'multiple' => 'multiple'
                ),
                "<img src='/images/icons/32x32/everyone.png' alt='Everyone' /> -L",
                $this->hUserDatabase->getGroups(
                    0,
                    false,
                    '',
                    $this->hContactGroupsSort('Alphabetically')
                ),
                10
            );

        if ($this->hUserPrivatePlugin(nil))
        {
            $this->plugin($this->hUserPrivatePlugin)
                 ->getContactForm($hForm);
        }

        $hForm
            ->addDiv(
                'hUserLoginData',
                'Account Activity'
            )
            ->addFieldset(
                'Account Activity',
                '100%',
                '150px,',
                'hUserAccountActivity'
            )

            ->addData(
                'hUserLogId',
                'User Id:'
            )
            ->addData(
                'hUserCreated',
                'Created:'
            )
            ->addData(
                'hUserLoginCount',
                'Number of Logins:'
            )
            ->addData(
                'hUserLastLogin',
                'Last Login:'
            )
            ->addData(
                'hUserLastFailedLogin',
                'Last Failed Login:'
            )
            ->addData(
                'hUserLastModified',
                'Last Modified:'
            )
            ->addData(
                'hUserLastModifiedBy',
                'Last Modified By:'
            )

            ->addFieldset(
                'Recently Viewed Documents',
                '100%',
                '100%',
                'hUserRecentlyViewedDocuments'
            )

            ->addTableCell(
                $this->getTemplate('History Table')
            )

            ->addFieldset(
                'Recent Activity',
                '100%',
                '100%',
                'hUserActivity'
            )

            ->addTableCell(
                $this->getTemplate('Recent Activity Table')
            );
    }

    public function getResultsHTML(array $results)
    {
        # @return HTML

        # @description
        # <h2>Retrieving User Search Results as HTML</h2>
        # <p>
        #   Takes an array of user search results and formats them as HTML.
        # </p>
        # @end

        $html = '';

        if (isset($results['key']))
        {
            unset($results['key']);
        }

        $resultCounter = 1;

        $disabledAccounts = '';

        foreach ($results as $key => $data)
        {
            $data['hContactRecordOdd'] = ($resultCounter & 1);

            $isGroup = $this->isGroup($data['hUserId']);

            if ($isGroup && empty($data['hContactId']))
            {
                // See if the group has a contact record...
                // if it doesn't make one on the fly...
                $data['hContactId'] = $this->fixGroupContactRecords($data['hUserId']);
            }

            $data['hContactResultEmailAddressEnabled'] = $this->hContactResultEmailAddressEnabled(true);

            $data['hContactResultUserNameEnabled'] = $this->hContactResultUserNameEnabled(
                $this->inAnyOfTheFollowingGroups(
                    array(
                        'Website Administrators',
                        'Contact Administrators'
                    )
                )
            );

            $result = $this->getTemplate(
                ($isGroup? 'Group ' : '').'Result',
                $data
            );

            if ($this->inGroup('Disabled User Accounts', $data['hUserId'], false))
            {
                $disabledAccounts .= $result;
            }
            else
            {
                $html .= $result;
            }

            $resultCounter++;
        }

        if ($disabledAccounts)
        {
            $html =
                $html.
                $this->getTemplate(
                    'Disabled Accounts',
                    array(
                        'disabledAccounts' => $disabledAccounts
                    )
                );
        }

        return $html;
    }

    public function getResultTemplates()
    {
        return(
            $this->getTemplate(
                'Group Result',
                array(
                    'hContactRecordOdd' => false,
                    'hContactRecordTemplate' => true,
                    'hContactId' => 0,
                    'hUserId' => 0,
                    'hUserName' => ''
                )
            ).
            $this->getTemplate(
                'Result',
                array(
                    'hContactRecordOdd' => false,
                    'hContactRecordTemplate' => true,
                    'hContactId' => -1,
                    'hUserId' => -1,
                    'hContactFirstName' => '',
                    'hContactLastName' => '',
                    'hContactTitle' => '',
                    'hContactCompany' => '',
                    'hUserEmail' => '',
                    'hUserName' => ' '
                )
            )
        );
    }

    public function fixGroupContactRecords($userId)
    {
        # @return integer

        # @description
        # <h2>Fixing Group Contact Records</h2>
        # <p>
        #   Corrects group records that do not have a corresponding contact entry in the
        #   <var>hContacts</var> table by inserting a record in that table.
        # </p>
        # @end

        $contactExists = $this->hContacts->selectExists(
            'hContactId',
            array(
                'hUserId' => (int) $userId
            )
        );

        if (!$contactExists)
        {
            return $this->hContacts->insert(
                array(
                    'hContactAddressBookId' => 1,
                    'hContactId' => 0,
                    'hUserId' => (int) $userId,
                    'hContactFirstName' => '',
                    'hContactLastName' => '',
                    'hContactDisplayName' => '',
                    'hContactNickName' => '',
                    'hContactWebsite' => '',
                    'hContactCompany' => '',
                    'hContactTitle' => '',
                    'hContactDepartment' => '',
                    'hContactGender' => 0,
                    'hContactDateOfBirth' => 0,
                    'hContactCreated' => time(),
                    'hContactLastModified' => 0
                )
            );
        }

        return 0;
    }

    public function save(&$response, $contactId)
    {
        $response = 1;

        if (empty($_POST['hUserName']) || empty($_POST['hUserEmail']) && empty($_POST['hUserIsGroup']))
        {
            $response = -5;
            return;
        }

        $this->hUserLogin = $this->library('hUser/hUserLogin');

        $isNewUser = empty($_POST['hUserId']);

        if (!empty($_POST['hUserIsGroup']))
        {
            if ($isNewUser)
            {
                $_POST['hUserPassword'] = $this->getRandomString(15, true, true);
            }

            $matches = array();

            preg_match_all('/(\w)+/', $_POST['hUserName'], $matches);

            $_POST['hUserEmail'] = implode('', $matches[0]).'@localhost';
        }

        $userId = $this->hUserDatabase->save(
            (int) $_POST['hUserId'],
            $_POST['hUserName'],
            $_POST['hUserEmail'],
            $_POST['hUserPassword'],
            true
        );

        if ($userId <= 0)
        {
            $response = $userId;
            return;
        }

        // 7710, 7627
        if (!empty($_POST['hUserIsGroup']))
        {
            if ($isNewUser && empty($_POST['hUserGroupPassword']))
            {
                $_POST['hUserGroupPassword'] = $this->getRandomString(15, true, true);
            }

            $userGroupOwner = 1;

            if (!empty($_POST['hUserGroupOwner']))
            {
                $userGroupOwner = $this->user->getUserId($_POST['hUserGroupOwner']);
            }

            $userGroupIsElevated = 0;

            if (isset($_POST['hUserGroupIsElevated']))
            {
                $userGroupIsElevated = (int) $_POST['hUserGroupIsElevated'];
            }

            $userGroupPassword = nil;

            if (!empty($_POST['hUserGroupPassword']))
            {
                $userGroupPassword = $this->hUserLogin->md5EncryptPassword($_POST['hUserGroupPassword']);
            }

            $userGroupLoginEnabled = 0;

            if (isset($_POST['hUserGroupLoginEnabled']))
            {
                $userGroupLoginEnabled = (int) $_POST['hUserGroupLoginEnabled'];
            }

            $this->hUserDatabase->saveGroupProperties(
                $userId,
                $userGroupOwner,
                $userGroupIsElevated,
                $userGroupPassword,
                $userGroupLoginEnabled
            );

            if ($this->hUserAutoGroupMembership(null))
            {
                if (!isset($_POST['hUserGroups']) || !is_array($_POST['hUserGroups']))
                {
                    $_POST['hUserGroups'] = array();
                }

                array_push(
                    $_POST['hUserGroups'],
                    $this->getGroupId($this->hUserAutoGroupMembership)
                );
            }
        }

        if (isset($_POST['hUserGroups']) && is_array($_POST['hUserGroups']))
        {
            $this->hUserDatabase->addUserToGroups(
                $_POST['hUserGroups'],
                $userId
            );
        }
        else
        {
            $this->hUserDatabase->removeUserFromGroups($userId, false);
        }

        if ($this->hUserSavePlugin(null))
        {
            $this->hUserSave = $this->plugin($this->hUserSavePlugin);

            $this->hUserSave->save(
                $response,
                $contactId,
                $userId
            );
        }

        $_POST['hUserId'] = $userId;
    }

    public function delete($response)
    {
        $userId = $this->contact->getUserId((int) $_GET['hContactId']);
        $this->hUserDatabase->delete($userId);

        $response = 1;
    }
}

?>