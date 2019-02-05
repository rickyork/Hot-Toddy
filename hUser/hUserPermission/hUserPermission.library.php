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
# <h1>Hot Toddy Permission API</h1>
# <p>
#   <var>hUserPermissionLibrary</var> provides an API for creating or modifying the permissions
#   associated with framework resources.  Framework resources can refer to any database table
#   where each record in that table can represent a record that can be owned by a person or a group.
# </p>
# <p>
#   The database table must follow this template:
# </p>
# <ul>
#   <li>It has a primary key, the primary key is an auto-incrementing integer.</li>
#   <li>It has a <var>hUserId</var> column, using that exact name.</li>
#   <li>It has a column that can be used as a somewhat unique plain English name that can be used to easily describe what the record is.</li>
# </ul>
# <p>
#   Framework resources are registered and kept track of using the
#   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFrameworkResources/hFrameworkResources.sql' class='code'>hFrameworkResource.sql</a>
#   database table.  Pre-defined framework resources are created upon installation of Hot Toddy
#   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hFrameworkResources/hFrameworkResources.insert.sql' class='code'>hFrameworkResource.insert.sql</a>
# </p>
# @end


class hUserPermissionLibrary extends hPlugin  {
    
    private $hFrameworkResource;

    private $groups = array();
    private $setInherit = 0;

    private $resourceId;
    private $resourceKey;
    private $resourceTable;
    private $resourcePrimaryKey;
    private $resourceNameColumn;
    private $resourceName;
    private $resourceOwner;
    
    public function hConstructor()
    {
        $this->hFrameworkResource = $this->library('hFramework/hFrameworkResource');
    }

    public function saveForm($resourceId, $resourceKey, $owner, $world, array $users, array $groups)
    {
        if (empty($resourceId) || empty($resourceKey))
        {
            return -5;
        }

        if (!$this->isLoggedIn())
        {
            return -6;
        }

        $resource = $this->hFrameworkResource->getResource($resourceId);

        $where = array();
        $where[$resource['hFrameworkResourcePrimaryKey']] = $resourceKey;

        $authentication = false;

        $isOwner = (bool) $this->hDatabase->selectExists(
            $resource['hFrameworkResourcePrimaryKey'],
            $resource['hFrameworkResourceTable'],
            array_merge(
                $where,
                array(
                    'hUserId' => (int) $_SESSION['hUserId']
                )
            )
        );

        switch (true)
        {
            case $this->inGroup('Website Administrators'):
            case $this->inGroup('root'):
            {
                $authentication = true;
                break;
            }
            case $isOwner:
            {
                $authentication = true;
                break;
            }
        }

        if (!$authentication)
        {
            return -1;
        }

        if (count($groups))
        {
            foreach ($groups as $group => $level)
            {
                $this->setGroup($group, $level);
            }
        }

        if (count($users))
        {
            foreach ($users as $user => $level)
            {
                $this->setGroup($user, $level);
            }
        }

        $this->save($resourceId, $resourceKey, $owner, $world);

        $this->activity(
            $resource['hFrameworkResourceTable'],
            "Modified Permissions: on '{$resource['hFrameworkResourceTable']}, {$resourceKey}' to ".
            "owner: '{$owner}', world: '{$world}', users: '".implode(', ', $users)."' groups: '".implode(', ', $groups)."'"
        );

        return 1;
    }

    public function inheritToCategories($categoryId, $groups, $owner, $world)
    {
        $categories = $this->getCategoriesInCategory($categoryId);

        if (count($categories))
        {
            foreach ($categories as $categoryId)
            {
                $this->setGroups($groups);

                $this->save(
                    'hCategories',
                    $categoryId,
                    $owner,
                    $world
                );

                if (isset($_POST['category']) && (int) $_POST['category'] == 2 && !empty($categoryId))
                {
                    $this->inheritToCategories(
                        $categoryId,
                        $groups,
                        $owner,
                        $world
                    );
                }
            }
        }
    }

