<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Library
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
# <h1>User API</h1>
# <p>
#   The <var>hUserLibrary</var> object is made globally available in every Hot Toddy
#   plugin as <var>$this-&gt;hUser</var>, it provides an API to easily store and retrieve
#   data associated with users.
# </p>
# <h2>Getting a User Id</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getUserId(<i>$userEmail</i> | <i>$userName</i>);
# </code>
# <p>
#   Returns the <var>userId</var> for the specified <var>$user</var>.  <var>$user</var>
#   can be <var>userName</var> or <var>userEmail</var>.
# </p>
# <p>
#   If <var>$user</var> is not specified the <var>userId</var> for the current user
#   is returned.
# </p>
# <p>
#   Once a <var>userId</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User Name</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getUserName(<i>$userEmail</i> | <i>$userId</i>);
# </code>
# <p>
#   Returns the <var>userName</var> for the specified <var>$user</var>.  <var>$user</var>
#   can be <var>userId</var> or <var>userEmail</var>.
# </p>
# <p>
#   If <var>$user</var> is not specified the <var>userName</var> for the current user
#   is returned.
# </p>
# <p>
#   Once a <var>userName</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User Email</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getUserEmail(<i>$userName</i> | <i>$userId</i>);
# </code>
# <p>
#   Returns the <var>userEmail</var> for the specified <var>$user</var>.  <var>$user</var>
#   can be <var>userId</var> or <var>userName</var>.
# </p>
# <p>
#   If <var>$user</var> is not specified the <var>userEmail</var> for the current user
#   is returned.
# </p>
# <p>
#   Once a <var>userEmail</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Full Name</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getFullName(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Alias of: <a href='#getDisplayName' class='code'>getDisplayName()</a>
# </p>
# <h2>Getting a User's Display Name</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getDisplayName(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactDisplayName</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactDisplayName</var> for the current user
#   is returned.
# </p>
# <p>
#   The display name can be any value entered in the database, but it is typically the
#   contactFirstName and contactLastName together, separated by a single space.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactDisplayName</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's First Name</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getFirstName(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactFirstName</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactFirstName</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactFirstName</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Last Name</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getLastName(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactLastName</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactLastName</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactLastName</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Company</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getCompany(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactCompany</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactCompany</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactCompany</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Job Title</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getTitle(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactTitle</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactTitle</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactTitle</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Contact Id</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getContactId(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactId</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactId</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactId</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Contact Date of Birth</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getDateOfBirth(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactDateOfBirth</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactDateOfBirth</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactDateOfBirth</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# <h2>Getting a User's Contact Gender</h2>
# <code>
#   <i>string</i> $this-&gt;hUser-&gt;getGender(<i>$userId = 0</i>, <i>$contactAddressBookId = 1</i>);
# </code>
# <p>
#   Returns the <var>contactGender</var> for the specified <var>$userId</var>.
#   If no user is specified, the <var>contactGender</var> for the current user
#   is returned.
# </p>
# <p>
#   <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
# </p>
# <p>
#   Once a <var>contactGender</var> is determined, it is cached in the <var>$user</var> member
#   variable for faster, more efficient subsequent lookups.
# </p>
# @end

class hUserLibrary extends hPlugin {

    public $userId = 0;
    public $userName = '';

    private $hContactDatabase;

    private $users = array();
    private $cache = true;

    private $methods = array(
        'getUserId',
        'getUserName',
        'getUserEmail'
    );

    private $contactMethods = array(
        'getFullName',
        'getDisplayName',
        'getFirstName',
        'getLastName',
        'getCompany',
        'getTitle',
        'getContactId',
        'getDateOfBirth',
        'getGender'
    );

    private $counter = 0;

    public function &setCache($cache)
    {
        # @return hUserLibrary

        # @description
        # <h2>Set Cache</h2>
        # <p>
        #   Enables or disables cache.
        # </p>
        # @end

        $this->cache = $cache;

        return $this;
    }

