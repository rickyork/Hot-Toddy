<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Database Library
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
# @description
# <h1>User Database API</h1>
# <p>
#   Provides an API for creating, managing, and deleting users and user groups and
#   user and user group related information.
# </p>
# @end

class hUserDatabase extends hPlugin {

    private $hUserLogin;
    private $hUserValidation;
    private $hUserHomeFolder;
    private $activityCount = 0;
    private $hSearch;
    private $hPagination;
    private $hSearchDatabase;

    public function &deleteGroupMembers($userGroupId)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Deleting Group Members</h2>
        # <p>
        #   Deletes all members from the group specified in <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> can be either a <var>hUserId</var> or <var>hUserName</var>.
        # </p>
        # @end

        if (!is_numeric($userGroupId))
        {
            $userGroupId = $this->user->getUserId($userGroupId);
        }

        if ($userGroupId > 0)
        {
            $this->hUserGroups->delete('hUserGroupId', (int) $userGroupId);
        }

        return $this;
    }

    public function saveGroupMembers($userGroupId, array $users)
    {
        # @return array | boolean
        # <p>
        #   An array containing users passed in to be added to the group
        #   that were found to be invalid.  If no users are found to be
        #   invalid, the method will return <var>true</var>.  If problems
        #   with the arguments prevent members from being added, the method
        #   will return <var>false</var> and errors will be logged to the
        #   Error Console.
        # </p>
        # @end

        # @description
        # <h2>Saving Group Members</h2>
        # <p>
        #   This method takes a group id or group name specified in <var>$userGroupId</var>,
        #   removes all existing group members, and adds the group members specified in
        #   <var>$users</var>.
        # </p>
        # <p>
        #   <var>$users</var> should be an array containing one or more <var>userIds</var> or
        #   <var>userNames</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if ($userGroupId > 0)
        {
            $this->deleteGroupMembers($userGroupId);

            $invalidGroups = array();

            if (is_array($users))
            {
                foreach ($users as $userId)
                {
                    if (!is_numeric($userId))
                    {
                        $userName = $userId;
                        $userId = $this->user->getUserId($userId);
                    }

                    if (!empty($userId))
                    {
                        $this->hUserGroups->insert(
                            array(
                                'hUserGroupId' => (int) $userGroupId,
                                'hUserId'      => (int) $userId
                            )
                        );

                        $this->modifyUser($userGroupId);
                        $this->modifyUser($userId);
                    }
                    else
                    {
                        array_push($invalidGroups, $userName);
                    }
                }

                if (count($invalidGroups))
                {
                    return $invalidGroups;
                }
                else
                {
                    return true;
                }
            }
            else
            {
                $this->warning('Argument $users must be an array of user ids.', __FILE__, __LINE__);
                return false;
            }
        }
        else
        {
            $this->warning('Unable to obtain a group user id from the user name, '.$userGroupId.'.', __FILE__, __LINE__);
            return false;
        }
    }

    public function getUserGroups($userId = 0, $selectMethod = 'selectColumnsAsKeyValue')
    {
        # @return array

        # @description
        # <h2>Getting a User's Groups</h2>
        # <p>
        #    Returns the <var>hUserGroupId</var> and <var>hUserName</var> of each group the
        #    provided <var>$userId</var> is a member of.
        # </p>
        # <p>
        #   <var>$userId</var> can be a <var>hUserId</var>, <var>hUserName</var>, or
        #   <var>hUserEmail</var>.
        # </p>
        # <p>
        #    If no <var>$userId</var> is provided, the <var>$userId</var> of the user
        #    presently logged in is used instead.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if ($userId > 0)
        {
            return $this->hDatabase->$selectMethod(
                array(
                    'hUserGroups' => 'hUserGroupId',
                    'hUsers' => 'hUserName'
                ),
                array(
                    'hUserGroups',
                    'hUsers'
                ),
                array(
                    'hUserGroups.hUserId' => (int) $userId,
                    'hUserGroups.hUserGroupId' => 'hUsers.hUserId'
                )
            );
        }

        return array();
    }

    public function &removeUserFromGroups($userId = 0, $preserveRoot = true)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Removing a User From Groups</h2>
        # <p>
        #   Removes the specified <var>$userId</var> from all groups that user is
        #   a member of, except the <var>root</var> group, if the user is in the <var>root</var>
        #   group and the <var>$preserveRoot</var> argument is <var>true</var>.
        # </p>
        # <p>
        #   <var>$userId</var> can be a <var>hUserId</var>, <var>hUserName</var>, or
        #   <var>hUserEmail</var>.
        # </p>
        # <p>
        #    If no <var>$userId</var> is provided, the <var>$userId</var> of the user
        #    presently logged in is used instead.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if ($userId > 0)
        {
            $this->deleteGroupCache($userId);

            if ($preserveRoot && $this->inGroup('root'))
            {
                $root = $this->getGroupId('root');
            }

            $userGroups = $this->hUserGroups->select('hUserGroupId');

            foreach ($userGroups as $userGroupId)
            {
                $this->modifyUser($userGroupId);
                $this->deleteCachedGroupData($userGroupId);
            }

            $this->hUsers->activity('Removed: '.$this->user->getUserName($userId).' from all groups');

            $this->hUserGroups->delete('hUserId', (int) $userId);

            $this->modifyUser($userId);

            if (!empty($root))
            {
                $this->addUserToGroup($root, $userId);
            }
        }

        return $this;
    }

    public function &deleteGroupCache($userId = 0)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Deleting a Group Cache</h2>
        # <p>
        #   Deletes cached group values for the specified <var>$userId</var>, from the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserPermissionsCache/hUserPermissionsCache.sql'>hUserPermissionsCache</a>
        #   database table.
        # </p>
        # <p>
        #   Cached group values are also removed from properties in
        #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php'>hUserAuthenticationLibrary</a>
        # </p>
        # <p>
        #   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or
        #   <var>userEmail</var>.
        # </p>
        # @end

        $this->user
            ->whichUserId($userId)
            ->setNumericUserId($userId);

        if ($userId > 0)
        {
            $this->hUserPermissionsCache->delete(
                array(
                    'hUserId' => (int) $userId
                )
            );

            # Data cached in properties in hUserAuthenticationLibrary must be
            # deleted as well.
            $this->deleteCachedGroupData($userId);
        }

        return $this;
    }

    public function &removeUserFromGroup($userGroupId, $userId = 0)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Removing a User From a Group</h2>
        # <p>
        #   Removes the user specified in <var>$userId</var>, from the group
        #   specified in <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> and <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        #   If <var>$userId</var> is not provided, <var>$userId</var> becomes the
        #   <var>userId</var> of the user logged in.  If no user is logged in, the
        #   function will fail.  The relevant errors will be logged to Hot Toddy's
        #   error console.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId)
            ->setNumericUserId($userGroupId);

        if ($userId > 0 && $userGroupId > 0)
        {
            $this->deleteGroupCache($userId);
            $this->deleteCachedGroupData($userGroupId);

            if (!is_numeric($userGroupId))
            {
                $userGroupId = $this->getGroupId($userGroupId);
            }

            if (!is_numeric($userId))
            {
                $userId = $this->user->getUserId($userId);
            }

            if ($userGroupId > 0 && $userId > 0)
            {
                $this->hUserGroups->delete(
                    array(
                        'hUserGroupId' => (int) $userGroupId,
                        'hUserId' => (int) $userId
                    )
                );

                $this->modifyUser($userGroupId)->modifyUser($userId);

                $this->hUsers->activity('Removed: '.$this->user->getUserName($userId).' from Group: '.$this->user->getUserName($userGroupId));
            }
        }
        else
        {
            $this->notice("Failed to remove user from group. Either 'userGroupId' or 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &addGroupsToGroup($userGroupId, array $groups)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Add Groups to a Group</h2>
        # <p>
        #   Adds the groups specified in <var>$groups</var>, to the group
        #   specified in <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> and <var>$groups</var> (<var>$groups</var> is an array)
        #   can be any one of the following:
        #   <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        foreach ($groups as $group)
        {
            $this->addUserToGroup($userGroupId, $group, true);
        }

        return $this;
    }

    public function &addUsersToGroup($userGroupId, array $users)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Add Users to a Group</h2>
        # <p>
        #   Adds the users specified in <var>$users</var>, to the group
        #   specified in <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> and <var>$users</var> (<var>$users</var> is an array)
        #   can be any one of the following:
        #   <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        foreach ($users as $user)
        {
            $this->addUserToGroup($userGroupId, $user);
        }

        return $this;
    }

    public function &addGroupToGroup($userGroupId, $userId)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Add a Group to a Group</h2>
        # <p>
        #   Adds the group specified in <var>$userId</var>, to the group
        #   specified in <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> and <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        return $this->addUserToGroup($userGroupId, $userId, true);
    }

    public function &addUserToGroup($userGroupId, $userId = 0, $userIsGroup = false)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Add a User to a Group</h2>
        # <p>
        #   Adds the user specified in <var>$userId</var>, to the group
        #   specified in <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> and <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        #   If <var>$userId</var> is not provided, <var>$userId</var> becomes the
        #   <var>userId</var> of the user logged in.  If no user is logged in, the
        #   function will fail.  The relevant errors will be logged to Hot Toddy's
        #   error console.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId)
            ->setNumericUserId($userGroupId);

        if ($userGroupId > 0 && $userId > 0)
        {
            if ($this->isGroup($userGroupId))
            {
                $this->deleteGroupCache($userId);
                $this->deleteCachedGroupData($userGroupId);

                $this->removeUserFromGroup($userGroupId, $userId);

                if (!$userIsGroup || $userIsGroup && $this->isGroup($userId))
                {
                    $this->hUserGroups->insert(
                        array(
                            'hUserGroupId' => (int) $userGroupId,
                            'hUserId'      => (int) $userId
                        )
                    );
                }
                else
                {
                    $this->warning("Failed to add group '{$userId}' to group because it is not a group, or had no value.");
                }

                $this->modifyUser($userGroupId)->modifyUser($userId);

                $this->hUsers->activity('Added: '.$this->user->getUserName($userId).' to Group: '.$this->user->getUserName($userGroupId));
            }
            else
            {
                $this->warning("Failed to add user to group because 'userGroupId', (value: '{$userGroupId}') is not a group.", __FILE__, __LINE__);
            }
        }
        else
        {
            $this->notice("Failed to add user to group. Either 'userGroupId' or 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &modifyUser($userId = 0)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Modifying a User</h2>
        # <p>
        #   Modifies the user specified in <var>$userId</var>.  This sets the
        #   <var>hUserLastModified</var> and <var>hUserLastModifiedBy</var> values
        #   in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserLog/hUserLog.sql'>hUserLog</a>
        #   database table.  <var>hUserLastModified</var> is a Unix Timestamp.
        #   <var>hUserLastModifiedBy</var> is the <var>hUserId</var> of the user
        #   to last modify the account.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        #   If <var>$userId</var> is not provided, <var>$userId</var> becomes the
        #   <var>userId</var> of the user logged in.  If no user is logged in, the
        #   function will fail.  The relevant errors will be logged to Hot Toddy's
        #   error console.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if ($userId > 0)
        {
            $this->hUserLog->update(
                array(
                    'hUserLastModified' => time(),
                    'hUserLastModifiedBy' => isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 1
                ),
                $userId
            );
        }
        else
        {
            $this->notice("Unable to modify user because no 'userId' was provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &addUserToGroups(array $userGroups, $userId = 0)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Add a User To Multiple Groups</h2>
        # <p>
        #   Removes the user from <b>ALL</b> groups that user is presently a
        #   member of.
        # </p>
        # <p>
        #   Then adds the user specified in <var>$userId</var>, to the groups
        #   specified in <var>$userGroups</var>.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        #   The groups specified in <var>$userGroups</var> can be an array of
        #   <var>userGroupIds</var>, <var>userGroupNames</var>, or <var>userGroupEmails</var>.
        # </p>
        # <p>
        #   If <var>$userId</var> is not provided, <var>$userId</var> becomes the
        #   <var>userId</var> of the user logged in.  If no user is logged in, the
        #   function will fail.  The relevant errors will be logged to Hot Toddy's
        #   error console.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if ($userId > 0)
        {
            $this->deleteGroupCache($userId);

            $this->hUserGroups->delete('hUserId', (int) $userId);

            $userName = $this->user->getUserName($userId);

            foreach ($userGroups as $userGroupId)
            {
                $this->user->setNumericUserId($userGroupId);

                if ($userGroupId > 0)
                {
                    if ($this->isGroup($userGroupId))
                    {
                        $this->hUserGroups->insert(
                            array(
                                'hUserGroupId' => (int) $userGroupId,
                                'hUserId' => (int) $userId
                            )
                        );

                        $this->modifyUser($userGroupId);
                        $this->hUsers->activity('Added: '.$userName.' to Group: '.$this->user->getUserName($userGroupId));
                        $this->deleteCachedGroupData($userGroupId);
                    }
                    else
                    {
                        $this->warning("Failed to add user to group because 'userGroupId', (value: '{$userGroupId}') is not a group.", __FILE__, __LINE__);
                    }
                }
                else
                {
                    $this->notice("Failed to add user to group because 'userGroupId' was empty.", __FILE__, __LINE__);
                }
            }

            $this->modifyUser($userId);
        }
        else
        {
            $this->notice("Failed to add user to groups because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &saveGroupProperties($userGroupId, $userGroupOwner = 1, $userGroupIsElevated = false, $userGroupPassword = null, $userGroupLoginEnabled = false)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Saving Group Properties</h2>
        # <p>
        #   Saves the group properties specified.  Group properties are one criteria that
        #   separates group accounts from regular user accounts.
        # </p>
        # <p>
        #   Group properties include the group owner specified in <var>$userGroupOwner</var>,
        #   (in Hot Toddy sometimes also called the group liaison), this is a user that is
        #   authorized to manage the group.  If this is not provided the default owner will
        #   be userId = 1 (always the root user and primary administrator in Hot Toddy).
        # </p>
        # <p>
        #   <var>$userGroupIsElevated</var> denotes whether the group is an elevated
        #   group with special privileges (most of these are built-in groups like
        #   <var>root</var>, <var>Website Administrators</var>, <var>Calendar Administrators</var>,
        #   or any other built-in group containing the word "Administrator".  The
        #   default value of this argument is <var>false</var>.
        # </p>
        # <p>
        #   <var>$userGroupPassword</var> is a password a user can type in to join the
        #   group without the intervention of an administrator.  If no group password
        #   is provided, it will not be saved.  No group password on an account will
        #   result in the functionality of joining a group by password not functioning.
        # </p>
        # <p>
        #   <var>$userGroupLoginEnabled</var> denotes whether typing the group's userName
        #   and password at a login prompt will result in a login.   The default value
        #   is <var>false</var>.  Allowing group login is not recommended.
        # </p>
        # <p>
        #   <var>$userId</var> or <var>$userGroupOwner</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userGroupId)
            ->setNumericUserId($userGroupOwner);

        if ($userGroupId > 0)
        {
            $columns = array(
                'hUserId' => $userGroupId,
                'hUserGroupOwner' => $userGroupOwner,
                'hUserGroupIsElevated' => (int) $userGroupIsElevated,
                'hUserGroupLoginEnabled' => (int) $userGroupLoginEnabled
            );

            if (!empty($userGroupPassword))
            {
                $columns['hUserGroupPassword'] = $userGroupPassword;
            }

            $this->hUserGroupProperties->save($columns);
        }
        else
        {
            $this->warning("Failed to save group properties because 'userGroupId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &deleteGroupProperties($userGroupId)
    {
        $this->user->setNumericUserId($userGroupId);

        if ($userGroupId > 0)
        {
            $his->deleteGroupMembers($userGroupId)
                ->deleteGroupCache($userGroupId)
                ->hUserGroupProperties
                    ->delete('hUserId', $userGroupId);
        }

        return this;
    }

    public function getGroupProperties($userGroupId)
    {
        # @return array

        # @description
        # <h2>Retrieving Group Properties</h2>
        # <p>
        #   Returns the group properties for the specified <var>$userGroupId</var> as an
        #   array.  The values returned are <var>hUserGroupOwner</var> (int),  <var>hUserGroupIsElevated</var> (bool),
        #   <var>hUserGroupLoginEnabled</var> (bool).
        # </p>
        # <p>
        #   <var>$userGroupId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if ($userGroupId > 0)
        {
            return $this->hUserGroupProperties->selectAssociative(
                array(
                    'hUserGroupOwner',
                    'hUserGroupIsElevated',
                    'hUserGroupLoginEnabled'
                ),
                (int) $userGroupId
            );
        }
        else
        {
            $this->notice("Failed to get group properties because 'userGroupId' was not provided.", __FILE__, __LINE__);
            return array();
        }
    }

    public function &setPassword($userId, $userPassword)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Setting a User's Password</h2>
        # <p>
        #   Updates the password for the user supplied in <var>$userId</var> to the value supplied
        #   in <var>$userPassword</var>.  Password are case-sensitive.  How the password is
        #   encypted depends on the settings of the two boolean framework variables:
        #   <var>hUserAuthenticateUseDatabaseHash</var> and <var>hUserAuthenticateUseMD5Hash</var>.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            if (!empty($userPassword))
            {
                $this->hUserLogin = $this->library('hUser/hUserLogin');

                $userPassword = trim($userPassword);

                $this->hUsers->update(
                    array(
                        'hUserPassword' => $this->hUserLogin->encryptPassword($userPassword)
                    ),
                    (int) $userId
                );
            }
        }
        else
        {
            $this->notice("Failed to set user's password because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &saveSecurityQuestion($userId, $userSecurityQuestionId, $userSecurityAnswer)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Saving a User's Security Question and Answer</h2>
        # <p>
        #   Saves the user's selected security question and answer for the user specified in <var>$userId</var>.
        #   Security questions are defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserSecurityQuestions/hUserSecurityQuestions.sql'>hUserSecurityQuestions</a>
        #   database table.  The user's selected question and response are stored in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a> database table.
        #   To save the question and response, the Id of the question is provided to this
        #   method in <var>$userSecurityQuestionId</var> and the response is provided in
        #   <var>$userSecurityAnswer</var>.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        if ($this->hUsers->columnExists('hUserSecurityQuestionId'))
        {
            $this->user->setNumericUserId($userId);

            if ($userId > 0)
            {
                $this->hUsers->update(
                    array(
                        'hUserSecurityQuestionId' => (int) $userSecurityQuestionId,
                        'hUserSecurityAnswer' => $userSecurityAnswer
                    ),
                    $userId
                );
            }
            else
            {
                $this->warning("Unable to save security question because 'userId' was not provided.", __FILE__, __LINE__);
            }
        }
        else
        {
            $this->warning('The column, hUserSecurityQuestion, does not exist on table hUsers, run the database update shell script to update the table.', __FILE__, __LINE__);
        }

        return $this;
    }

    public function getSecurityQuestions()
    {
        # @return array

        # @description
        # <h2>Retrieving All Security Questions</h2>
        # <p>
        #   Returns all security questions defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserSecurityQuestions/hUserSecurityQuestions.sql'>hUserSecurityQuestions</a>
        #   database table.  The array is returned with the <var>hUserSecurityQuestionId</var> column
        #   comprising the array indices and the <var>hUserSecurityQuestion</var> column comprising
        #   the array values.
        # </p>
        # @end

        return $this->hUserSecurityQuestions->selectColumnsAsKeyValue(
            array(
                'hUserSecurityQuestionId',
                'hUserSecurityQuestion'
            )
        );
    }

    public function getSecurityQuestionByEmail($userEmail, $returnQuestionText = true)
    {
        # @return array

        # @description
        # <h2>Retrieving A User's Security Question By Email Address</h2>
        # <p>
        #   Returns a user's security question (as defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserSecurityQuestions/hUserSecurityQuestions.sql'>hUserSecurityQuestions</a>
        #   database table).  If <var>$returnQuestionText</var> is <var>true</var> the
        #   copy of the security question is returned.  If <var>$returnQuestionText</var> is
        #   false, the unique <var>hUserSecurityQuestionId</var> is returned instead of the text
        #   of the question.
        # </p>
        # <h3>Response Codes</h3>
        # <p>
        #   This method is typically called via a listener services request (via AJAX), therefore
        #   it returns listener response codes as defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hPluginListenerResponseCodes/hPluginListenerResponseCodes.sql'>hPluginListenerResponseCodes</a>
        #   database table.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Response Code</th>
        #           <th>Response Text</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>0</td>
        #           <td>An unknown error has occured.</td>
        #       </tr>
        #       <tr>
        #           <td>-1</td>
        #           <td>You are not authorized to perform the selected operation.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        if ($this->hUsers->columnExists('hUserSecurityQuestionId'))
        {
            if ($this->emailExists($userEmail))
            {
                $userSecurityQuestionId = $this->hUsers->selectColumn(
                    'hUserSecurityQuestionId',
                    array(
                        'hUserEmail' => $userEmail
                    )
                );

                if (!empty($userSecurityQuestionId))
                {
                    if (!$returnQuestionText)
                    {
                        return $userSecurityQuestionId;
                    }

                    if ($userSecurityQuestionId > 0)
                    {
                        return $this->hUserSecurityQuestions->selectColumn(
                            'hUserSecurityQuestion',
                            (int) $userSecurityQuestionId
                        );
                    }
                    else
                    {
                        return -1;
                    }
                }

                return -1;
            }

            return false;
        }

        return -1;
    }

    public function isSecurityAnswer($userEmail, $userSecurityAnswer)
    {
        # @return array

        # @description
        # <h2>Validating a User's Response to a Security Question</h2>
        # <p>
        #   Validates a user's response to a security question (as defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserSecurityQuestions/hUserSecurityQuestions.sql'>hUserSecurityQuestions</a>
        #   database table).  The value provided in <var>$userSecurityAnswer</var> is validated
        #   against the stored response for the user specified in <var>$userEmail</var>.
        #   Only the user's email address on record is valid.
        # </p>
        # @end

        $userSecurityAnswer = $this->hUsers->selectColumn(
            'hUserSecurityAnswer',
            array(
                'hUserEmail' => $userEmail
            )
        );

        if (empty($userSecurityAnswer))
        {
            return false;
        }

        return ($userSecurityAnswer == trim($userSecurityAnswer));
    }

    public function save($userId, $userName, $userEmail, $userPassword, $userIsActivated = 0, $userReferredBy = 0, $userRegistrationTracker = 0, $homeFolder = false)
    {
        # @return array

        # @description
        # <h2>Saving a User or Group</h2>
        # <p>
        #   Saves a user to the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a>
        #   database table.  If the user exists, the <var>userId</var> of that user should be provided
        #   in <var>$userId</var>.  The <var>hUserName</var> is provided in <var>$userName</var>, the
        #   <var>hUserEmail</var> is provided in <var>$userEmail</var>.  If the user's password has
        #   changed the new password should be provided in <var>$userPassword</var>, otherwise, if the
        #   password has not changed, <var>$userPassword</var> should be <var>null</var>.  Whether
        #   or not the user's account is activated is provided in <var>$userIsActivated</var> (a boolean
        #   value and <var>false</var>, by default).  The <var>hUserId</var> of the user referring the
        #   user is provided in <var>$userReferredBy</var> (optional, and 0 by default).
        #   <var>$userRegistrationTracker</var> is used to provide a string or some other value for
        #   tracking certain user registrations (also optional, and 0 by default).  Finally, <var>$homeFolder</var>
        #   designates whether or not there should be a Hot Toddy home folder created for the user.  This is
        #   also optional, a boolean value, and <var>false</var>, by default.
        # </p>
        # <h3>Response Codes</h3>
        # <p>
        #   This method is typically called via a listener services request (via AJAX), therefore
        #   it returns listener response codes as defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hPluginListenerResponseCodes/hPluginListenerResponseCodes.sql'>hPluginListenerResponseCodes</a>
        #   database table.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Response Code</th>
        #           <th>Response Text</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>&gt; 0</td>
        #           <td>
        #               If the value returned is greater than zero, the save or creation was successful.
        #               The value returned is the <var>hUserId</var> of the user saved or created.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>0</td>
        #           <td>An unknown error has occured.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>-13</td>
        #           <td>Username already exists.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>-14</td>
        #           <td>Email already exists.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>-23</td>
        #           <td>The email address provided is not formatted correctly.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->hUserLogin = $this->library('hUser/hUserLogin');
        $this->hUserValidation = $this->library('hUser/hUserValidation');

        $userName = trim($userName);
        $userEmail = trim($userEmail);
        $userPassword = trim($userPassword);

        if (!$this->hUserValidation->isValidEmailAddress($userEmail))
        {
            return -23;
        }

        if (!empty($userId))
        {
            $data = $this->hUsers->selectAssociative(
                array(
                    'hUserId',
                    'hUserName',
                    'hUserEmail'
                ),
                (int) $userId
            );

            if (count($data))
            {
                if (!$this->userNameExists($userName, $userId))
                {
                    if (!$this->emailExists($userEmail, $userId))
                    {
                        $password = array();

                        if (!empty($userPassword))
                        {
                            $password = array('hUserPassword' => $this->hUserLogin->encryptPassword($userPassword));
                        }

                        if ($this->hUserEnableHomeFolder(false) || $homeFolder || $this->inGroup('root', $userId))
                        {
                            $this->hUserHomeFolder = $this->library('hUser/hUserHomeFolder');

                            $this->hUserHomeFolder->save(
                                $userName,
                                $userId,
                                $this->user->getUserName($userId)
                            );
                        }

                        $this->hUsers->update(
                            array_merge(
                                array(
                                    'hUserName' => $userName,
                                    'hUserEmail' => $userEmail
                                ),
                                $password
                            ),
                            (int) $userId
                        );

                        $this->log(
                            $userId,
                            $userReferredBy,
                            $userRegistrationTracker
                        );

                        $this->hUsers->activity('Modified Account: '.$userName);
                        $this->deleteGroupCache($userId);

                        return $userId;
                    }
                    else
                    {
                        $this->console(
                            "Save User Failed: ".
                            "An email address already exists for {$userEmail}. (Error: -14) \n".
                            "The existing user is: ".$this->user->getUserId($userEmail).
                            " a.k.a. ".$this->user->getUserName($userEmail)."\n", __FILE__, __LINE__
                        );

                        return -14;
                    }
                }
                else
                {
                    $this->console(
                        "Save User Failed: ".
                        "A user name already exists for {$userName}. (Error: -13) \n".
                        "The existing user is: ".$this->user->getUserId($userName).
                        " a.k.a. ".$this->user->getUserEmail($userName)."\n", __FILE__, __LINE__
                    );

                    return -13;
                }
            }
            else
            {
                $this->warning("Failed to save user. The userId '{$userId}' does not exist.", __FILE__, __LINE__);
            }
        }
        else if (!$this->userNameExists($userName, $userId))
        {
            if (!$this->emailExists($userEmail, $userId))
            {
                $userId = $this->hUsers->insert(
                    array(
                        'hUserId' => null,
                        'hUserName' => $userName,
                        'hUserEmail' => $userEmail,
                        'hUserPassword' => (!empty($userPassword)? $this->hUserLogin->encryptPassword($userPassword) : "''"),
                        'hUserConfirmation' => $this->hUserLogin->generatePassword(),
                        'hUserIsActivated' => ($userIsActivated? 1 : 0)
                    )
                );

                $this->hUsers->activity('Created Account: '.$userName);

                $this->log($userId, $userReferredBy, $userRegistrationTracker);

                if ($this->hUserEnableHomeFolder(false) || $homeFolder)
                {
                    $this->hUserHomeFolder = $this->library('hUser/hUserHomeFolder');
                    $this->hUserHomeFolder->save($userName, $userId);
                }

                return $userId;
            }
            else
            {
                $this->console(
                    "Create User Failed: ".
                    "An email address already exists for {$userEmail}. (Error: -14) \n".
                    "The existing user is: ".$this->user->getUserId($userEmail).
                    " a.k.a. ".$this->user->getUserName($userEmail)."\n", __FILE__, __LINE__
                );

                return -14;
            }
        }
        else
        {
            $this->console(
                "Create User Failed: ".
                "An email address already exists for {$userName}. (Error: -13) \n".
                "The existing user is: ".$this->user->getUserId($userName).
                " a.k.a. ".$this->user->getUserName($userName)."\n", __FILE__, __LINE__
            );

            return -13;
        }

        return 0;
    }

    public function loginExists($user)
    {
        return (bool) $this->getUserIdFromLogin($user);
    }

    public function getUserIdFromLogin($user)
    {
        $userId = $this->hUsers->selectColumn(
            'hUserId',
            array(
                'hUserName',
                'hUserEmail'
            ),
            'OR'
        );

        if (!empty($userId))
        {
            $userId = $this->hUserAliases->selectColumn(
                'hUserId',
                array(
                    'hUserNameAlias' => addslashes($user)
                )
            );
        }

        return (int) $userId;
    }

    public function userNameExists($userName, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if a User Name Exists</h2>
        # <p>
        #   Returns a boolean value indicated whether or not the
        #   supplied <var>$userName</var> exists in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a>
        #   database table.
        # </p>
        # @end

        $existingUserId = (int) $this->hUsers->selectColumn(
            'hUserId',
            array(
                'hUserName' => addslashes($userName)
            )
        );

        if (empty($existingUserId))
        {
            $existingUserId = (int) $this->hUserAliases->selectColumn(
                'hUserId',
                array(
                    'hUserNameAlias' => addslashes($userName)
                )
            );

            if (!empty($existingUserId))
            {
                return !((int) $userId === (int) $existingUserId);
            }
            else
            {
                return false;
            }
        }
        else if ((int) $existingUserId === (int) $userId)
        {
            return false;
        }

        # else
        # {
        #     $users = $this->hUserAliases->select(
        #         array(
        #             'hUserId',
        #             'hUserNameAlias'
        #         ),
        #         array(
        #             'hUserNameAlias' => addslashes($userName)
        #         )
        #     );
        #
        #     foreach ($users as $user)
        #     {
        #         if ((int) $user['hUserId'] !== (int) $userId)
        #         {
        #             $this->console(
        #                 "User exists: '{$user['hUserNameAlias']}', checking existence in the context of ".
        #                 "{$userId} a.k.a. ".$this->user->getUserName($userId), __FILE__, __LINE__
        #             );
        #
        #             return true;
        #         }
        #     }
        # }

        return false;
    }

    public function emailExists($userEmail, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if an Email Address Exists</h2>
        # <p>
        #   Returns a boolean value indicated whether or not the
        #   supplied <var>$userEmail</var> exists in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a>
        #   database table.
        # </p>
        # @end

        $existingUserId = (int) $this->hUsers->selectExists(
            'hUserId',
            array(
                'hUserEmail' => addslashes($userEmail)
            )
        );

        if (empty($existingUserId))
        {
            $existingUserId = (int) $this->hUserAliases->selectColumn(
                'hUserId',
                array(
                    'hUserNameAlias' => addslashes($userEmail)
                )
            );

            if (!empty($existingUserId))
            {
                return !((int) $userId === (int) $existingUserId);
            }
            else
            {
                return false;
            }
        }
        else if ((int) $existingUserId === (int) $userId)
        {
            return false;
        }

        # else
        # {
        #     $users = $this->hUserAliases->select(
        #         array(
        #             'hUserId',
        #             'hUserNameAlias'
        #         ),
        #         array(
        #             'hUserNameAlias' => addslashes($userEmail)
        #         )
        #     );
        #
        #     foreach ($users as $user)
        #     {
        #         if ((int) $user['hUserId'] !== (int) $userId)
        #         {
        #             $this->console(
        #                 "User exists: '{$user['hUserNameAlias']}', checking existence in the context of ".
        #                 "{$userId} a.k.a. ".$this->user->getUserName($userId), __FILE__, __LINE__
        #             );
        #
        #             return true;
        #         }
        #     }
        # }
        #
        # return (bool) $userId;
    }

    public function &saveUnixProperties($userId, $userUnixUId, $userUnixGId, $userUnixHome, $userUnixShell)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Saving a User's Unix Properties</h2>
        # <p>
        #   Saves the provided Unix OS properties for the provided <var>$userId</var>.
        #   Unix properties are only associated with a Hot Toddy user if the user in
        #   question is an account that is synced with the desktop OS.  User accounts
        #   are synced with a desktop OS or network using the
        #   <a href='/Hot Toddy/Documentation?hUser/hUserDirectory/hUserDirectory.library.php'>hUserDirectoryLibrary</a>
        #   and <a href='/Hot Toddy/Documentation?hContact/hContactDirectory/hContactDirectory.library.php'>hContactDirectoryLibrary</a>
        #   plugins.
        # </p>
        # <p>
        #   User accounts are synced with a Mac OS X Desktop (local desktop accounts, no directory involved),
        #   Mac OS X Open Directory Server, Mac OS X Open Directory or Mac OS X Active Directory Client.
        # </p>
        # <p>
        #   Unix properties are provided as the Unix UId in <var>$userUnixUId</var>, the Unix GId in
        #   <var>$userUnixGId</var>, the Unix Home Directory in <var>$userUnixHome</var>, and the
        #   path to the user's preferred shell application in <var>$userUnixShell</var>.
        # </p>
        # <p>
        #   Unix properties are stored in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserUnixProperties/hUserUnixProperties.sql'>hUserUnixProperties</a>
        #   database table.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if (!empty($userId))
        {
            $this->hUserUnixProperties->save(
                array(
                    'hUserId' => $userId,
                    'hUserUnixUId' => (int) $userUnixUId,
                    'hUserUnixGId' => (int) $userUnixGId,
                    'hUserUnixHome' => $userUnixHome,
                    'hUserUnixShell' => $userUnixShell
                )
            );
        }
        else
        {
            $this->notice("Unable to save Unix properties because no 'userId' was provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function &delete($userId)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Deleting a User or Group</h2>
        # <p>
        #   Deletes the specified user from the following tables:
        # </p>
        # <ul>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserUnixProperties/hUserUnixProperties.sql'>hUserUnixProperties</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserVariables/hUserVariables.sql'>hUserVariables</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserPermissionsCache/hUserPermissionsCache.sql'>hUserPermissionsCache</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserLog/hUserLog.sql'>hUserLog</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserGroups/hUserGroups.sql'>hUserGroups</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserGroupProperties/hUserGroupProperties.sql'>hUserGroupProperties</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hSubscriptionUsers/hSubscriptionUsers.sql'>hSubscriptionUsers</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hContactUsers/hContactUsers.sql'>hContactUsers</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFileComments/hFileComments.sql'>hFileComments</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFileStatusLog/hFileStatusLog.sql'>hFileStatusLog</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFileUserStatistics/hFileUserStatistics.sql'>hFileUserStatistics</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserActivityLog/hUserActivityLog.sql'>hUserActivityLog</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserNewsletter/hUserNewsletter.sql'>hUserNewsletter</a></li>
        # </ul>
        # <p>
        #   A deleted user's assets are re-assigned to the root user (userId=1).  So
        #   far re-assignment includes the following resources:
        # </p>
        # <ul>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hCalendars/hCalendars.sql'>hCalendars</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hCategories/hCategories.sql'>hCategories</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hContactAddressBooks/hContactAddressBooks.sql'>hContactAddressBooks</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hDirectories/hDirectories.sql'>hDirectories</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFiles/hFiles.sql'>hFiles</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hForumPosts/hForumPosts.sql'>hForumPosts</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hForums/hForums.sql'>hForums</a></li>
        #   <li><a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hForumTopics/hForumTopics.sql'>hForumTopics</a></li>
        # </ul>
        # <p>
        #   An alternative to deleting a user is placing the user in the <var>Disabled User Accounts</var> user
        #   group, which prevents the user from logging in, but maintains the existence of the account
        #   and ownership of assets.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            $this->hUsers->activity('Deleted Account: '.$this->user->getUserName($userId));

            // Reassign anything owned by this user to root.
            $this->hContacts->update(
                array(
                    'hUserId' => 1
                ),
                array(
                    'hContactAddressBookId' => array('>', 1),
                    'hUserId' => (int) $userId
                )
            );

            $tables = array(
                'hCalendars',
                'hCategories',
                'hContactAddressBooks',
                'hDirectories',
                'hFiles',
                'hForumPosts',
                'hForums',
                'hForumTopics'
            );

            foreach ($tables as $table)
            {
                $this->hDatabase->update(
                    array(
                        'hUserId' => 1
                    ),
                    array(
                        'hUserId' => (int) $userId
                    ),
                    $table
                );
            }

            // Remove all traces of the user
            $this->hDatabase->delete(
                array(
                    'hUserUnixProperties',
                    'hUserVariables',
                    'hUsers',
                    'hUserPermissionsCache',
                    'hUserLog',
                    'hUserGroups',
                    'hUserGroupProperties',
                    'hSubscriptionUsers',
                    'hContactUsers',
                    'hFileComments',
                    'hFileStatusLog',
                    'hFileUserStatistics',
                    'hUserActivityLog',
                    'hUserNewsletter',
                    'hUserAliases',
                    'hUserAuthenticationLog',
                    'hUserDirectory'
                ),
                'hUserId',
                $userId
            );

            $this->hDatabase->delete(
                array(
                    'hUserGroups',
                    'hUserPermissionsGroups'
                ),
                'hUserGroupId',
                $userId
            );

            $this->deleteGroupCache($userId);
        }
        else
        {
            $this->notice("Unable to delete user because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function activateUser($userId, $userConfirmation)
    {
        # @return boolean

        # @description
        # <h2>Activating a User</h2>
        # <p>
        #   Activates the user provided in <var>$userId</var> with the confirmation code
        #   provided in <var>$userConfirmation</var>.
        # </p>
        # <p>
        #   User activation exists as a method of verifying that the user has a valid email
        #   address and receives mail to it.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            $userConfirmation = trim($userConfirmation);

            $exists = $this->hUsers->selectExists(
                'hUserId',
                array(
                    'hUserId' => $userId,
                    'hUserConfirmation' => $userConfirmation
                )
            );

            if ($exists)
            {
                // Confirmation is valid
                $this->hUsers->update(
                    array(
                        'hUserIsActivated' => 1
                    ),
                    array(
                        'hUserId' => $userId,
                        'hUserConfirmation' => $userConfirmation
                    )
                );

                $this->modifyUser($userId);

                return true;
            }
        }
        else
        {
            $this->notice("Unable to activate user because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return false;
    }

    public function getConfirmation($userId)
    {
        # @return boolean

        # @description
        # <h2>Generating and Returning a User's Confirmation Code</h2>
        # <p>
        #   Creates a seven-character long confirmation code, which is emailed to
        #   user to verify the validity of their email address.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            $query = $this->hUsers->selectQuery(
                'hUserConfirmation',
                array(
                    'hUserId' => $userId
                )
            );

            if ($this->hDatabase->resultsExist($query))
            {
                $userConfirmation = $this->hDatabase->getColumn($query);

                if (empty($userConfirmation))
                {
                    $userConfirmation = $this->getRandomString(7);

                    $this->hUsers->update(
                        array(
                            'hUserConfirmation' => $userConfirmation
                        ),
                        array(
                            'hUserId' => $userId
                        )
                    );
                }

                return $userConfirmation;
            }
        }
        else
        {
            $this->notice("Unable to get confirmation code because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return false;
    }

    public function getUserIdByConfirmation($userId, $userConfirmation)
    {
        # @return boolean

        # @description
        # <h2>Getting a Numeric userId Via userName or userEmail and Confirmation Code</h2>
        # <p>
        #   Returns the user's numeric <var>userId</var> for the <var>userName</var> or
        #   <var>userEmail</var> supplied in <var>$userId</var> and the confirmation
        #   code supplied in <var>$userConfirmation</var>.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            return $this->hUsers->selectColumn(
                'hUserId',
                array(
                    'hUserId' => $userId,
                    'hUserConfirmation' => $userConfirmation
                )
            );
        }
        else
        {
            $this->notice("Get confirmation code by user failed because 'userId' was not provided.", __FILE__, __LINE__);
            return 0;
        }
    }

    public function authenticateUser($userId, $userPassword)
    {
        # @return boolean

        # @description
        # <h2>Authenticating a User</h2>
        # <p>
        #   Authenticates a user via their <var>userId</var>, <var>userName</var>, or <var>userEmail</var>,
        #   supplied in <var>$userId</var> and their password supplied in <var>$userPassword</var>.
        # </p>
        # <p>
        #    If the framework variable <var>hFrameworkRootPassword</var> is specified and the root password
        #    is provided and matches identically, authentication is successful.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        #   Use this method if you only need to authenticate an acount, this method does
        #   not support disabling user accounts, account activation, sessions, or a variety of other
        #   features.  For the full spectrum of authentication support, use
        #   <a href='/Hot Toddy/Documentation?hUser/hUserLogin/hUserLogin.library.php'>hUserLoginLibrary</a>
        # </p>
        # @end
        $this->console($userId);

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            $userName = $this->user->getUserName($userId);
            $userEmail = $this->user->getUserEmail($userId);

            $this->console($userName);
            $this->console($userEmail);

            $this->hUserLogin = $this->library('hUser/hUserLogin');

            if ($this->hFrameworkRootPassword(null) && !empty($userPassword) && ($userPassword === $this->hFrameworkRootPassword(null)))
            {
                return true;
            }

            if ($this->hUserAuthenticateDatabaseEncryption(false))
            {
                $exists = $this->hUsers->selectExists(
                    'hUserId',
                    array(
                        'hUserName' => $userName,
                        'hUserPassword' => "password('{$userPassword}')"
                    )
                );

                if (!$exists)
                {
                    $exists = $this->hUsers->selectExists(
                        'hUserId',
                        array(
                            'hUserEmail' => $userName,
                            'hUserPassword' => "password('{$userPassword}')"
                        )
                    );

                    if (!$exists)
                    {
                        $userId = $this->hUserAliases->selectColumn(
                            'hUserId',
                            array(
                                'hUserAliasName' => addslashes($userName)
                            )
                        );

                        if (!empty($userId))
                        {
                            return $this->hUsers->selectExists(
                                'hUserId',
                                array(
                                    'hUserId' => $userId,
                                    'hUserPassword' => "password('{$userPassword}')"
                                )
                            );
                        }
                        else
                        {
                            return false;
                        }
                    }
                }
                else
                {
                    return true;
                }
            }
            else
            {
                $password = $this->hUsers->selectColumn(
                    'hUserPassword',
                    array(
                        'hUserName' => $userName
                    )
                );

                $this->console($password);

                if (!empty($password))
                {
                    if (!$this->hUserLogin->isMd5Password($userPassword, $password))
                    {
                        $password = $this->hUsers->selectColumn(
                            'hUserPassword',
                            array(
                                'hUserEmail' => $userEmail
                            )
                        );

                        if (!$this->hUserLogin->isMd5Password($userPassword, $password))
                        {
                            $userId = $this->hUserAliases->selectColumn(
                                'hUserId',
                                array(
                                    'hUserAliasName' => addslashes($userName)
                                )
                            );

                            if (!empty($userId))
                            {
                                $password = $this->hUsers->selectColumn(
                                    'hUserPassword',
                                    array(
                                        'hUserId' => $userId
                                    )
                                );

                                return $this->hUserLogin->isMd5Password($userPassword, $password);
                            }
                        }
                        else
                        {
                            return true;
                        }
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            $this->notice("Unable to authenticate user because 'userId' was not provided.", __FILE__, __LINE__);
        }
    }

    public function updateEmailAddress($userId, $userEmailOld, $userEmailNew, $userPassword)
    {
        # @return integer

        # @description
        # <h2>Update a User's Email Address</h2>
        # <p>
        #   Authenticates a user via their <var>userId</var>, <var>userName</var>, or <var>userEmail</var>,
        #   supplied in <var>$userId</var> and their password supplied in <var>$userPassword</var>.
        # </p>
        # <p>
        #   Then updates their email address from <var>$userEmailOld</var> to <var>$userEmailNew</var>.
        # </p>
        # <p>
        #   This method is used as part of user activation. If a user wishes to change the
        #   email address they registered with, they are permitted, so long as they
        #   supply the correct current password and the correct current email address and
        #   username.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <h3>Response Codes</h3>
        # <p>
        #   This method is typically called via a listener services request (via AJAX), therefore
        #   it returns listener response codes as defined in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hPluginListenerResponseCodes/hPluginListenerResponseCodes.sql'>hPluginListenerResponseCodes</a>
        #   database table.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Response Code</th>
        #           <th>Response Text</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>&gt; 0</td>
        #           <td>
        #               If the value returned is greater than zero, the email
        #               address was successfully updated.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>0</td>
        #           <td>An unknown error has occured.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>-23</td>
        #           <td>The email address provided is not formatted correctly.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            $this->hUserValidation = $this->library('hUser/hUserValidation');

            if (!$this->hUserValidation->isValidEmailAddress($userEmailNew))
            {
                return -23;
            }

            // User is authenticated to prevent malicious users from hijacking
            // others' accounts via this API.
            if ($this->authenticateUser($userName, $userPassword))
            {
                $this->hUsers->update(
                    array(
                        'hUserEmail' => $userEmailNew
                    ),
                    array(
                        'hUserId' => $userId,
                        'hUserEmail' => $userEmailOld
                    )
                );

                return 1;
            }
        }
        else
        {
            $this->notice("Failed to update email address from '{$userEmailOld}' to '{$userEmailNew}' because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return 0;
    }

    public function isActivated($userId)
    {
        # @return boolean

        # @description
        # <h2>Determining If a User Is Activated</h2>
        # <p>
        #   Returns whether or not the user supplied in <var>$userId</var> is activated.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId);

        if ($userId > 0)
        {
            $userName = $this->user->getUserName($userId);

            if ($this->userNameExists($userName))
            {
                return (bool) $this->hUsers->selectColumn(
                    'hUserIsActivated',
                    array(
                        'hUserName' => $userName
                    )
                );
            }
        }
        else
        {
            $this->notice("Failed to determine if the user is activated because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return false;
    }

    public function getEmailAddress($userName, $userPassword)
    {
        # @return string | boolean

        # @description
        # <h2>Getting a User's Email Address</h2>
        # <p>
        #   Returns a user's email address, provided that the user supplied the
        #   correct user name and password for the account.
        # </p>
        # @end

        if ($this->authenticateUser($userName, $userPassword))
        {
            return $this->user->getUserEmail($userName);
        }

        return false;
    }

    public function getUserName($userEmail, $userPassword)
    {
        # @return string | boolean

        # @description
        # <h2>Getting a UserName</h2>
        # <p>
        #   Returns a user's <var>userName</var>, provided that the user supplied the
        #   correct user email and password for the account.
        # </p>
        # @end

        if ($this->authenticateUser($userEmail, $userPassword))
        {
            return $this->user->getUserName($userEmail);
        }

        return false;
    }

    public function &log($userId = 0, $userReferredBy = 0, $userRegistrationTracker = 0)
    {
        # @return hUserDatabaseLibrary

        # @description
        # <h2>Logging a User's Account Activity</h2>
        # <p>
        #   Creates or updates a log of user account activity for the user specified in
        #   <var>$userId</var>.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <h3>Data Tracked</h3>
        # <p>
        #   Following is a explanation of the data tracked in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserLog/hUserLog.sql'>hUserLog</a>
        #   database table.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Field</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hUserLoginCount</td>
        #           <td>
        #               The number of times the user has logged in successfully.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserFailedLoginCount</td>
        #           <td>
        #               The number of times the user has failed to login successfully,
        #               after three failed attempts the user account is automatically locked
        #               for ten minutes.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserCreated</td>
        #           <td>A Unix Timestamp representing when the user account was created.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastLogin</td>
        #           <td>A Unix Timestamp representing when someone last logged into the account.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastFailedLogin</td>
        #           <td>A Unix Timestamp representing when someone last failed to login to the account.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastModified</td>
        #           <td>A Unix Timestamp representing when the user account was last modified.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastModifiedBy</td>
        #           <td>A <var>hUserId</var> representing the user to last modify the account.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserReferredBy</td>
        #           <td>A <var>hUserId</var> representing the user to refer this user to this site.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserRegistrationTracker</td>
        #           <td>
        #               An integer representing a unique id that is used to track account registrations.
        #               This field is provided to house a custom id created by a custom tracking process.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileId</td>
        #           <td>The id of the file in Hot Toddy's HtFS the account registration occurred on.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if ($userId > 0)
        {
            if (!$this->hUserLog->selectExists('hUserId', (int) $userId))
            {
                $this->hUserLog->save(
                    array(
                        'hUserId' => $userId,
                        'hUserLoginCount' => 0,
                        'hUserFailedLoginCount' => 0,
                        'hUserCreated' => time(),
                        'hUserLastLogin' => 0,
                        'hUserLastFailedLogin' => 0,
                        'hUserLastModified' => 0,
                        'hUserLastModifiedBy' => isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 0,
                        'hUserReferredBy' => (int) $userReferredBy,                   # Referred BY
                        'hUserRegistrationTracker' => (int) $userRegistrationTracker, # Registration Tracker
                        'hFileId' => (int) $this->hFileId                             # File Id
                    )
                );
            }
            else
            {
                $this->hUserLog->update(
                    array(
                        'hUserLastModified' => time(),
                        'hUserLastModifiedBy' => (isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 0)
                    ),
                    (int) $userId
                );
            }
        }
        else
        {
            $this->notice("Failed to update or create user log because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $this;
    }

    public function getLog($userId = 0, $timeFormat = 'l, F jS, Y g:ia')
    {
        # @return array

        # @description
        # <h2>Getting a User's Log Data</h2>
        # <p>
        #   Returns a user's log data for the user specified in <var>$userId</var> with all
        #   timestamps formatted in the format specified in <var>$timeFormat</var>.
        # </p>
        # <p>
        #   See <a href='http://www.php.net/date'>date()</a> for information on how to format
        #   time.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <h3>Data Returned</h3>
        # <p>
        #   Following is a explanation of the associative array returned containing user log data stored in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserLog/hUserLog.sql'>hUserLog</a>
        #   database table.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Field</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hUserId</td>
        #           <td>The unique, numeric <var>userId</var></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLoginCount</td>
        #           <td>
        #               The number of times the user has logged in successfully.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserFailedLoginCount</td>
        #           <td>
        #               The number of times the user has failed to login successfully,
        #               after three failed attempts the user account is automatically locked
        #               for ten minutes.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserCreated</td>
        #           <td>A Unix Timestamp representing when the user account was created.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserCreatedFormatted</td>
        #           <td>
        #               The Unix Timestamp provided in <var>hUserCreated</var> formatted
        #               using the format provided in <var>$timeFormat</var>.  If the value of the
        #               unix timestamp is zero (meaning no created date was logged), 'Error' will
        #               be returned instead of a date.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastLogin</td>
        #           <td>A Unix Timestamp representing when someone last logged into the account.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastLoginFormatted</td>
        #           <td>
        #               The Unix Timestamp provided in <var>hUserLastLogin</var> formatted
        #               using the format provided in <var>$timeFormat</var>.  If the user has
        #               never logged into the account, the value 'Never' will be returned
        #               instead of a date.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastFailedLogin</td>
        #           <td>A Unix Timestamp representing when someone last failed to login to the account.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastFailedLoginFormatted</td>
        #           <td>
        #               The Unix Timestamp provided in <var>hUserLastFailedLogin</var> formatted
        #               using the format provided in <var>$timeFormat</var>.  If there has never
        #               been a failed login the value 'Never' will be returned instead of a date.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastModified</td>
        #           <td>A Unix Timestamp representing when the user account was last modified.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastModifiedFormatted</td>
        #           <td>
        #               The Unix Timestamp provided in <var>hUserLastModified</var> formatted
        #               using the format provided in <var>$timeFormat</var>.  If the account has
        #               never been modified, the value 'Never' will be returned instead of a date.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastModifiedBy</td>
        #           <td>A <var>hUserId</var> representing the user to last modify the account.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserLastModifiedByName</td>
        #           <td>
        #               The <var>hUserName</var> representing the user to last modify the account.  If
        #               the account has never been modified, the value returned will be 'N/A' instead.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserReferredBy</td>
        #           <td>A <var>hUserId</var> representing the user to refer this user to this site for registration.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserReferredByName</td>
        #           <td>A <var>hUserName</var> representing the user to refer this user to this site for registration.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserRegistrationTracker</td>
        #           <td>
        #               An integer representing a unique id that is used to track account registrations.
        #               This field is provided to house a custom id created by a custom tracking process.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFileId</td>
        #           <td>The id of the file in Hot Toddy's HtFS the account registration occurred on.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hFilePath</td>
        #           <td>The file path of the file in Hot Toddy's HtFS the account registration occurred on.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if ($userId > 0)
        {
            $data = $this->hUserLog->selectAssociative(
                array(
                    'hUserId',
                    'hUserLoginCount',
                    'hUserFailedLoginCount',
                    'hUserCreated',
                    'hUserLastLogin',
                    'hUserLastFailedLogin',
                    'hUserLastModified',
                    'hUserLastModifiedBy',
                    'hUserReferredBy',
                    'hUserRegistrationTrackingId',
                    'hFileId'
                ),
                array(
                    'hUserId' => $userId
                )
            );

            if (count($data))
            {
                $data['hUserCreatedFormatted'] = 'Error';

                if ($data['hUserCreated'] > 0)
                {
                    $data['hUserCreatedFormatted'] = date($timeFormat, $data['hUserCreated']);
                }

                $data['hUserLastLoginFormatted'] = 'Never';

                if ($data['hUserLastLogin'] > 0)
                {
                    $data['hUserLastLoginFormatted'] = date($timeFormat, $data['hUserLastLogin']);
                }

                $data['hUserLastFailedLoginFormatted'] = 'Never';

                if ($data['hUserLastFailedLogin'] > 0)
                {
                    $data['hUserLastFailedLoginFormatted'] = date($timeFormat, $data['hUserLastFailedLogin']);
                }

                $data['hUserLastModifiedFormatted'] = 'Never';

                if ($data['hUserLastModified'] > 0)
                {
                    $data['hUserLastModifiedFormatted'] = date($timeFormat, $data['hUserLastModified']);
                }

                $data['hUserLastModifiedByName'] = 'N/A';

                if ($data['hUserLastModifiedBy'] > 0)
                {
                    $data['hUserLastModifiedByName'] = $this->user->getUserName($data['hUserLastModifiedBy']);
                }

                $data['hUserReferredByName'] = 'No Referrer';

                if ($data['hUserReferredBy'] > 0)
                {
                    $data['hUserReferredByName'] = $this->user->getUserName($data['hUserReferredBy']);
                }

                $data['hFilePath'] = 'No File Path';

                if ($data['hFileId'] > 0)
                {
                    $data['hFilePath'] = $this->getFilePathByFileId($data['hFileId']);
                }
            }
            else
            {
                $this->log($userId);
            }
        }
        else
        {
            $this->notice("Failed to retrieve user log because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $data;
    }

    public function &authenticationLog($message, $userName = null, $userEmail = null, $userId = 0)
    {
        # @return hUserDatabase

        # @description
        # <h2>Loggin Authentication Attempts</h2>
        # <p>
        #   Logs the specified <var>$message</var> to the user authentication log for the
        #   user specified in <var>$userName</var>, <var>$userEmail</var>, or <var>$userId</var>.
        # </p>
        # @end

        if (empty($userName) && !empty($userId))
        {
            $userName = $this->user->getUserName($userId);
        }

        if (empty($userEmail) && !empty($userId))
        {
            $userEmail = $this->user->getUserEmail($userId);
        }

        $this->hUserAuthenticationLog->insert(
            array(
                'hUserId' => $userId,
                'hUserName' => $userName,
                'hUserEmail' => $userEmail,
                'hUserAuthenticationError' => $message,
                'hUserAuthenticationTime' => time()
            )
        );

        return $this;
    }

    public function getActivity($userId = 0, $limit = '0,25', $timeFormat = 'm/d/y h:i a')
    {
        # @return array

        # @description
        # <h2>Getting a User Activity Data</h2>
        # <p>
        #   Returns a user's activity data for the user specified in <var>$userId</var> with all
        #   timestamps formatted in the format specified in <var>$timeFormat</var>, and the
        #   data limited (via SQL <var>LIMIT</var> clause) using the value provided in <var>$limit</var>.
        # </p>
        # <p>
        #   See <a href='http://www.php.net/date'>date()</a> for information on how to format
        #   time.
        # </p>
        # <p>
        #   <var>$userId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <h3>Data Returned</h3>
        # <p>
        #   Following is a explanation of the associative array returned containing user activity data stored in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserActivityLog/hUserActivityLog.sql'>hUserActivityLog</a>
        #   database table.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Field</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hUserId</td>
        #           <td>The unique, numeric <var>userId</var></td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserActivity</td>
        #           <td>Text describing the activity that took place.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserActivityComponent</td>
        #           <td>The Hot Toddy component affected (the name of the primary database table associated with the resource).</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserActivityTime</td>
        #           <td>A Unix Timestamp representing when the activity occurred.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hUserActivityTimeFormatted</td>
        #           <td>
        #               The Unix Timestamp provided in <var>hUserActivityTime</var> formatted
        #               using the format provided in <var>$timeFormat</var>.  If the value of the
        #               unix timestamp is zero (meaning no created date was logged), 'Error' will
        #               be returned instead of a date.
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->user->setNumericUserId($userId)->whichUserId($userId);

        $query = array();

        if ($userId > 0)
        {
            $query = $this->hUserActivityLog->select(
                array(
                    'SQL_CALC_FOUND_ROWS',
                    'hUserId',
                    'hUserActivity',
                    'hUserActivityComponent',
                    'hUserActivityTime',
                    'hUserActivityIP'
                ),
                array(
                    'hUserId' => $userId
                ),
                'AND',
                array(
                    'hUserActivityTime',
                    'DESC'
                ),
                $limit
            );

            $this->activityCount = $this->hDatabase->getResultCount();

            foreach ($query as $i => $data)
            {
                $query[$i]['hUserActivityTimeFormatted'] = $query[$i]['hUserActivityTime'] > 0? date($timeFormat, $query[$i]['hUserActivityTime']) : 'Error';
            }
        }
        else
        {
            $this->notice("Failed to retrieve user activity because 'userId' was not provided.", __FILE__, __LINE__);
        }

        return $query;
    }

    public function getActivityCount()
    {
        # @return integer

        # @description
        # <h2>Getting the User's Activity Count</h2>
        # <p>
        #   Returns the total overall number of activity items logged in
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUserActivityLog/hUserActivityLog.sql'>hUserActivityLog</a>
        #   for the specified user.  To get an activity count, you must first retrieve activity
        #   for the user by calling <a href='#getActivity'>getActivity()</a>.
        # </p>
        # @end

        return $this->activityCount;
    }

    public function replaceRootWithAdministrators(array $groups)
    {
        # @return array

        # @description
        # <h2>Renaming the root User Group</h2>
        # <p>
        #   Renames the <var>root</var> user group to <var>Developers</var>.  This is intended only
        #   for display of the group to novice users (e.g., when using the 'Contacts' application).
        # </p>
        # @end

        foreach ($groups as $userId => $userName)
        {
            if ($groups[$userId] == 'Administrators')
            {
                unset($groups[$userId]);
                continue;
            }

            if ($groups[$userId] == 'root')
            {
                $groups[$userId] = 'Developers';
            }

            $unixExists = $this->hUserUnixProperties->selectExists(
                'hUserId',
                array(
                    'hUserId' => $userId
                )
            );

            if ($unixExists)
            {
/*
                $realName = $this->pipeCommand(
                    '/usr/bin/dscl',
                    '. '.
                    '-read '.escapeshellarg('/Groups/'.$userName).' RealName'
                );

                if ($realName)
                {
                    $realName = explode(':', $realName);
                    $groups[$userId] = trim(array_pop($realName));
                }
*/
            }
        }

        asort($groups);

        return $groups;
    }

    public function getGroupMemberUsers($userGroupId = 0, $separateDisabledUserAccounts = true)
    {
        # @return array

        # @description
        # <h2>Get Group Users</h2>
        # <p>
        #   Returns user members of a group.  Only immediate group members are returned,
        #   to retrieve group members recursively, use:
        #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#getGroupMembers'>hUser/hUserAuthenication/hUserAuthenication.library.php::getGroupMembers()</a>
        #   instead.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        $users = $this->hDatabase->getResults(
            $this->getTemplateSQL(
                array(
                    'userGroupId' => (int) $userGroupId
                )
            )
        );

        if ($separateDisabledUserAccounts)
        {
            $disabledGroupId = $this->user->getUserId('Disabled User Accounts');

            $data = array(
                'enabled' => array(),
                'disabled' => array()
            );

            foreach ($users as $userId)
            {
                if ($this->inGroup($disabledGroupId, $userId, false))
                {
                    array_push($data['disabled'], $userId);
                }
                else
                {
                    array_push($data['enabled'], $userId);
                }
            }

            return $data;
        }

        return $users;
    }

    public function getGroupMemberGroups($userGroupId)
    {
        # @return array

        # @description
        # <h2>Retrieving Member Groups of Groups</h2>
        # <p>
        #   Retrieves all member groups of the specified <var>$userGroupId</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        return $this->hDatabase->getResults(
            $this->getTemplateSQL(
                array(
                    'userGroupId' => (int) $userGroupId
                )
            )
        );
    }

    public function getGroups($userGroupId = 0, $prependValue = false, $prependString = '', $sortingMethod = 'Alphabetically')
    {
        # @return array

        # @description
        # <h2>Getting User Groups</h2>
        # <p>
        #   Returns user groups (an array containing an <var>hUserId</var> and <var>hUserName</var>
        #   for each group).  The 'root' group is renamed 'Developers' in the array returned.
        #   The array is sorted alphabetically by 'hUserName' (group name), by default, but can
        #   also be sorted by 'hUserName' by numbers contained in the group name (numbers can appear
        #   anywhere within the group name).  This method of sorting is called 'Subnumerically', and
        #   to invoke that method of sorting, provide the value 'Subnumerically' to
        #   <var>$sortingMethod</var>.
        # </p>
        # <p>
        #   Subnumeric Sorting Example:
        # </p>
        # <code>
        #   Tire Barn Warehouse #01
        #   Tire Barn Warehouse #02
        #   Tire Barn Warehouse #03
        #   Tire Barn Warehouse #04
        # </code>
        # <p>
        #   Subnumeric sorting extracts only the digits in the above example and sorts the groups
        #   by the digits instead of by the whole string.  If there are no digits in a user group
        #   name, null will be used to sort, which will become zero when converted to a number, and
        #   those groups will appear at the beginning of the list.
        # </p>
        # </p>
        # <h3>Prepending an Option</h3>
        # <p>
        #   A prepend value can be attached to the beginning of the
        #   returned array by setting <var>$prependValue</var> and <var>$prependString</var>.  This
        #   is useful if the groups returned will be used in an HTML <var>&lt;select&gt;</var>
        #   element.
        # </p>
        # <h3>Returning Sub-Groups</h3>
        # <p>
        #   If <var>$userGroupId</var> is provided, only groups that are members of that group will
        #   be returned.
        # </p>
        # <p>
        #   <var>$userGroupId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if ($prependValue)
        {
            $this->hDatabase->setPrependResult($prependString);
        }

        if (empty($userGroupId))
        {
            $groups = $this->replaceRootWithAdministrators(
                $this->hDatabase->selectColumnsAsKeyValue(
                    array(
                        'hUsers' => array('hUserId', 'hUserName')
                    ),
                    array(
                        'hUsers',
                        'hUserGroupProperties'
                    ),
                    array(
                        'hUsers.hUserId' => 'hUserGroupProperties.hUserId'
                    ),
                    'AND',
                    array(
                        'hUsers' => 'hUserName'
                    )
                )
            );
        }
        else if ($userGroupId > 0)
        {
            $groups = $this->replaceRootWithAdministrators(
                $this->hDatabase->selectColumnsAsKeyValue(
                    array(
                        'hUsers' => array('hUserId', 'hUserName')
                    ),
                    array(
                        'hUsers',
                        'hUserGroups',
                        'hUserGroupProperties'
                    ),
                    array(
                        'hUserGroups.hUserId' => array(
                            array('=', 'hUsers.hUserId'),
                            array('=', 'hUserGroupProperties.hUserId')
                        ),
                        'hUserGroups.hUserGroupId' => (int) $userGroupId
                    ),
                    'AND',
                    array(
                        'hUsers' => 'hUserName'
                    )
                )
            );
        }

        if ($sortingMethod == 'Subnumerically')
        {
            $index = array();

            foreach ($groups as $userId => $userName)
            {
                $matches = array();

                preg_match('/\d{1,}/', $userName, $matches);

                if (!isset($matches[0]) || empty($matches[0]))
                {
                    $matches[0] = 0;
                }

                $index[$userId] = $matches[0];
            }

            asort($index, SORT_NUMERIC);

            $sorted = array();

            foreach ($index as $userId => $i)
            {
                $sorted[$userId] = $groups[$userId];
            }

            $groups = $sorted;
        }

        return $groups;
    }

    public function getMemberGroups($userGroupId)
    {
        # @return array

        # @description
        # <h2>Retrieving Member Groups</h2>
        # <p>
        #   Returns groups that are a member of the group specified in <var>$userGroupId</var>.
        #   Data returned includes <var>hUserId</var>, <var>hUserName</var>, <var>hContactId</var>
        #   and <var>hContactCompany</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> can be any one of the
        #   following: <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if ($userGroupId > 0)
        {
            # Designed to retrieve "Company" groups... which are groups that
            # have contact information entered.
            return $this->hDatabase->select(
                array(
                    'hUsers'    => array('hUserId', 'hUserName'),
                    'hContacts' => array('hContactId', 'hContactCompany')
                ),
                array(
                    'hUsers',
                    'hContacts',
                    'hUserGroups',
                    'hUserGroupProperties'
                ),
                array(
                    'hUserGroups.hUserId' => array(
                        array('=', 'hUsers.hUserId'),
                        array('=', 'hContacts.hUserId'),
                        array('=', 'hUserGroupProperties.hUserId')
                    ),
                    'hUserGroups.hUserGroupId' => (int) $userGroupId
                ),
                'AND',
                array(
                    'hContacts' => 'hContactCompany'
                )
            );
        }
        else
        {
            $this->notice("Failed to get member groups because 'userGroupId' was not provided.", __FILE__, __LINE__);
            return array();
        }
    }

    public function queryGroupsByWildcard($term)
    {
        # @return array

        # @description
        # <h2>Querying Groups By Wildcard</h2>
        # <p>
        #   Returns groups that have a group name matching the wildcard provided in
        #   <var>$term</var>.
        # </p>
        # <p>
        #   <var>$term</var> is used in an SQL <var>LIKE</var> clause, it may be formatted
        #   with a modulus to indicate where the wildcard match should occur.  For example:
        # </p>
        # <code>
        #   term%
        # </code>
        # <p>
        #   Matches group names that <b>begin with</b> the string <i>term</i>.
        # </p>
        # <code>
        #   %term%
        # </code>
        # <p>
        #   Matches group names that <b>begins with</b>, <b>ends with</b>, or <b>contains</b> the string <i>term</i>.
        # </p>
        # <code>
        #   %term
        # </code>
        # <p>
        #   Matches group names that <b>ends with</b> the string <i>term</i>.
        # </p>
        # <p>
        #   The fastest query will be looking for strings that <b>begin with</b> <var>$term</var>,
        #   other types of wildcard matches will take much longer in relation to the size of the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a>
        #   database table involved.
        # </p>
        # <p>
        #   Data returned from this method is an array consisting of a <var>hUserId</var>, <var>hUserName</var>, and <var>hUserEmail</var>
        #   for each matching group.
        # </p>
        # @end

        if (!empty($term))
        {
            return $this->hDatabase->select(
                array(
                    'DISTINCT',
                    'hUsers' => array('hUserId', 'hUserName', 'hUserEmail')
                ),
                array(
                    'hUsers',
                    'hUserGroupProperties'
                ),
                array(
                    'hUsers.hUserId'   => 'hUserGroupProperties.hUserId',
                    'hUsers.hUserName' => array('LIKE', $term)
                ),
                'AND',
                array(
                    'hUsers' => 'hUserName'
                )
            );
        }
        else
        {
            $this->warning("Unable to query groups by wildcard because no search term was not provided.", __FILE__, __LINE__);
            return array();
        }
    }

    public function queryUsersByWildcard($term, $column = 'hContacts.hContactLastName', $contactAddressBookId = 1)
    {
        # @return array

        # @description
        # <h2>Querying Users By Wildcard</h2>
        # <p>
        #   Returns users that have a last name matching the wildcard provided in
        #   <var>$term</var>.
        # </p>
        # <p>
        #   <var>$term</var> is used in an SQL <var>LIKE</var> clause, it may be formatted
        #   with a modulus to indicate where the wildcard match should occur.  For example:
        # </p>
        # <code>
        #   term%
        # </code>
        # <p>
        #   Matches last names that <b>begin with</b> the string <i>term</i>.
        # </p>
        # <code>
        #   %term%
        # </code>
        # <p>
        #   Matches last names that <b>begins with</b>, <b>ends with</b>, or <b>contains</b> the string <i>term</i>.
        # </p>
        # <code>
        #   %term
        # </code>
        # <p>
        #   Matches last names that <b>ends with</b> the string <i>term</i>.
        # </p>
        # <p>
        #   The fastest query will be looking for strings that <b>begin with</b> <var>$term</var>,
        #   other types of wildcard matches will take much longer in relation to the size of the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hUsers/hUsers.sql'>hUsers</a>
        #   database table involved.
        # </p>
        # <p>
        #   Data returned from this method is an array consisting of a
        #   <var>hUserId</var>, <var>hUserName</var>, <var>hUserEmail</var>,
        #   <var>hContactFirstName</var>, <var>hContactLastName</var>, and <var>hContactCompany</var>
        #   for each matching user.
        # </p>
        # <p>
        #   The column used for the user search is specified in <var>$column</var> and can be
        #   any column in the <var>hUsers</var> or <var>hContacts</var> table that is a string.
        #   The default column is <var>hContacts.hContactLastName</var>.  To change the table column,
        #   simply provide the new value as <i>&lt;table&gt;</i>.<i>&lt;column&gt;</i>.  For example:
        #   <var>hUsers.hUserName</var> would be used to search user names instead of last names in
        #   the rolodex.
        # </p>
        # @end

        if (!empty($term))
        {
            $where = array(
                'hUsers.hUserId' => 'hContacts.hUserId',
                'hContacts.hContactAddressBookId' => (int) $contactAddressBookId
            );

            $where[$column] = array('LIKE', $term);

            return $this->hDatabase->select(
                array(
                    'DISTINCT',
                    'hUsers' => array('hUserId', 'hUserName', 'hUserEmail'),
                    'hContacts' => array('hContactFirstName', 'hContactLastName', 'hContactCompany')
                ),
                array(
                    'hUsers',
                    'hContacts'
                ),
                $where,
                'AND',
                array(
                    'hContacts' => 'hContactLastName'
                )
            );
        }
        else
        {
            $this->warning("Unable to query users by wildcard because no search term was not provided.", __FILE__, __LINE__);
            return array();
        }
    }

    public function getDocumentHistories($userId = 0)
    {
        # @return array

        # @description
        # <h2>Getting User Document History</h2>
        # <p>
        #   Retrieves document viewing history for the specified <var>$userId</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId)->whichUserId($userId);

        $this->hSearchDatabase = $this->database('hSearch');
        $this->hSearchHistoryRecent = true;
        $this->hSearch = $this->library('hSearch');

        $this->hSearchResultsPerPage = 25;
        $this->hSearchPagesPerChapter = 7;

        $limit = $this->hSearch->getLimit();

        $recentDocuments = $this->hSearchDatabase->queryHistory(
            null, $limit, $this->hUserSearchHistoryDirectories(array('/'.$this->hFrameworkSite)), array(), (int) $userId
        );

        $count = $this->hSearchDatabase->getResultCount();

        $this->hSearch->setParameters($count);

        return array(
            'history' => $recentDocuments,
            'pagination' => $this->hSearch->getNavigationHTML()
        );
    }

    public function getActivities($userId = 0)
    {
        # @return array

        # @description
        # <h2>Getting User Activity</h2>
        # <p>
        #   Retrieves user database activity for the specified <var>$userId</var>.
        # </p>
        # @end

        $this->user->setNumericUserId($userId)->whichUserId($userId);

        $this->hSearch = $this->library('hSearch');

        $this->hSearchResultsPerPage = 25;
        $this->hSearchPagesPerChapter = 7;

        $limit = $this->hSearch->getLimit();

        $activities = $this->getActivity((int) $userId, $limit);

        $count = $this->getActivityCount();

        $this->hSearch->setParameters($count);

        return array(
            'activity' => $this->hDatabase->getResultsForTemplate($activities),
            'pagination' => $this->hSearch->getNavigationHTML()
        );
    }

    public function getLoginInformation($userId = 0, $contactId = 0)
    {
        # @return array

        # @description
        # <h2>Getting User Login Information</h2>
        # <p>
        #   Retrieves user login information for the specified <var>$userId</var> or <var>$contactId</var>.
        #   If a <var>$contactId</var> is specified, login information is retrieved for the <var>hUserId</var>
        #   specified as the owner of the <var>hContactId</var>.
        # </p>
        # @end

        if (!empty($contactId))
        {
            $userId = $this->contact->getUserId((int) $contactId);
        }
        else
        {
            $this->user->setNumericUserId($userId)->whichUserId($userId);
        }

        if ($this->isGroup($userId))
        {
            $userGroup = $this->getGroupProperties($userId);
        }

        $history  = $this->getDocumentHistories($userId);
        $activity = $this->getActivities($userId);

        $pluginData = array();

        if ($this->hUserLoginInformationPlugin(null))
        {
            $this->hUserLoginInformation = $this->plugin($this->hUserLoginInformationPlugin);
            $pluginData = $this->hUserLoginInformation->getLoginInformation($userId);
        }

        return array_merge(
            array(
                'hUserName'  => $this->user->getUserName($userId),
                'hUserEmail' => $this->user->getUserEmail($userId),
                'hUserGroups' => $this->getGroupMembership($userId, array(), false),
                'hUserIsGroup' => $this->isGroup($userId),
                'hUserGroupOwner' => isset($userGroup['hUserGroupOwner'])? $this->user->getUserName($userGroup['hUserGroupOwner']) : '',
                'hUserGroupIsElevated' => isset($userGroup['hUserGroupIsElevated'])? $userGroup['hUserGroupIsElevated'] : '',
                'hUserGroupLoginEnabled' => isset($userGroup['hUserGroupLoginEnabled'])?$userGroup['hUserGroupLoginEnabled'] : '',
                'hUserHistory' => $history['history'],
                'hUserHistoryPagination' => $history['pagination'],
                'hUserActivity' => $activity['activity'],
                'hUserActivityPagination' => $activity['pagination']
            ),
            $this->getLog($userId),
            $pluginData
        );
    }
}

?>