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
# <h1>File Database API</h1>
# <p>
#   The file database API provides methods for accessing a file's meta data,
#   and methods for saving a file's meta data.
# </p>
# @end

class hFileDatabase extends hPlugin {

    public $fileId = 0;
    public $filePath = '';
    public $directory;
    public $directoryPath;

    private $hUserPermissions;
    private $hListDatabase;
    private $hCalendarDatabase;
    private $hCategoryDatabase;

    private $file;
    private $document;
    private $properties;
    private $headers;
    private $aliases = array();
    private $domains = array();
    private $variables = array();

    private $tables = array(
        'hFiles',
        'hFileDocuments',
        'hFileAliases',
        'hFileDomains',
        'hFileHeaders',
        'hFilePasswords',
        'hFileProperties',
        'hFileVariables',
        'hCalendarFiles',
        'hLists'
    );

//    private $variables = array(
//        'hFileSubTitle',
//        'hFileHeadingTitle',
//        'hFileBreadcrumbTitle',
//        'hFileMenuTitle',
//        'hFileSideboxTitle',
//        'hFileTooltipTitle',
//        'hGoogleSitemapPriority',
//        'hGoogleSitemapChangeFrequency'
//    );

    private $columns = array();
    private $hFile = array();
    private $data = array();
    private $setUnset = array();

    public function &getColumns()
    {
        // Retreive the columns for each table.
        foreach ($this->tables as $table)
        {
            $columns = $this->$table->getColumnNames($table);

            $this->columns[$table] = array();

            foreach ($columns as $column)
            {
                $this->columns[$table][$column] = '';
            }
        }

        return $this;
    }

    public function &setFileId($fileId)
    {
        $this->fileId = (int) $fileId;
        $this->filePath = $this->getFilePathByFileId($fileId);
        $this->directory = dirname($this->filePath); // don't use this!
        $this->directoryPath = dirname($this->filePath);

        return $this;
    }

    public function get($key)
    {
        switch ($key)
        {
            case 'hFileId':
            {
                return (int) $this->fileId;
            }
            case 'hFilePath':
            {
                return $this->filePath;
            }
            case 'hDirectoryPath':
            {
                return $this->directoryPath;
            }
        }

        if (!count($this->document))
        {
            $this->getAll();
        }

        if ($key == 'hLanguageId' && empty($this->fileId))
        {
            return $this->hLanguageId(1);
        }

        if (!count($this->columns))
        {
            $this->getColumns();
        }

        // What table is the $key in?
        foreach ($this->tables as $table)
        {
            if (array_key_exists($key, $this->columns[$table]))
            {
                switch ($table)
                {
                    case 'hFiles':
                    {
                        return $this->file[$key];
                    }
                    case 'hFileDocuments':
                    {
                        return $this->document[$key];
                    }
                    case 'hFileHeaders':
                    {
                        return $this->headers[$key];
                    }
                    case 'hFileProperties':
                    {
                        return $this->properties[$key];
                    }
                }
            }
        }

        if (array_key_exists($key, $this->variables))
        {
            return $this->variables[$key];
        }

        return '';
    }

    public function variableExists($variable)
    {
        $this->getVariables();

        return array_key_exists(
            $variable,
            $this->variables
        );
    }

    public function &getAll()
    {
        $methods = array(
            'getAliases',
            'getDomains',
            'getVariables',
            'getFile',
            'getDocument',
            'getProperties',
            'getHeaders'
        );

        foreach ($methods as $method)
        {
            $this->$method();
        }

        return $this;
    }

    public function getAliases()
    {
        $this->aliases = $this->hFileAliases->selectResults(
            'hFileAliasPath',
            array(
                'hFileId' => (int) $this->fileId
            )
        );

        return $this->aliases;
    }

    public function getDomains()
    {
        $this->domains = $this->hFileDomains->selectResults(
            'hFileDomain',
            array(
                'hFileId' => (int) $this->fileId
            )
        );

        return $this->domains;
    }