    private function getCategoriesInCategory($categoryId)
    {
        return $this->hCategories->selectResults(
            'hCategoryId',
            array(
                'hCategoryParentId' => (int) $categoryId
            )
        );
    }

    private function getFilesInDirectory($directoryId)
    {
        return $this->hFiles->selectResults(
            'hFileId',
            array(
                'hDirectoryId' => (int) $directoryId
            )
        );
    }

    private function getAllSubDirectories()
    {
        $path = $this->getDirectoryPath($this->resourceKey);

        return $this->hDirectories->selectResults(
            'hDirectoryId',
            array(
                'hDirectoryPath' => array(
                    array('=', $path),
                    array('LIKE', $path.'/%')
                )
            ),
            'OR',
            'hDirectoryPath'
        );
    }

    private function inheritToFiles($files, $owner, $world, $groups)
    {
        foreach ($files as $fileId)
        {
            $this->setGroups($groups);

            $this->save(
                'hFiles',
                $fileId,
                $owner,
                $world
            );
        }
    }

    private function inheritToDirectory($directories, $owner, $world, $groups)
    {
        foreach ($directories as $directoryId)
        {
            $this->setGroups($groups);

            $this->save(
                'hDirectories',
                $directoryId,
                $owner,
                $world
            );
        }
    }

    public function saveWorldAccess($frameworkResource, $frameworkResourceKey, $userPermissionsWorld = '')
    {
        $this->deleteCache(
            $frameworkResource,
            $frameworkResourceKey
        );

        $frameworkResourceId = $this->hFrameworkResource->getResourceId($frameworkResource);

        $userPermissionsId = $this->getPermissionsId(
            $frameworkResourceId,
            $frameworkResourceKey
        );

        if ($this->validate($userPermissionsWorld))
        {
            if (!$userPermissionsId)
            {
                $userPermissionsId = $this->insert(
                    $frameworkResourceId,
                    $frameworkResourceKey,
                    'rw',
                    $userPermissionsWorld
                );
            }
            else
            {
                $this->update(
                    $userPermissionsId,
                    $frameworkResourceId,
                    $frameworkResourceKey,
                    $this->getField(
                        $userPermissionsId,
                        'hUserPermissionsOwner'
                    ),
                    $userPermissionsWorld
                );
            }
        }
    }

    public function exist($frameworkResourceId, $frameworkResourceKey)
    {
        $this->hFrameworkResource->numericResourceId($frameworkResourceId);

        return $this->hUserPermissions->selectExists(
            'hUserPermissionsId',
            array(
                'hFrameworkResourceId' => (int) $frameworkResourceId,
                'hFrameworkResourceKey' => (int) $frameworkResourceKey
            )
        );
    }

    public function createIfNot($frameworkResourceId, $frameworkResourceKey)
    {

    }