    public function __call($method, $arguments)
    {
        # @return

        # @description
        # <h2>Dynamically Overloaded Methods</h2>
        # <p>
        #
        # </p>
        # @end

        $isUnderscoreMethod = substr($method, 0, 1) == '_';
        $underscoreMethodName = null;

        if ($isUnderscoreMethod)
        {
            $underscoreMethodName = substr($method, 1);
        }

        $isUserMethod = (
            in_array($method, $this->methods, true) ||
            in_array($method, $this->contactMethods, true) ||
            in_array($underscoreMethodName, $this->methods, true) ||
            in_array($underscoreMethodName, $this->contactMethods, true)
        );

        if (!$isUserMethod)
        {
            return parent::__call($method, $arguments);
        }
        else
        {
            if ($isUnderscoreMethod)
            {
                $user = isset($arguments[0])? $arguments[0] : 0;

                if (in_array($underscoreMethodName, $this->methods))
                {
                    switch ($method)
                    {
                        case '_getUserId':
                        {
                            if (empty($user) && $this->isLoggedIn())
                            {
                                return $_SESSION['hUserId'];
                            }

                            return (int) $this->hUsers->selectColumn(
                                'hUserId',
                                array(
                                    'hUserName' => $user,
                                    'hUserEmail' => $user
                                ),
                                'OR'
                            );
                        }
                        case '_getUserName':
                        {
                            if (empty($user) && $this->isLoggedIn())
                            {
                                return $_SESSION['hUserName'];
                            }

                            return $this->hUsers->selectColumn(
                                'hUserName',
                                is_numeric($user)?
                                    array(
                                        'hUserId' => (int) $user
                                    ) :
                                    array(
                                        'hUserEmail' => $user
                                    )
                            );
                        }
                        case '_getUserEmail':
                        {
                            if (empty($user) && $this->isLoggedIn())
                            {
                                return $_SESSION['hUserEmail'];
                            }

                            return $this->hUsers->selectColumn(
                                'hUserEmail',
                                is_numeric($user)?
                                    array(
                                        'hUserId' => (int) $user
                                    ) :
                                    array(
                                        'hUserName' => $user
                                    )
                            );
                        }
                    }
                }
                else if (in_array($underscoreMethodName, $this->contactMethods))
                {
                    $userId = $arguments[0];
                    $contactAddressBookId = $arguments[1];

                    return $this->getContactField(
                        $this->getContactFieldName($underscoreMethodName), $userId, $contactAddressBookId
                    );
                }
            }
            else
            {
                $user = isset($arguments[0])? $arguments[0] : 0;

                if (in_array($method, $this->methods, true))
                {
                    $sessionKey = '';
                    $key = '';

                    switch ($method)
                    {
                        case 'getUserId':
                        {
                            $sessionKey = 'hUserId';
                            break;
                        }
                        case 'getUserName':
                        {
                            $sessionKey = 'hUserName';
                            break;
                        }
                        case 'getUserEmail':
                        {
                            $sessionKey = 'hUserEmail';
                            break;
                        }
                    }

                    if (!empty($sessionKey) && empty($user))
                    {
                        if ($this->isLoggedIn())
                        {
                            if (!empty($_SESSION[$sessionKey]))
                            {
                                $this->users[$method.':'.$user] = $_SESSION[$sessionKey];
                                return $this->users[$method.':'.$user];
                            }
                            else
                            {
                                $this->warning("Unable to map a default value to $user using '{$method}' because there is no session.", __FILE__, __LINE__);
                            }
                        }
                    }

                    $key = $method.':'.$user;

                    if (!isset($this->users[$key]))
                    {
                        $this->users[$key] = $this->{"_{$method}"}($user);
                    }
                }
                else if (in_array($method, $this->contactMethods, true))
                {
                    $this->whichUserId($user)->setNumericUserId($user);

                    $contactAddressBookId = isset($arguments[1])? (int) $arguments[1] : 1;

                    $key = $method.':'.$contactAddressBookId.':'.$user;

                    if (!isset($this->users[$key]))
                    {
                        $this->users[$key] = $this->{"_{$method}"}($user, $contactAddressBookId);
                    }
                }

                if ($this->cache)
                {
                    return $this->users[$key];
                }
                else
                {
                    $data = $this->users[$key];
                    unset($this->users[$key]);
                    return $data;
                }
            }
        }
    }

