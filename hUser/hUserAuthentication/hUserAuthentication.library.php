<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Authentication Library
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
# <h1>User Authentication API</h1>
# <p>
#
# </p>
# @end

class hUserAuthenticationLibrary extends hPlugin {

    private $isGroupCache = array();
    private $inGroupCache = array();
    private $worldReadCache = array();
    private $cache = array();
    private $groupMembers = array();
    private $permissionsIds = array();
    private $calendarDates = array();
    private $backtrace = false;

    public function isLoggedIn($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>See If a User Is Logged In</h2>
        # <p>
        #   See if a user is logged in, if no user is provided in the <var>$userId</var>
        #   argument.  If no user is provided, the function applies to the current user.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        if (empty($userId))
        {
            return (isset($_SESSION['hUserId']) && !empty($_SESSION['hUserId']));
        }
        else
        {
            $this->user->setNumericUserId($userId);

            // hUserId|i:7338
            return $this->hUserSessions->selectExists(
                'hUserSessionId',
                array(
                    'hUserSessionData' => array(
                        'LIKE',
                        "%hUserId|i:{$userId};%"
                    )
                )
            );
        }
    }

    public function setHTTPAuthentication($realm = null)
    {
        # @return boolean

        # @description
        # <h2>Invoking HTTP Authentication</h2>
        # <p>
        #   Call <var>setHTTPAuthentication()</var> to invoke HTTP authentication headers.
        # </p>
        # @end

        $isLoggedIn = (!empty($_SERVER['PHP_AUTH_USER']) && (
            $_SERVER['PHP_AUTH_USER'] == $_SESSION['hUserName'] ||
            $_SERVER['PHP_AUTH_USER'] == $_SESSION['hUserEmail']
        ));

        if (empty($_SERVER['PHP_AUTH_USER']) || !$isLoggedIn)
        {
            header('WWW-Authenticate: Basic realm="'.($realm? $realm : $this->hFrameworkName).'"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Login required.';
            exit;
        }
    }

    public function isActivated($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>See If a User Is Activated</h2>
        # <p>
        #   Determine if the specified user in the <var>$userId</var> argument is activated.
        #   If no user is specified, the function applies to the current user.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end
        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        return (bool) $this->hUsers->selectColumn(
            'hUserIsActivated',
            (int) $userId
        );
    }

    public function isSSLEnabled()
    {
        # @return boolean

        # @description
        # <h2>See If SSL Is Enabled and Active</h2>
        # <p>
        #   If the framework variable <var>hFileSSLEnabled</var> is <var>true</var>, SSL is
        #   enabled in the framework.  If <var>hFileSSLEnabled</var> is <var>true</var>, this
        #   function determines if the current request is over an active SSL/HTTPS connection.
        # </p>
        # @end

        return ($this->hFileSSLEnabled(false)? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') : true);
    }

    public function SSL()
    {
        # @return void

        # @description
        # <h2>Redirect to SSL</h2>
        # <p>
        #   If SSL is enabled, but the current request is not over an active SSL connection,
        #   this function redirects to an SSL version of the same page.
        # </p>
        # @end

        if (!$this->isSSLEnabled())
        {
            header('Location: https://'.$this->hServerHost.$this->href($this->hFilePath, $_GET));
            exit;
        }
    }

    public function setCache($userId, $userPermissionsType, $userPermissionsVariable, $userPermissionsValue)
    {
        # @return mixed

        # @description
        # <h2>Storing a Value in the User Permissions Cache</h2>
        # <p>
        #   This function determines if the <var>$userPermissionsType</var> and <var>$userPermissionsVariable</var>
        #   exists in the
        #   <a href='/System/Framework/Hot Toddy/hUser/hUserPermissions/Database/hUserPermissionsCache/hUserPermissionsCache.sql'>hUserPermissionsCache</a>
        #   for the specified <var>$userId</var>.  If the
        #   <var>$userPermissionsType</var> and <var>$userPermissionsVariable</var> do not exist in the
        #   <a href='/System/Framework/Hot Toddy/hUser/hUserPermissions/Database/hUserPermissionsCache/hUserPermissionsCache.sql'>hUserPermissionsCache</a>,
        #   the <var>$userPermissionsValue</var> is inserted.
        # </p>
        # @end

        $exists = $this->hUserPermissionsCache->selectExists(
            'hUserPermissionsValue',
            array(
                'hUserId'                  => (int) $userId,
                'hUserPermissionsType'     => $userPermissionsType,
                'hUserPermissionsVariable' => $userPermissionsVariable
            )
        );

        if (!$exists)
        {
            $this->hUserPermissionsCache->insert(
                array(
                    'hUserId'                  => (int) $userId,
                    'hUserPermissionsType'     => $userPermissionsType,
                    'hUserPermissionsVariable' => $userPermissionsVariable,
                    'hUserPermissionsValue'    => (int) $userPermissionsValue
                )
            );
        }

        return $userPermissionsValue;
    }

    public function getCache($userId, $userPermissionsType, $userPermissionsVariable)
    {
        # @return mixed

        # @description
        # <h2>Getting a Value From the User Permissions Cache</h2>
        # <p>
        #   This function returns a cached value from the
        #   <a href='/System/Framework/Hot Toddy/hUser/hUserPermissions/Database/hUserPermissionsCache/hUserPermissionsCache.sql' class='code' target='_blank'>hUserPermissionsCache</a>.  Values are
        #   first created and stored in memory in the <var>$cache</var> member property.  If the value
        #   if not found in the <var>$cache</var> array, the database table
        #   <a href='/System/Framework/Hot Toddy/hUser/hUserPermissions/Database/hUserPermissionsCache/hUserPermissionsCache.sql' class='code' target='_blank'>hUserPermissionsCache</a>
        #   is queried for the specified <var>$userId</var>, <var>$userPermissionsType</var>, and
        #   <var>$userPermissionsVariable</var>.
        # </p>
        # <p>
        #   This function makes certain inquires much faster.  For example, determining the group membership
        #   of a user or determining if a user has access to a particular document or resource.
        # </p>
        # <h3>Permissions Types</h3>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hUserPermissions</td>
        #           <td>A read/write permissions inquiry for a resource.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>isInElevated</td>
        #           <td>
        #               Whether or not the user is in a group of elevated privilege.
        #               provide administrative access of some sort.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>inGroup:0</td>
        #           <td>Whether or not a user is in a group (a non-root group).</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>inGroup:1</td>
        #           <td>Whether or not a user is in the <var>root</var> group.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $key = $userId.':'.$userPermissionsType.':'.$userPermissionsVariable;

        if (!isset($this->cache[$key]))
        {
            $results = $this->hUserPermissionsCache->select(
                'hUserPermissionsValue',
                array(
                    'hUserId'                  => (int) $userId,
                    'hUserPermissionsType'     => $userPermissionsType,
                    'hUserPermissionsVariable' => $userPermissionsVariable
                )
            );

            $result = (count($results)? (bool) $results[0]['hUserPermissionsValue'] : -1);

            if ($result !== -1)
            {
                // Cache the cache!
                $this->cache[$key] = $result;
            }

            return $result;
        }
        else
        {
            return $this->cache[$key];
        }
    }

    public function hasWorldRead($resource)
    {
        # @return boolean

        # @description
        # <h2>Determining World-Read On a Resource</h2>
        # <p>
        #   This function determines whether or not the specified resource has world-read access.
        # </p>
        # <p>
        #   <var>$resource</var> must be provided in the format <var>hFiles:1</var>. The table
        #   is specified before the colon, the unique id of the resource is specified after
        #   the colon.  See the database table
        #   <a href='/System/Framework/Hot Toddy/hFramework/hFrameworkResources/Database/hFrameworkResources/hFrameworkResources.sql' class='code' target='_blank'>hFrameworkResources</a>
        #   for a list of resources that can have ownership and read/write permissions applied to them.
        # </p>
        # @end

        if (!isset($this->worldReadCache[$resource]))
        {
            list($table, $key) = explode(':', $resource);

            $result = $this->hUserPermissions->selectExists(
                'hUserPermissionsId',
                array(
                    'hFrameworkResourceId'  => $this->getResourceId($table),
                    'hFrameworkResourceKey' => (int) $key,
                    'hUserPermissionsWorld' => array('LIKE', 'r%')
                )
            );

            $this->worldReadCache[$resource] = $result;
            return $result;
        }
        else
        {
            return $this->worldReadCache[$resource];
        }
    }

    public function checkPermissions(&$checkPermissions)
    {
        # @return hUserAuthenticationLibrary

        # @description
        # <h2>Determining Whether to Check Permissions</h2>
        # <p>
        #   For methods that implement permissions checks directly via database queries,
        #   these methods typically implement a pattern that allows permissions checks
        #   to be explicitly enabled or disabled. In those situations, if the user is
        #   logged in and is a member of the <var>root</var> group, permissions checks
        #   should be explicitly disabled, since <var>root</var> users have access to
        #   everything, by default.
        # </p>
        # <p>
        #   Such methods should create a variable such as <var>$checkPermissions</var>
        #   and pass that variable by reference to this method, where it will be
        #   set <var>false</var> if the user is, in fact, a member of the <var>root</var>
        #   group.
        # </p>
        # @end

        $checkPermissions = $this->inGroup('root')? false : $checkPermissions;

        #return $this;
    }

    public function checkWorldPermissions(&$checkPermissions)
    {
        # @return hUserAuthenticationLibrary

        # @description
        # <h2>Determining Whether to Check World Permissions</h2>
        # <p>
        #   For methods that implement permissions checks directly via database queries,
        #   these methods typically implement a pattern that allows permissions checks
        #   to be explicitly enabled or disabled. In those situations, if the user is
        #   logged in and is a member of the <var>root</var> group, permissions checks
        #   should be explicitly disabled, since <var>root</var> users have access to
        #   everything, by default.
        # </p>
        # <p>
        #   Such methods should create a variable such as <var>$checkPermissions</var>
        #   and pass that variable by reference to this method, where it will be
        #   set <var>false</var> if the user is, in fact, a member of the <var>root</var>
        #   group.
        # </p>
        # @end

        if ($checkPermissions === 'auto')
        {
            return $this->isLoggedIn() && !$this->inGroup('root') || !$this->isLoggedIn();
        }

        $checkPermissions = $this->inGroup('root') ? false : $checkPermissions;

        #return $this;
    }

    public function getUserGroupsForTemplate($checkPermissions, $userId)
    {
        # @return array

        # @description
        # <h2>Getting a List of the User's Groups For SQL Templates</h2>
        # <p>
        #   For methods that implement permissions checks directly via database queries,
        #   these methods typically implement a pattern that allows permissions checks
        #   to be explicitly enabled or disabled. In those situations, if the user is
        #   logged in and is a member of the <var>root</var> group, permissions checks
        #   should be explicitly disabled, since <var>root</var> users have access to
        #   everything, by default.
        # </p>
        # <p>
        #   If the user is not a <var>root</var> user and the <var>$checkPermissions</var>
        #   is <var>true</var>, a list of the user's groups is built so that the user's
        #   group membership can be queried as part of the permissions check for a resource.
        # </p>
        # <p>
        #   This method returns a list of groups suitable for inclusion in an SQL template.
        # </p>
        # <p>
        #   <a href='#getPermissionsVariablesForTemplate' class='code'>getPermissionsVariablesForTemplate()</a>
        #   returns all permissions-related variables needed for a permissions query in an
        #   SQL template.
        # </p>
        # @end

        $groups = array();

        if ($checkPermissions && $this->isLoggedIn())
        {
            $groupMembers = $this->getGroupMembership($userId);

            $groups = array(
                'hUserGroupId' => $groupMembers,
                'userGroupId'  => $groupMembers
            );
        }

        return $groups;
    }

    public function getPermissionsVariablesForTemplate($checkPermissions, $checkWorldPermissions = false, $level = 'r', $userId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Permissions Variables For Templates</h2>
        # <p>
        #   <var>$checkPermissions</var>, whether or not permissions should be checked in
        #   all categories, owner, group, and world.
        # </p>
        # <p>
        #   <var>$checkWorldPermissions</var>, whether or not permissions should be checked
        #   for world-read access.
        # </p>
        # <p>
        #   <var>$level</var>, the level of access, <var>r</var> for read, and <var>rw</var>
        #   for read-write.
        # </p>
        # <p>
        #   This function returns all template variables needed to query permissions using
        #   an SQL query.
        # </p>
        # @end

        $this->checkPermissions($checkPermissions);
        $this->checkWorldPermissions($checkWorldPermissions);

        if (empty($userId))
        {
            $userId = $this->isLoggedIn()? $_SESSION['hUserId'] : 0;
        }

        $userGroups = $this->getUserGroupsForTemplate($checkPermissions, $userId);

        return array(
            'checkPermissions'      => $checkPermissions,
            'checkWorldPermissions' => $checkWorldPermissions,
            'hUserId'               => $userId,
            'userId'                => $userId,
            'hUserGroups'           => $userGroups,
            'userGroups'            => $userGroups,
            'level'                 => $level
        );
    }

    public function hasPermission($resource, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Check a User's Permissions for Access to a Resource</h2>
        # <p>
        #   A <var>$resource</var> is the item for which you are checking the user's ability
        #   to access.  A resource is comprised of three components, built like so:
        #   <var>resourceName:resourceId:level</var>.
        #   The <var>resourceName</var> is the collection you are querying.  See the database
        #   table <a href='/System/Framework/Hot Toddy/hFramework/hFrameworkResources/Database/hFrameworkResources/hFrameworkResources.sql' class='code' target='_blank'>hFrameworkResources</a>
        #   for a list of resources that can have ownership
        #   and read/write permissions applied to them.  The <var>resourceId</var> is the unique
        #   id of the item you are querying permissions on.  For example, the resource might be
        #   a file, in which case the <var>resourceName</var> would be <var>hFiles</var>, and the
        #   <var>resourceId</var> would be the unique <var>hFileId</var> of the file you want
        #   to check the permissions of.  Finally, the <var>level</var> will be the type of access
        #   required, for read-only, you'd specify a level of <var>r</var>.  For read-write you'd
        #   specify a level of <var>rw</var>.
        # </p>
        # <p>
        #   The <var>$userId</var> is the user you want to check permissions for, if this argument
        #   is left empty, you will check permissions against the current user.  If the current user
        #   is not logged in, you'll be checking anoymous access, i.e., 'world' access.  When
        #   there is no user specified, you need access to be given to everyone in order to access
        #   a resource, therefore world-read is required for access to a resource.
        # </p>
        # <p>
        #   If the user cannot be found to have access, the function returns <var>false</var>.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        $type = 'hUserPermissions';

        // User has root access.
        if ($this->inGroup('root', $userId))
        {
            return $this->setCache($userId, $type, $resource, true);
        }

        list($table, $key, $access) = explode(':', $resource);

        // Symbolic Links inherit permissions
        if ($table == 'hFiles' && $this->hFileSymbolicLinkTo(0, $key))
        {
            $field[1] = $ln;
        }

        $frameworkResourceId = $this->getResourceId($table);

        $table = $this->getResource($frameworkResourceId);

        if (empty($userId) && $frameworkResourceId == 1)
        {
            // See if the content is timely, and deny access if accessing outside the roll on / roll off date.
            // This is not a complete solution, but it'll work for now.
            if (!isset($this->calendarDates[$key]))
            {
                $calendarFiles = $this->hCalendarFiles->select(
                    array(
                        'hCalendarBegin',
                        'hCalendarEnd'
                    ),
                    array(
                        'hFileId' => (int) $key
                    )
                );

                $this->calendarDates[$key] = false;

                foreach ($calendarFiles as $calendarFile)
                {
                    if ($calendarFile['hCalendarBegin'] > 0 && $calendarFile['hCalendarBegin'] >= time() || $calendarFile['hCalendarEnd'] > 0 && $calendarFile['hCalendarEnd'] <= time())
                    {
                        $this->calendarDates[$key] = true;
                        break;
                    }
                }
            }

            if ($this->calendarDates[$key])
            {
                return false;
            }
        }

        if (!isset($this->permissionsIds[$frameworkResourceId.','.$key]))
        {
            $userPermissionsId = $this->hUserPermissions->selectColumn(
                'hUserPermissionsId',
                   array(
                    'hFrameworkResourceId'  => (int) $frameworkResourceId,
                    'hFrameworkResourceKey' => (int) $key
                )
            );

            $this->permissionsIds[$frameworkResourceId.','.$key] = (int) $userPermissionsId;
        }

        $userPermissionsId = $this->permissionsIds[$frameworkResourceId.','.$key];

        if (empty($userPermissionsId))
        {
            return $this->setCache($userId, $type, $resource, false);
        }

        $hasWorldAccess = $this->hUserPermissions->selectExists(
            'hUserPermissionsId',
            array(
                'hUserPermissionsId' => (int) $userPermissionsId,
                'hUserPermissionsWorld' => array(
                    $access == 'r'? 'LIKE' : '=',
                    $access == 'r'? 'r%'   : 'rw'
                )
            )
        );

        if (!empty($hasWorldAccess))
        {
            return $this->setCache($userId, $type, $resource, true);
        }

        // In order to check the "owner" access privilege there must exist a user_id field
        // in the same table
        if (!empty($userId))
        {
            $columns[$table['hFrameworkResourcePrimaryKey']] = (int) $key;
            $columns['hUserId'] = (int) $userId;

            $isResourceOwner = $this->hDatabase->selectColumn(
                'hUserId',
                $table['hFrameworkResourceTable'],
                $columns
            );

            // Is the user is the owner of the resource?
            if (!empty($isResourceOwner))
            {
                $hasOwnerAccess = $this->hUserPermissions->selectExists(
                    'hUserPermissionsId',
                    array(
                        'hUserPermissionsId'    => (int) $userPermissionsId,
                        'hUserPermissionsOwner' => array(
                            $access == 'r'? 'LIKE' : '=',
                            $access == 'r'? 'r%'   : 'rw'
                        )
                    )
                );

                if (!empty($hasOwnerAccess))
                {
                     return $this->setCache(
                        $userId,
                        $type,
                        $resource,
                        true
                    );
                }
            }

            // Get all per user/group privileges that match the required access level.
            // If the access level is 'r', verifying 'read' access, include
            // get all users/groups with access 'r' or 'rw'.

            // If the access level is 'rw' get only users/groups with 'rw'.
            $userGroups = $this->hUserPermissionsGroups->select(
                'hUserGroupId',
                array(
                    'hUserPermissionsId'    => (int) $userPermissionsId,
                    'hUserPermissionsGroup' => array(
                        $access == 'r'? 'LIKE' : '=',
                        $access == 'r'? 'r%'   : 'rw'
                    )
                )
            );

            // Iterate through the returned ids, if the id is a group, see if the
            // id passed is a member of that group.  If the id is a user, see if
            // the id passed matches that id.
            foreach ($userGroups as $userGroupId)
            {
                if ($this->inGroup($userGroupId, $userId) || (int) $userGroupId === (int) $userId)
                {
                    return $this->setCache(
                        $userId,
                        $type,
                        $resource,
                        true
                    );
                }
            }

            return $this->setCache(
                $userId,
                $type,
                $resource,
                false
            );
        }
        else
        {
            return false;
        }

        return false;
    }

    public function notLoggedIn()
    {
        # @return hUserAuthenticationLibrary

        # @description
        # <h2>Automatically Providing Login/Registration</h2>
        # <p>
        #   If a user is found to not be logged in, a call to this function will
        #   provide a login form and a registration form, which can be fully customized.
        #   The login form is provided automatically when a user does not have the
        #   authorization to access a resource without first logging in.
        # </p>
        # @end

        $this->plugin('hUser/hUserLogin');

        #return $this;
    }

    public function loggedIn()
    {
        # @return boolean

        # @description
        # <h2>Automatically Providing Login/Registration</h2>
        # <p>
        #   If a user is found to not be logged in, a call to this function will
        #   provide a login form and a registration form, which can be fully customized.
        #   The login form is provided automatically when a user does not have the
        #   authorization to access a resource without first logging in.
        # </p>
        # <p>
        #   This function does an additional check to see if a user is logged in before
        #   presenting a login/registration form.
        # </p>
        # @end

        if ($this->isLoggedIn())
        {
            return true;
        }
        else
        {
            $this->notLoggedIn();
            return false;
        }
    }

    public function notAuthorized($fileTitle = null, $fileDocument = null)
    {
        # @return hUserAuthenticationLibrary

        # @description
        # <h2>Not Authorized</h2>
        # <p>
        #   If a user is found to not have authorization to access a resource, even
        #   after logging in, the user meets with a simple, bleak error message explaining
        #   that the user is not authorized to access a resource.  This message is
        #   provided by this function call.  The 'Not Authorized' message is entirely
        #   customizable with the framework variables <var>hUserAuthenticationNotAuthorizedTitle</var>
        #   and <var>hUserAuthenticationNotAuthorizedTemplate</var>.
        # </p>
        # @end

        if ($this->isLoggedIn())
        {
            if (!empty($fileTitle))
            {
                $this->hFileTitle = $fileTitle;
            }
            else
            {
                $this->hFileTitle = $this->hUserAuthenticationNotAuthorizedTitle('Not Authorized');
            }

            if (!empty($fileDocument))
            {
                $this->hFileDocument = $fileDocument;
            }
            else
            {
                $this->hFileDocument = $this->getTemplate(
                    $this->hUserAuthenticationNotAuthorizedTemplate('Not Authorized')
                );
            }
        }
        else
        {
            $this->plugin('hUser/hUserLogin');
        }

        #return $this;
    }

    public function inGroup($userGroupId, $userId = 0, $root = true)
    {
        # @return boolean

        # @description
        # <h2>Determining Group Membership</h2>
        # <p>
        #   Determines whether the specified <var>$userId</var> is in the group specified in
        #   <var>$userGroupId</var>.  Searches group members, if the group member is another
        #   group it searches members of that group, and so on, recusively gathering group members
        #   until a match is found.
        # </p>
        # <p>
        #   If the user is a member of the <var>root</var> group, this function will always
        #   return <var>true</var>.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->hUser
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if (empty($userId))
        {
            return false;
        }

        if (!is_numeric($userGroupId))
        {
            if ($userGroupId == 'root')
            {
                $root = false;
            }

            $userGroupId = $this->getGroupId($userGroupId);
        }
        else if ($userGroupId == $this->getGroupId('root'))
        {
            $root = false;
        }

        // Group is in itself!
        if ($userId === $userGroupId)
        {
            return true;
        }

        if (($value = $this->getCache($userId, 'inGroup:'.(int) $root, $userGroupId)) !== -1)
        {
            if (empty($value) && $root)
            {
                return $this->inGroup(
                    'root',
                    $userId,
                    false
                );
            }

            return $value;
        }

        $group = $this->getGroupMembers($userGroupId);

        if (in_array($userId, $group['hUserGroups']) || in_array($userId, $group['hUsers']))
        {
            $this->setCache(
                $userId,
                'inGroup:'.(int) $root,
                $userGroupId,
                true
            );

            return true;
        }
        else if ($root)
        {
            $this->setCache(
                $userId,
                'inGroup:'.(int) $root,
                $userGroupId,
                false
            );

            return $this->inGroup(
                'root',
                $userId,
                false
            );
        }
        else
        {
            $this->setCache(
                $userId,
                'inGroup:'.(int) $root,
                $userGroupId,
                false
            );

            return false;
        }
    }

    public function inAnyOfTheFollowingGroups(array $groups, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining Membership in Any of a Selection of Groups</h2>
        # <p>
        #   This method returns true if the user is a member of any of the supplied
        #   groups.
        # </p>
        # @end

        $this->hUser
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        foreach ($groups as $group)
        {
            if ($this->inGroup($group, $userId))
            {
                return true;
            }
        }

        return false;
    }

    public function inAllOfTheFollowingGroups(array $groups, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining Membership in All of a Selection of Groups</h2>
        # <p>
        #   This method returns true if the user is a member of all of the supplied
        #   groups.
        # </p>
        # @end

        $this->hUser
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        foreach ($groups as $group)
        {
            if (!$this->inGroup($group, $userId))
            {
                return false;
            }
        }

        return true;
    }

    public function deleteCachedGroupData($userGroupId)
    {
        # @return hUserAuthenticationLibrary

        # @description
        # <h2>Deleteing Local Group Cache</h2>
        # <p>
        #   Removed cached group values for group membership and 'isGroup' from memory.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if (isset($this->isGroupCache[$userGroupId]))
        {
            unset($this->isGroupCache[$userGroupId]);
        }

        if (isset($this->groupMembers[$userGroupId]))
        {
            unset($this->groupMembers[$userGroupId]);
        }

        #return $this;
    }

    public function getGroupMembership($userId = 0, $results = array(), $recursive = true)
    {
        # @return array

        # @description
        # <h2>Getting Group Membership</h2>
        # <p>
        #   Returns the group(s) the user <var>$userId</var> is a member of.  If no user is
        #   specified in <var>$userId</var>, the current user is assumed.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        // Get the groups this user is a member of, and the groups those groups are a member of.
        $userGroups = $this->hUserGroups->select(
            'hUserGroupId',
            array(
                'hUserId' => (int) $userId
            )
        );

        foreach ($userGroups as $userGroupId)
        {
            if (!in_array((int) $userGroupId, $results))
            {
                $results[] = (int) $userGroupId;
            }

            if ($recursive)
            {
                $results = array_merge(
                    $this->getGroupMembership((int) $userGroupId),
                    $results
                );
            }
        }

        return array_unique($results);
    }

    public function isDomainGroup($group)
    {
        # @return boolean

        # @description
        # <h2>Identifying Domain Groups</h2>
        # <p>
        #   Determines if a group is a synced network domain group based on the
        #   values set for the framework variables <var>hUserWinbindDomain</var>
        #   and <var>hUserWinbindSeparator</var>.
        # </p>
        # @end

        $domain = $this->hUserWinbindDomain.$this->hUserWinbindSeparator;
        return (substr($group, 0, strlen($domain)) == $domain);
    }

    public function isElevated($userGroupId)
    {
        # @return boolean

        # @description
        # <h2>Identifying Elevated Groups</h2>
        # <p>
        #   Determines if a group is an elevated group.  Elevated groups typically
        #   have significant administrative privileges.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        return (bool) $this->hUserGroupProperties->selectColumn(
            'hUserGroupIsElevated',
            array(
                'hUserId' => $userGroupId
            )
        );
    }

    public function isInElevated($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Identifying Whether a User Is In An Elevated Group</h2>
        # <p>
        #   Determines if a user is in an elevated group. If no user is
        #   passed in the <var>$userId</var> argument, the current user is
        #   assumed.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user
            ->setNumericUserId($userId)
            ->whichUserId($userId);

        if (empty($userId))
        {
            return false;
        }

        if (($value = $this->getCache($userId, 'isInElevated', 'isInElevated')) >= 0)
        {
            return $value;
        }

        $userGroups = $this->hUserGroups->select(
            'hUserGroupId',
            array(
                'hUserId' => (int) $userId
            )
        );

        foreach ($userGroups as $i => $userGroupId)
        {
            $userGroupIsElevated = (int) $this->hUserGroupProperties->selectColumn(
                'hUserGroupIsElevated',
                array(
                    'hUserId' => $userGroupId
                )
            );

            if (!empty($userGroupIsElevated))
            {
                return $this->setCache(
                    $userId,
                    'isInElevated',
                    'isInElevated',
                    true
                );
            }
        }

        return $this->setCache(
            $userId,
            'isInElevated',
            'isInElevated',
            false
        );
    }

    public function getGroupLiaison($userGroupId)
    {
        # @return boolean

        # @description
        # <h2>Getting the Group Liaison</h2>
        # <p>
        #   Returns the group liaison for the specified <var>$userGroupId</var>.
        # </p>
        # <p>
        #   <var>$userGroupId</var> may be a <var>userGroupId</var>, <var>userGroupName</var>, or
        #   <var>groupEmailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        return $this->hUserGroupProperties->selectColumn(
            'hUserGroupOwnerId',
            array(
                'hUserId' => (int) $userGroupId
            )
        );
    }