    public function save($frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner = 'rw', $userPermissionsWorld = '')
    {
        $this->deleteCache(
            $frameworkResourceId,
            $frameworkResourceKey
        );
            
        $this->hFrameworkResource->numericResourceId(
            $frameworkResourceId
        );

        $userPermissionsId = $this->getPermissionsId(
            $frameworkResourceId,
            $frameworkResourceKey
        );

        if ($this->setInherit > 0)
        {
            $userPermissionsOwner = $this->getField(
                $this->setInherit,
                'hUserPermissionsOwner'
            );

            $userPermissionsWorld = $this->getField(
                $this->setInherit,
                'hUserPermissionsWorld'
            );
        }

        if ($this->validate($userPermissionsOwner) && $this->validate($userPermissionsWorld))
        {
            if (!$userPermissionsId)
            {
                $userPermissionsId = $this->insert(
                    $frameworkResourceId,
                    $frameworkResourceKey,
                    $userPermissionsOwner,
                    $userPermissionsWorld
                );
            }
            else
            {
                $this->update(
                    $userPermissionsId,
                    $frameworkResourceId,
                    $frameworkResourceKey,
                    $userPermissionsOwner,
                    $userPermissionsWorld
                );
            }
        }

        if (!empty($userPermissionsId))
        {
            $this->hUserPermissionsGroups->delete(
                'hUserPermissionsId',
                (int) $userPermissionsId
            );

            if ($this->setInherit > 0)
            {
                $this->groups = $this->hUserPermissionsGroups->selectColumnsAsKeyValue(
                    array(
                        'hUserGroupId',
                        'hUserPermissionsGroup'
                    ),
                    array(
                        'hUserPermissionsId' => (int) $this->setInherit
                    )
                );
            }

            if (is_array($this->groups) && count($this->groups))
            {
                foreach ($this->groups as $userGroupId => $userPermissionsGroup)
                {
                    if (!is_numeric($userGroupId))
                    {
                        $userGroupId = $this->user->getUserId($userGroupId);
                    }

                    if (!empty($userGroupId) && $this->validate($userPermissionsGroup))
                    {
                        $this->hUserPermissionsGroups->insert(
                            array(
                                'hUserPermissionsId' => (int) $userPermissionsId,
                                'hUserGroupId' => (int) $userGroupId,
                                'hUserPermissionsGroup' => $userPermissionsGroup
                            )
                        );
                    }
                }
            }
        }

        $this->setInherit = 0;
    }

    public function delete($frameworkResource, $frameworkResourceKey)
    {
        $this->deleteCache(
            $frameworkResource,
            $frameworkResourceKey
        );

        $userPermissionsId = $this->getPermissionsId(
            $frameworkResource,
            $frameworkResourceKey
        );

        $this->hDatabase->delete(
            array(
                'hUserPermissionsGroups',
                'hUserPermissions'
            ),
            'hUserPermissionsId',
            $userPermissionsId
        );
    }

    public function &deleteCache($frameworkResource, $frameworkResourceKey, $userPermissionsType = 'hUserPermissions')
    {
        if (is_numeric($frameworkResource))
        {
            $frameworkResource = $this->hFrameworkResource->getResource($frameworkResource);
            $frameworkResource = $frameworkResource['hFrameworkResourceTable'];
        }

        $this->hUserPermissionsCache->delete(
            array(
                'hUserPermissionsType' => $userPermissionsType,
                'hUserPermissionsVariable' => array(
                    'LIKE',
                    "{$frameworkResource}:{$frameworkResourceKey}:%"
                )
            )
        );

        return $this;
    }

