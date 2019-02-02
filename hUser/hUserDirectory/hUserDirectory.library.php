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

class hUserDirectoryLibrary extends hPlugin {

    private $hUserDatabase;
    private $hContactDirectory;
    private $hContactDatabase;
    private $hUserValidation;
    private $hUserLogin;
    private $hFileCache;
    private $userName;
    private $groups = array();
    private $prependDomain;
    private $domain;
    private $separator;

    private $loginUserName;

    public function hConstructor($arguments)
    {
        if (isset($arguments['userName']))
        {
            $this->syncAccount(
                $arguments['userName'],
                isset($arguments['password'])? $arguments['password'] : null
            );
        }
    }

    public function syncAccount($userName, $password)
    {
        # @return void

        # @description
        # <h2>Syncing An Account</h2>
        # <p>
        #   This method syncs a directory account from Open Directory or Active Directory
        #   with Hot Toddy at login time. Syncing enables Hot Toddy to utilize directory
        #   login, accounts, and group permissions.
        # </p>
        # @end

        if (!empty($userName))
        {
            $userName = trim(strtolower($userName));

            $this->loginUserName = $userName;

            $userId = $this->user->getUserId($userName);
            $userName = $this->getDirectoryUserName();

            if (empty($userId))
            {
                 $userId = $this->hUserAliases->selectColumn(
                     'hUserId',
                     array(
                         'hUserNameAlias' => $userName
                     )
                 );
            }

            if (!empty($userId))
            {
                $userUniqueId = $this->hUserDirectory->selectColumn(
                    'hUserDirectoryId',
                    array(
                        'hUserId' => $userId
                    )
                );
            }

            $this->console("userId From Alias: {$userId}");

            $this->console("User Directory Sync: {$userName}");

            $this->hContactDatabaseSyncDirectory = false;

            $this->hUserDatabase = $this->database('hUser');
            $this->hUserValidation = $this->library('hUser/hUserValidation');
            $this->hContactDatabase = $this->database('hContact');
            $this->hContactDirectory = $this->library('hContact/hContactDirectory');
            $this->hUserLogin = $this->library('hUser/hUserLogin');
            $this->hFileCache = $this->library('hFile/hFileCache');

            $this->prependDomain = $this->hContactDirectoryPrependDomain(false);
            $this->domain = $this->hContactDirectoryDomain(null);
            $this->separator = $this->hContactDirectorySeparator("\\");
            $this->userName = $userName;

            $networkUsername = hString::entitiesToUTF8($userName, false);
            $userPassword = $password;

            if ($this->prependDomain)
            {
                 if (substr($userName, 0, strlen($this->prependDomain.$this->separator)) != $this->prependDomain.$this->separator)
                 {
                     return;
                 }
            }

            $emailDomain = $this->hContactDirectoryEmailDomain('localhost');
            $userGroupId = $this->user->getUserId('Employees');

            $this->console("Employees userGroupId: {$userGroupId}");

            $this->hContactDirectory->setUser($userName, true);

            $names = $this->hContactDirectory->getRecordNames();

            $this->console("Default Username: {$userName}");

            $id = $this->pipeCommand(
                '/usr/bin/id',
                escapeshellarg(
                    (!empty($this->domain)? $this->domain.$this->separator : '').$userName
                )
            );

            $this->console("Groups: {$id}");

            preg_replace_callback(
                "/\(([^{}]+|(?R))*\)/U",
                array(
                    $this,
                    'parseGroups'
                ),
                $id
            );

            $userEmail = $this->hContactDirectory->getEmailAddress();

            $saveEmailAddress = true;

            if (empty($userEmail) || !$this->hUserValidation->isValidEmailAddress($userEmail))
            {
                $matches = array();
                preg_match_all("/\w/", $userName, $matches);

                if (isset($matches[0]) && is_array($matches[0]))
                {
                    $userEmail = implode('', $matches[0]).'@'.$emailDomain;

                    if (!$this->hUserValidation->isValidEmailAddress($userEmail))
                    {
                        return;
                    }
                }
                else
                {
                    return;
                }

                $saveEmailAddress = false;
            }

            if (empty($userId))
            {
                $userId = $this->user->getUserId($userName);
            }

            $this->console("userId Determination: {$userId}");

            $userExists = $userId > 0;

            if (empty($userPassword) && !$userExists)
            {
                $userPassword = $this->getRandomString(10, true, true);
            }

            $response = $this->hUserDatabase->save(
                $userId,
                $userName,
                $userEmail,
                $userPassword
            );

            if ($response > 0)
            {
                $userId = $response;

                $this->userName = $userName;

                $this->hUserDatabase->addUserToGroup($userGroupId, $userId);

                if (!$userExists)
                {
                    $userInfo = $this->pipeCommand(
                        '/usr/bin/dscacheutil',
                        '-q user -a name '.escapeshellarg($networkUsername),
                        1,
                        false
                    );

                    $this->hUserDatabase->saveUnixProperties(
                        $userId,
                        $this->getUId($userInfo),
                        $this->getGId($userInfo),
                        $this->getHome($userInfo),
                        $this->getShell($userInfo)
                    );
                }

                $uniqueIdExists = $this->hUserDirectory->selectExists(
                    'hUserDirectoryId',
                    array(
                        'hUserId' => $userId
                    )
                );

                if (!$uniqueIdExists)
                {
                    $this->hUserDirectory->insert(
                        array(
                            'hUserId' => $userId,
                            'hUserDirectoryId' => $this->getUniqueId()
                        )
                    );
                }

                $this->hContactDatabase->setDuplicateFields(false);

                $contactId = $this->hContactDatabase->save(
                    array(
                        'hContactFirstName' => $this->hContactDirectory->getFirstName(),
                        'hContactLastName' => $this->hContactDirectory->getLastName(),
                        'hContactDisplayName' => $this->hContactDirectory->getName(),
                        'hContactCompany' => $this->hContactDirectory->getCompany(),
                        'hContactTitle' => $this->hContactDirectory->getTitle(),
                        'hContactDepartment' => $this->hContactDirectory->getDepartment()
                    ), 1, $userId, $this->user->getContactId($userId)
                );

                $this->hContactDatabase->saveAddress(
                    array(
                        'hContactAddressStreet' => $this->hContactDirectory->getStreet(),
                        'hContactAddressCity' => $this->hContactDirectory->getCity(),
                        'hLocationStateId' => $this->hContactDirectory->getState(),
                        'hContactAddressPostalCode' => $this->hContactDirectory->getPostalCode(),
                        'hLocationCountryId' => $this->hContactDirectory->getCountry()
                    ),
                    2
                );

                $phoneNumber = $this->hContactDirectory->getPhoneNumber();

                if (!empty($phoneNumber))
                {
                    $this->hContactDatabase->savePhoneNumber($phoneNumber, 6);
                }

                $mobileNumber = $this->hContactDirectory->getMobileNumber();

                if (!empty($mobileNumber))
                {
                    $this->hContactDatabase->savePhoneNumber($mobileNumber, 5);
                }

                $pagerNumber = $this->hContactDirectory->getPagerNumber();

                if (!empty($pagerNumber))
                {
                    $this->hContactDatabase->savePhoneNumber($pagerNumber, 10);
                }

                $faxNumber = $this->hContactDirectory->getFaxNumber();

                if (!empty($faxNumber))
                {
                    $this->hContactDatabase->savePhoneNumber($faxNumber, 9);
                }

                if ($saveEmailAddress)
                {
                    $this->hContactDatabase->saveEmailAddress($userEmail, 20);
                }

                $this->hUserAliases->delete('hUserId', $userId);

                foreach ($names as $name)
                {
                    if (trim(strtolower($name)) !== trim(strtolower($userName)))
                    {
                        $userAliasId = $this->hUserAliases->selectColumn(
                            'hUserId',
                            array(
                                'hUserNameAlias' => trim(addslashes($name))
                            )
                        );

                        if (empty($userAliasId))
                        {
                            $this->hUserAliases->insert(
                                array(
                                    'hUserId' => $userId,
                                    'hUserNameAlias' => trim(addslashes($name))
                                )
                            );
                        }
                    }
                }

                $syncGroups = $this->hContactDirectorySyncGroups(null);
                $groupPrefix = $this->hContactDirectoryGroupPrefix(null).$this->separator;

                $this->console("Directory group sync prefix is: ".addslashes($groupPrefix));

                if (is_array($syncGroups))
                {
                    foreach ($syncGroups as $group)
                    {
                        $this->console("Directory sync group: {$group}");
                    }
                }

                foreach ($this->groups as $userGroup)
                {
                    $this->console("Syncing user {$userName} to directory group: {$userGroup}");

                    if (!empty($userGroup))
                    {
                        if ($groupPrefix)
                        {
                            $this->console("Checking group prefix ".addslashes(substr($userGroup, 0, strlen($groupPrefix))).' != '.addslashes($groupPrefix));

                            if (substr($userGroup, 0, strlen($groupPrefix)) != $groupPrefix)
                            {
                                if (is_array($syncGroups))
                                {
                                    if (!in_array($userGroup, $syncGroups))
                                    {
                                        continue;
                                    }
                                }
                                else
                                {
                                    continue;
                                }
                            }
                        }
                        else if (is_array($syncGroups) && !in_array($userGroup, $syncGroups))
                        {
                            continue;
                        }

                        $userGroupPassword = $this->getRandomString(15, true, true);

                        $userGroupEmail = $this->getScrubbedGroupName($userGroup).'@localhost';

                        $userGroupId = $this->user->getUserId($userGroup);

                        if ($userGroupId <= 0)
                        {
                            $userGroupId = $this->hUserDatabase->save(
                                $userGroupId,
                                $userGroup,
                                $userGroupEmail,
                                $userGroupPassword
                            );

                            $this->console("Directory group {$userGroup} has hUserId: ".$userGroupId);

                            if ($userGroupId > 0)
                            {
                                $this->hUserDatabase->saveGroupProperties(
                                    $userGroupId,
                                    1,
                                    1,
                                    $this->hUserLogin->md5EncryptPassword($userGroupPassword),
                                    0
                                );

                                $groupInfo = $this->pipeCommand(
                                    '/usr/bin/dscacheutil',
                                    '-q group -a name '.escapeshellarg($userGroup),
                                    1,
                                    false
                                );

                                $this->hUserDatabase->saveUnixProperties(
                                    $userGroupId,
                                    0,
                                    $this->getGId($groupInfo),
                                    '',
                                    ''
                                );
                            }
                            else
                            {
                                $this->console("Failed to Sync Group: '{$userGroup}' Response: {$userGroupId}");
                            }
                        }

                        $this->hUserDatabase->addUserToGroup($userGroupId, $userId);
                    }

                    $groups = $this->getGroupMembership($userId, array(), false);

                    $this->console("Reviewing group membership for ".$userName);

                    foreach ($groups as $group)
                    {
                        $userGroup = addslashes($this->user->getUserName($group));

                        $this->console("User is a member of ".$userGroup);

                        if (!in_array($userGroup, $this->groups) && (is_array($syncGroups) && in_array($userGroup, $syncGroups) || substr($userGroup, 0, strlen($groupPrefix)) == $groupPrefix))
                        {
                            $exists = $this->hUserUnixProperties->selectExists(
                                'hUserId',
                                array(
                                    'hUserId' => $group
                                )
                            );

                            if ($exists)
                            {
                                $this->console("Removing user from group: ".$userGroup);
                                $this->hUserDatabase->removeUserFromGroup($group, $userId);
                            }
                        }
                    }
                }
            }
            else
            {
                $this->hUserDatabase->authenticationLog(
                    "Account Sync Error: The directory account could not be synced because hUserDatabase::save() responded with error {$response}"
                );

                $this->hUserAuthenticationLog->insert(
                    array(
                        'hUserId' => $userId,
                        'hUserName' => $userName,
                        'hUserEmail' => $this->user->getUserEmail($userId),
                        'hUserAuthenticationError' => "Account Sync Error: The directory account could not be synced because hUserDatabase::save() responded with error {$response}",
                        'hUserAuthenticationTime' => time()
                    )
                );
            }
        }
    }