    public function getGroupMembers($userGroupId)
    {
        # @return array

        # @description
        # <h2>Getting Group Members</h2>
        # <p>
        #   Returns group members of the group specified in <var>$userGroupId</var>,
        #   group membership is determined recursively, a group is considered a member of any
        #   groups the groups it is in is a member of, and so on.
        # </p>
        # <p>
        #   Group membership, once determined, is cached in the <var>$groupMembers</var> member
        #   variable.
        # </p>
        # <p>
        #   <var>$userGroupId</var> may be a <var>userGroupId</var>, <var>userGroupName</var>, or
        #   <var>groupEmailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if (!isset($this->groupMembers[$userGroupId]))
        {
            $this->setGroupMembers($userGroupId);
        }

        return $this->groupMembers[$userGroupId];
    }

    private function setGroupMembers($userGroupId, $userSubGroupId = 0)
    {
        # @return array

        # @description
        # <h2>Setting Group Members</h2>
        # <p>
        #   Caches group membership in the <var>$groupMembers</var> member variable.
        # </p>
        # <p>
        #   To get group members, call <a href='#getGroupMembers' class='code'>getGroupMembers()</a>
        # </p>
        # <p>
        #   <var>$userGroupId</var> may be a <var>userGroupId</var>, <var>userGroupName</var>, or
        #   <var>groupEmailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        $userGroupId = (int) $userGroupId;
        $userSubGroupId = (int) $userSubGroupId;

        if (!isset($this->groupMembers[$userGroupId]))
        {
            $this->groupMembers[$userGroupId] = array(
                'hUserGroups' => array(),
                'hUsers' => array()
            );
        }

        $users = $this->hUserGroups->select(
            'hUserId',
            array(
                'hUserGroupId' => empty($userSubGroupId)? (int) $userGroupId : (int) $userSubGroupId
            )
        );

        if (count($users))
        {
            foreach ($users as $userId)
            {
                $userId = (int) $userId;

                if ($this->isGroup($userId))
                {
                    if (!in_array($userId, $this->groupMembers[$userGroupId]['hUserGroups']))
                    {
                        $this->groupMembers[$userGroupId]['hUserGroups'][] = $userId;

                        if (!empty($userSubGroupId))
                        {
                            $this->groupMembers[$userSubGroupId]['hUserGroups'][] = $userId;
                        }

                        $this->setGroupMembers($userGroupId, $userId);
                    }
                }
                else
                {
                    if (!in_array($userId, $this->groupMembers[$userGroupId]['hUsers']))
                    {
                        $this->groupMembers[$userGroupId]['hUsers'][] = $userId;

                        if (!empty($userSubGroupId))
                        {
                            $this->groupMembers[$userSubGroupId]['hUsers'][] = $userId;
                        }
                    }
                }
            }
        }

        return;
    }

