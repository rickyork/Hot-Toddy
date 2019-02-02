<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Permissions
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

class hUserPermissions extends hPlugin {

    private $hFrameworkResourceId;
    private $hFrameworkResourceKey;
    private $hFrameworkResourceTable;
    private $hFrameworkResourceName;
    private $hFrameworkResourceNameColumn;
    private $hFrameworkResourcePrimaryKey;
    private $hFrameworkResourceOwner;
    private $hUserSelect;
    private $hUserPermissions;

    private $hForm;
    private $hDialogue;

    public function hConstructor()
    {
        $this->plugin('hApplication');

        $this->hFileTitle = 'Permissions Manager';
        #$this->hTemplatePath = '/hUser/hUserPermissions/hUserPermissions.template.php';

        $this->hUserPermissions = $this->library('hUser/hUserPermissions');
        $this->hUserSelect = $this->library('hUser/hUserSelect');

        $this->getPluginFiles();
        $this->getPluginCSS('ie6');

        $authentication = false;
        $validation = false;

        switch (true)
        {
            case (!isset($_GET['hFrameworkResourceId']) || !isset($_GET['hFrameworkResourceKey'])):
            {
                $validation = false;
                break;
            }
            case (!$this->isLoggedIn()):
            {
                $this->notLoggedIn();
                $validation = false;
                break;
            }
            default:
            {
                $validation = true;
            }
        }

        if ($validation)
        {
            $this->hFrameworkResourceId = hString::scrubWord($_GET['hFrameworkResourceId']);
            $this->hFrameworkResourceKey = (int) $_GET['hFrameworkResourceKey'];

            $resource = $this->getResource($this->hFrameworkResourceId);

            $this->hFrameworkResourceTable      = $resource['hFrameworkResourceTable'];
            $this->hFrameworkResourcePrimaryKey = $resource['hFrameworkResourcePrimaryKey'];
            $this->hFrameworkResourceNameColumn = $resource['hFrameworkResourceNameColumn'];

            $this->hFrameworkResourceName = $this->hDatabase->selectColumn(
                $this->hFrameworkResourceNameColumn,
                $this->hFrameworkResourceTable,
                array(
                    $this->hFrameworkResourcePrimaryKey => $this->hFrameworkResourceKey
                )
            );

            $this->hFrameworkResourceOwner = $this->user->getUserName(
                $this->hDatabase->selectColumn(
                    'hUserId',
                    $this->hFrameworkResourceTable,
                    array(
                        $this->hFrameworkResourcePrimaryKey => $this->hFrameworkResourceKey
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
                case (
                    (bool) $this->hDatabase->selectExists(
                        $this->hFrameworkResourcePrimaryKey,
                        $this->hFrameworkResourceTable,
                        array(
                            $this->hFrameworkResourcePrimaryKey => $this->hFrameworkResourceKey,
                            'hUserId' => (int) $_SESSION['hUserId']
                        )
                    )
                ): {
                    $authentication = true;
                    break;
                }
                default:
                {
                    $this->hFileDocument = $this->getTemplate(
                        'Permission Denied',
                        array(
                            'hFrameworkResourceOwner' => $this->hFrameworkResourceOwner
                        )
                    );

                    $authentication = false;
                }
            }
        }

        if ($authentication)
        {
            $this->save();
            $this->dialogue();
        }
    }

    private function save()
    {
        if (isset($_POST['hUserPermissionsForm']))
        {
            $owner = '';

            if (!empty($_POST['hUserPermissionsOwner']['r']))
            {
                $owner .= 'r';
            }

            if (!empty($_POST['hUserPermissionsOwner']['w']))
            {
                $owner .= 'w';
            }

            $world = '';

            if (!empty($_POST['hUserPermissionsWorld']['r']))
            {
                $world .= 'r';
            }

            if (!empty($_POST['hUserPermissionsWorld']['w']))
            {
                $world .= 'w';
            }

            # hUserGroups[r][]
            # hUsers[r][]

            $userGroups = array();

            $users = array();
            $groups = array();

            if (isset($_POST['hUsers']['r']) && is_array($_POST['hUsers']['r']))
            {
                foreach ($_POST['hUsers']['r'] as $userId)
                {
                    $userGroups[$userId] = 'r';

                    array_push(
                        $users,
                        $this->user->getUserName($userId).'=r'
                    );
                }
            }

            if (isset($_POST['hUsers']['w']) && is_array($_POST['hUsers']['w']))
            {
                foreach ($_POST['hUsers']['w'] as $userId)
                {
                    $userGroups[$userId] = isset($userGroups[$userId])? 'rw' : 'w';

                    array_push(
                        $users,
                        $this->user->getUserName($userId).'=rw'
                    );
                }
            }

            if (isset($_POST['hUserGroups']['r']) && is_array($_POST['hUserGroups']['r']))
            {
                foreach ($_POST['hUserGroups']['r'] as $userId)
                {
                    $userGroups[$userId] = 'r';

                    array_push(
                        $groups,
                        $this->user->getUserName($userId).'=r'
                    );
                }
            }

            if (isset($_POST['hUserGroups']['w']) && is_array($_POST['hUserGroups']['w']))
            {
                foreach ($_POST['hUserGroups']['w'] as $userId)
                {
                    $userGroups[$userId] = isset($userGroups[$userId])? 'rw' : 'w';

                    array_push(
                        $groups,
                        $this->user->getUserName($userId).'=rw'
                    );
                }
            }

            $this->hUserPermissions->setGroups($userGroups);

            $this->hUserPermissions->save(
                $this->hFrameworkResourceId,
                $this->hFrameworkResourceKey,
                $owner,
                $world
            );

            $this->activity(
                $this->hFrameworkResourceTable,
                "Modified Permissions: on '{$this->hFrameworkResourceTable}, {$this->hFrameworkResourceKey}' to ".
                "owner: '{$owner}', world: '{$world}', users: '".implode(', ', $users)."' groups: '".implode(', ', $groups)."'"
            );

            if ($this->hFrameworkResourceTable == 'hDirectories' && isset($_POST['hDirectory']) && !empty($_POST['hDirectory']))
            {
                switch ((int) $_POST['hDirectory'])
                {
                    case 1:
                    {
                        $this->inheritToFiles(
                            $this->getFilesInDirectory($this->hFrameworkResourceKey),
                            $owner,
                            $world,
                            $userGroups
                        );
                        break;
                    }
                    case 2:
                    {
                        $this->inheritToDirectory(
                            $this->getAllSubDirectories(),
                            $owner,
                            $world,
                            $userGroups
                        );

                        break;
                    }
                    case 3:
                    {
                        # All files in directory and sub-directories.
                        $directories = $this->getAllSubDirectories();

                        foreach ($directories as $directoryId)
                        {
                            $this->inheritToFiles(
                                $this->getFilesInDirectory($directoryId),
                                $owner,
                                $world,
                                $userGroups
                            );
                        }

                        break;
                    }
                    case 4:
                    {
                        # All files in directory and sub-directories.
                        $directories = $this->getAllSubDirectories();

                        foreach ($directories as $directoryId)
                        {
                            $this->inheritToFiles(
                                $this->getFilesInDirectory($directoryId),
                                $owner,
                                $world,
                                $userGroups
                            );

                            $this->hUserPermissions->setGroups($userGroups);

                            $this->hUserPermissions->save(
                                'hDirectories',
                                $directoryId,
                                $owner,
                                $world
                            );
                        }

                        break;
                    }
                }
            }

            if ($this->hFrameworkResourceTable == 'hCategories' && isset($_POST['hCategory']) && !empty($_POST['hCategory']))
            {
                $this->inheritToCategories(
                    $this->hFrameworkResourceKey,
                    $userGroups,
                    $owner,
                    $world
                );
            }
        }
    }

    private function inheritToCategories($categoryId, $userGroups, $owner, $world)
    {
        $subCategories = $this->getCategoriesInCategory($categoryId);

        if (count($subCategories))
        {
            foreach ($subCategories as $subCategoryId)
            {
                $this->hUserPermissions->setGroups($userGroups);

                $this->hUserPermissions->save(
                    'hCategories',
                    $subCategoryId,
                    $owner,
                    $world
                );

                if (isset($_POST['hCategory']) && (int) $_POST['hCategory'] == 2 && !empty($subCategoryId))
                {
                    $this->inheritToCategories(
                        $subCategoryId,
                        $userGroups,
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
        $path = $this->getDirectoryPath($this->hFrameworkResourceKey);

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
            $this->hFiles
                ->setGroups($groups)
                ->savePermissions(
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
            $this->hDirectories
                ->setGroups($groups)
                ->savePermissions(
                    $directoryId,
                    $owner,
                    $world
                );
        }
    }

    private function dialogue()
    {
        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');

        $html = '';

        $permissions = $this->hUserPermissions->getPermissions(
            $this->hFrameworkResourceId,
            $this->hFrameworkResourceKey
        );

        $this->hForm->hFormValidate = false;

        $this->hForm
            ->addDiv('hUserPermissions', 'Owner &amp; World')
            ->addFieldset('Instructions', '100%', '100%')
                ->addTableCell(
                    $this->getTemplate(
                        'Copy',
                        array(
                            'hFrameworkResourceName' => $this->hFrameworkResourceName,
                            'hFrameworkResourceOwner' => $this->hFrameworkResourceOwner,
                            'isPosted' => isset($_POST['hUserPermissionsForm'])
                        )
                    )
                )

            ->addFieldset('Owner', '100%', '1px,')
                ->addCheckboxInput(
                    array(
                        'name' => 'hUserPermissionsOwner[r]',
                        'id' => 'hUserPermissionsOwnerRead'
                    ),
                    nil,
                    $this->isChecked($permissions['hUserPermissionsOwner'], 'r')
                )
                ->setLabelCellAttributes(
                    array(
                        'class' => 'hUserPermissionsLabel'
                    )
                )
                ->addInputLabel('hUserPermissionsOwnerRead', 'Read')
                ->addCheckboxInput(
                    array(
                        'name' => 'hUserPermissionsOwner[w]',
                        'id' => 'hUserPermissionsOwnerWrite'
                    ),
                    nil,
                    $this->isChecked($permissions['hUserPermissionsOwner'], 'w')
                )
                ->setLabelCellAttributes(
                    array(
                        'class' => 'hUserPermissionsLabel'
                    )
                )
                ->addInputLabel('hUserPermissionsOwnerWrite', 'Write')

            ->addFieldset('World', '100%', '1px,')
                ->addCheckboxInput(
                    array(
                        'name' => 'hUserPermissionsWorld[r]',
                        'id' => 'hUserPermissionsWorldRead'
                    ),
                    nil,
                    $this->isChecked($permissions['hUserPermissionsWorld'], 'r')
                )
                ->setLabelCellAttributes(
                    array(
                        'class' => 'hUserPermissionsLabel'
                    )
                )
                ->addInputLabel('hUserPermissionsWorldRead', 'Read')
                ->addCheckboxInput(
                    array(
                        'name' => 'hUserPermissionsWorld[w]',
                        'id' => 'hUserPermissionsWorldWrite'
                    ),
                    nil,
                    $this->isChecked($permissions['hUserPermissionsWorld'], 'w')
                )
                ->setLabelCellAttributes(
                    array(
                        'class' => 'hUserPermissionsLabel'
                    )
                )
                ->addInputLabel('hUserPermissionsWorldWrite', 'Write');

        if ($this->hFrameworkResourceTable == 'hDirectories')
        {
            $this->hForm
                ->addFieldset('Folder Inheritance Options', '100%', '100%')
                ->addRadioInput(
                    'hDirectory',
                    nil,
                    array(
                        0 => 'No inheritance',
                        1 => 'Files in this folder inherit its permissions',
                        2 => 'Folders in this folder inherit its permissions',
                        3 => 'Files in this folder and its subfolders inherit its permissions',
                        4 => 'Files and folders in this folder and its subfolders inherit its permissions'
                    )
                );
        }
        else if ($this->hFrameworkResourceTable == 'hCategories')
        {
            $this->hForm
                ->addFieldset('Category Inheritance Options', '100%', '100%')
                ->addRadioInput(
                    'hCategory',
                    nil,
                    array(
                        0 => 'No Inheritance',
                        1 => 'Categories in this category inherit its permissions',
                        2 => 'Categories in this category and its subcategories inherit its permissions'
                    )
                );
        }

        $this->hForm
            ->addDiv('hUserPermissionsUsers', 'Users')
            ->addFieldset('Users', '100%', '100%')
                ->addTableCell(
                    $this->getTemplate(
                        'Users',
                        array(
                            'hUserPermissionsUsersRead'  => $this->getOptions($permissions['hUsers'], 'r'),
                            'hUserPermissionsUsersWrite' => $this->getOptions($permissions['hUsers'], 'w'),
                            'hUserPermissionsUsers' => $this->hUserSelect->get('PermissionsUsers')
                        )
                    )
                )
            ->addDiv('hUserPermissionsGroups', 'Groups')
            ->addFieldset('Groups', '100%', '100%')
                ->addTableCell(
                    $this->getTemplate(
                        'Groups',
                        array(
                            'hUserPermissionsGroupsRead'  => $this->getOptions($permissions['hUserGroups'], 'r'),
                            'hUserPermissionsGroupsWrite' => $this->getOptions($permissions['hUserGroups'], 'w'),
                            'hUserPermissionsGroups'      => $this->hUserSelect->get('PermissionsGroups', true)
                        )
                    )
                )
                ->addHiddenInput('hUserPermissionsForm', 1)
                ->setFormAttribute('action', $this->hFilePath);

        $this->hDialogueFullScreen = true;
        $this->hDialogueAction = $this->hFilePath.'?'.$this->getQueryString($_GET);
        $this->hDialoguePrepend = "<h4 id='hUserPermissionsResourceTitle'>Setup Sharing for: {$this->hFrameworkResourceName}</h4>\n";

        $this->hFileDocument = (
            $this->hDialogue
                ->setForm($this->hForm)
                ->newDialogue('hUserPermissions')
                ->addButtons('Save', 'Cancel')
                ->getDialogue()
        );
    }

    private function isChecked(&$permissions, $value)
    {
        return (isset($permissions) && strstr($permissions, $value)? 1 : 0);
    }

    private function getOptions(&$users, $value)
    {
        $hUsers = array();

        if (isset($users) && is_array($users))
        {
            foreach ($users as $hUserId => $permissions)
            {
                if (strstr($permissions, $value))
                {
                    $hUsers['hUserId'][] = $hUserId;
                    $hUsers['hUserName'][] = $this->user->getUserName($hUserId);
                }
            }
        }

        return $this->getTemplate(
            'Options',
            array(
                'hUsers' => $hUsers
            )
        );
    }
}

?>