    public function getUserName()
    {
        # @return string

        # @description
        # <h2>Getting the Current User Name</h2>
        # <p>
        #   Returns the user name of the current user.
        # </p>
        # @end

        return $this->userName;
    }

    private function getScrubbedGroupName($group)
    {
        # @return string

        # @description
        # <h2>Getting a Group Name</h2>
        # <p>
        #   Returns a group name with special characters removed.
        # </p>
        # @end

        $matches = array();
        preg_match_all('/\w+/', $group, $matches);
        return strtolower(implode('', $matches[0]));
    }

    private function getUId($string)
    {
        # @return integer

        # @description
        # <h2>Getting the User's UID</h2>
        # <p>
        #   Returns the user's Unix UID.
        # </p>
        # @end

        $matches = array();
        preg_match('/^uid\: (\d*)$/m', $string, $matches);
        return isset($matches[1])? $matches[1] : 0;
    }

    private function getGId($string)
    {
        # @return integer

        # @description
        # <h2>Getting the User's GID</h2>
        # <p>
        #   Returns the user's Unix GID.
        # </p>
        # @end

        $matches = array();
        preg_match('/^gid\: (\d*)$/m', $string, $matches);
        return isset($matches[1])? $matches[1] : 0;
    }

    private function getHome($string)
    {
        # @return string

        # @description
        # <h2>Getting the User's Home Directory</h2>
        # <p>
        #   Returns the path to the user's home directory.
        # </p>
        # @end

        $matches = array();
        preg_match('/^dir\: (.*)$/m', $string, $matches);
        return isset($matches[1])? $matches[1] : '';
    }

