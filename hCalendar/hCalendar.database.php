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
# <h1>Calendar Database API</h1>
# <p>
#
# </p>
# @end

class hCalendarDatabase extends hPlugin {

    private $hFile;
    private $hFileIcon;
    private $hFileCache;
    private $permissionsMethod = 'all';
    private $categoryId = 0;
    private $hCategoryDatabase;
    private $hList;
    private $datesInQuery = array();
    private $resultCount = 0;
    private $fileCalendars = array();

    public function saveCalendar($calendarId = 0, $userId = 0, $calendarName = '')
    {
        # @return integer

        # @description
        # <h2>Saving Calendars</h2>
        # <p>
        # A call to <var>saveCalendar()</var> saves a calendar (updates an existing
        # calendar or inserted a new one depending on whether the calendarId is 0 (or nil) or
        # 1 or greater).  The arguments passed to this function must match the column
        # count of the <var>hCalendars</var> database table.
        # </p>
        # <p>
        # If activity logging is enabled, activity will be logged.
        # </p>
        # <p>
        # For caching purposes, a calendar is marked 'modified' if its data changes.
        # </p>
        # @end

        $this->hCalendars->activity(
            (empty($calendarId)? 'Created' : 'Modified').' Calendar: '.$calendarName
        );

        $columns = array(
            'hCalendarId' => $calendarId,
            'hUserId' => $userId,
            'hCalendarName' => $calendarName
        );

        if (empty($calendarId))
        {
            $columns['hCalendarCreated'] = mktime();
        }

        $calendarId = $this->hCalendars->save($columns);

        $this->syncCalendarResources();
        $this->modifiedResource($calendarId);

        return $calendarId;
    }

    private function compareExpirationData($query, $expires)
    {
        # @return array

        # @description
        # <h2>Comparing Expiration Data</h2>
        # <p>
        #
        # </p>
        # @end

        $columns = array();

        if ($expires)
        {
            foreach ($query as $data)
            {
                if ($data['hCalendarResourceCacheExpires'] > $expires || !$data['hCalendarResourceCacheExpires'])
                {
                    $columns['hCalendarResourceCacheExpires'] = $expires;
                }
            }
        }

        return $columns;
    }

    public function modifiedResource($calendarId = 0, $calendarCategoryId = 0, $begin = 0, $end = 0)
    {
        # @return void

        # @description
        # <h2>Marking a Calendar Resource Modified</h2>
        # <p>
        # Marks a calendar resource modified.
        # </p>
        # @end

        $expires = 0;

        if ($begin > $end && $end != 0)
        {
            $expires = $end;
        }
        else if ($begin)
        {
            $expires = $begin;
        }
        else
        {
            $expires = $end;
        }

        $where = array();
        $columns = array();

        if (!empty($calendarId))
        {
            $where['hCalendarId'] = (int) $calendarId;
            $this->hCalendars->modifyResource($calendarId);
        }

        if (!empty($calendarCategoryId))
        {
            $where['hCalendarCategoryId'] = (int) $calendarCategoryId;
        }

        if ($expires)
        {
            $columns = $this->compareExpirationData(
                $this->hCalendarResources->select('hCalendarResourceCacheExpires', $where),
                $expires
            );
        }

        $this->hCalendarResources->update(
            array_merge(
                array(
                    'hCalendarResourceLastModified' => time()
                ),
                $columns
            ),
            $where
        );
    }

    public function resourceExists($calendarId, $calendarCategoryId)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Calendar Resource Exists</h2>
        # <p>
        #   A call to <var>resourceExists()</var> determines whether or not a resource
        #   configuration exists for the specified <var>$calendarId</var> and <var>$calendarCategoryId</var>
        #   combination.
        # </p>
        # @end