    public function getVariables($excludeVariables = array())
    {
        $this->variables = $this->hFileVariables->selectColumnsAsKeyValue(
            array(
                'hFileVariable',
                'hFileValue'
            ),
            array(
                'hFileId' => (int) $this->fileId
            )
        );

        if (count($excludeVariables))
        {
            $rtn = array();

            foreach ($this->variables as $key => $value)
            {
                if (!in_array($key, $excludeVariables))
                {
                    $rtn[$key] = $value;
                }
            }

            return $rtn;
        }
        else
        {
            return $this->variables;
        }
    }

    public function getFile()
    {
        return $this->queryTable('file', 'hFiles');
    }

    public function getDocument()
    {
        return $this->queryTable('document', 'hFileDocuments');
    }

    public function getProperties()
    {
        return $this->queryTable('properties', 'hFileProperties');
    }

    public function getHeaders()
    {
        return $this->queryTable('headers', 'hFileHeaders');
    }

    private function queryTable($variable, $table)
    {
        if (!count($this->columns))
        {
            $this->getColumns();
        }

        if (empty($this->$variable))
        {
            $data = $this->$table->selectAssociative(
                '*',
                array(
                    'hFileId' => (int) $this->fileId
                )
            );

            if (count($data))
            {
                $this->$variable = $data;
                return $this->$variable;
            }
            else
            {
                return $this->columns[$table];
            }
        }
        else
        {
            return $this->$variable;
        }
    }

    private function &getValue($key, $default = '')
    {
        if (isset($this->hFile[$key]))
        {
            $this->data[$key] = $this->hFile[$key];
            unset($this->hFile[$key]);
        }
        else if ($this->setUnset)
        {
            $this->data[$key] = $default;
        }

        return $this;
    }

    private function &getRequiredValue($key)
    {
        if (!empty($this->hFile[$key]))
        {
            $this->data[$key] = $this->hFile[$key];
            unset($this->hFile[$key]);
        }
        else
        {
            $this->warning('Required value, '.$key.', not provided.', __FILE__, __LINE__);
        }

        return $this;
    }

    private function &resetData($fileId)
    {
        $this->data = array();
        $this->data['hFileId'] = (int) $fileId;

        return $this;
    }