    private function getContactFieldName($method)
    {
        # @return string

        # @description
        # <h2>Get Contact Field Name</h2>
        # <p>
        #   Returns a contact field based on the name of the provided method.
        #   For example, for the method name <var>getDisplayName</var>, the
        #   field <var>hContactDisplayName</var> is returned.
        # </p>
        # @end

        if ($method == 'getFullName')
        {
            $method = 'getDisplayName';
        }

        if ($method == 'getContactId')
        {
            return 'hContactId';
        }

        return 'hContact'.str_replace('get', '', $method);
    }

    public function getFilePath($userId = 0, $contactAddressBookId = 1, array $options = array())
    {
        # @return string

        # @description
        # <h2>Get File Path for User Image or File</h2>
        # <p>
        #   Returns a file path for a user image or file, such as for a photograph
        #   assigned to a user account.
        # </p>
        # @end

        $this->whichUserId($userId)
             ->setNumericUserId($userId);

        return $this->getFilePathByFileId(
            $this->getFileId(
                $userId,
                $contactAddressBookId,
                $options
            )
        );
    }

    public function getFileId($userId = 0, $contactAddressBookId = 1, array $options = array())
    {
        # @return integer

        # @description
        # <h2>Get File Id For User Image or File</h2>
        # <p>
        #   Returns a fileId for a user image or file, such as for a photograph
        #   assigned to a user account.
        # </p>
        # @end

        $this->whichUserId($userId)
             ->setNumericUserId($userId);

        $options['hContactId'] = $this->getContactId($userId, $contactAddressBookId);

        if (!isset($options['hContactFileCatgoryId']))
        {
            $options['hContactFileCategoryId'] = 1;
        }

        if (!isset($options['hContactIsProfilePhoto']))
        {
            $options['hContactIsProfilePhoto'] = 1;
        }

        if (!isset($options['hContactIsDefaultProfilePhoto']))
        {
            $options['hContactIsDefaultProfilePhoto'] = 1;
        }

        return $this->hContactFiles->selectColumn('hFileId', $options);
    }

    public function getGenderLabel($userId = 0, $contactAddressBookId = 1, $male = 'Male', $female = 'Female')
    {
        # @return string

        # @description
        # <h2>Getting a Gender Label</h2>
        # <p>
        #   Returns the gender label for the specified user in the specified address
        #   book. The gender labels returned can be provided in the <var>$male</var>
        #   and <var>$female</var> arguments.
        # </p>
        # @end

        $this->whichUserId($userId)
             ->setNumericUserId($userId);

        if ($this->getGender($userId, $contactAddressBookId))
        {
            return $male;
        }
        else
        {
            return $female;
        }
    }

    public function &deleteContacts($userId = 0, $contactAddressBookId = 1)
    {
        # @return hUserLibrary

        # @description
        # <h2>Deleting Contacts By User Id</h2>
        # <p>
        #   Deletes contacts from the address book based on the provided userId.
        # </p>
        # @end

        $this->whichUserId($userId)
             ->setNumericUserId($userId);

        $contacts = $this->hContacts->select(
            'hContactId',
            array(
                'hContactAddressBookId' => $contactAddressBookId,
                'hUserId' => $userId
            )
        );

        $this->hContactDatabase = $this->database('hContact');

        foreach ($contacts as $contactId)
        {
            $this->hContactDatabase->delete($contactId);
        }

        return $this;
    }

    public function getContactField($field, $userId = 0, $contactAddressBookId = 1)
    {
        # @return mixed

        # @description
        # <h2>Getting a Contact Field For a User</h2>
        # <p>
        # Returns the <var>contactGender</var> for the specified <var>$userId</var>.
        # If no user is specified, the <var>contactGender</var> for the current user
        # is returned.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <p>
        # Once a <var>contactGender</var> is determined, it is cached in the <var>$user</var> member
        # variable for faster, more efficient subsequent lookups.
        # </p>
        # @end

        $this->whichUserId($userId)
             ->setNumericUserId($userId);

        $this->hDatabase->setDefaultResult(null);

        return $this->hContacts->selectColumn(
            $field,
            array(
                'hUserId' => (int) $userId,
                'hContactAddressBookId' => $contactAddressBookId
            )
        );
    }

