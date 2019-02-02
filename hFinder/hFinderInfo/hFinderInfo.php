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

class hFinderInfo extends hPlugin {

    private $hFile;
    private $hFileIcon;
    private $hDialogue;
    private $hUserPermissions;
    private $hFinderLabel;

    public function hConstructor()
    {
        if (!isset($_GET['path']))
        {
            $this->warning('Unable to retrieve information because no path was supplied.', __FILE__, __LINE__);
        }

        $this->hFileCSS = '';
        $this->hFileJavaScript = '';

        $this->getPluginFiles();

        $this->hDialogue = $this->library('hDialogue');

        hString::safelyDecodeURL($_GET['path']);

        $path = $_GET['path'];

        $this->hFile = $this->library('hFile');
        $this->hFile->query($path);
        $this->hFileIcon = $this->library('hFile/hFileIcon');
        $this->hFinderLabel = $this->library('hFinder/hFinderLabel');

        $data = $this->hFile->getMetaData($path);

        if (isset($data['PixelWidth']))
        {
            // Should work on this and get a properly resized thumbnail.
            $filePreviewPath = $_GET['path'];
        }
        else
        {
            $this->hFileIcon = $this->library('hFile/hFileIcon');

            if (!empty($data['hFileIconId']))
            {
                $filePreviewPath = $this->hFileIcon->getIconPathById($data['hFileIconId'], '128x128');
            }
            else
            {
                $filePreviewPath = $this->hFileIcon->getIconPath(
                    $this->hFile->getMIMEType(),
                    $this->hFile->fileName,
                    '128x128'
                );
            }
        }

        $this->hDialogueFullScreen = true;

        $this->hDialogue->newDialogue('hFinderProperties');

        $this->hUserPermissions = $this->library('hUser/hUserPermissions');

        $resource = $this->hUserPermissions->isAuthorized(
            $this->hFile->isDirectory? 'hDirectories' : 'hFiles',
            $this->hFile->isDirectory? $this->hFile->directoryId : $this->hFile->fileId
        );

        $resourceKey = 0;
        $resourceId = 0;

        if ($this->hFile->isCategory)
        {
            $resourceKey = $this->hFile->categoryId;
            $resourceId  = 20;
        }
        else if ($this->hFile->isDirectory)
        {
            $resourceKey = $this->hFile->directoryId;
            $resourceId = 2;
        }
        else
        {
            $resourceKey = $this->hFile->fileId;
            $resourceId = 1;
        }

        $statement = '';

        $hUserPermissionsOwner = '';
        $hUserPermissionsWorld = '';
        $userPermissionsGroups = array();
        $hUserPermissionEmpties = array();

        if (!$this->hFile->isServer && $resource !== false)
        {
            if ($this->hFile->isDirectory)
            {
                $permissions = $this->hDirectories->getPermissions($this->hFile->directoryId);
            }
            else
            {
                $permissions = $this->hFiles->getPermissions($this->hFile->fileId);
            }

            if (!count($permissions))
            {
                $permissions = array(
                    'hUserPermissionsOwner' => '',
                    'hUserPermissionsWorld' => '',
                    'hUserGroups' => array()
                );
            }

            if ($resource['hFrameworkIsResourceOwner'])
            {
                $frameworkIsResourceOwner = true;
            }
            else
            {
                $frameworkIsResourceOwner = false;
            }

            $highestLevel = '';

            $i = 0;

            if (isset($permissions['hUserGroups']) && is_array($permissions['hUserGroups']))
            {
                // If you are in one the groups
                foreach ($permissions['hUserGroups'] as $userGroupId => $groupAccess)
                {
                    if ($this->inGroup($userGroupId) && $highestLevel != 'rw')
                    {
                        if ($groupAccess == 'r' || $groupAccess == 'rw')
                        {
                            $highestLevel = $groupAccess;
                        }
                    }

                    $userPermissionsGroups['hUserName'][]             = $this->user->getUserName($userGroupId);
                    $userPermissionsGroups['hUserId'][]               = (int) $userGroupId;
                    $userPermissionsGroups['isGroup'][]               = true;
                    $userPermissionsGroups['hUserPermissionsGroup'][] = $this->getPermissions($groupAccess);

                    $i++;
                }
            }

            if (isset($permissions['hUsers']) && is_array($permissions['hUsers']))
            {
                foreach ($permissions['hUsers'] as $userId => $userAccess)
                {
                    $userPermissionsGroups['hUserName'][] = $this->user->getUserName($userId);
                    $userPermissionsGroups['hUserId'][]   = (int) $userId;
                    $userPermissionsGroups['isGroup'][]   = false;
                    $userPermissionsGroups['hUserPermissionsGroup'][] = $this->getPermissions($userAccess);

                    $i++;
                }
            }

            $e = (5 - $i);

            if ($e > 0)
            {
                for ($c = 0; $c < $e; $c++)
                {
                    $hUserPermissionEmpties['empty'][] = '';
                }
            }

            if ($frameworkIsResourceOwner && $permissions['hUserPermissionsOwner'] == 'rw' && $highestLevel != 'rw')
            {
                $highestLevel = 'rw';
            }

            if ($highestLevel != 'rw' && (empty($highestLevel) || !strstr($highestLevel, 'r') && !empty($permissions['hUserPermissionsWorld'])))
            {
                $highestLevel = $permissions['hUserPermissionsWorld'];
            }

            $statement = $this->getPermissionsStatement($highestLevel);
        }

        //var_dump($data);

        $this->hFileTitle = $this->hFile->fileName.' Info';

        if (!empty($data['hFileIconId']))
        {
            $icon32x32Path = $this->hFileIcon->getIconPathById($data['hFileIconId'], '32x32');
        }
        else
        {
            $icon32x32Path = $this->hFileIcon->getIconPath($this->hFile->getMIMEType($path), $this->hFile->fileName, '32x32');
        }

        $fileAccessCount = 0;

        if ($this->hFile->fileId)
        {
            $fileAccessCount = $this->hFileStatistics->selectColumn(
                'hFileAccessCount',
                array(
                    'hFileId' => $this->hFile->fileId
                )
            );
        }

        $this->hFileDocument = $this->hDialogue->getDialogue(
            $this->getTemplate(
                'Info',
                array(
                    'isDirectory'                     => $this->hFile->isDirectory,
                    'hFileIconPath'                   => $icon32x32Path,
                    'isFile'                          => $this->hFile->fileId > 0,
                    'hFilePath'                       => $path,
                    'hFileAccessCount'                => $fileAccessCount,
                    'hFileName'                       => $path == '/'? $this->hFinderDiskName($this->hServerHost) : $this->hFile->fileName,
                    'hFileShortLastModifiedDate'      => isset($data['ContentModifiedDate'])? date('M j, Y g:i A', $data['ContentModifiedDate']) : 'Never',
                    'hFileLastModifiedDate'           => isset($data['ContentModifiedDate'])? date('l, M j, Y g:i A', $data['ContentModifiedDate']) : 'Never',
                    'hFileComments'                   => '',
                    'hFinderLabels' => $this->hFinderLabel->get(
                        array(
                            'hFileLabel'       => !empty($data['kMDItemFSLabel']) || !empty($data['FSLabel']),
                            'hFileLabelRed'    => $this->isLabel('red', 6, $data),
                            'hFileLabelOrange' => $this->isLabel('orange', 7, $data),
                            'hFileLabelYellow' => $this->isLabel('yellow', 5, $data),
                            'hFileLabelGreen'  => $this->isLabel('green', 2, $data),
                            'hFileLabelBlue'   => $this->isLabel('blue', 4, $data),
                            'hFileLabelPurple' => $this->isLabel('purple', 3, $data),
                            'hFileLabelGray'   => $this->isLabel('gray', 1, $data)
                        )
                    ),
                    'hFileKind'                       => !empty($data['hDirectoryIsApplication'])? 'Application' : isset($data['Kind'])? $data['Kind'] : 'Unknown',
                    'hFileSize'                       => $this->hFile->isDirectory? '--' : $data['FSSize'],
                    'hFileBasePath'                   => dirname($this->hFile->filePath),
                    'hFileCreatedDate'                => isset($data['ContentCreationDate'])? date('l, M j, Y g:i A', $data['ContentCreationDate']) : 'Error',
                    'hFileColorSpace'                 => isset($data['ColorSpace'])? $data['ColorSpace'] : '',
                    'hFileColorProfile'               => isset($data['ProfileName'])? $data['ProfileName'] : '',
                    'hFileDimensions'                 => isset($data['PixelWidth'])? $data['PixelWidth'].' x '.$data['PixelHeight'] : '',
                    'hFileAlphaChannel'               => isset($data['HasAlphaChannel'])? ($data['HasAlphaChannel'] == '0'? 'No' : 'Yes') : '',
                    'hFileLastAccessedDate'           => !empty($data['ContentAccessedDate'])? date('l, M j, Y g:i A', $data['ContentAccessedDate'])  : 'Never',
                    'hFilePreviewPath'                => $filePreviewPath,
                    'hFinderInfoPermissionsStatement' => $statement,
                    'hFrameworkResourceId'            => (int) $resourceId,
                    'hFrameworkResourceKey'           => (int) $resourceKey,
                    'hFrameworkResourceOwner'         => isset($resource['hFrameworkResourceOwner'])? $this->user->getUserName($resource['hFrameworkResourceOwner']) : 'Unknown',
                    'hFrameworkIsResourceOwner'       => isset($frameworkIsResourceOwner)? $frameworkIsResourceOwner : false,
                    'hUserPermissionsOwner'           => $this->getPermissions($permissions['hUserPermissionsOwner']),
                    'hUserPermissionsWorld'           => $this->getPermissions($permissions['hUserPermissionsWorld']),
                    'hUserPermissionsGroups'          => $userPermissionsGroups,
                    'hUserPermissionEmpties'          => $hUserPermissionEmpties,
                    'canSetPermissions'               => $resource !== false
                )
            ),
            'Properties'
        );
    }

    private function isLabel($color, $colorNumber, &$data)
    {
        return (
            isset($data['kMDItemFSLabel']) && ($data['kMDItemFSLabel'] == $color || (int) $data['kMDItemFSLabel'] == 6) ||
            isset($data['FSLabel']) && (int) $data['FSLabel'] == $colorNumber
        );
    }

    public function getPermissionsStatement(&$access)
    {
        if (isset($access))
        {
            switch ($access)
            {
                case 'r':
                {
                    return 'You can only read';
                }
                case 'w':
                {
                    return 'You can only write (Drop Box)';
                }
                case 'rw':
                {
                    return 'You can read &amp; write';
                }
                default:
                {
                    return 'You have no access';
                }
            }
        }

        return 'Unknown';
    }

    public function getPermissions(&$access)
    {
        if (isset($access))
        {
            switch ($access)
            {
                case 'r':
                {
                    return 'Read only';
                }
                case 'w':
                {
                    return 'Write only (Drop Box)';
                }
                case 'rw':
                {
                    return 'Read &amp; Write';
                }
                default:
                {
                    return 'No Access';
                }
            }
        }

        return 'Unknown';
    }
}

?>