    public function save($file, $setUnset = true)
    {
        if (isset($file['hFilePath']))
        {
            $file['hFileName'] = basename($file['hFilePath']);
            $file['hDirectoryPath'] = dirname($file['hFilePath']);

            unset($file['hFilePath']);
        }

        // If the hFileId isn't empty, and the hFileName and hDirectoryId
        // aren't provided, fill them in automatically.
        if (!isset($file['hFileName']) && !empty($file['hFileId']))
        {
            $file['hFileName'] = $this->hFiles->selectColumn(
                'hFileName',
                (int) $file['hFileId']
            );
        }

        if (!empty($file['hFileName']))
        {
            $file['hFileName'] = str_replace("/", '', $file['hFileName']);
        }

        if (!isset($file['hDirectoryId']) && !empty($file['hFileId']))
        {
            $file['hDirectoryId'] = $this->hFiles->selectColumn(
                'hDirectoryId',
                (int) $file['hFileId']
            );
        }

        if (isset($file['hDirectoryPath']))
        {
            $file['hDirectoryId'] = $this->getDirectoryId($file['hDirectoryPath']);
            unset($file['hDirectoryPath']);
        }

        # Make sure the same file name isn't created twice.
        $existingFileId = (int) $this->hFiles->selectColumn(
            'hFileId',
            array(
                'hFileName' => $file['hFileName'],
                'hDirectoryId' => (int) $file['hDirectoryId']
            )
        );

        if (!empty($existingFileId))
        {
            $file['hFileId'] = $existingFileId;
        }

        $extension = $this->getExtension($file['hFileName']);

        $this->data['hFileId'] = isset($file['hFileId'])? (int) $file['hFileId'] : 0;

        $this->hFile = $file;

        $this->setUnset = $setUnset;

        $owner = false;

        if (isset($this->hFile['hFileOwner']))
        {
            $this->hFile['hUserId'] = $this->user->getUserId($this->hFile['hFileOwner']);
            unset($this->hFile['hFileOwner']);
            $owner = true;
        }

        if (isset($this->hFile['hLanguageId']))
        {
            $this->getValue('hLanguageId', 1);
        }

        $directoryId = $this->hFile['hDirectoryId'];

        $this->getRequiredValue('hDirectoryId');

        if (empty($this->data['hFileId']) && !$owner || !empty($this->hFile['hUserId']))
        {
            $this->getValue(
                'hUserId',
                isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 0
            );
        }

        if (isset($this->hFile['hFileParentId']))
        {
            $this->getValue('hFileParentId', 0);
        }

        $this->getRequiredValue('hFileName');

        if (isset($this->hFile['hPlugin']))
        {
            $this->getValue('hPlugin', '');
        }

        if (isset($this->hFile['hFileSortIndex']))
        {
            $this->getValue('hFileSortIndex', 0);
        }

        if (isset($this->hFile['hFileLastModified']))
        {
            $this->getValue('hFileLastModified');
        }
        else
        {
            $this->data['hFileLastModified'] = time();
        }

        $this->data['hFileLastModifiedBy'] = isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 1;

        if (isset($this->hFile['hFileCreated']))
        {
            $this->getValue('hFileCreated');
        }
        else if (empty($this->data['hFileId']))
        {
            $this->data['hFileCreated'] = time();
        }

        $fileId = $this->hFiles->save($this->data);

        $this->hFiles->modifyResource();

        if (empty($fileId))
        {
            $this->warning('File save failed, database save returned empty fileId.', __FILE__, __LINE__);
        }

        $filePath = $this->getFilePathByFileId($fileId);

        $this->hFiles->activity(
            (empty($this->data['hFileId'])? 'Created' : 'Modified').' File: '.$filePath
        );

        $this->resetData($fileId);

        if (isset($this->hFile['hFileIconId']) || isset($this->hFile['hFileMIME']) || isset($this->hFile['hFileDownload']) || isset($this->hFile['hFileIsSystem']) || isset($this->hFile['hFileLabel']))
        {
            // Increment the last modified time,
            // update the file size
            $delete = false;

            if (empty($this->hFile['hFileIconId']) && empty($this->hFile['hFileMIME']) && empty($this->hFile['hFileDownload']) && empty($this->hFile['hFileIsSystem']) && empty($this->hFile['hFileLabel']))
            {
                $this->hFileProperties->delete('hFileId' , $fileId);
                $delete = true;
            }

            $this
                ->getValue('hFileIconId', 0)
                ->getValue('hFileMIME', '')
                ->getValue('hFileSize', 0)
                ->getValue('hFileDownload', 0)
                ->getValue('hFileIsSystem', 0)
                ->getValue('hFileSystemPath', '')
                ->getValue('hFileMD5Checksum', '')
                ->getValue('hFileLabel', '');

            if (!$delete)
            {
                $this->hFileProperties->save($this->data);
            }

            $this->resetData($fileId);
        }

        $this->data['hFileDocumentId'] = $this->hFileDocuments->selectColumn(
            'hFileDocumentId',
            array(
                'hFileId' => (int) $fileId
            )
        );

        if (isset($this->hFile['hFileDocument']))
        {
            $this->hFile['hFileDocument'] = $this->parseDocument($this->hFile['hFileDocument']);
        }

        $this
            ->getValue('hFileDescription')
            ->getValue('hFileKeywords')
            ->getValue('hFileTitle')
            ->getValue('hFileDocument')
            ->getValue('hFileDocumentLastModified');

        if (isset($this->hFile['hFileDocumentCreated']))
        {
            $this->getValue('hFileDocumentCreated');
        }
        else if (empty($this->data['hFileDocumentId']))
        {
            $this->data['hFileDocumentCreated'] = time();
        }

        $this->hFileDocuments->save($this->data);
        $this->resetData($fileId);

        if (isset($file['hFileCSS']) || isset($file['hFileJavaScript']))
        {
            $delete = false;

            if (empty($file['hFileCSS']) && empty($file['hFileJavaScript']))
            {
                $this->hFileHeaders->delete('hFileId', $fileId);
                $delete = true;
            }

            $this->getValue('hFileCSS')
                 ->getValue('hFileJavaScript');

            if (!$delete)
            {
                $this->hFileHeaders->save($this->data);
            }

            $this->resetData($fileId);
        }

        if (isset($file['hFileDomains']) && is_array($file['hFileDomains']))
        {
            foreach ($file['hFileDomains'] as $fileDomain)
            {
                $this->hFileDomains->delete('hFileDomain', $fileDomain);

                $this->hFileDomains->insert(
                    array(
                        'hFileDomainId' => 0,
                        'hFileDomain' => $fileDomain,
                        $fileId
                    )
                );
            }

            unset($file['hFileDomains']);
        }

        $this->hListDatabase = $this->database('hList');

        if (isset($this->hFile['hLists']))
        {
            $this->hListDatabase->deleteFileLists(
                $fileId,
                $this->hFile['hLists']
            );

            unset($this->hFile['hLists']);
        }

        if (isset($this->hFile['hListFiles']) && is_array($this->hFile['hListFiles']))
        {
            $this->hListDatabase->saveFileLists(
                $fileId,
                $this->hFile['hListFiles']
            );

            unset($this->hFile['hListFiles']);
        }

        unset(
            $this->hFile['hFileId'],
            $this->hFile['hFileMIME']
        );

        if (isset($this->hFile['hFileCalendarId']) && !empty($this->hFile['hFileCalendarId']) && !is_array($this->hFile['hFileCalendarId']))
        {
            $this->hFile['hFileCalendarId'] = array(
                $this->hFile['hFileCalendarId']
            );
        }

        if (isset($this->hFile['hFileCalendarId']) && is_array($this->hFile['hFileCalendarId']))
        {
            $this->hCalendarDatabase = $this->database('hCalendar');

            $usedCalendars = array();

            foreach ($this->hFile['hFileCalendarId'] as $calendarId)
            {
                array_push($usedCalendars, (int) $calendarId);

                $this->hCalendarDatabase->save(
                    array(
                        'hCalendarFileId'     => $this->hCalendarDatabase->getCalendarFileId($calendarId, $fileId),
                        'hCalendarId'         => $calendarId,
                        'hCalendarCategoryId' => $this->hFile['hFileCalendarCategoryId'],
                        'hFileId'             => $fileId,
                        'hCalendarBegin'      => $this->getFileItem($this->hFile['hFileCalendarBegin']),
                        'hCalendarEnd'        => $this->getFileItem($this->hFile['hFileCalendarEnd']),
                        'hCalendarRange'      => $this->getFileItem($this->hFile['hFileCalendarRange']),
                        'hCalendarDate'       => $this->hFile['hFileCalendarDate'],
                        'hCalendarBeginTime'  => $this->getFileItem($this->hFile['hFileCalendarBeginTime']),
                        'hCalendarEndTime'    => $this->getFileItem($this->hFile['hFileCalendarEndTime']),
                        'hCalendarAllDay'     => $this->getFileItem($this->hFile['hFileCalendarAllDay'])
                    )
                );
            }

            // Set variables for blog posts...
            $this->hFile['hCalendarId'] = $calendarId;
            $this->hFile['hCalendarCategoryId'] = $this->hFile['hFileCalendarCategoryId'];

            $calendars = $this->hCalendarDatabase->getCalendarIds();

            foreach ($calendars as $calendarId)
            {
                if (!in_array((int) $calendarId, $usedCalendars))
                {
                    $this->hCalendarDatabase->delete($calendarId, 0, $fileId);
                }
            }

            if ($this->hFile['hCalendarCategoryId'] == 3)
            {
                $this->hFile['hCalendarBlogPost'] = 1;
            }
        }

        unset(
            $this->hFile['hFileCalendarId'],
            $this->hFile['hFileCalendarCategoryId'],
            $this->hFile['hFileCalendarDate'],
            $this->hFile['hFileCalendarBegin'],
            $this->hFile['hFileCalendarEnd'],
            $this->hFile['hFileCalendarRange'],
            $this->hFile['hFileCalendarBeginTime'],
            $this->hFile['hFileCalendarEndTime'],
            $this->hFile['hFileCalendarAllDay']
        );

        if (isset($this->hFile['hContactId']))
        {
            $exists = $this->hContactFiles->selectExists(
                'hContactId',
                array(
                    'hContactId' => (int) $this->hFile['hContactId'],
                    'hFileId' => (int) $fileId
                )
            );

            if ($exists)
            {
                $this->hContactFiles->update(
                    array(
                        'hContactFileCategoryId' => isset($this->hFile['hContactFileCategoryId'])? (int) $this->hFile['hContactFileCategoryId'] : 1,
                        'hContactIsProfilePhoto' => isset($this->hFile['hContactIsProfilePhoto'])? (int) $this->hFile['hContactIsProfilePhoto'] : 1,
                        'hContactIsDefaultProfilePhoto' => isset($this->hFile['hContactIsDefaultProfilePhoto'])? (int) $this->hFile['hContactIsDefaultProfilePhoto'] : 1
                    ),
                    array(
                        'hContactId' => (int) $this->hFile['hContactId'],
                        'hFileId' => (int) $fileId
                    )
                );
            }
            else
            {
                $this->hContactFiles->insert(
                    array(
                        'hContactId' => (int) $this->hFile['hContactId'],
                        'hFileId' => (int) $fileId,
                        'hContactFileCategoryId' => isset($this->hFile['hContactFileCategoryId'])? (int) $this->hFile['hContactFileCategoryId'] : 1,
                        'hContactIsProfilePhoto' => isset($this->hFile['hContactIsProfilePhoto'])? (int) $this->hFile['hContactIsProfilePhoto'] : 1,
                        'hContactIsDefaultProfilePhoto' => isset($this->hFile['hContactIsDefaultProfilePhoto'])? (int) $this->hFile['hContactIsDefaultProfilePhoto'] : 1
                    )
                );
            }

            unset(
                $this->hFile['hContactId'],
                $this->hFile['hContactFileCategoryId'],
                $this->hFile['hContactIsProfilePhoto'],
                $this->hFile['hContactIsDefaultProfilePhoto']
            );
        }

        $this->saveAliases('hFileAliases', $fileId);
        $this->saveAliases('hFileAlias', $fileId);

        if (isset($this->hFile['hFileAliasPath']))
        {
            $this->saveAlias(
                isset($this->hFile['hFileAliasId'])? $this->hFile['hFileAliasId'] : 0,
                $fileId,
                $this->hFile['hFileAliasPath'],
                isset($this->hFile['hFileAliasRedirect'])? (int) $this->hFile['hFileAliasRedirect'] : 0,
                isset($this->hFile['hFileAliasExpires'])? (int) $this->hFile['hFileAliasExpires'] : 0
            );
        }

        unset(
            $this->hFile['hFileAlias'],
            $this->hFile['hFileAliases'],
            $this->hFile['hFileAliasPath'],
            $this->hFile['hFileAliasId'],
            $this->hFile['hFileAliasRedirect'],
            $this->hFile['hFileAliasExpires']
        );

        if (isset($this->hFile['hCategories']))
        {
            $this->hCategoryDatabase = $this->database('hCategory');
            $this->hCategoryDatabase->addFileToCategories($fileId, $this->hFile['hCategories']);
            unset($this->hFile['hCategories']);
        }

        if (isset($this->hFile['hUserPermissions']))
        {
            $setWorld = false;

            if (isset($this->hFile['hUserPermissionsWorld']) && !isset($this->hFile['hUserPermissionsOwner']) && !isset($this->hFile['hUserPermissionsGroups']))
            {
                $setWorld = true;
            }

            $owner = 'rw';

            if (isset($this->hFile['hUserPermissionsOwner']))
            {
                $owner = $this->hFile['hUserPermissionsOwner'];
            }

            $world = '';

            if (isset($this->hFile['hUserPermissionsWorld']))
            {
                $world = $this->hFile['hUserPermissionsWorld'];
            }

            $inherit = false;

            if (isset($this->hFile['hUserPermissionsGroups']))
            {
                if (is_array($this->hFile['hUserPermissionsGroups']) && count($this->hFile['hUserPermissionsGroups']))
                {
                    foreach ($this->hFile['hUserPermissionsGroups'] as $group => $permission)
                    {
                        $this->console("Setting Permissions");
                        $this->console($group.' '.$permission);
                        $this->hFiles->setGroup($group, $permission);
                    }
                }
            }
            else if (isset($this->hFile['hUserPermissionsInherit']))
            {
                $this->hDirectories->inheritPermissionsFrom($directoryId);
                $this->hFiles->savePermissions($fileId);

                $inherit = true;
            }

            if (!$setWorld && !$inherit)
            {
                $this->hFiles->savePermissions($fileId, $owner, $world);
            }
            else if (!$inherit || $inherit && !empty($world))
            {
                $this->hFiles->saveWorldPermissions($fileId, $world);
            }
        }

        unset(
            $this->hFile['hUserPermissions'],
            $this->hFile['hUserPermissionsOwner'],
            $this->hFile['hUserPermissionsWorld'],
            $this->hFile['hUserPermissionsGroups'],
            $this->hFile['hUserPermissionsInherit'],
            $this->hFile['hFileReplace'],
            $this->hFile['hFileTempPath']
        );

        if (!empty($this->hFile['hFileVariables']))
        {
            unset($this->hFile['hFileVariables']);

            $this->hFileVariables->delete('hFileId', (int) $fileId);

            // Save the variables...
            foreach ($this->hFile as $key => $value)
            {
                $this->$key($value, $fileId, true);
            }
        }

        $this->hFile = array();
        $this->data = array();

        return $fileId;
    }