    public function &whichUserName(&$userName, $setToAuthor = false)
    {
        # @return hUserLibrary

        # @description
        # <h2>Autosetting a User Name</h2>
        # <p>
        # This method is called in other methods that accept a <var>$userName</var>
        # argument, if no value is passed in the original <var>$userName</var> argument,
        # the value of <var>$userName</var> is set to <var>$_SESSION['hUserName']</var>,
        # the <var>userName</var> of the user logged in.
        # </p>
        # <p>
        # Optionally, the <var>$userName</var> can be set to the document author's <var>userName</var>
        # by setting the optional <var>$setToAuthor</var> argument to <var>true</var>.
        # </p>
        # @end

        if (empty($userName))
        {
            if ($this->isLoggedIn())
            {
                $userName = $_SESSION['hUserName'];
            }
            else if ($setToAuthor && !empty($this->hUserName))
            {
                $userName = $this->hUserName;
            }
        }
        else
        {
            $userName = is_numeric($userName)? $this->getUserName($userName) : $userName;
        }

        return $this;
    }

    public function &whichUserId(&$userId, $setToAuthor = false)
    {
        # @return hUserLibrary

        # @description
        # <h2>Autosetting a User Id</h2>
        # <p>
        # This method is called in other methods that accept a <var>$userId</var>
        # argument, if no value is passed in the original <var>$userId</var> argument,
        # the value of <var>$userId</var> is set to <var>$_SESSION['hUserId']</var>,
        # the <var>userId</var> of the user logged in.
        # </p>
        # <p>
        # Optionally, the <var>$userId</var> can be set to the document author's <var>userId</var>
        # by setting the optional <var>$setToAuthor</var> argument to <var>true</var>.
        # </p>
        # @end

        if (empty($userId))
        {
            if ($this->isLoggedIn())
            {
                $userId = (int) $_SESSION['hUserId'];
            }
            else if ($setToAuthor && !empty($this->hUserId) && !empty($this->hUserId))
            {
                $userId = (int) $this->hUserId;
            }
        }
        else
        {
            $userId = is_numeric($userId)? (int) $userId : $this->getUserId($userId);
        }

        return $this;
    }

    public function &setNumericUserId(&$userId)
    {
        # @return integer

        # @description
        # <h2>Autosetting a Numeric User Id</h2>
        # <p>
        # This method is called in other methods that accept a <var>$userId</var>
        # argument and allow the <var>$userId</var> argument to contain either the
        # <var>userId</var>, <var>userName</var>, or <var>userEmail</var>, for
        # the most flexibility.
        # </p>
        # @end

        if (!is_numeric($userId))
        {
            $userId = $this->getUserId(trim($userId));
        }

        return $this;
    }

    public function getVariable($userVariable, $default = '', $userId = 0)
    {
        # @return mixed

        # @description
        # <h2>Getting a User Variable</h2>
        # <p>
        # This method returns the user variable specified in <var>$userVariable</var>.
        # If the variable does not exist the value of the <var>$default</var> argument
        # is returned.  If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        switch ($userVariable)
        {
            case 'hUserUnixUId':
            case 'hUserUnixGId':
            case 'hUserUnixHome':
            case 'hUserUnixShell':
            {
                $this->hDatabase->setDefaultResult($default);
                return $this->queryUnixProperty($userVariable, $userId);
                break;
            }
            default:
            {
                return $this->hDatabase->getResult($this->queryVariable($userVariable, $userId), $default);
            }
        }
    }