        return $this->hCalendarResources->selectExists(
            'hCalendarResourceId',
            array(
                'hCalendarId' => (int) $calendarId,
                'hCalendarCategoryId' => (int) $calendarCategoryId
            )
        );
    }

    public function getResourceId($calendarId, $calendarCategoryId)
    {
        # @return integer

        # @description
        # <h2>Fetching a Calendar Resource's Id</h2>
        # <p>
        #   A call to <var>getResourceId()</var> returns the unique <var>hCalendarResourceId</var>
        #   that is created for a <var>hCalendarId</var> and <var>hCalendarCategoryId</var> combination.
        # </p>
        # @end

        return $this->hCalendarResources->selectColumn(
            'hCalendarResourceId',
            array(
                'hCalendarId' => (int) $calendarId,
                'hCalendarCategoryId' => (int) $calendarCategoryId
            )
        );
    }

    public function getResource($calendarId, $calendarCategoryId = 0)
    {
        # @return array or false

        # @description
        # <h2>Retrieving a Calendar Resource Configuration</h2>
        # <p>
        #   The <var>getResource()</var> method returns a configuration for a <var>hCalendarId</var>
        #   and <var>hCalendarCategoryId</var> combination.  The returned information is then
        #   used to attach a particular plugin, define the directory where the event file will be
        #   created, and determine what permissions should be applied to the event file.
        # </p>
        # <p>
        #   To return a resource you may supply one argument if you wish to retrieve
        #   a calendar resource by <var>hCalendarResourceId</var>. For example:
        # </p>
        # <code>
        #   $this-&gt;getResource(<i>$calendarResourceId</i>);
        # </code>
        # <p>
        #   You may also retrieve a calendar resource by providing the <var>hCalendarId</var>
        #   and <var>hCalendarCategoryId</var>.  For example:
        # </p>
        # <code>
        #   $this-&gt;getResource(<i>$calendarId</i>, <i>$calendarCategoryId</i>);
        # </code>
        # <p>
        #   <var>getResource()</var> returns <var>false</var> if no resource configuration exists,
        #   otherwise it returns an associative array of resource configuration data.
        # </p>
        # <p>
        #   The following data is returned if a resource configuration exists:
        # </p>
        # <table>
        #    <thead>
        #       <tr>
        #           <th>Returned Data</th>
        #           <th>Description</th>
        #       </tr>
        #    </thead>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hCalendarResourceId</td>
        #            <td>The unique resource id</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCalendarResourceName</td>
        #            <td>The resource name</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserId</td>
        #            <td>The user that owns the resource.</td>
        #        </tr>
        #        <tr>
        #           <td class='code'>hPlugin</td>
        #           <td>
        #               The path to the plugin.
        #           </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hDirectoryId</td>
        #            <td>
        #               The directory id of the folder the event file will be created in.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserPermissionsOwner</td>
        #            <td>
        #               The level of access granted to the owner of the event file i.e.,
        #               'r' (read) or 'rw' (read/write).
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserPermissionsWorld</td>
        #            <td>
        #               The level of access granted to the public for the event file i.e.,
        #               'r' (read) or 'rw' (read/write).
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserPermissionsGroups</td>
        #            <td>
        #               An array that defines individual user or group access for the event file.
        #               See <a href='#userPermissionsGroups'>Group Permissions</a>
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserPermissionsInherit</td>
        #            <td>
        #               A boolean value indicating whether or not permissions should be inherited
        #               from the parent calendar.  If inheritence is enabled, it takes precedence over
        #               <var>hUserPermissionsOwner</var>, <var>hUserPermissionsWorld</var>, and
        #               <var>hUserPermissionsGroups</var>.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCalendarResourceLastModified</td>
        #            <td>
        #               A unix timestamp indicating when the resource was last modified.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCalendarResourceLastModifiedFormatted</td>
        #            <td>
        #               A date formatted version of the timestamp indicating when the resource was
        #               last modified.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCalendarResourceLastModifiedBy</td>
        #            <td>
        #               The <var>hUserId</var> of the user who last modified the resource.
        #               If the resource was last modified by shell command the root <var>hUserId</var> is
        #               specified.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCalendarResourceCacheExpires</td>
        #            <td>
        #               A unix timestamp indicating when cached data derived from the calendar resource
        #               should be purged, if no expiration is set cached data is automatically purged
        #               when the resource is modified.  An explicit expiration time should be specified
        #               in instances where a calendar resource is assigned a beginning or ending time.
        #            </td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hCalendarResourceCacheExpiresFormatted</td>
        #            <td>
        #               A date formatted version of the timestamp indicating when cached data should
        #               be purged.
        #            </td>
        #        </tr>
        #    </tbody>
        # </table>
        # <h4 id='userPermissionsGroups'>Individual User and Group Permissions</h4>
        # <p>
        #   An array describing individual user and group permissions will be returned in the
        #   following format:
        # </p>
        # <table>
        #    <thead>
        #       <tr>
        #           <th>Key</th>
        #           <th>Value</th>
        #       </tr>
        #    </thead>
        #    <tbody>
        #       <td>hUserGroupId</td>
        #       <td>hUserPermissionsGroup</td>
        #   </tbody>
        # </table>
        # <p>
        #   Individual user and group permissions are returned as an array where the key
        #   is the <var>hUserId</var> or <var>hUserGroupId</var> and the value is the
        #   user's or group's level of access, i.e., 'r' (read) or 'rw' (read/write).
        # </p>
        # @end

        $where = array();

        if (empty($calendarCategoryId))
        {
            $where = array(
                'hCalendarResourceId' => (int) $calendarId
            );
        }
        else
        {
            $where = array(
                'hCalendarId' => (int) $calendarId,
                'hCalendarCategoryId' => (int) $calendarCategoryId
            );
        }

        $resource = $this->hCalendarResources->selectAssociative(
            array(
                'hCalendarResourceId',
                'hCalendarResourceName',
                'hUserId',
                'hPlugin',
                'hDirectoryId',
                'hUserPermissionsOwner',
                'hUserPermissionsWorld',
                'hUserPermissionsInherit',
                'hCalendarResourceLastModified',
                'hCalendarResourceLastModifiedBy',
                'hCalendarResourceCacheExpires'
            ),
            $where
        );

        if (!is_array($resource) || !count($resource))
        {
            return false;
        }

        $resource['hCalendarPermissionsGroups'] =
            $this->hCalendarResourcePermissionsGroups->selectColumnsAsKeyValue(
                array(
                    'hUserGroupId',
                    'hUserPermissionsGroup'
                ),
                array(
                    'hCalendarResourceId' => (int) $resource['hCalendarResourceId']
                )
            );

        return $resource;
    }

    public function saveResource(array $columns, $userId = 0, $begin = 0, $end = 0)
    {
        # @return integer

        # @description
        # <h2>Saving a Calendar Resource</h2>
        # <p>
        #
        # </p>
        # @end

        $this->user->whichUserId($userId);

        if (isset($columns['hPluginPath']))
        {
            $columns['hPlugin'] = $columns['hPluginPath'];

            unset($columns['hPluginPath']);
        }

        if (!isset($columns['hPlugin']))
        {
            $columns['hPlugin'] = '';
        }

        if (isset($columns['hDirectoryPath']))
        {
            $this->hFile = $this->library('hFile');

            if (!$this->hFile->exists($columns['hDirectoryPath']))
            {
                $columns['hDirectoryId'] = $this->hFile->makePath(
                    $columns['hDirectoryPath'],
                    array(
                        'hUserId' => (int) $userId,
                        'hUserPermissionsOwner' => 'rw',
                        'hUserPermissionsWorld' => 'r',
                        'hUserPermissionsGroups' => array(
                            'Website Administrators' => 'rw'
                        )
                    )
                );
            }
            else
            {
                $columns['hDirectoryId'] = $this->getDirectoryId($columns['hDirectoryPath']);
            }
        }

        if (!$this->resourceExists($columns['hCalendarId'], $columns['hCalendarCategoryId']))
        {
            $calendarResourceId = $this->hCalendarResources->insert(
                array(
                    'hCalendarResourceId' => 0,
                    'hCalendarId' => (int) $columns['hCalendarId'],
                    'hCalendarCategoryId' => (int) $columns['hCalendarCategoryId'],
                    'hCalendarResourceName' => isset($columns['hCalendarResourceName']) ? $columns['hCalendarResourceName'] : '',
                    'hUserId' => $userId,
                    'hPlugin' => $columns['hPlugin'],
                    'hDirectoryId' => (int) $columns['hDirectoryId'],
                    'hUserPermissionsOwner' => isset($columns['hUserPermissionsOwner']) ? $columns['hUserPermissionsOwner'] : 'rw',
                    'hUserPermissionsWorld' => isset($columns['hUserPermissionsWorld']) ? $columns['hUserPermissionsWorld'] : 'r',
                    'hUserPermissionsInherit' => (int) !empty($columns['hUserPermissionsInherit']),
                    'hCalendarResourceCreated' => time(),
                    'hCalendarResourceLastModified' => 0,
                    'hCalendarResourceLastModifiedBy' => isset($_SESSION['hUserId']) ? $_SESSION['hUserId'] : 0,
                    'hCalendarResourceCacheExpires' => isset($columns['hCalendarResourceCacheExpires']) ? (int) $columns['hCalendarResourceCacheExpires'] : 0
                )
            );
        }
        else
        {
            $calendarResourceId = $this->getResourceId(
                $columns['hCalendarId'],
                $columns['hCalendarCategoryId']
            );

            $this->hCalendarResources->update(
                array(
                    'hCalendarResourceId' => $calendarResourceId,
                    'hCalendarResourceName' => isset($columns['hCalendarResourceName']) ? $columns['hCalendarResourceName'] : '',
                    'hUserId' => $userId,
                    'hPlugin' => $columns['hPlugin'],
                    'hDirectoryId' => (int) $columns['hDirectoryId'],
                    'hUserPermissionsOwner' => isset($columns['hUserPermissionsOwner']) ? $columns['hUserPermissionsOwner'] : 'rw',
                    'hUserPermissionsWorld' => isset($columns['hUserPermissionsWorld']) ? $columns['hUserPermissionsWorld'] : 'r',
                    'hUserPermissionsInherit' => (int) !empty($columns['hUserPermissionsInherit']),
                    'hCalendarResourceLastModified' => time(),
                    'hCalendarResourceLastModifiedBy' => isset($_SESSION['hUserId']) ? $_SESSION['hUserId'] : 0,
                    'hCalendarResourceCacheExpires' => isset($columns['hCalendarResourceCacheExpires']) ? (int) $columns['hCalendarResourceCacheExpires'] : 0
                ),
                array(
                    'hCalendarId' => (int) $columns['hCalendarId'],
                    'hCalendarCategoryId' => (int) $columns['hCalendarCategoryId']
                )
            );
        }

        if (isset($columns['hUserPermissionsGroups']))
        {
            $this->saveResourceGroups($calendarResourceId, $columns['hUserPermissionsGroups']);
        }

        $this->modifiedResource(
            $columns['hCalendarId'],
            $columns['hCalendarCategoryId'],
            $begin,
            $end
        );

        return $calendarResourceId;
    }

    public function &saveResourceGroups($calendarResourceId, $userPermissionsGroups)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Saving Calendar Resource Groups</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hCalendarResourcePermissionsGroups->delete(
            'hCalendarResourceId',
            (int) $calendarResourceId
        );

        foreach ($userPermissionsGroups as $group => $level)
        {
            $this->hCalendarResourcePermissionsGroups->insert(
                array(
                    'hCalendarResourceId' => (int) $calendarResourceId,
                    'hUserGroupId' => is_numeric($group) ? $group : $this->user->getUserId($group),
                    'hUserPermissionsGroup' => $level
                )
            );
        }

        return this;
    }

    public function hasAccessToCalendar($calendarId = 0, $fileId = 0, $level = 'r')
    {
        # @return boolean

        # @description
        # <h2>Determining Access to Calendars</h2>
        # <p>
        # Determines if a user has access to a calendar.  Based on the level specified in
        # <var>$level</var> (presently allowed are 'r' and 'rw').  If a user is logged in and
        # is a member of <var>Calendar Administrators</var>, the user has access to
        # a calendar automatically.
        # </p>
        # <p>
        # Otherwise, access to a calendar is determined by the permissions set on
        # the calendar itself or the permissions set on the file posted to the
        # calendar.
        # </p>
        # @end

        if ($this->isLoggedIn() && $this->inGroup('Calendar Administrators'))
        {
            return true;
        }

        if (!empty($calendarId))
        {
            if (is_array($calendarId))
            {
                foreach ($calendarId as $calendar)
                {
                    if ($this->hCalendars->hasPermission($calendar, $level))
                    {
                        return true;
                    }
                }
            }
            else if ($this->hCalendars->hasPermission($calendarId, $level))
            {
                return true;
            }
        }

        if (!empty($fileId))
        {
            return $this->hFiles->hasPermission($fileId, $level);
        }

        return false;
    }

    public function getCategories()
    {
        # @return array

        # @description
        # <h2>Getting Calendar Categories</h2>
        # <p>
        # Returns an associative array of calendar categories where each key
        # in the array is an <var>hCalendarCategoryId</var> and each value in the
        # array is the <var>hCalendarCategoryName</var>.
        # </p>
        # @end

        return $this->hCalendarCategories->selectColumnsAsKeyValue(
            array(
                'hCalendarCategoryId',
                'hCalendarCategoryName'
            )
        );
    }

    public function getCalendars($level = 'r', $checkPermissions = true, $checkWorldPermissions = false)
    {
        # @return array

        # @description
        # <h2>Getting Calendars</h2>
        # <p>
        # Returns calendars as an associative array where each key in the array is
        # an <var>hCalendarId</var> and each value in an array is an <var>hCalendarName</var>.
        # </p>
        # <p>
        # Depending on whether the arguments <var>$checkPermissions</var>
        # or <var>$checkWorldPermissions</var> are set to <var>true</var>, permissions
        # are checked via 'all' or 'world', and only calendars the user explicitly
        # has access to are returned.
        # </p>
        # @end

        return $this->hDatabase->getAssociativeArray(
            $this->getTemplateSQL(
                $this->getPermissionsVariablesForTemplate(
                    $checkPermissions,
                    $checkWorldPermissions,
                    $level
                )
            )
        );
    }

    public function getCalendarIds()
    {
        # @return array

        # @description
        # <h2>Getting Calendar Ids</h2>
        # <p>
        # Returns all <var>hCalendarId</var>s in the <var>hCalendars</var> database table.
        # </p>
        # @end

        return $this->hCalendars->select('hCalendarId');
    }

    public function getFileCalendars($fileId)
    {
        # @return array

        # @description
        # <h2>Getting Calendar Files</h2>
        # <p>
        # Returns all <var>hCalendarId</var>s in the <var>hCalendarFiles</var> database table
        # for the specified <var>$fileId</var>.
        # </p>
        # @end

        return $this->hCalendarFiles->select(
            'hCalendarId',
            array(
                'hFileId' => (int) $fileId
            )
        );
    }

    public function getCalendarFileIds($calendarId = 0, $calendarCategoryId = 0, $fileId = 0)
    {
        # @return array

        # @description
        # <h2>Getting Calendar File Ids</h2>
        # <p>
        # Returns all <var>hCalendarFileIds</var> from the <var>hCalendarFiles</var> database
        # table based on the values passed in the  <var>$calendarId</var>,
        # <var>$calendarCategoryId</var>, or <var>$fileId</var> arguments.
        # </p>
        # @end

        $columns = array();

        if (!empty($calendarId))
        {
            $columns['hCalendarId'] = (int) $calendarId;
        }

        if (!empty($calendarCategoryId))
        {
            $columns['hCalendarCategoryId'] = (int) $calendarId;
        }

        if (!empty($fileId))
        {
            $columns['hFileId'] = (int) $fileId;
        }

        return $this->hCalendarFiles->select(
            'hCalendarFileId',
            $columns
        );
    }

    public function getFileCategories($fileId)
    {
        # @return array

        # @description
        # <h2>Getting Calendar File Categories</h2>
        # <p>
        # Returns all <var>hCalendarCategoryIds</var> in the <var>hCalendarFiles</var>
        # database table, depending on the value of the <var>$fileId</var> passed in.
        # </p>
        # @end

        return $this->hCalendarFiles->select(
            'hCalendarCategoryId',
            array(
                'hFileId' => (int) $fileId
            )
        );
    }

    public function newCalendar($calendarName)
    {
        # @return integer

        # @description
        # <h2>Creating a Calendar</h2>
        # <p>
        # Create a new calendar named <var>$calendarName</var>.
        # If the calendar already exists, the <var>$calendarId</var>
        # of the existing calendar is returned.
        # </p>
        # <p>
        # If the calendar does not exist, it is created.  It's modified time
        # is set.  Activity is logged.  And the new <var>$calendarId</var>
        # created is returned.
        # </p>
        # @end

        $calendarId = $this->hCalendars->selectColumn(
            'hCalendarId',
            array(
                'hCalendarName' => $calendarName
            )
        );

        if (!empty($calendarId))
        {
            return (int) $calendarId;
        }
        else
        {
            $calendarId = $this->hCalendars->insert(
                array(
                    'hCalendarId' => 0,
                    'hUserId' => (int) $_SESSION['hUserId'],
                    'hCalendarName' => $calendarName
                )
            );

            $this->syncCalendarResources();

            $this->hCalendars->activity(
                'Created Calendar: '.$calendarName
            );

            return $calendarId;
        }
    }

    public function newCategory($calendarCategoryName)
    {
        # @return integer

        # @description
        # <h2>Creating a Calendar Category</h2>
        # <p>
        # Creates a new calendar category of the name <var>$calendarCategoryName</var>.
        # If the calendar category already exists, the <var>$calendarCategoryId</var> of that
        # calendar category is returned.
        # </p>
        # <p>
        # If the calendar category does not exist, it is created.  It's modified time
        # is set.  Activity is logged.  And the new <var>$calendarCategoryId</var> created
        # is returned.
        # </p>
        # @end

        $calendarCategoryId = $this->hCalendarCategories->selectColumn(
            'hCalendarCategoryId',
            array(
                'hCalendarCategoryName' => $calendarCategoryName
            )
        );

        if (!empty($calendarCategoryId))
        {
            return (int) $calendarCategoryId;
        }
        else
        {
            $calendarCategoryId = $this->hCalendarCategories->insert(
                array(
                    'hCalendarCategoryId' => 0,
                    'hCalendarCategoryName' => $calendarCategoryName
                )
            );

            $this->syncCalendarResources();

            $this->hCalendars->activity(
                'Created Calendar Category: '.$calendarCategoryName
            );

            return $calendarCategoryId;
        }
    }

    public function &deleteCalendar($calendarId)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Deleting a Calendar</h2>
        # <p>
        # Deletes a calendar, and all events posts to that calendar of the calendarId
        # specified in <var>$calendarId</var>.  The files posted to the calendar are
        # also permenently and irrevocably deleted.  The calendar resource used for
        # caching is also deleted,  and the activity of deleting the calendar is
        # logged in the activity log.
        # </p>
        # @end

        $this->hCalendars->activity(
            'Deleted Calendar: '.$this->getCalendarName($calendarId)
        );

        $this->hFile = $this->library('hFile');

        $files = $this->hCalendarFiles->select(
            'hFileId',
            array(
                'hCalendarId' => (int) $calendarId
            )
        );

        if (count($files))
        {
            foreach ($files as $data)
            {
                $this->hFile->delete($this->getFilePathByFileId($data['hFileId']));
            }
        }

        $files = $this->hCalendarFiles->select(
            'hCalendarFileId',
            array(
                'hCalendarId' => (int) $calendarId
            )
        );

        foreach ($files as $data)
        {
            $this->delete($data['hCalendarFileId']);
        }

        $this->hCalendars->delete(
            'hCalendarId',
            (int) $calendarId
        );

        $this->hCalendarResources->delete(
            'hCalendarId',
            (int) $calendarId
        );

        return $this;
    }

    public function &deleteCategory($calendarCategoryId)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Deleting a Calendar Category</h2>
        # <p>
        # Deletes the calendar category specified in <var>$calendarCategoryId</var>.
        # This method logs the activity of deleting the calendar category in the activity log.
        # Files posted to the calendar category are also permanently and irrevocably
        # deleted.  Associated calendar resources created for caching are also deleted.
        # </p>
        # @end

        $this->hCalendars->activity(
            'Deleted Calendar Category: '.$this->getCategoryName($calendarCategoryId)
        );

        $this->hFile = $this->library('hFile');

        $files = $this->hCalendarFiles->select(
            'hFileId',
            array(
                'hCalendarCategoryId' => (int) $calendarCategoryId
            )
        );

        if (count($files))
        {
            foreach ($files as $data)
            {
                $this->hFile->delete(
                    $this->getFilePathByFileId($data['hFileId'])
                );
            }
        }

        $this->hCalendarFiles->delete(
            'hCalendarCategoryId',
            (int) $calendarCategoryId
        );

        $this->hCalendarCategories->delete(
            'hCalendarCategoryId',
            (int) $calendarCategoryId
        );

        $this->hCalendarResources->delete(
            'hCalendarCategoryId',
            (int) $calendarCategoryId
        );

        return $this;
    }

    public function &syncCalendarResources()
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Syncing Calendar Resources</h2>
        # <p>
        #
        # </p>
        # @end

        # Verify that all calendars and categories are present as resources.
        $calendars = $this->hCalendars->select('hCalendarId');

        foreach ($calendars as $calendarId)
        {
            $calendarCategories = $this->hCalendarCategories->select('hCalendarCategoryId');

            foreach ($calendarCategories as $calendarCategoryId)
            {
                $exists = $this->hCalendarResources->selectExists(
                    'hCalendarResourceId',
                    array(
                        'hCalendarId'         => (int) $calendarId,
                        'hCalendarCategoryId' => (int) $calendarCategoryId
                    )
                );

                if (!$exists)
                {
                    $this->hCalendarResources->insert(
                        array(
                            'hCalendarResourceId'           => nil,
                            'hCalendarId'                   => (int) $calendarId,
                            'hCalendarCategoryId'           => (int) $calendarCategoryId,
                            'hUserId'                       => 1,
                            'hCalendarResourceCreated'      => time(),
                            'hCalendarResourceLastModified' => 0
                        )
                    );
                }
            }
        }

        # Make sure non-existant stuff isn't being kept as resources.
        $query = $this->hCalendarResources->select(
            array(
                'DISTINCT',
                'hCalendarId'
            )
        );

        foreach ($query as $calendarId)
        {
            if (!in_array($calendarId, $calendars))
            {
                $this->hCalendarResources->delete(
                    'hCalendarId',
                    (int) $calendarId
                );
            }
        }

        $query = $this->hCalendarResources->select(
            array(
                'DISTINCT',
                'hCalendarCategoryId'
            )
        );

        foreach ($query as $calendarCategoryId)
        {
            if (!in_array($calendarCategoryId, $calendarCategories))
            {
                $this->hCalendarResources->delete(
                    'hCalendarCategoryId',
                    (int) $calendarCategoryId
                );
            }
        }

        return $this;
    }

    public function getOldestDate($calendarId = 1, $calendarCategoryId = 1)
    {
        # @return integer

        # @description
        # <h2>Getting Oldest Calendar Date</h2>
        # <p>
        # Returns the olded date posted to the calendar and calendar category.
        # </p>
        # @end

        return $this->hDatabase->getResult(
            $this->getTemplateSQL(
                'getDate',
                array(
                    'hCalendarId'         => (int) $calendarId,
                    'hCalendarCategoryId' => (int) $calendarCategoryId,
                    'sort'                => 'ASC'
                )
            )
        );
    }

    public function getNewestDate($calendarId = 1, $calendarCategoryId = 1)
    {
        # @return integer

        # @description
        # <h2>Getting Newest Calendar Date</h2>
        # <p>
        # Returns the newest date posted to the calendar and calendar category.
        # </p>
        # @end

        return $this->hDatabase->getResult(
            $this->getTemplateSQL(
                'getDate',
                array(
                    'hCalendarId' => (int) $calendarId,
                    'hCalendarCategoryId' => (int) $calendarCategoryId,
                    'sort' => 'DESC'
                )
            )
        );
    }

    public function getArchiveMonths($year, $calendarId = 1, $calendarCategoryId = 1)
    {
        # @return boolean

        # @description
        # <h2>Getting Months for Archive Links</h2>
        # <p>
        # Returns a list of months containing posts for the purpose of
        # creating links in an archive.  Months are returned for a single year only,
        # specified in the <var>$year</var> argument. Archive links are returned for
        # the specified calendar and calendar category passed in the
        # <var>$calendarId</var> and <var>$calendarCategoryId</var> arguments.
        # </p>
        # @end

        $months = range(1, 12);

        $results = array(
            'hCalendarMonth'      => array(),
            'hCalendarMonthLabel' => array(),
            'hCalendarMonthCount' => array()
        );

        foreach ($months as $month)
        {
            $monthStart = mktime(0, 0, 0, $month, 1, $year);
            $monthEnd   = mktime(0, 0, 0, $month, date('t', $monthStart), $year);

            $count = $this->hDatabase->selectCount(
                array(
                    'hCalendarFiles' => 'hFileId'
                ),
                array(
                    'hCalendarFiles',
                    'hCalendarFileDates'
                ),
                array(
                    'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                    'hCalendarFiles.hCalendarId' => (int) $calendarId,
                    'hCalendarFiles.hCalendarCategoryId' => (int) $calendarCategoryId,
                    'hCalendarFileDates.hCalendarDate' => array(
                        array('>=', (int) $monthStart),
                        array('<=', (int) $monthEnd)
                    )  # Space added to make the key unique
                )
            );

            if ($count > 0)
            {
                $results['hCalendarMonth'][] = $monthStart;
                $results['hCalendarMonthLabel'][] = date('F', $monthStart);
                $results['hCalendarMonthCount'][] = $count;
            }
        }

        return $results;
    }

    public function getOwned($userId = 0)
    {
        $this->user->whichUserId($userId);

        # @return array

        # @description
        # <h2>Getting Owned Calendars</h2>
        # <p>
        # Returns calendars owned by the the user specified in <var>$userId</var>.
        # If no user is specified, the user presently logged in is assumed, if there
        # is one.
        # </p>
        # @end

        return $this->hCalendars->selectAssociative(
            array(
                'hCalendarId',
                'hCalendarName'
            ),
            array(
                'hUserId' => (int) $userId
            )
        );
    }

    public function getShared($userId = 0)
    {
        $this->user->whichUserId($userId);

        # @return array

        # @description
        # <h2>Getting Shared Calendars</h2>
        # <p>
        # Returns calendars shared with the user specified by <var>$userId</var>.
        # If no user is specified, the user presently logged in is assumed, if there
        # is one.
        # </p>
        # @end

        return $this->hDatabase->getAssociativeArray(
            $this->getTemplateSQL(
                array(
                    'hUserId' => (int) $userId,
                    'hFrameworkResourceId' => (int) $this->hCalendars->getResourceId()
                )
            )
        );
    }

    public function getDefault($userId = 0)
    {
        $this->user->whichUserId($userId);

        # @return integer

        # @description
        # <h2>Getting Default Calendar</h2>
        # <p>
        # Returns the default calendar for the user specified by <var>$userId</var>.
        # If no user is specified, the user presently logged in is assumed, if there
        # is one.
        # </p>
        # @end

        return $this->hCalendars->selectColumn(
            'hCalendarId',
            array(
                'hUserId' => (int) $userId
            ),
            'AND',
            'hCalendarId',
            1
        );
    }

    public function getCalendarName($calendarId)
    {
        # @return string

        # @description
        # <h2>Getting a Calendar's Name</h2>
        # <p>
        # Returns the calendar name for the calendar specified in <var>$calendarId</var>.
        # </p>
        # @end

        return $this->hCalendars->selectColumn(
            'hCalendarName',
            (int) $calendarId
        );
    }

    public function getCategoryName($calendarCategoryId)
    {
        # @return string

        # @description
        # <h2>Getting a Calendar Category's Name</h2>
        # <p>
        # Returns the calendar category name for the calendar category specified in <var>$calendarCategoryId</var>.
        # </p>
        # @end

        return $this->hCalendarCategories->selectColumn(
            'hCalendarCategoryName',
            (int) $calendarCategoryId
        );
    }

    public function getCalendarFileId($calendarId, $fileId)
    {
        # @return integer

        # @description
        # <h2>Getting a Calendar File Id</h2>
        # <p>
        # Returns an array of <var>hCalendarFileIds</var> for the calendar specified in <var>$calendarId</var>
        # and the file specified in <var>$fileId</var>.
        # </p>
        # @end

        return $this->hCalendarFiles->selectColumn(
            'hCalendarFileId',
            array(
                'hCalendarId' => (int) $calendarId,
                'hFileId'     => (int) $fileId
            )
        );
    }

    public function save($columns)
    {
        # @return integer

        # @description
        # <h2>Saving Calendar Events</h2>
        # <p>
        # The method <var>save()</var> is used to create or update calendar events.
        # </p>
        # <h3>Columns</h3>
        # <p>
        # Following is a list of indices that you should provide in the <var>$columns</var> argument
        # to define an event that you wish to save.
        # </p>
        # <p>
        # When saving an event, whether or not a new event should be created, or an old one updated
        # depends whether an event exists with the same <var>calendarId</var>, <var>calendarCategoryId</var>,
        # and <var>fileId</var>.  These three fields together are used to define a unique event.
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>hCalendarId</td>
        #       <td>The Id of the calendar the event is posted to.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarCategoryId</td>
        #       <td>The Id of the calendar category the event is posted to.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hFileId</td>
        #       <td>The Id of the file to post to the calendar.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarFileId</td>
        #       <td>This field is not typically set, it is usually determined automatically from other fields.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarBegin</td>
        #       <td>A Unix Timestamp representing the time the event should begin showing on the website (the roll-on date).
        #       If a string is supplied instead, a Unix Timestamp is automatically determined from the string supplied.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarEnd</td>
        #       <td>A Unix Timestamp representing the time the event should stop showing on the website (the roll-off date).
        #       If a string is supplied instead, a Unix Timestamp is automatically determined from the string supplied.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarRange</td>
        #       <td>Not presently implemented in the calendar UI, but this field is intended to dictate whether a date is
        #       a single day or a range of days.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarDate, hCalendarDate[]</td>
        #       <td>A Unix Timestamp representing the date of the event.
        #       If a string is supplied instead, a Unix Timestamp is automatically determined from the string supplied.
        #       Multiple dates can be supplied, if multiple dates are supplied,
        #       <var>hCalendarDate</var> should be an array and the array should be matching lengths for each of the
        #       <var>hCalendarDate</var>, <var>hCalendarBeginTime</var>, <var>hCalendarEndTime</var> and <var>hCalendarAllDay</var>
        #       indices.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarBeginTime, hCalendarBeginTime[]</td>
        #       <td>A Unix Timestamp representing the time the event begins (optional).
        #       If a string is supplied instead, a Unix Timestamp is automatically determined from the string supplied.
        #       Multiple dates can be supplied, if multiple dates are supplied,
        #       <var>hCalendarBeginTime</var> should be an array and the array should be matching lengths for each of the
        #       <var>hCalendarDate</var>, <var>hCalendarBeginTime</var>, <var>hCalendarEndTime</var> and <var>hCalendarAllDay</var>
        #       indices.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarEndTime, hCalendarEndTime[]</td>
        #       <td>A Unix Timestamp representing the time the event ends (optional).
        #       If a string is supplied instead, a Unix Timestamp is automatically determined from the string supplied.
        #       Multiple dates can be supplied, if multiple dates are supplied,
        #       <var>hCalendarEndTime</var> should be an array and the array should be matching lengths for each of the
        #       <var>hCalendarDate</var>, <var>hCalendarBeginTime</var>, <var>hCalendarEndTime</var> and <var>hCalendarAllDay</var>
        #       indices.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>hCalendarAllDay, hCalendarAllDay[]</td>
        #       <td>Whether or not the event is an 'all-day' event (optional), if provided, this should be provided as a boolean value.
        #       If multiple 'all-day' toggles are supplied, <var>hCalendarAllDay</var> should be an array and the array should be
        #       matching lengths for each of the <var>hCalendarDate</var>, <var>hCalendarBeginTime</var>, <var>hCalendarEndTime</var>
        #       and <var>hCalendarAllDay</var> indices.</td>
        #     </tr>
        #   </tbody>
        # </table>
        # @end

        $begin = 0;
        $end = 0;

        if (!isset($columns['hCalendarFileId']))
        {
            $columns['hCalendarFileId'] = $this->getCalendarFileId(
                $columns['hCalendarId'],
                $columns['hFileId']
            );
        }

        if (isset($columns['hCalendarBegin']) && !is_numeric($columns['hCalendarBegin']) && !empty($columns['hCalendarBegin']))
        {
            $columns['hCalendarBegin'] = strtotime($columns['hCalendarBegin']);
            $begin = $columns['hCalendarBegin'];
        }

        if (isset($columns['hCalendarEnd']) && !is_numeric($columns['hCalendarEnd']) && !empty($columns['hCalendarEnd']))
        {
            $columns['hCalendarEnd'] = strtotime($columns['hCalendarEnd']);
            $end = $columns['hCalendarEnd'];
        }

        # Do a sanity check, and make sure that only one record exists with this
        # hCalendarId / hCalendarCategoryId / hFileId combination.
        $calendarFiles = $this->hCalendarFiles->select(
            'hCalendarFileId',
            array(
                'hCalendarId' => (int) $columns['hCalendarId'],
                'hCalendarCategoryId' => (int) $columns['hCalendarCategoryId'],
                'hFileId' => (int) $columns['hFileId']
            )
        );

        if (count($calendarFiles) > 1)
        {
            $i = 0;

            foreach ($calendarFiles as $calendarFileId)
            {
                if (!$i)
                {
                    $columns['hCalendarFileId'] = (int) $calendarFileId;
                    $i++;
                    continue;
                }

                $this->deleteDates((int) $calendarFileId);

                $this->hCalendarFiles->delete(
                    'hCalendarFileId',
                    (int) $calendarFileId
                );

                $i++;
            }
        }

        $this->hCalendars->activity(
            'Saved Event: '.$this->getFileTitle($columns['hFileId'])
        );

        $this->modifiedResource(
            $columns['hCalendarId'],
            $columns['hCalendarCategoryId'],
            $begin,
            $end
        );

        $calendarFileId = $this->hCalendarFiles->save(
            array(
                'hCalendarFileId'     => (int) $columns['hCalendarFileId'],
                'hCalendarId'         => (int) $columns['hCalendarId'],
                'hCalendarCategoryId' => (int) $columns['hCalendarCategoryId'],
                'hFileId'             => (int) $columns['hFileId'],
                'hCalendarBegin'      => $this->getTimeStamp($columns['hCalendarBegin']),
                'hCalendarEnd'        => $this->getTimeStamp($columns['hCalendarEnd']),
                'hCalendarRange'      => empty($columns['hCalendarRange'])? 0 : 1
            )
        );

        if (isset($columns['hCalendarDate']))
        {
            $this->deleteDates((int) $columns['hCalendarFileId']);

            if (is_array($columns['hCalendarDate']))
            {
                foreach ($columns['hCalendarDate'] as $i => $calendarDate)
                {
                    $this->insertDate(
                        array(
                            'hCalendarFileId'    => $calendarFileId,
                            'hCalendarDate'      => $this->getTimeStamp($calendarDate),
                            'hCalendarBeginTime' => $this->getTimeStamp($columns['hCalendarBeginTime'][$i]),
                            'hCalendarEndTime'   => $this->getTimeStamp($columns['hCalendarEndTime'][$i]),
                            'hCalendarAllDay'    => $this->getTimeStamp($columns['hCalendarAllDay'][$i])
                        )
                    );
                }
            }
            else
            {
                $this->insertDate(
                    array(
                        'hCalendarFileId'    => $calendarFileId,
                        'hCalendarDate'      => $this->getTimeStamp($columns['hCalendarDate']),
                        'hCalendarBeginTime' => $this->getTimeStamp($columns['hCalendarBeginTime']),
                        'hCalendarEndTime'   => $this->getTimeStamp($columns['hCalendarEndTime']),
                        'hCalendarAllDay'    => $this->getTimeStamp($columns['hCalendarAllDay'])
                    )
                );
            }
        }

        return $calendarFileId;
    }

    private function getTimeStamp(&$calendarDate)
    {
        # @return integer

        # @description
        # <h2>Getting Timestamps</h2>
        # <p>
        #
        # </p>
        # @end

        if (isset($calendarDate))
        {
            if (!is_numeric($calendarDate) && !empty($calendarDate))
            {
                return strtotime($calendarDate);
            }
            else
            {
                return (int) $calendarDate;
            }
        }
        else
        {
            return 0;
        }
    }

    public function &insertDate(array $columns)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Inserting Event Dates</h2>
        # <p>
        #   Inserts a record into the
        #   <a href='/System/Framework/Hot Toddy/hCalendar/Database/hCalendarFileDates/hCalendarFileDates.sql' class='code' target='_blank'>hCalendarFileDates</a>
        #   database table. The columns provided in the <var>$columns</var> argument are:
        # </p>
        # <table>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hCalendarFileId</td>
        #           <td>
        #               Links the date to a record in the
        #               <a href='/System/Framework/Hot Toddy/hCalendar/Database/hCalendarFiles/hCalendarFiles.sql' class='code' target='_blank'>hCalendarFiles</a>
        #               database table, which is ultimately a Hot Toddy file representing the event.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarDate</td>
        #           <td>A Unix timestamp representing the day of the event.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarBeginTime</td>
        #           <td>A Unix timestamp representing the time of day the event begins (optional).</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarEndTime</td>
        #           <td>A Unix timestamp representing the time of day the event ends (optional)</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarAllDay</td>
        #           <td>A boolean value indicating whether or not the event is all day (optional).</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        $this->hCalendarFileDates->insert(
            array(
                'hCalendarFileId'    => (int) $columns['hCalendarFileId'],
                'hCalendarDate'      => (int) $columns['hCalendarDate'],
                'hCalendarBeginTime' => isset($columns['hCalendarBeginTime'])? (int) $columns['hCalendarBeginTime'] : 0,
                'hCalendarEndTime'   => isset($columns['hCalendarEndTime'])? (int) $columns['hCalendarEndTime'] : 0,
                'hCalendarAllDay'    => isset($columns['hCalendarAllDay'])? (int) $columns['hCalendarAllDay'] : 0
            )
        );

        return $this;
    }

    public function &delete($calendarId = 0, $calendarCategoryId = 0, $fileId = 0)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Deleting a Calendar Event</h2>
        # <p>
        #   Deletes a calendar event based on the calendar indicated in <var>$calendarId</var>,
        #   the calendar category provided in <var>$calendarCategoryId</var> and the <var>$fileId</var>
        #   in <var>$fileId</var>.  All three arguments are optional.  If only a <var>$calendarId</var>
        #   is provided, all events in that calendar are deleted.  If only a <var>$calendarCategoryId</var>
        #   is passed, all events with that calendar category id are deleted.  If a <var>$calendarId</var>
        #   and a <var>$calendarCategoryId</var> are provided, then only events from that calendar with
        #   that calendar category id are deleted.  If only a <var>$fileId</var> is provided, only events
        #   with the specified <var>$fileId</var> will be removed (a single event can be posted to multiple
        #   calendars and calendar categories).  If all three arguments are provided, then events
        #   with the specified <var>$fileId</var> are removed from the specified <var>$calendarId</var> and
        #   <var>$calendarCategoryId</var>, but events with the same <var>$fileId</var> will continue to exist
        #   in other calendars and/or calendar categories.
        # </p>
        # @end

        $calendarFiles = $this->getCalendarFileIds($calendarId, $calendarCategoryId, $fileId);

        if (count($calendarFiles))
        {
            foreach ($calendarFiles as $calendarFileId)
            {
                $this->hCalendarFileDates->delete(
                    'hCalendarFileId',
                    (int) $calendarFileId
                );
            }
        }

        $columns = array();

        if (!empty($calendarId))
        {
            $columns['hCalendarId'] = (int) $calendarId;
        }

        if (!empty($calendarCategoryId))
        {
            $columns['hCalendarCategoryId'] = (int) $calendarCategoryId;
        }

        $this->modifiedResource($calendarId, $calendarCategoryId);

        if (!empty($fileId))
        {
            $columns['hFileId'] = (int) $fileId;
        }

        $this->hCalendarFiles->delete($columns);

        return $this;
    }

    public function &deleteDates($calendarFileId)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Deleting Event Dates</h2>
        # <p>
        #   Deletes individual event dates with the specified <var>$calendarFileId</var>.
        # </p>
        # @end

        $this->hCalendarFileDates->delete(
            'hCalendarFileId',
            (int) $calendarFileId
        );

        return $this;
    }

    public function getFilesWithMetaData($calendarId = 0, $calendarCategoryId = 0, $count = 10, $timeRange = nil, $withinTimeBoundaries = true, $sort = 'ASC')
    {
        # @return boolean

        # @description
        # <h2>Get Calendar Files</h2>
        # <p>
        # Alias of: <a href='#getFiles' class='code'>getFiles()</a>
        # </p>
        # @end

        # The original function now returns meta data by default, so this function is pretty much useless.
        return $this->getFiles(
            $calendarId,
            $calendarCategoryId,
            $count,
            $timeRange,
            $withinTimeBoundaries,
            $sort
        );
    }

    public function getCached($fileCacheResource, $calendarId = 0, $calendarCategoryId = 0, $fileCacheResourceId = 0)
    {
        # @return string

        # @description
        # <h2>Getting Cached Data</h2>
        # <p>
        #   Returns data from the
        #   <a href='/System/Framework/Hot Toddy/hFile/hFileCache/Database/hFileCache/hFileCache.sql' class='code' target='_blank'>hFileCache</a>
        #   database table, which stores cached calendar data.
        # </p>
        # @end

        if (empty($fileCacheResourceId))
        {
            $fileCacheResourceId = $this->hFileId;
        }

        if (empty($calendarId))
        {
            $calendarId = $this->hCalendarId(1);
        }

        if (empty($calendarCategoryId))
        {
            $calendarCategoryId = $this->hCalendarCategoryId(3);
        }

        $this->hFileCache = $this->library('hFile/hFileCache');

        if (!isset($_GET['updateCache']))
        {
            $cal = $this->hCalendarResources->selectAssociative(
                array(
                    'hCalendarResourceLastModified',
                    'hCalendarResourceCacheExpires'
                ),
                array(
                    'hCalendarId' => $calendarId,
                    'hCalendarCategoryId' => $calendarCategoryId
                )
            );

            if (!isset($cal['hCalendarResourceLastModified']))
            {
                $cal = array(
                    'hCalendarResourceLastModified' => time(),
                    'hCalendarResourceCacheExpires' => 0
                );
            }
        }
        else
        {
            $cal = array(
                'hCalendarResourceLastModified' => time(),
                'hCalendarResourceCacheExpires' => 0
            );
        }

        return $this->hFileCache->getCachedDocument(
            $fileCacheResource,
            $fileCacheResourceId,
            $cal['hCalendarResourceLastModified']
        );
    }

    public function getExpirationDate($calendarId, $calendarCategoryId)
    {
        # @return integer

        # @description
        # <h2>Get Expiration Date</h2>
        # <p>
        #   Returns the <var>hCalendarEndTime</var> from the
        #   <a href='/System/Framework/Hot Toddy/hCalendar/Database/hCalendarFiles/hCalendarFiles.sql' class='code' target='_blank'>hCalendarFiles</a>
        #   table. The <var>hCalendarBeginTime</var> and <var>hCalendarEndTime</var> are attributes
        #   used to time the display of information. When the current time is greater than
        #   <var>hCalendarBeginTime</var>, an event is displayed. When the current time
        #   is greater than <var>hCalendarEndTime</var> an event is removed.
        # </p>
        # @end

        $nextBeginTime = $this->hDatabase->getColumn(
            $this->getTemplateSQL(
                'getNextBeginTime',
                array(
                    'hCalendarId' => (int) $calendarId,
                    'hCalendarCategoryId' => (int) $calendarCategoryId
                )
            ),
            0
        );

        $nextEndTime = $this->hDatabase->getColumn(
            $this->getTemplateSQL(
                'getNextEndTime',
                array(
                    'hCalendarId' => (int) $calendarId,
                    'hCalendarCategoryId' => (int) $calendarCategoryId
                )
            ),
            0
        );

        if ($nextBeginTime < $nextEndTime && $nextBeginTime > 0)
        {
            return (int) $nextBeginTime;
        }
        else if ($nextEndTime > 0)
        {
            return (int) $nextEndTime;
        }
        else
        {
            return 0;
        }
    }

    public function &saveToCache($fileCacheDocument, $fileCacheResource, $calendarId = 0, $calendarCategoryId = 0, $fileCacheResourceId = 0)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Saving Calendar Data to Cache</h2>
        # <p>
        #
        # </p>
        # @end

        # $fileCacheResource
        #   A short unique name for the cached information that you invent to
        #   identify the cached information.  For example hCalendarNewsPosts.
        #
        # $calendarId
        #   Unique integer identifier for the calendar.
        #
        # $calendarCategoryId
        #   Unique integer identifier for the calendar category.

        if (empty($fileCacheResourceId))
        {
            $fileCacheResourceId = $this->hFileId;
        }

        if (empty($calendarId))
        {
            $calendarId = $this->hCalendarId(1);
        }

        if (empty($calendarCategoryId))
        {
            $calendarCategoryId = $this->hCalendarCategoryId(3);
        }

        if (empty($this->hFileCache))
        {
            $this->hFileCache = $this->library('hFile/hFileCache');
        }

        # What is the current expiration date?
        $this->hFileCache->saveDocumentToCache(
            $fileCacheResource,
            $fileCacheResourceId,
            $fileCacheDocument,
            $this->getExpirationDate(
                $calendarId,
                $calendarCategoryId
            )
        );

        return $this;
    }

    private function &setData(&$data, &$file, $item)
    {
        # @return hCalendarDatabase

        # @description
        # <p>
        #
        # </p>
        # @end

        if (!isset($data[$file['hFileId']]))
        {
            $data[$file['hFileId']] = array();
        }

        if (!isset($data[$file['hFileId']][$item]))
        {
            $data[$file['hFileId']][$item] = array();
        }

        array_push(
            $data[$file['hFileId']][$item],
            $file[$item]
        );

        return $this;
    }

    public function getEvent($fileId, $getCalendar = true)
    {
        # @return array

        # @description
        # <h2>Getting an Event</h2>
        # <p>
        #
        # </p>
        # @end

        $data = $this->hDatabase->selectAssociative(
            array(
                'DISTINCT',
                'hFiles' => array(
                    'hFileId',
                    'hDirectoryId',
                    'hUserId',
                    'hFileName',
                    'hPlugin'
                ),
                'hFileDocuments' => array(
                    'hFileDescription',
                    'hFileTitle',
                    'hFileDocument'
                )
            ),
            array(
                'hFiles',
                'hFileDocuments'
            ),
            array(
                'hFiles.hFileId' => array(
                    array('=', 'hFileDocuments.hFileId'),
                    array('=', (int) $fileId)
                )
            )
        );

        $data['hFileTitle'] = hString::decodeEntitiesAndUTF8($data['hFileTitle']);

        $data['hFileHeadingTitle'] = hString::decodeEntitiesAndUTF8(
            $this->hFileHeadingTitle(nil, $data['hFileId'])
        );

        $data['hFileDocument'] = $this->expandDocumentIds(
            hString::decodeHTML($data['hFileDocument'])
        );

        $data['hFileDescription'] = hString::decodeHTML(
            $data['hFileDescription']
        );

        $data['hFileCommentsEnabled'] = $this->hFileCommentsEnabled(false, $data['hFileId']);

        $data['hUserName'] = $this->user->getUserName((int) $data['hUserId']);
        $data['hDirectoryPath'] = $this->getDirectoryPath((int) $data['hDirectoryId']);
        $data['hFileName'] = str_replace('.html', '', $data['hFileName']);

        $data['hUserPermissionsWorld'] = (int) $this->hFiles->hasWorldRead($fileId);

        $data['hCalendarLink'] = $this->hCalendarLink('', $fileId);

        if ($this->hCalendarFileCategoryId(nil) !== nil || $this->hCalendarTagCategoryId(nil))
        {
            $this->hCategoryDatabase = $this->database('hCategory');

            $this->hCategoryDatabase->setDatabaseReturnFormat('select');

            $data['hCategories'] = $this->hCategoryDatabase->getFileCategories($fileId, 'hCategoryId');
        }

        if ($this->hCalendarAttachMovie(nil))
        {
            $this->hList = $this->plugin('hList');

            $movies = $this->hList->getListFiles('Movies', $fileId);

            if (count($movies))
            {
                $data['hFileMovieId'] = $movies[0];
            }
        }

        $thumbnailFileId = (int) $this->hCalendarFileThumbnailId(0, $data['hFileId']);

        $data['hCalendarThumbnailId'] = $thumbnailFileId;
        $data['hCalendarThumbnailPath'] = $thumbnailFileId? $this->getFilePathByFileId($thumbnailFileId) : '';

        if ($this->hCalendarJobCompanyEnabled(true))
        {
            $data['hCalendarJobCompany'] = $this->hCalendarJobCompany(nil, $data['hFileId']);
        }

        if ($this->hCalendarJobLocationEnabled(true))
        {
            $data['hCalendarJobLocation'] = $this->hCalendarJobLocation(nil, $data['hFileId']);
        }

        if ($getCalendar)
        {
            $calendarFiles = $this->hDatabase->select(
                array(
                    # COLUMNS
                    'hCalendarFiles' => array(
                        'hCalendarFileId',
                        'hCalendarId',
                        'hCalendarCategoryId',
                        'hCalendarBegin',
                        'hCalendarEnd',
                        'hCalendarRange'
                    ),
                    'hCalendarFileDates' => array(
                        'hCalendarDate',
                        'hCalendarBeginTime',
                        'hCalendarEndTime',
                        'hCalendarAllDay'
                    )
                ),
                array(
                    # FROM
                    'hCalendarFiles',
                    'hCalendarFileDates'
                ),
                array(
                    # WHERE
                    'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                    'hCalendarFiles.hFileId' => (int) $fileId
                )
            );

//            echo $this->hDatabase->getLastQuery();

            if (count($calendarFiles))
            {
                $calendars = array();

                foreach ($calendarFiles as $calendar)
                {
                    array_push($calendars, $calendar['hCalendarId']);

                    if (!isset($data['hCalendarCategoryId']))
                    {
                        $data['hCalendarCategoryId']         = $calendar['hCalendarCategoryId'];
                        $data['hCalendarDate']               = $calendar['hCalendarDate'];
                        $data['hCalendarDateFormatted']      = date('m/d/Y', $calendar['hCalendarDate']);
                        $data['hCalendarBegin']              = $calendar['hCalendarBegin'];
                        $data['hCalendarBeginFormatted']     = $calendar['hCalendarBegin'] > 0 ? date('m/d/Y', $calendar['hCalendarBegin']) : '';
                        $data['hCalendarEnd']                = $calendar['hCalendarEnd'];
                        $data['hCalendarEndFormatted']       = $calendar['hCalendarEnd'] > 0 ? date('m/d/Y', $calendar['hCalendarEnd']) : '';
                        $data['hCalendarBeginTime']          = $calendar['hCalendarBeginTime'];
                        $data['hCalendarBeginTimeFormatted'] = $calendar['hCalendarBeginTime'] > 0 ? date('m/d/Y', $calendar['hCalendarBeginTime']) : '';
                        $data['hCalendarBeginTimeHour']      = $calendar['hCalendarBeginTime'] > 0 ? date('g', $calendar['hCalendarBeginTime'])     : '1';
                        $data['hCalendarBeginTimeMinute']    = $calendar['hCalendarBeginTime'] > 0 ? date('i', $calendar['hCalendarBeginTime'])     : '00';
                        $data['hCalendarBeginTimeMeridiem']  = $calendar['hCalendarBeginTime'] > 0 ? date('A', $calendar['hCalendarBeginTime'])     : 'AM';
                        $data['hCalendarEndTime']            = $calendar['hCalendarEndTime'];
                        $data['hCalendarEndTimeFormatted']   = $calendar['hCalendarEndTime'] > 0 ?   date('m/d/Y', $calendar['hCalendarEndTime'])   : '';
                        $data['hCalendarEndTimeHour']        = $calendar['hCalendarEndTime'] > 0 ?   date('g', $calendar['hCalendarEndTime'])       : '1';
                        $data['hCalendarEndTimeMinute']      = $calendar['hCalendarEndTime'] > 0 ?   date('i', $calendar['hCalendarEndTime'])       : '00';
                        $data['hCalendarEndTimeMeridiem']    = $calendar['hCalendarEndTime'] > 0 ?   date('A', $calendar['hCalendarEndTime'])       : 'AM';
                        $data['hCalendarAllDay']             = $calendar['hCalendarAllDay'];
                        $data['hCalendarRange']              = $calendar['hCalendarRange'];
                        $data['hCalendarFileId']             = $calendar['hCalendarFileId'];
                    }
                }

                $data['hCalendarId'] = $calendars;
            }
        }

        return $data;
    }

    public function &deleteEvent($fileId)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Deleting an Event</h2>
        # <p>
        #
        # </p>
        # @end
        $this->hCalendars->activity(
            'Deleted Event: '.$this->getFileTitle($fileId)
        );

        $query = $this->hCalendarFiles->select(
            array(
                'hCalendarId',
                'hCalendarCategoryId'
            ),
            array(
                'hFileId' => $fileId
            )
        );

        foreach ($query as $data)
        {
            $this->modifiedResource(
                $data['hCalendarId'],
                $data['hCalendarCategoryId']
            );
        }

        $this->hFile = $this->library('hFile');

        $files = $this->hCalendarFiles->select(
            'hCalendarFileId',
            array(
                'hFileId' => (int) $fileId
            )
        );

        foreach ($files as $data)
        {
            if (isset($data['hCalendarFileId']))
            {
                $this->hCalendarFileDates->delete(
                    'hCalendarFileId',
                    (int) $data['hCalendarFileId']
                );
            }
            else if (!is_array($data) && is_numeric($data))
            {
                $this->hCalendarFileDates->delete('hCalendarFileId', (int) $data);
            }
        }

        $this->hCalendarFiles->delete('hFileId', (int) $fileId);

        $this->hFile->delete($this->getFilePathByFileId($fileId));

        return $this;
    }

    public function getFileDates($files)
    {
        # @return array

        # @description
        # <h2>Getting All File Dates</h2>
        # <p>
        #
        # </p>
        # @end

        $dates = array();

        foreach ($files as $file)
        {
            $dates[$file['hFileId']] = $file['hCalendarDate'];
        }

        return $dates;
    }

    public function &setCategoryId($categoryId)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Setting a Category Id</h2>
        # <p>
        #   Sets the internal <var>$categoryId</var> property, which limits file queries
        #   for calendars to files within a specific category. This can be used for things
        #   like tagging and categorizing blog posts.
        # </p>
        # @end

        $this->categoryId = (int) $categoryId;

        return $this;
    }

    public function getFile($fileId, $calendarId = 0, $calendarCategoryId = 0)
    {
        # @return array

        # @description
        # <h2>Getting a Single File</h2>
        # <p>
        #   Returns a single file of <var>$fileId</var> from <var>$calendarId</var> and
        #   <var>$calendarCategoryId</var>. If <var>$calendarId</var> and <var>$calendarCategoryId</var>
        #   are not provided and the <var>$fileId</var> appears in multiple calendars or calendar
        #   categories, the last match is returned.
        # </p>
        # @end

        $files = $this->getFiles(
            $calendarId,
            $calendarCategoryId,
            0,
            nil,
            false,
            'ASC',
            $fileId
        );

        return array_pop($files);
    }

    public function &setResultCount()
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Setting a Result Count</h2>
        # <p>
        #   Sets the internal <var>$resultCount</var> property by retrieving the value of
        #   <a href='/Hot Toddy/Documentation?hDatabase#getResultCount' class='code'>hDatabase::getResultCount()</a>
        # </p>
        # @end

        $this->resultCount = $this->hDatabase->getResultCount();

        return $this;
    }

    public function getResultCount()
    {
        # @return integer

        # @description
        # <h2>Returning a Result Count</h2>
        # <p>
        #   Returns the value of the internal <var>$resultCount</var> property.
        # </p>
        # @end

        return $this->resultCount;
    }

    public function &setFileCalendars(array $calendars = array())
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Limiting Files Returned to Read/Write on a Calendar</h2>
        # <p>
        #
        # </p>
        # @end

        $this->fileCalendars = $calendars;

        return $this;
    }

    public function getFiles($calendarId = 0, $calendarCategoryId = 0, $limit = 10, $timeRange = nil, $withinTimeBoundaries = true, $sort = 'ASC', $fileId = 0, $calendarDate = nil)
    {
        # @return array

        # @description
        # <h2>Getting Multiple Calendar Files</h2>
        # <p>
        #   Returns calendar files that match the criteria of the supplied arguments.
        # </p>
        # @end

        $events = false;
        $recentEvents = false;
        $monthOrRecent = false;

        if (!empty($timeRange))
        {
            switch (strtolower($timeRange))
            {
                case 'events':
                {
                    $events = true;
                    $timeOperator = '>=';
                    $time = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
                    break;
                }
                case 'recentevents':
                {
                    $sort = "`hCalendarFileDates`.`hCalendarBeginTime` DESC";
                    $recentEvents = true;
                    break;
                }
                case 'news':
                {
                    $sort = "`hCalendarFileDates`.`hCalendarDate` DESC";
                    break;
                }
                case 'monthorrecent':
                {
                    $monthOrRecent = true;
                }
                case 'month':
                {
                    $timeOperator = '>=';
                    $time = mktime(
                        0, 0, 0,
                        !is_null($calendarDate)? date('m', $calendarDate) : date('m'),
                        1,
                        !is_null($calendarDate)? date('Y', $calendarDate) : date('Y')
                    );

                    $timeOperator2 = '<=';
                    $time2 = mktime(
                        0, 0, 0,
                        !is_null($calendarDate)? date('m', $calendarDate) : date('m'),
                        !is_null($calendarDate)? date('t', $calendarDate) : date('t'),
                        !is_null($calendarDate)? date('Y', $calendarDate) : date('Y')
                    );
                    break;
                }
                default:
                {
                    if (!strstr($timeRange, ','))
                    {
                        # operator $timestamp
                        list($timeOperator, $time) = explode(' ', trim($timeRange));
                    }
                    else
                    {
                        # operator timestamp, operator timestamp
                        $ranges = explode(',', $timeRange);

                        list($timeOperator, $time) = explode(' ', trim($ranges[0]));
                        list($timeOperator2, $time2) = explode(' ', trim($ranges[1]));
                    }
                }
            }
        }

        $customSort = '';

        if ($sort != 'ASC' && $sort != 'DESC')
        {
            $customSort = $sort;
            $sort = '';
        }

        $checkPermissions = !$this->inGroup('root') && $this->permissionsMethod == 'all';
        $checkWorldPermissions = !$this->inGroup('root') && $this->permissionsMethod == 'world';

        $multipleCalendars = false;

        if (is_array($calendarId))
        {
            $calendars = array();

            foreach ($calendarId as $calendar)
            {
                array_push(
                    $calendars,
                    "`hCalendarFiles`.`hCalendarId` = {$calendar}"
                );
            }

            $multipleCalendars = implode(' OR ', $calendars);
        }

        $fileCalendarSQL = '';

        if (is_array($this->fileCalendars) && count($this->fileCalendars))
        {
            $fileCalendarSQL = array();

            foreach ($this->fileCalendars as $fileCalendarId)
            {
                array_push(
                    $fileCalendarSQL,
                    '`hCalendarFiles`.`hCalendarId` = '.$fileCalendarId
                );
            }

            $fileCalendarSQL = implode(' OR ', $fileCalendarSQL);
        }

        $sql = $this->getTemplateSQL(
            array_merge(
                array(
                    'calendarId'            => $calendarId,
                    'multipleCalendars'     => $multipleCalendars,
                    'calendarCategoryId'    => $calendarCategoryId,
                    'fileId'                => $fileId,
                    'categoryId'            => (int) $this->categoryId,
                    'timeRange'             => isset($time) ? $time : nil,
                    'timeRangeOperator'     => isset($timeOperator) ? $timeOperator : nil,
                    'timeRange2'            => isset($time2) ? $time2 : nil,
                    'timeRangeOperator2'    => isset($timeOperator2) ? $timeOperator2 : nil,
                    'withinTimeBoundaries'  => $withinTimeBoundaries,
                    'time'                  => time(),
                    'sort'                  => $sort,
                    'customSort'            => $customSort,
                    'limit'                 => $limit,
                    'fileCalendarSQL'       => $fileCalendarSQL
                ),
                $this->getPermissionsVariablesForTemplate(
                    $checkPermissions,
                    $checkWorldPermissions
                )
            )
        );

        $query = $this->hDatabase->getResults($sql);

        $files = array();

        $fields = array(
            'hCalendarDate',
            'hCalendarBeginTime',
            'hCalendarEndTime',
            'hCalendarAllDay',
            'hCalendarId',
            'hCalendarCategoryId'
        );

        if (count($query))
        {
            $icons = false;

            if ($this->hCalendarFileIcons(false))
            {
                $icons = true;
                $this->hFileIcon = $this->library('hFile/hFileIcon');
            }

            $this->setResultCount();

            $i = 0;

            foreach ($query as $data)
            {
                foreach ($data as $key => $value)
                {
                    switch ($key)
                    {
                        case 'hFileDescription':
                        case 'hFileDocument':
                        {
                            $data[$key] = hString::decodeHTML($data[$key]);
                            break;
                        }
                    }
                }

                $data['hFileDocument'] = $this->expandDocumentIds($data['hFileDocument']);

                $data['hFileHeadingTitle'] = $this->hFileHeadingTitle(
                    $data['hFileTitle'],
                    (int) $data['hFileId']
                );

                $data['hCalendarLink'] = $this->hCalendarLink(
                    nil,
                    (int) $data['hFileId']
                );

                if ($icons)
                {
                    $data['hFileIconPath'] = $this->hFileIcon->getFileIconPath(
                        (int) $data['hFileId'],
                        nil,
                        nil,
                        $this->hCalendarFileIconResolution('32x32')
                    );
                }

                if ($this->hCalendarEnableThumbnail(false))
                {
                    $fileId = $this->hCalendarFileThumbnailId(0, $data['hFileId']);

                    $data['hCalendarFileThumbnailId'] = $fileId;
                    $data['hCalendarFileThumbnailPath'] = $this->getFilePathByFileId($fileId);
                }

                $identifier = $data['hFileId'];

                if (!isset($files[$identifier]))
                {
                    $files[$identifier] = $data;
                }
                else
                {
                    $calendarDate       = $files[$identifier]['hCalendarDate'];
                    $calendarBeginTime  = $files[$identifier]['hCalendarBeginTime'];
                    $calendarEndTime    = $files[$identifier]['hCalendarEndTime'];
                    $calendarAllDay     = $files[$identifier]['hCalendarAllDay'];

                    # Make sure that all date information is taken into account...
                    # If any one bit of date information is different than what's already there,
                    # it is considered unique.
                    $isUnique =
                        $data['hCalendarDate'] != $calendarDate ||
                        $data['hCalendarBeginTime'] != $calendarBeginTime ||
                        $data['hCalendarEndTime'] != $calendarEndTime ||
                        $data['hCalendarAllDay'] != $calendarAllDay;

                    foreach ($fields as $field)
                    {
                        if (!is_array($files[$identifier][$field]))
                        {
                            $item = $files[$identifier][$field];

                            if ($field == 'hCalendarId' || $field == 'hCalendarCategoryId' || $isUnique)
                            {
                                $files[$identifier][$field] = ($item != $data[$field])? array($item, $data[$field]) : $item;
                            }
                        }
                        else
                        {
                            if ($field == 'hCalendarId' || $field == 'hCalendarCategoryId' || $isUnique)
                            {
                                if (!in_array($data[$field], $files[$identifier][$field]))
                                {
                                    array_push($files[$identifier][$field], $data[$field]);
                                }
                            }
                        }
                    }
                }

                $i++;
            }
        }
        else
        {
            if ($events || $monthOrRecent)
            {
                $this->hCalendarRecentEvents = true;

                return $this->getFiles(
                    $calendarId,
                    $calendarCategoryId,
                    $this->hCalendarRecentEventCount($limit),
                    'RecentEvents',
                    $withinTimeBoundaries,
                    $sort,
                    $fileId
                );
            }
        }

        if ($recentEvents)
        {
            $files = array_reverse($files, true);
        }

        return $files;
    }

    public function getFilesForTemplate($dateFormats, $calendarId = 0, $calendarCategoryId = 0, $limit = 10, $timeRange = nil, $withinTimeBoundaries = true, $sort = 'ASC', $fileId = 0, $calendarDate = nil)
    {
        # @return array

        # @description
        # <h2>Getting Multiple Calendar Files For a Template</h2>
        # <p>
        #   Returns files attached to calendars based on the criteria passed in arguments ready for
        #   insertion into a Hot Toddy template.
        # </p>
        # @end

        if (!is_array($dateFormats))
        {
            $dateFormats = array(
                'hCalendarDate' => $dateFormats
            );
        }

        $files = $this->getFiles(
            $calendarId,
            $calendarCategoryId,
            $limit,
            $timeRange,
            $withinTimeBoundaries,
            $sort,
            $fileId,
            $calendarDate
        );

        $this->datesInQuery = array();

        foreach ($files as &$file)
        {
            array_push($this->datesInQuery, $file['hCalendarDate']);

            if (!is_array($file['hCalendarDate']))
            {
                $file['hCalendarDateFormatted'] = date(
                    $dateFormats['hCalendarDate'],
                    $file['hCalendarDate']
                );

                if (isset($dateFormats['hCalendarBeginTime']))
                {
                    $file['hCalendarBeginTimeFormatted'] = date(
                        $dateFormats['hCalendarBeginTime'],
                        $file['hCalendarBeginTime']
                    );
                }

                if (isset($dateFormats['hCalendarEndTime']))
                {
                    $file['hCalendarEndTimeFormatted'] = date(
                        $dateFormats['hCalendarEndTime'],
                        $file['hCalendarEndTime']
                    );
                }
            }
            else
            {
                $dates = array();
                $beginTimes = array();
                $endTimes = array();

                foreach ($file['hCalendarDate'] as $i => $calendarDate)
                {
                    $dates[] = date(
                        $dateFormats['hCalendarDate'],
                        $file['hCalendarDate'][$i]
                    );

                    if (isset($dateFormats['hCalendarBeginTime']))
                    {
                        $beginTimes[] = date(
                            $dateFormats['hCalendarBeginTime'],
                            $file['hCalendarBeginTime'][$i]
                        );
                    }

                    if (isset($dateFormats['hCalendarEndTime']))
                    {
                        $endTimes[] = date(
                            $dateFormats['hCalendarEndTime'],
                            $file['hCalendarEndTime'][$i]
                        );
                    }
                }

                $file['hCalendarDateFormatted'] = implode(', ', $dates);
                $file['hCalendarBeginTimeFormatted'] = implode(', ', $beginTimes);
                $file['hCalendarEndTimeFormatted'] = implode(', ', $endTimes);
            }

            if ($this->hCalendarEventPost)
            {
                $file['hCalendarEventPost'] = true;
            }

            if ($this->hCalendarBlogPost)
            {
                $file['hCalendarBlogPost'] = true;
            }
        }

        return $this->hDatabase->getResultsForTemplate($files);
    }

    public function getDatesInLastFileQuery()
    {
        # @return array

        # @description
        # <h2>Get Dates in Last Query</h2>
        # <p>
        #   Returns the calendar dates returned in the last file query.
        # </p>
        # @end

        # It only gets the dates for getFilesForTemplate() method presently.
        return $this->datesInQuery;
    }

    public function getFileDate($fileId)
    {
        # @return integer

        # @description
        # <h2>Getting Last File Date</h2>
        # <p>
        #   Returns the last (latest) date for a given calendar.
        # </p>
        # @end

        $dates = $this->hDatabase->select(
            array(
                'hCalendarFileDates' => 'hCalendarDate'
            ),
            array(
                'hCalendarFiles',
                'hCalendarFileDates'
            ),
            array(
                'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                'hCalendarFiles.hFileId' => (int) $fileId
            )
        );

        $date = count($dates) > 1 ? array_pop($dates) : isset($dates[0])? $dates[0] : nil;

        return (int) $date;
    }

    public function &updateFileDate($fileId, $calendarDate)
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Updating File Dates</h2>
        # <p>
        #   Updates all <var>$calendarDate</var> entries for the specified <var>$fileId</var>
        # </p>
        # @end

        # Get current dates
        $query = $this->hDatabase->select(
            array(
                'hCalendarFileDates' => array(
                    'hCalendarFileId',
                    'hCalendarDate',
                    'hCalendarBeginTime',
                    'hCalendarEndTime'
                ),
                'hCalendarFiles' => array(
                    'hCalendarId',
                    'hCalendarCategoryId'
                )
            ),
            array(
                'hCalendarFileDates',
                'hCalendarFiles'
            ),
            array(
                'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                'hCalendarFiles.hFileId' => (int) $fileId
            )
        );

        foreach ($query as $data)
        {
            $this->modifiedResource(
                $data['hCalendarId'],
                $data['hCalendarCategoryId']
            );

            $calendarBeginTime = 0;

            if ($data['hCalendarEndTime'] > 0)
            {
                $calendarBeginTime = mktime(
                    date('H', $data['hCalendarBeginTime']),
                    date('i', $data['hCalendarBeginTime']),
                    date('s', $data['hCalendarBeginTime']),
                    date('n', $calendarDate),
                    date('j', $calendarDate),
                    date('Y', $calendarDate)
                );
            }

            $calendarEndTime = 0;

            if ($data['hCalendarEndTime'] > 0)
            {
                $calendarEndTime = mktime(
                    date('H', $data['hCalendarEndTime']),
                    date('i', $data['hCalendarEndTime']),
                    date('s', $data['hCalendarEndTime']),
                    date('n', $calendarDate),
                    date('j', $calendarDate),
                    date('Y', $calendarDate)
                );
            }

            $this->hCalendarFileDates->update(
                array(
                    'hCalendarDate'      => $calendarDate,
                    'hCalendarBeginTime' => $calendarBeginTime,
                    'hCalendarEndTime'   => $calendarEndTime
                ),
                array(
                    'hCalendarFileId' => $data['hCalendarFileId']
                )
            );
        }

        return $this;
    }

    public function getFileTime($fileId)
    {
        # @return string

        # @description
        # <h2>Getting File Begin and End Times</h2>
        # <p>
        #   Returns the begin and end times for the specified <var>$fileId</var>.
        # </p>
        # @end

        $calendarBeginTime = $this->hDatabase->selectColumn(
            array(
                'hCalendarFileDates' => 'hCalendarBeginTime'
            ),
            array(
                'hCalendarFiles',
                'hCalendarFileDates'
            ),
            array(
                'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                'hCalendarFiles.hFileId' => (int) $fileId
            )
        );

        $calendarEndTime = $this->hDatabase->selectColumn(
            array(
                'hCalendarFileDates' => 'hCalendarEndTime'
            ),
            array(
                'hCalendarFiles',
                'hCalendarFileDates'
            ),
            array(
                'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId',
                'hCalendarFiles.hFileId' => (int) $fileId
            )
        );

        return (
            date('g:i A', $calendarBeginTime).
            ' - '.
            date('g:i A', $calendarEndTime)
        );
    }

    public function &setPermissionsMethodToWorld()
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Setting Permissions to World</h2>
        # <p>
        #   Sets the internal <var>$permissionsMethod</var> to <var>world</var>, which causes
        #   all queries to execute in the context of world-read permission, rather than
        #   individual user-level permission determinations.
        # </p>
        # @end

        $this->permissionsMethod = 'world';
        return $this;
    }

    public function &setPermissionsMethodToEverything()
    {
        # @return hCalendarDatabase

        # @description
        # <h2>Setting Permissions to All</h2>
        # <p>
        #   Sets the internal <var>$permissionsMethod</var> to <var>all</var>, which makes permission
        #   checks in queries execute individual user-level permission determinations.
        # </p>
        # @end

        $this->permissionsMethod = 'all';
        return $this;
    }
}

?>