    private function getFileItem(&$item, $default = 0)
    {
        return isset($item)? $item : $default;
    }

    public function parseDocument($document)
    {
        $document = hString::decodeHTML($document);

        $document = preg_replace_callback(
            "/(href|action|src|background)\=(\'|\")(.*)(\'|\")/iU",
            array(
                $this,
                'attributePathCallback'
            ),
            $document
        );

        return hString::encodeHTML($document);
    }

    public function attributePathCallback($matches)
    {
        # $matches[1] // Attribute
        # $matches[2] // Left Quote
        # $matches[3] // Path
        # $matches[4] // Right Quote
        $attribute = $matches[1];
        $quote     = $matches[2];
        $path      = $matches[3];

        if (substr($path, 0, strlen('{hFileId:')) == '{hFileId:' || substr($path, 0, strlen('{$hFileId:')) == '{$hFileId:')
        {
            return $attribute.'='.$quote.$path.$quote;
        }

        if ($this->beginsPath($path, 'http://'.$this->hServerHost))
        {
            $path = $this->getEndOfPath($path, 'http://'.$this->hServerHost);
        }

        if ($this->beginsPath($path, 'http://'.$this->hFrameworkSite))
        {
            $path = $this->getEndOfPath($path, 'http://'.$this->hFrameworkSite);
        }

        if ($this->beginsPath($path, 'https://'.$this->hServerHost))
        {
            $path = $this->getEndOfPath($path, 'https://'.$this->hServerHost);
        }

        if ($this->beginsPath($path, 'https://'.$this->hFrameworkSite))
        {
            $path = $this->getEndOfPath($path, 'https://'.$this->hFrameworkSite);
        }

        $url = parse_url($path);

        if (isset($url['query']))
        {
            $url['query'] = preg_replace('/hFileLastModified\=(\d+)/', '', $url['query']);
        }

        if (empty($url['host'])) // Internally pointing link
        {
            if (!empty($url['path']))
            {
                //$uri['path'] = hString::safelyDecodeURL($url['path']);

                $url['path'] = urldecode($url['path']);
                $url['path'] = hString::safelyDecodeURLPath($url['path']);

                $url['path'] = mb_convert_encoding($url['path'], 'UTF-8', 'HTML-ENTITIES');
                $url['path'] = mb_convert_encoding($url['path'], 'UTF-8', 'HTML-ENTITIES');
                $url['path'] = htmlspecialchars($url['path'], ENT_QUOTES);
                $url['path'] = mb_convert_encoding($url['path'], 'HTML-ENTITIES', 'UTF-8');

                $fileId = $this->getFileIdByFilePath(trim($url['path']));

                if ($fileId <= 0)
                {
                    $fileId = $this->getFileIdByFilePath('/'.$this->hFrameworkSite.$url['path']);
                }

                if ($fileId > 0)
                {
                    $path = '{hFileId:'.$fileId.'}';

                    if (!empty($url['query']))
                    {
                        $path .= '?'.$url['query'];
                    }

                    if (!empty($url['fragment']))
                    {
                        $path .= '#'.$url['fragment'];
                    }
                }
                else
                {
                    $this->verbose("Unable to find File Id for path '{$url['path']}' derived from '{$path}'.", __FILE__, __LINE__);
                }
            }
        }

        return $attribute.'='.$quote.$path.$quote;
    }