    private function getShell($string)
    {
        # @return string

        # @description
        # <h2>Getting the User's Shell</h2>
        # <p>
        #   Returns the user's default shell.
        # </p>
        # @end

        $matches = array();
        preg_match('/^shell\: (.*)$/m', $string, $matches);
        return isset($matches[1])? $matches[1] : '';
    }

    public function parseGroups($matches)
    {
        # @return void

        # @description
        # <h2>Parsing Groups</h2>
        # <p>
        #   Parses the user's member groups and adds them to an internal
        #   <var>$groups</var> property array.
        # </p>
        # @end

        $group = addslashes(substr($matches[0], 1, -1));

        if (!in_array($group, $this->groups) && $group != $this->userName)
        {
            array_push($this->groups, $group);
        }
    }

    public function getDirectoryUserName()
    {
        # @return string

        # @description
        # <h2>Getting the User's Directory User Name</h2>
        # <p>
        #   Returns the user's directory user name, which is the main user name
        #   (rather than an alias) attached to the user's directory account.
        # </p>
        # @end

        return strtolower(
            $this->getKey('dsAttrTypeNative:sAMAccountName')
        );
    }

    public function getUniqueId()
    {
        # @return

        # @description
        # <h2>Getting the User's Unique Id</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getKey('UniqueID');
    }

    public function getKey($key, $encodeResult = true)
    {
        # @return string

        # @description
        # <h2>Getting an Account Key</h2>
        # <p>
        #   Returns the specified <var>$key</var>, which is data attached to the user's
        #   directory account such as the first name, last name, address, etc.
        # </p>
        # @end

        $result = $this->pipeCommand(
            '/usr/bin/dscl',
            ($this->hContactDirectoryAdministratorUser(null)? '-u '.escapeshellarg($this->hContactDirectoryAdministratorUser(null)).' ' : '').
            ($this->hContactDirectoryAdministratorPassword(null)? '-P '.escapeshellarg($this->hContactDirectoryAdministratorPassword(null)).' ' : '').
            escapeshellarg($this->hContactDirectoryPath('.')).' '.
            '-read '.escapeshellarg('/Users/'.$this->loginUserName).' '.$key,
            1,
            false
        );

        $result = strstr($result, 'No such key:')? '' : trim(substr($result, strlen($key.':')));

        return $encodeResult? hString::encodeHTML($result) : $result;
    }
}

?>