    private function validate($access)
    {
        switch ($access)
        {
            case 'r':
            case 'rw':
            case 'w':
            case '':
            {
                return true;
            }
            default:
            {
                $this->warning(
                    'Permissions access level: '.$access.' has not been defined.',
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    private function getField($userPermissionsId, $field)
    {
        $query = $this->hUserPermissions->selectQuery(
            $field,
            (int) $userPermissionsId
        );

        if ($this->hDatabase->resultsExist($query))
        {
            return $this->hDatabase->getColumn($query);
        }
        else
        {
            $this->warning(
                'Unable to query hUserPermissions for field: '.$field .' '.
                'using hUserPermissionsId: '.$userPermissionsId.'.',
                __FILE__,
                __LINE__
            );
        }
    }

    private function insert($frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner, $userPermissionsWorld)
    {
        $args = func_get_args();

        return $this->hUserPermissions->insert(
            array(
                'hUserPermissionsId'    => null,
                'hFrameworkResourceId'  => (int) $frameworkResourceId,
                'hFrameworkResourceKey' => (int) $frameworkResourceKey,
                'hUserPermissionsOwner' => $userPermissionsOwner,
                'hUserPermissionsWorld' => $userPermissionsWorld
            )
        );
    }

    private function update($userPermissionsId, $frameworkResourceId, $frameworkResourceKey, $userPermissionsOwner, $userPermissionsWorld)
    {
        $this->deleteCache(
            $frameworkResourceId,
            $frameworkResourceKey
        );

        $this->hUserPermissions->update(
            array(
                'hUserPermissionsId'    => $userPermissionsId,
                'hFrameworkResourceId'  => $frameworkResourceId,
                'hFrameworkResourceKey' => $frameworkResourceKey,
                'hUserPermissionsOwner' => $userPermissionsOwner,
                'hUserPermissionsWorld' => $userPermissionsWorld
            ),
            $userPermissionsId
        );
    }

    public function setGroup($userGroupId, $userPermissionsGroup = 'r')
    {
        $this->user->setNumericUserId($userGroupId);
        $this->groups[$userGroupId] = $userPermissionsGroup;
    }

    public function addGroup($userGroupId, $userPermissionsGroup = 'r')
    {
        $this->setGroup(
            $userGroupId,
            $userPermissionsGroup
        );
    }

    public function setGroups(array $userGroupIds)
    {
        $this->groups = $userGroupIds;
    }

    public function addGroups(array $userGroupIds)
    {
        $this->setGroups($userGroupIds);
    }

    private function getPermissionsId($frameworkResourceId, $frameworkResourceKey)
    {
        $this->hFrameworkResource->numericResourceId($frameworkResourceId);
        
        return $this->hUserPermissions->selectColumn(
            'hUserPermissionsId',
            array(
                'hFrameworkResourceId'  => (int) $frameworkResourceId,
                'hFrameworkResourceKey' => (int) $frameworkResourceKey
            )
        );
    }

    public function setInherit($frameworkResourceId = 0, $frameworkResourceKey = 0)
    {
        $this->hFrameworkResource->numericResourceId($frameworkResourceId);

        if (empty($frameworkResourceId) && empty($frameworkResourceKey))
        {
            $this->setInherit = 0;
        }
        else if (!empty($frameworkResourceKey))
        {
            $this->setInherit = $this->getPermissionsId(
                $frameworkResourceId,
                $frameworkResourceKey
            );

            if (!$this->setInherit)
            {
                $this->warning(
                    'hUserPermissionsId could not be pulled from hFrameworkResourceId and hFrameworkResourceKey.',
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            $this->warning(
                'hFrameworkResourceId and hFrameworkResourceKey are required to '.
                'inherit permissions from another resource.',
                __FILE__,
                __LINE__
            );
        }
    }

    public function getPermissions($frameworkResourceId, $frameworkResourceKey)
    {
        $this->hFrameworkResource->numericResourceId($frameworkResourceId);
        
        $rtn = $this->hUserPermissions->selectAssociative(
            array(
                'hUserPermissionsId',
                'hUserPermissionsOwner',
                'hUserPermissionsWorld'
            ),
            array(
                'hFrameworkResourceId' => (int) $frameworkResourceId,
                'hFrameworkResourceKey' => (int) $frameworkResourceKey
            )
        );

        if (count($rtn))
        {
            $query = $this->hUserPermissionsGroups->select(
                array(
                    'hUserGroupId',
                    'hUserPermissionsGroup'
                ),
                array(
                    'hUserPermissionsId' => (int) $rtn['hUserPermissionsId']
                )
            );

            if (count($query))
            {
                foreach ($query as $data)
                {
                    if ($this->hUserGroupProperties->selectExists('hUserId', (int) $data['hUserGroupId']))
                    {
                        $rtn['hUserGroups'][$data['hUserGroupId']] = $data['hUserPermissionsGroup'];
                    }
                    else
                    {
                        $rtn['hUsers'][$data['hUserGroupId']] = $data['hUserPermissionsGroup'];
                    }
                }
            }
        }

        return $rtn;
    }

    public function isAuthorized($frameworkResourceId, $frameworkResourceKey, $userId = 0)
    {
        $this->hFrameworkResource->numericResourceId($frameworkResourceId);

        $this->user->whichUserId($userId);

        switch (true)
        {
            case (empty($frameworkResourceId) || empty($frameworkResourceKey)):
            {
                return false;
            }
            case (!$this->isLoggedIn()):
            {
                return false;
            }
        }

        $resource = $this->hFrameworkResource->getResource($frameworkResourceId);

        $frameworkResourceTable      = $resource['hFrameworkResourceTable'];
        $frameworkResourcePrimaryKey = $resource['hFrameworkResourcePrimaryKey'];
        $frameworkResourceNameColumn = $resource['hFrameworkResourceNameColumn'];

        $columns[$frameworkResourcePrimaryKey] = $frameworkResourceKey;

        $frameworkResourceName = $this->hDatabase->selectColumn(
            $frameworkResourceNameColumn,
            $frameworkResourceTable,
            $columns
        );

        $frameworkResourceOwner = (int) $this->hDatabase->selectColumn(
            'hUserId',
            $frameworkResourceTable,
            $columns
        );

        $frameworkIsResourceOwner = $this->isResourceOwner(
            $frameworkResourceTable,
            $frameworkResourcePrimaryKey,
            $frameworkResourceKey,
            $userId
        );

        if (!$frameworkIsResourceOwner && !$this->inGroup('Website Administrators'))
        {
            return false;
        }

        return array_merge(
            $resource,
            array(
                'hFrameworkResourceName'    => $frameworkResourceName,
                'hFrameworkResourceOwner'   => $frameworkResourceOwner,
                'hFrameworkIsResourceOwner' => $frameworkIsResourceOwner
            )
        );
    }

    public function isResourceOwner($frameworkResourceTable, $frameworkResourcePrimaryKey, $frameworkResourceKey, $userId = 0)
    {
        $this->user->whichUserId($userId);

        $columns[$frameworkResourcePrimaryKey] = $frameworkResourceKey;

        return $this->hDatabase->selectExists(
            $frameworkResourcePrimaryKey,
            $frameworkResourceTable,
            array_merge(
                $columns,
                array(
                    'hUserId' => (int) $userId
                )
            )
        );
    }

    public function chown($frameworkResourceId, $frameworkResourceKey, $userId = 0)
    {
        # @return integer

        # @description
        # <h2>Changing the Owner of a Resource</h2>
        # <p>
        #   This function changes the <var>userId</var> associated with a resource.
        # </p>
        # <p>
        #   <var>$frameworkResourceId</var> can be an <var>hFrameworkResourceId</var> or <var>hFrameworkResource</var>,
        #   the name of the resource being modified.  This is typically the same as the table name.  For example,
        #   <var>hFiles</var>, <var>hDirectories</var>, etc, are examples of valid framework resources.
        # </p>
        # <p>
        #   <var>$frameworkResourceKey</var> is the unique id of the resource you wish to change the owner of.
        #   For example, this would be an <var>hFileId</var> if the resource is <var>hFiles</var>, an <var>hDirectoryId</var>
        #   if the resource is <var>hDirectories</var>, and so on.
        # </p>
        # <p>
        #   <var>$userId</var> is the new resource owner.  The column <var>hUserId</var> will be updated to the
        #   value you provide.  If no value is provided, the user controlling the session becomes the owner.
        #   <var>$userId</var> can be an <var>hUserId</var>, <var>hUserName</var>, or <var>hUserEmail</var>.
        # </p>
        # @end

        $this->hFrameworkResource->numericResourceId($frameworkResourceId);

        $this->user->whichUserId($userId)->setNumericUserId($userId);

        $resource = $this->hFrameworkResource->getResource($frameworkResourceId);

        $where = array();
        $where[$resource['hFrameworkResourcePrimaryKey']] = $frameworkResourceKey;

        return $this->hDatabase->update(
            array(
                'hUserId' => (int) $userId
            ),
            $where,
            $resource['hFrameworkResourceTable']
        );
    }
}

?>