    private function saveAliases($index, $fileId)
    {
        if (!empty($fileId))
        {
            if (isset($this->hFile[$index]) && is_array($this->hFile[$index]))
            {
                if (isset($this->hFile[$index]['hFileAliasPath']))
                {
                    $this->saveAlias(
                        isset($this->hFile[$index]['hFileAliasId'])? $this->hFile['hFileAliases']['hFileAliasId'] : 0,
                        $fileId,
                        $this->hFile[$index]['hFileAliasPath'],
                        isset($this->hFile[$index]['hFileAliasRedirect'])? (int) $this->hFile['hFileAliases']['hFileAliasRedirect'] : 0,
                        isset($this->hFile[$index]['hFileAliasExpires'])? (int) $this->hFile['hFileAliases']['hFileAliasExpires'] : 0,
                        isset($this->hFile[$index]['hFileAliasDestination'])? $this->hFile[$index]['hFileAliasDestination'] : nil
                    );
                }
                else
                {
                    foreach ($this->hFile[$index] as $alias)
                    {
                        $this->saveAlias(
                            isset($alias['hFileAliasId'])? $alias['hFileAliasId'] : 0,
                            $fileId,
                            $alias['hFileAliasPath'],
                            isset($alias['hFileAliasRedirect'])? (int) $alias['hFileAliasRedirect'] : 0,
                            isset($alias['hFileAliasExpires'])?  (int) $alias['hFileAliasExpires']  : 0,
                            isset($alias['hFileAliasDestination'])? $alias['hFileAliasDestination'] : nil
                        );
                    }
                }
            }
        }
        else
        {
            $this->verbose("Unable to create aliases because no fileId was provided.", __FILE__, __LINE__);
        }
    }