    public function &saveVariable($userVariable, $userValue, $userId = 0)
    {
        # @return hUserLibrary

        # @description
        # <h2>Saving a User Variable</h2>
        # <p>
        # This method saves the user variable specified in <var>$userVariable</var>
        # with the value <var>$userValue</var>.
        # If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        if (!empty($userId))
        {
            if ($this->hDatabase->resultsExist($this->queryVariable($userVariable, $userId)))
            {
                $this->hUserVariables->update(
                    array(
                        'hUserValue' => $userValue
                    ),
                    array(
                        'hUserId'       => $userId,
                        'hUserVariable' => $userVariable
                    )
                );
            }
            else
            {
                $this->hUserVariables->insert(
                    array(
                        'hUserId'       => $userId,
                        'hUserVariable' => $userVariable,
                        'hUserValue'    => $userValue
                    )
                );
            }
        }

        return $this;
    }

    public function &deleteVariables($userId = 0)
    {
        # @return hUserLibrary

        # @description
        # <h2>Deleting all User Variables</h2>
        # <p>
        # This method deletes all user variables associated with the provided <var>$userId</var>.
        # If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        if (!empty($userId))
        {
            $this->hUserVariables->delete('hUserId', $userId);
        }

        return $this;
    }

    public function &deleteVariable($userVariable, $userId = 0)
    {
        # @return hUserLibrary

        # @description
        # <h2>Deleting a User Variable</h2>
        # <p>
        # This method deletes the user variable specified in <var>$userVariable</var>.
        # If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        if (!empty($userId))
        {
            $this->hUserVariables->delete(
                array(
                    'hUserId'       => $userId,
                    'hUserVariable' => $userVariable
                )
            );
        }

        return $this;
    }

    public function queryUnixProperty($userVariable, $userId = 0)
    {
        # @return mixed

        # @description
        # <h2>Querying a Unix Property</h2>
        # <p>
        # Queries a unix property for the user.
        # If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # <p>
        # <var>$userId</var> can be a <var>userId</var>, <var>userName</var>, or <var>userEmail</var>.
        # </p>
        # <h3>Unix Properties</h3>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td>hUserUnixUid</td>
        #       <td>Unix userId</td>
        #     </tr>
        #     <tr>
        #       <td>hUserUnixGid</td>
        #       <td>Unix groupId</td>
        #     </tr>
        #     <tr>
        #       <td>hUserUnixHome</td>
        #       <td>Unix home directory path.</td>
        #     </tr>
        #     <tr>
        #       <td>hUserUnixShell</td>
        #       <td>Unix default shell.</td>
        #     </tr>
        #   </tbody>
        # </table>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        return $this->hUserUnixProperties->selectColumn(
            $userVariable,
            array(
                'hUserId' => (int) $userId
            )
        );
    }

    private function queryVariable($userVariable, $userId = 0)
    {
        # @return resource, object

        # @description
        # <h2>Querying a User Variable</h2>
        # <p>
        # Returns a query for a user variable, i.e., what would be returned by either <var>mysqli_query()</var>
        # <var>mysql_query()</var>.
        # If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        return $this->hUserVariables->selectQuery(
            'hUserValue',
            array(
                'hUserId' => $userId,
                'hUserVariable' => $userVariable
            )
        );
    }

    public function isDirectoryUser($userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Identifying a Directory User</h2>
        # <p>
        # Determines if the user is a network user synced from the network
        # directory service, such as Open Directory or Active Directory, or
        # even from local accounts on the server.
        # If no user is specified in the <var>$userId</var> argument,
        # the current user is assumed.
        # </p>
        # @end

        $this->whichUserId($userId)->setNumericUserId($userId);

        return $this->hUserUnixProperties->selectExists('hUserId', $userId);
    }

    public function &setVariables()
    {
        # @return hUserLibrary

        # @description
        # <h2>Importing User Variables as Framework Variables</h2>
        # <p>
        # This method imports all variables for the user presently logged in
        # as framework variables.
        # </p>
        # @end

        if ($this->isLoggedIn())
        {
            $this->setVariables(
                $this->hUserVariables->selectAssociative(
                    array(
                        'hUserVariable',
                        'hUserValue'
                    ),
                    array(
                        'hUserId' => (int) $_SESSION['hUserId']
                    )
                )
            );
        }

        return $this;
    }
}

?>