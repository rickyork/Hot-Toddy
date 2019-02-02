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

class hFrameworkImportLibrary extends hShell {

    private $hUserDatabase;
    private $hContactDatabase;
    private $hFileDatabase;
    private $hFile;

    private $hUserId               = array();
    private $hFileId               = array();
    private $hDirectoryId          = array();
    private $hCalendarId           = array();
    private $hContactId            = array();
    private $hContactAddressBookId = array();
    private $hUserPermissionsId    = array();

    private $directoryPaths        = array();
    private $updateFile            = array();
    private $usernames             = array();
    private $directoryOwners       = array();


    public function hConstructor()
    {
        $this->hUserDatabase    = $this->database('hUser');
        $this->hContactDatabase = $this->database('hContact');
        $this->hFileDatabase    = $this->database('hFile');
        $this->hFile            = $this->library('hFile');
    }

    public function fromJSON($import, array $columns = array())
    {
        if (isset($import->hUsers))
        {
            // Import users first
            // Build an array of old hUserIds to new hUserIds
            $this->console("Importing users");

            foreach ($import->hUsers as $row)
            {
                $hUserId = $this->user->getUserId($row->hUserName);

                // $hUserId, $hUserName, $hUserEmail, $hUserPassword, $hUserIsActivated = 0
                // Need to iterate and then update these columns later
                // --> $hUserReferredBy = 0, $hUserRegistrationTracker = 0
                $hUserId = $this->hUsers->save(
                    array(
                        'hUserId'                 => $hUserId,
                        'hUserName'               => $row->hUserName,
                        'hUserEmail'              => $row->hUserEmail,
                        'hUserPassword'           => $row->hUserPassword,
                        'hUserConfirmation'       => $row->hUserConfirmation,
                        'hUserSecurityQuestionId' => $row->hUserSecurityQuestionId,
                        'hUserIsActivated'        => $row->hUserIsActivated
                    )
                );

                $this->hUserId[$row->hUserId] = $hUserId;

                $this->console("User synced \"{$row->hUserName}\" ({$row->hUserId}) was synced to ({$hUserId})");
               // $this->usernames[$row->hUserId] = $row->hUserName;
            }

            $directories = array();
            $directoryIds = array();

            $this->console("\nImporting directories");

            foreach ($import->hDirectories as $row)
            {
                $directoryIds[$row->hDirectoryPath] = $row->hDirectoryId;

                $this->directoryPaths[$row->hDirectoryId] = $row->hDirectoryPath;
                $this->directoryOwners[$row->hDirectoryId] = $this->hUserId[$row->hUserId];

                $exists = $this->hDirectories->selectExists(
                    'hDirectoryId',
                    array(
                        'hDirectoryPath' => $row->hDirectoryPath
                    )
                );

                $directories[$row->hDirectoryPath] = $exists;

                if ($exists)
                {
                    $this->hDirectoryId[$row->hDirectoryId] = $this->getDirectoryId($row->hDirectoryPath);

                    $this->hDirectories->update(
                        array(
                            'hUserId' => $this->hUserId[$row->hUserId],
                            'hDirectoryLastModified' => time()
                        )
                    );
                }
            }

            ksort($directories);

            foreach ($directories as $directory => $bool)
            {
                if (!$bool)
                {
                    $oldDirectoryId = $directoryIds[$directory];

                    $hDirectoryId = $this->hFile->newDirectory(
                        dirname($directory),
                        basename($directory),
                        $this->directoryOwners[$oldDirectoryId]
                    );

                    $this->hDirectoryId[$oldDirectoryId] = $hDirectoryId;

                    $this->console("Directory created: {$directory} {$oldDirectoryId} became {$hDirectoryId}");
                }

            }

            $this->console("\nImporting files");

            foreach ($import->hFiles as $row)
            {
                // See if the file exists by location.
                $path = $this->getConcatenatedPath($this->directoryPaths[$row->hDirectoryId], $row->hFileName);
                $hFileId = $this->getFileIdByFilePath($path);

                if (empty($hFileId))
                {
                    $hFileId = $this->hFiles->insert(
                        array(
                            'hFileId'            => null,
                            'hLanguageId'        => (int) $row->hLanguageId,
                            'hDirectoryId'       => (int) $this->hDirectoryId[$row->hDirectoryId],
                            'hUserId'            => (int) $this->hUserId[$row->hUserId],
                            'hFileParentId'      => 0,
                            'hFileName'          => $row->hFileName,
                            'hPlugin'            => (int) $row->hPlugin,
                            'hFileSortIndex'     => (int) $row->hFileSortIndex,
                            'hFileCreated'       => (int) $row->hFileCreated,
                            'hFileLastModified'  => time()
                        )
                    );

                    $this->console("File: \"{$path}\" did not exist, it was created ({$row->hFileId}) became ({$hFileId})");
                }
                else
                {
                    $this->hFiles->update(
                        array(
                            'hDirectoryId'      => (int) $this->hDirectoryId[$row->hDirectoryId],
                            'hLanguageId'       => $row->hLanguageId,
                            'hUserId'           => $this->hUserId[$row->hUserId],
                            'hFileSortIndex'    => $row->hFileSortIndex,
                            'hFileLastModified' => time()
                        ),
                        $hFileId
                    );

                    $this->console("File: \"{$path}\" did exist, it was synced");
                }

                $this->hFileId[$row->hFileId] = $hFileId;
            }

            foreach ($import->hFiles as $row)
            {
                if (isset($this->hFileId[$row->hFileId]) && isset($this->hFileId[$row->hFileParentId]))
                {
                    $this->hFiles->update(
                        array(
                            'hFileId'       => $this->hFileId[$row->hFileId],
                            'hFileParentId' => $this->hFileId[$row->hFileParentId]
                        ),
                        $this->hFileId[$row->hFileId]
                    );
                }
            }

            $this->console("Deleting file variables from files imported or synced");

            foreach ($import->hFileVariables as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFileVariables->delete(
                        'hFileId',
                        $this->hFileId[$row->hFileId]
                    );
                }
            }

            $this->console("Importing file variables");

            foreach ($import->hFileVariables as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFileVariables->insert(
                        array(
                            'hFileId'       => $this->hFileId[$row->hFileId],
                            'hFileVariable' => $row->hFileVariable,
                            'hFileValue'    => $row->hFileValue
                        )
                    );
                }
            }