    public function getUsersInGroup($userGroupId)
    {
        # @return array

        # @description
        # <h2>Getting a Group's User Members</h2>
        # <p>
        #   Returns user members of the group specified in <var>$userGroupId</var>,
        #   group membership is determined recursively, a group is considered a member of any
        #   groups the groups it is in is a member of, and so on.
        # </p>
        # <p>
        #   Group membership, once determined, is cached in the <var>$groupMembers</var> member
        #   variable, and furture inquiries for the group's membership will return the cached
        #   copy.
        # </p>
        # <p>
        #   <var>$userGroupId</var> may be a <var>userGroupId</var>, <var>userGroupName</var>, or
        #   <var>groupEmailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $group = $this->getGroupMembers($userGroupId);

        if (isset($group['hUsers']))
        {
            return $group['hUsers'];
        }

        return array();
    }

    public function getGroupsInGroup($userGroupId)
    {
        # @return array

        # @description
        # <h2>Getting a Group's Group Members</h2>
        # <p>
        #   Returns group members of the group specified in <var>$userGroupId</var>,
        #   group membership is determined recursively, a group is considered a member of any
        #   groups the groups it is in is a member of, and so on.
        # </p>
        # <p>
        #   Group membership, once determined, is cached in the <var>$groupMembers</var> member
        #   variable, and furture inquiries for the group's membership will return the cached
        #   copy.
        # </p>
        # <p>
        #   <var>$userGroupId</var> may be a <var>userGroupId</var>, <var>userGroupName</var>, or
        #   <var>groupEmailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $group = $this->getGroupMembers($userGroupId);

        if (isset($group['hUserGroups']))
        {
            return $group['hUserGroups'];
        }

        return array();
    }