    public function saveAlias($fileAliasId, $fileId, $fileAliasPath, $fileAliasRedirect = 1, $fileAliasExpires = 0, $fileAliasDestination = nil)
    {
        # Make sure the path doesn't already exist in the alias database.
        if (!empty($fileId))
        {
            $aliasExists = $this->hFileAliases->selectExists(
                'hFileAliasId',
                array(
                    'hFileAliasPath' => $fileAliasPath
                )
            );

            if (!$aliasExists)
            {
                $existingFileId = (int) $this->getFileIdByFilePath($fileAliasPath);

                if (!$existingFileId)
                {
                    $fields = array(
                        'hFileAliasId' => $fileAliasId,
                        'hFileId' => $fileId,
                        'hFileAliasPath' => $fileAliasPath,
                        'hFileAliasDestination' => $fileAliasDestination,
                        'hFileAliasRedirect' => $fileAliasRedirect,
                        'hFileAliasExpires' => $fileAliasExpires
                    );

                    if (empty($fileAliasId))
                    {
                        $fields['hFileAliasCreated'] = time();
                    }

                    return $this->hFileAliases->save($fields);
                }
                else
                {
                    $this->verbose("Unable to save alias '{$fileAliasPath}', alias already exists as a file in HtFS at fileId '{$existingFileId}'.", __FILE__, __LINE__);
                }
            }
            else
            {
                $this->verbose("Unable to save alias '{$fileAliasPath}', alias already exists.", __FILE__, __LINE__);
            }
        }
        else
        {
            $this->verbose("Unable to save alias '{$fileAliasPath}', no fileId was provided.", __FILE__, __LINE__);
        }

        return 0;
    }
}

?>