            $this->console("Importing file documents");

            foreach ($import->hFileDocuments as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFileDocuments->save(
                        array(
                            'hFileId'         => $this->hFileId[$row->hFileId],
                            'hFileDocumentId' => $this->hFileDocuments->selectColumn(
                                'hFileDocumentId',
                                array(
                                    'hFileId' => $this->hFileId[$row->hFileId]
                                )
                            ),
                            'hFileDescription'=> $row->hFileDescription,
                            'hFileKeywords'   => $row->hFileKeywords,
                            'hFileTitle'      => $row->hFileTitle,
                            'hFileDocument'   => $row->hFileDocument,
                            'hFileDocumentCreated' => $row->hFileDocumentCreated,
                            'hFileDocumentLastModified' => time()
                        )
                    );
                }
            }

            $this->console("Importing file headers");

            foreach ($import->hFileHeaders as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFileHeaders->save(
                        array(
                            'hFileId'         => $this->hFileId[$row->hFileId],
                            'hFileCSS'        => $row->hFileCSS,
                            'hFileJavaScript' => $row->hFileJavaScript
                        )
                    );
                }
            }

            $this->console("Importing file logs");

            foreach ($import->hFileLog as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFileLog->save(
                        array(
                            'hFileLogId'       => $this->hFileLog->selectColumn(
                                'hFileLogId',
                                array(
                                    'hFileId' => $this->hFileId[$row->hFileId]
                                )
                            ),
                            'hFileId'          => $this->hFileId[$row->hFileId],
                            'hUserIP'          => $row->hFileCSS,
                            'hUserISP'         => $row->hUserISP,
                            'hUserAgent'       => $row->hUserAgent,
                            'hFileAccessCount' => $row->hFileAccessCount,
                            'hFileLastAccessed' => $row->hFileLastAccessed
                        )
                    );
                }
            }

            $this->console("Deleting file passwords from files imported or synced");

            foreach ($import->hFilePasswords as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFilePasswords->delete(
                        'hFileId',
                        $this->hFileId[$row->hFileId]
                    );
                }
            }

            $this->console("Importing file passwords");

            foreach ($import->hFilePasswords as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFilePasswords->insert(
                        array(
                            'hFileId'                       => $this->hFileId[$row->hFileId],
                            'hFilePassword'                 => $row->hFilePassword,
                            'hFilePasswordLifetime'         => $row->hFilePasswordLifetime,
                            'hFilePasswordExpirationAction' => $row->hFilePasswordExpirationAction,
                            'hFilePasswordRequired'         => $row->hFilePasswordRequired,
                            'hFilePasswordCreated'          => $row->hFilePasswordCreated,
                            'hFilePasswordExpires'          => $row->hFilePasswordExpires
                        )
                    );
                }
            }

            $this->console("Importing file properties");

            foreach ($import->hFileProperties as $row)
            {
                if (isset($this->hFileId[$row->hFileId]))
                {
                    $this->hFileProperties->save(
                        array(
                            'hFileId' => $this->hFileId[$row->hFileId],
                            'hFileIconId' => $row->hFileIconId,
                            'hFileMIME'   => $row->hFileMIME,
                            'hFileSize'   => $row->hFileSize,
                            'hFileDownload' => $row->hFileDownload,
                            'hFileIsSystem' => $row->hFileIsSystem,
                            'hFileLabel'    => $row->hFileLabel
                        )
                    );
                }
            }

            $this->console("Importing user and group membership");

            foreach ($import->hUserGroups as $row)
            {
                $this->hUserGroups->delete('hUserGroupId', $this->hUserId[$row->hUserGroupId]);
            }

            foreach ($import->hUserGroups as $row)
            {
                $this->hUserGroups->insert(
                    array(
                        'hUserGroupId' => $this->getUserId($row->hUserGroupId),
                        'hUserId'      => $this->getUserId($row->hUserId)
                    )
                );
            }

            $this->console("Importing group properties");

            foreach ($import->hUserGroupProperties as $row)
            {
                if (!empty($row->hUserId) && !empty($this->hUserId[$row->hUserId]))
                {
                    $this->hUserGroupProperties->save(
                        array(
                            'hUserId'                => $this->getUserId($row->hUserId),
                            'hUserGroupOwner'        => $this->getUserId($row->hUserGroupOwner),
                            'hUserIsElevated'        => $this->hUserIsElevated,
                            'hUserGroupPassword'     => $this->hUserGroupPassword,
                            'hUserGroupLoginEnabled' => $this->hUserGroupLoginEnabled
                        )
                    );
                }
            }

            $this->console("Deleting user variables");

            foreach ($import->hUsers as $row)
            {
                $this->hUserVariables->delete(
                    'hUserId',
                    $this->hUserId[$row->hUserId]
                );
            }

            $this->console("Importing user variables");

            foreach ($import->hUserVariables as $row)
            {
                $this->hUserVariables->insert(
                    array(
                        'hUserId'       => $this->hUserId[$row->hUserId],
                        'hUserVariable' => $row->hUserVariable,
                        'hUserValue'    => $row->hUserValue
                    )
                );
            }

            $this->console("Importing user log");

            foreach ($import->hUserLog as $row)
            {
                if (!empty($row->hUserId) && !empty($this->hUserId[$row->hUserId]))
                {
                    $this->hUserLog->save(
                        array(
                            'hUserId'                     => $this->hUserId[$row->hUserId],
                            'hUserLoginCount'             => $row->hUserLoginCount,
                            'hUserFailedLoginCount'       => $row->hUserFailedLoginCount,
                            'hUserCreated'                => $row->hUserCreated,
                            'hUserLastLogin'              => $row->hUserLastLogin,
                            'hUserLastFailedLogin'        => $row->hUserLastFailedLogin,
                            'hUserLastModified'           => $row->hUserLastModified,
                            'hUserLastModifiedBy'         => $this->getUserId($row->hUserLastModifiedBy),
                            'hUserReferredBy'             => $this->getUserId($row->hUserReferredBy),
                            'hUserRegistrationTrackingId' => $row->hUserRegistrationTrackingId,
                            'hFileId'                     => $this->getFileId($row->hFileId)
                        )
                    );
                }
            }

            $this->console("Truncating permissions cache");

            $this->hUserPermissionsCache->delete();

            $this->console("Importing user permissions");

            foreach ($import->hUserPermissions as $row)
            {
                switch ($row->hFrameworkResourceId)
                {
                    case 1:
                    {
                        if (isset($this->hFileId[$row->hFrameworkResourceKey]))
                        {
                            $hFrameworkResourceKey = $this->hFileId[$row->hFrameworkResourceKey];
                        }

                        break;
                    }
                    case 2:
                    {
                        $hFrameworkResourceKey = $this->hDirectoryId[$row->hFrameworkResourceKey];
                        break;
                    }
                    case 5:
                    {
                        $hFrameworkResourceKey = $this->hContactId[$row->hFrameworkResourceKey];
                        break;
                    }
                    case 6:
                    {
                        $hFrameworkResourceKey = $this->hCalendarId[$row->hFrameworkResourceKey];
                        break;
                    }
                    case 7:
                    {
                        $hFrameworkResourceKey = $this->hCalendarAddressBookId[$row->hFrameworkResourceKey];
                        break;
                    }
                }

                if (empty($hFrameworkResourceKey))
                {
                    continue;
                }

                $hUserPermissionsId = $this->hUserPermissions->save(
                    array(
                        'hUserPermissionsId' => $this->hUserPermissions->selectColumn(
                            'hUserPermissionsId',
                            array(
                                'hFrameworkResourceId'  => $row->hFrameworkResourceId,
                                'hFrameworkResourceKey' => $hFrameworkResourceKey
                            )
                        ),
                        'hFrameworkResourceId'  => $row->hFrameworkResourceId,
                        'hFrameworkResourceKey' => $hFrameworkResourceKey,
                        'hUserPermissionsOwner' => $row->hUserPermissionsOwner,
                        'hUserPermissionsWorld' => $row->hUserPermissionsWorld
                    )
                );

                $this->hUserPermissionsId[$row->hUserPermissionsId] = $hUserPermissionsId;
            }

            $this->console("Deleting previously set group permissions");

            foreach ($import->hUserPermissionsGroups as $row)
            {
                $this->hUserPermissionsGroups->delete(
                    'hUserPermissionsId',
                    $this->hUserPermissionsId[$row->hUserPermissionsId]
                );
            }

            $this->console("Importing group permissions");

            foreach ($import->hUserPermissionsGroups as $row)
            {
                $this->hUserPermissionsGroups->insert(
                    array(
                        'hUserPermissionsId'    => $this->hUserPermissionsId[$row->hUserPermissionsId],
                        'hUserGroupId'          => $this->hUserId[$row->hUserId],
                        'hUserPermissionsGroup' => $row->hUserPermissionsGroup
                    )
                );
            }

            $this->console("Importing contacts");

            foreach ($import->hContacts as $row)
            {
                $hContactId = $this->hContacts->save(
                    array(
                        'hContactId' => $this->hContacts->selectColumn(
                            'hContactId',
                            array(
                                'hContactAddressBookId' => $row->hContactAddressBookId,
                                'hUserId' => $this->hUserId[$row->hUserId]
                            )
                        ),
                        'hUserId'             => $this->hUserId[$row->hUserId],
                        'hContactFirstName'   => $row->hContactFirstName,
                        'hContactLastName'    => $row->hContactLastName,
                        'hContactDisplayName' => $row->hContactFirstName.' '.$row->hContactLastName,
                        'hContactNickName'    => $row->hContactNickName,
                        'hContactWebsite'     => $row->hContactWebsite,
                        'hContactCompany'     => $row->hContactCompany,
                        'hContactTitle'       => $row->hContactTitle,
                        'hContactDepartment'  => $row->hContactDepartment,
                        'hContactGender'      => $row->hContactGender,
                        'hContactDateOfBirth' => $row->hContactDateOfBirth,
                        'hContactCreated'     => $row->hContactCreated,
                        'hContactLastModified'=> time()
                    )
                );

                $this->hContactId[$row->hContactId] = $hContactId;

                $this->hContactAddresses->delete(
                    'hContactId',
                    $hContactId
                );

                $this->hContactEmailAddresses->delete(
                    'hContactId',
                    $hContactId
                );

                $this->hContactPhoneNumbers->delete(
                    'hContactId',
                    $hContactId
                );

                $this->hContactInternetAccounts->delete(
                    'hContactId',
                    $hContactId
                );

                $this->hContactVariables->delete(
                    'hContactId',
                    $hContactId
                );
            }

            $this->console("Importing contact email addresses");

            foreach ($import->hContactEmailAddresses as $row)
            {
                if (array_key_exists($row->hContactId, $this->hContactId))
                {
                    $this->hContactEmailAddresses->insert(
                        array(
                            'hContactId' => $this->hContactId[$row->hContactId],
                            'hContactEmailAddressId' => nil,
                            'hContactFieldId' => $row->hContactFieldId,
                            'hContactEmailAddress' => $row->hContactEmailAddress
                        )
                    );
                }
            }

            $this->console("Importing contact phone numbers");

            foreach ($import->hContactPhoneNumbers as $row)
            {
                if (array_key_exists($row->hContactId, $this->hContactId))
                {
                    $this->hContactPhoneNumbers->insert(
                        array(
                            'hContactId' => $this->hContactId[$row->hContactId],
                            'hContactPhoneNumberId' => nil,
                            'hContactFieldId' => $row->hContactFieldId,
                            'hContactPhoneNumber' => $row->hContactPhoneNumber
                        )
                    );
                }
            }

            $this->console("Importing contact internet accounts");

            foreach ($import->hContactInternetAccounts as $row)
            {
                if (array_key_exists($row->hContactId, $this->hContactId))
                {
                    $this->hContactInternetAccounts->insert(
                        array(
                            'hContactId' => $this->hContactId[$row->hContactId],
                            'hContactInternetAccountId' => nil,
                            'hContactFieldId' => $row->hContactFieldId,
                            'hContactInternetAccount' => $row->hContactInternetAccount
                        )
                    );
                }
            }

            $this->console("Importing contact addresses");

            foreach ($import->hContactAddresses as $row)
            {
                if (array_key_exists($row->hContactId, $this->hContactId))
                {
                    $this->hContactAddresses->insert(
                        array(
                            'hContactId'                 => $this->hContactId[$row->hContactId],
                            'hContactAddressId'          => nil,
                            'hContactFieldId'            => $row->hContactFieldId,
                            'hContactAddressStreet'      => $row->hContactAddressStreet,
                            'hContactAddressCity'        => $row->hContactAddressCity,
                            'hLocationStateId'           => $row->hLocationStateId,
                            'hContactAddressPostalCode'  => $row->hContactAddressPostalCode,
                            'hLocationCountryId'         => $row->hLocationCountryId,
                            'hContactAddressLatitude'    => $row->hContactAddressLatitude,
                            'hContactAddressLongitude'   => $row->hContactAddressLongitude,
                            'hContactAddressDefault'     => $row->hContactAddressDefault
                        )
                    );
                }
            }

            $this->console("Importing contact variables");

            foreach ($import->hContactVariables as $row)
            {
                if (array_key_exists($row->hContactId, $this->hContactId))
                {
                    $this->hContactVariables->insert(
                        array(
                            'hContactId'        => $this->hContactId[$row->hContactId],
                            'hContactVariables' => $row->hContactVariable,
                            'hContactValue'     => $row->hContactValue
                        )
                    );
                }
            }

            $this->console("Importing calendars");

            foreach ($import->hCalendars as $row)
            {
                $exists = $this->hCalendars->selectExists(
                    'hCalendarId',
                    array(
                        'hCalendarName' => $row->hCalendarName
                    )
                );

                if (!$exists)
                {
                    $this->hCalendars->insert(
                        array(
                            'hCalendarId' => nil,
                            'hUserId' => $this->hUserId[$row->hUserId],
                            'hCalendarName' => $row->hCalendarName
                        )
                    );
                }
            }
        }
    }

    private function getUserId($hUserId)
    {
        if (!empty($hUserId) && !empty($this->hUserId[$hUserId]))
        {
            return $this->hUserId[$hUserId];
        }

        return 0;
    }

    private function getFileId($hFileId)
    {
        if (!empty($hFileId) && !empty($this->hFileId[$hFileId]))
        {
            return $this->hFileId[$hFileId];
        }

        return 0;
    }
}

?>