    public function getGroupId($userGroup)
    {
        # @return integer, false

        # @description
        # <h2>Getting a Group Id</h2>
        # <p>
        #   Gets the <var>userGroupId</var> (same thing as a <var>userId</var>,
        #   of a group from the group name specified in <var>$userGroup</var>.  Since
        #   all groups are users, this just calls on the library method
        #   <a href='/Hot Toddy/Documentation?hUser/hUser.library.php#getUserId' class='code'>$this-&gt;hUser-&gt;getUserId()</a>, this
        #   method, however, also checks that the <var>$userGroup</var> is a valid group.
        # </p>
        # <p>
        #   If <var>$userGroup</var> is not a valid group, this method throws a warning to
        #   the error console alerting of that fact.
        # </p>
        # @end

        if (!empty($userGroup))
        {
            $userId = $this->user->getUserId($userGroup);

            if (!empty($userId))
            {
                if ($this->isGroup($userId))
                {
                    return $userId;
                }
                else
                {
                    $this->warning(
                        "Group name provided '{$userGroup}' is not a group.",
                        __FILE__,
                        __LINE__
                    );

                    return false;
                }
            }
            else
            {
                $this->warning(
                    "Unable to get a user id from group name '{$userGroup}'.",
                    __FILE__,
                    __LINE__
                );

                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function isGroup($userGroupId)
    {
        # @return boolean

        # @description
        # <h2>Determining If a Group Is a Group</h2>
        # <p>
        #   Determines if the group passed as <var>$userGroup</var> is a group.
        # </p>
        # @end

        $this->user->setNumericUserId($userGroupId);

        if (isset($this->isGroupCache[$userGroupId]))
        {
            return $this->isGroupCache[$userGroupId];
        }

        $isGroup = (bool) $this->hUserGroupProperties->selectColumn(
            'hUserId',
            array(
                'hUserId' => (int) $userGroupId
            )
        );

        return ($this->isGroupCache[$userGroupId] = $isGroup);
    }

    public function groupExists($userGroup)
    {
        # @return boolean

        # @description
        # <h2>Determining If a Group Exists</h2>
        # <p>
        #   Determines if the group passed as <var>$userGroup</var> exists.
        # </p>
        # @end

        if (is_numeric($userGroup))
        {
            $userGroup = $this->user->getUserName($userGroup);
        }

        return (bool) $this->hDatabase->selectColumn(
            array(
                'hUserGroupProperties' => 'hUserId'
            ),
            array(
                'hUsers',
                'hUserGroupProperties'
            ),
            array(
                'hUsers.hUserId'   => 'hUserGroupProperties.hUserId',
                'hUsers.hUserName' => $userGroup
            )
        );
    }

    public function searchForGroup($term, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Search For Group a User Is a Member Of</h2>
        # <p>
        #   Searches the users account specified in <var>$userId</var> for the term
        #   specified in <var>$term</var>.  <var>$term</var> can include modulus wildcard
        #   charcters. For example, search 'Store #%' if you have groups named for stores
        #   but don't know the store number.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user->setNumericUserId($userId)
             ->whichUserId($userId);

        return $this->hDatabase->selectColumn(
            array(
                'hUsers' => 'hUserName'
            ),
            array(
                'hUsers',
                'hUserGroups',
                'hUserGroupProperties'
            ),
            array(
                'hUsers.hUserId' => array(
                    array('=', 'hUserGroupProperties.hUserId'),
                    array('=', 'hUserGroups.hUserGroupId')
                ),
                'hUserGroups.hUserId' => (int) $userId,
                'hUsers.hUserName'    => array('LIKE', $term)
            )
        );
    }

    public function searchForGroups($term, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Search For Groups a User Is a Member Of</h2>
        # <p>
        #   Searches the users account specified in <var>$userId</var> for the term
        #   specified in <var>$term</var>.  <var>$term</var> can include modulus wildcard
        #   charcters. For example, search 'Store #%' if you have groups named for stores
        #   but don't know the store number.
        # </p>
        # <p>
        #   <var>$userId</var> may be a <var>userId</var>, <var>userName</var>, or
        #   <var>emailAddress</var>, any of these three unique login identifiers.
        # </p>
        # @end

        $this->user->setNumericUserId($userId)
             ->whichUserId($userId);

        return $this->hDatabase->select(
            array(
                'hUsers' => array(
                    'hUserId',
                    'hUserName'
                )
            ),
            array(
                'hUsers',
                'hUserGroups',
                'hUserGroupProperties'
            ),
            array(
                'hUsers.hUserId' => array(
                    array('=', 'hUserGroupProperties.hUserId'),
                    array('=', 'hUserGroups.hUserGroupId')
                ),
                'hUserGroups.hUserId' => (int) $userId,
                'hUsers.hUserName' => array('LIKE', $term)
            )
        );
    }

    public function isAuthor()
    {
        # @return boolean

        # @description
        # <h2>Determining the Document Author</h2>
        # <p>
        #   <var>isAuthor()</var> returns <var>true</var> if the current user is the
        #   author of the current document.
        # </p>
        # @end

        return (isset($_SESSION['hUserId']) && $_SESSION['hUserId'] == $this->hUserId);
    }
}

?>