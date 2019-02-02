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
# <h1>JSON Plugin Database API</h1>
# <p>
#
# </p>
# @end

class hPluginDatabaseJSON2Library extends hPlugin {

    private $hFile;
    private $hFileImport;
    private $hFileDatabase;
    private $hUserPermissions;
    private $hTemplateDatabase;
    private $hFileIconDatabase;
    private $hFileIconInstall;
    private $hPluginDatabase;
    private $hJSON;
    private $hPluginUpdate;

    private $pluginPath;
    private $pluginType;
    private $jsonPath;
    private $repository = array();

    public function register($jsonPath, $plugin = nil, $pluginName = nil, $pluginPath = nil)
    {
        # @return void

        # @description
        # <h2>Registering a Plugin</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hPluginDatabase = $this->database('hPlugin');
        $this->hPluginUpdate = $this->library('hPlugin/hPluginUpdate');

        if (empty($jsonPath))
        {
            return;
        }

        $this->jsonPath = $jsonPath;

        $this->console("Parsing a plugin's JSON configuration file at {$jsonPath}");

        // Get the JSON file and parse it
        $jsonLibraryPath = '/hJSON/hJSON.library.php';

        if (!class_exists('hJSONLibrary'))
        {
            include_once $this->hServerDocumentRoot.$jsonLibraryPath;
        }

        $this->hJSON = new hJSONLibrary($jsonLibraryPath);

        $json = $this->hJSON->getJSON($jsonPath);

        if (empty($json))
        {
            $this->fatal(
                "Failed to parse plugin's JSON configuration file",
                __FILE__,
                __LINE__
            );
        }

        $this->walkTemplateObject($json);

        if (empty($plugin))
        {
            $plugin = $json->plugin->path;

            $pluginName = $this->hPluginDatabase->getPluginName($plugin);
            $pluginPath = $this->hPluginDatabase->getPluginPath($plugin);

            $this->console("Setting plugin name to {$pluginName}");
            $this->console("Setting plugin path to {$pluginPath}");
        }

        $this->downloadSource($json);
        $this->recordPluginRepositoryData($plugin);

        if (isset($json->plugin))
        {
            $this->console("Found a plugin configuration");

            if (is_object($json->plugin) && isset($json->plugin->name))
            {
                $name = $json->plugin->name;

                $description = nil;

                if (isset($json->plugin->description))
                {
                    $description = $json->plugin->description;
                }

                $isReusable = !empty($json->plugin->isReusable);
            }
            else if (is_string($json->plugin))
            {
                $name = $json->plugin;
                $description = nil;
                $isReusable = false;
            }
        }

        $libraryPath = $pluginPath.'/'.$pluginName.'.library.php';

        if (isset($json->plugin->library))
        {
            $this->console("Found a library plugin configuration");

            if (is_object($json->plugin->library))
            {
                $name = $json->plugin->library->name;

                $description = nil;

                if (isset($json->plugin->library->description))
                {
                    $description = $json->plugin->library->description;
                }
            }
            else if (is_string($json->plugin->library))
            {
                $name = $json->plugin->library;
                $description = nil;
            }
        }

        $databasePath = $pluginPath.'/'.$pluginName.'.database.php';

        if (isset($json->plugin->database))
        {
            $this->console("Found a database plugin configuration");

            if (is_object($json->plugin->database))
            {
                $name = $json->plugin->database->name;

                $description = nil;

                if (isset($json->plugin->database->description))
                {
                    $description = $json->plugin->database->description;
                }
            }
            else if (is_string($json->plugin->database))
            {
                $name = $json->plugin->database;

                $description = nil;
            }
        }

        $shellPath = $pluginPath.'/'.$pluginName.'.shell.php';

        if (isset($json->plugin->shell))
        {
            $this->console("Found a plugin shell configuration");

            if (is_object($json->plugin->shell))
            {
                $name = $json->plugin->shell->name;

                $description = nil;

                if (isset($json->plugin->shell->description))
                {
                    $description = $json->plugin->shell->description;
                }
            }
            else if (is_string($json->plugin->shell))
            {
                $name = $json->plugin->shell;
                $description = nil;
            }
        }

        if (isset($json->plugin->service) && isset($json->plugin->service->name))
        {
            $this->console("Found a service plugin configuration");

            $description = nil;

            $name = $json->plugin->service->name;
            $servicePath = $pluginPath.'/'.$pluginName.'.service.php';

            if (isset($json->plugin->service->description))
            {
                $description = $json->plugin->service->description;
            }

            $this->console("Found some plugin service methods, deleting all existing methods");

            if ($this->hDatabase->tableExists('hPluginServices'))
            {
                $this->hPluginServices->delete(
                    'hPlugin',
                    $servicePath
                );
            }

            if (is_array($json->plugin->service->methods))
            {
                foreach ($json->plugin->service->methods as $method)
                {
                    $this->console(
                        "Inserting a new service method: '{$method}' on service ".
                        "'{$pluginPath}/{$pluginName}.service.php'"
                    );

                    $this->hPluginServices->modify();

                    $serviceExists = $this->hPluginServices->selectExists(
                        'hPlugin',
                        array(
                            'hPluginServiceMethod' => $method,
                            'hPlugin' => $servicePath
                        )
                    );

                    if (!$serviceExists)
                    {
                        $this->hPluginServices->insert(
                            array(
                                'hPluginServiceMethod' => $method,
                                'hPlugin' => $servicePath
                            )
                        );
                    }
                }
            }
        }

        if ($this->hPluginInstallFiles(true))
        {
            if (isset($json->file))
            {
                $this->installFile(
                    $json->file,
                    $pluginPath,
                    $json
                );
            }

            if (isset($json->files))
            {
                $this->console("Found multiple files to attach this plugin to");

                foreach ($json->files as $file)
                {
                    $this->installFile(
                        $file,
                        $pluginPath,
                        $json
                    );
                }
            }
        }

        // Install template...
        if (isset($json->template))
        {
            $this->console("Found a new template");

            if (!empty($json->template->name) && !empty($json->template->path))
            {
                $this->hTemplateDatabase = $this->database('hTemplate');

                $templateId = $this->hTemplateDatabase->templateExists(
                    $json->template->name
                );

                $templateId = $this->hTemplateDatabase->save(
                    $templateId,
                    $json->template->path,
                    $json->template->name,
                    isset($json->template->description)?      $json->template->description            : '',
                    isset($json->template->toggleVariables)?  (int) $json->template->toggleVariables  : false,
                    isset($json->template->cascadeVariables)? (int) $json->template->cascadeVariables : false,
                    isset($json->template->mergeVariables)?   (int) $json->template->mergeVariables   : false
                );

                $this->console("Template: '{$json->template->name}' successfully created");

                $this->hTemplateDatabase->saveTemplatePlugin(
                    $templateId,
                    $pluginPath
                );

                $this->console("Template plugin successfully set");

                $directories = array();

                if (isset($json->template->directories))
                {
                    if (is_array($json->template->directories))
                    {
                        $directories = $json->template->directories;
                    }
                }
                else
                {
                    $this->console("No template directories are defined");
                }

                if (is_array($directories) && count($directories))
                {
                    foreach ($directories as $directory)
                    {
                        $directory = trim($directory);

                        if (!empty($directory))
                        {
                            if (!$this->hFile->exists($directory))
                            {
                                $directoryId = $this->makePath($directory);
                            }
                            else
                            {
                                $directoryId = $this->getDirectoryId($directory);
                            }

                            $this->hTemplateDatabase->saveTemplateDirectory($templateId, $directoryId);

                            $this->console("Installed template directory: {$directory}");
                        }
                        else
                        {
                            $this->console(
                                "Unable to install template directory because the provided directory string is empty"
                            );
                        }
                    }
                }
            }
            else
            {
                $this->console(
                    "Unable to install the template because either the template name or path was not provided"
                );
            }
        }

        if (isset($json->contact->addressBook->name))
        {
            $this->console(
                'Found a new address book, creating a new address book and attaching this plugin to it'
            );

            $userId = 1;

            if (isset($json->contact->addressBook->userId))
            {
                $userId = (int) $json->contact->addressBook->userId;
            }

            $contactAddressBook = 'Address Book';

            if (isset($json->contact->addressBook->name))
            {
                $contactAddressBook = $json->contact->addressBook->name;
            }

            $contactAddressBookIsDefault = 0;

            if (isset($json->contact->addressBook->isDefault))
            {
                $contactAddressBookIsDefault = (int) $json->contact->addressBook->isDefault;
            }

            $contactAddressBookId = $this->hContactAddressBooks->save(
                array(
                    'hContactAddressBookId' => $this->hContactAddressBooks->selectColumn(
                        'hContactAddressBookId',
                        array(
                            'hPlugin' => $pluginPath
                        )
                    ),
                    'hUserId' => $userId,
                    'hContactAddressBookName' => $contactAddressBook,
                    'hPlugin' => $pluginPath,
                    'hContactAddressBookIsDefault' => $contactAddressBookIsDefault
                )
            );
        }

        $this->console("Finished installing plugin\n\n");
    }

    public function downloadSource($json)
    {
        if (isset($json->source))
        {
            $this->console("Found a plugin source");

            if (isset($json->source->destination))
            {
                $destination = $json->source->destination;

                if ($json->plugin->type)
                {
                    switch ($json->plugin->type)
                    {
                        case 'Public':
                        {
                            $destination = $this->hFrameworkPath.$this->hFrameworkRoot('/Hot Toddy').$destination;
                            break;
                        }
                        case 'Private':
                        {
                            $destination = $this->hFrameworkPluginPath('/Plugins').$destination;
                            break;
                        }
                        case 'Application':
                        {
                            $destination = $this->hFrameworkApplicationPath('/Applications').$destination;
                            break;
                        }
                    }
                }

                $this->console("Plugin destination: {$destination}");
            }

            if (isset($json->source->repository))
            {
                $this->console("Found a plugin source repository");

                $data = array();

                if (isset($json->source->repository->software))
                {
                    $data['software'] = $json->source->repository->software;
                }

                if (isset($json->source->repository->user))
                {
                    $data['user'] = $json->source->repository->user;
                }

                if (isset($json->source->repository->password))
                {
                    $data['password'] = $json->source->repository->password;
                }

                if (isset($json->source->repository->baseURI))
                {
                    $data['baseURI'] = $json->source->repository->baseURI;
                }

                if (isset($json->source->repository->path))
                {
                    $data['path'] = $json->source->repository->path;
                }

                if (isset($json->source->repository->checkout))
                {
                    $data['checkout'] = $json->source->repository->checkout;
                }

                if (isset($json->source->repository->readonly))
                {
                    $data['readonly'] = $json->source->repository->readonly;
                }

                if (isset($json->source->repository->revision))
                {
                    $data['revision'] = $json->source->repository->revision;
                }

                $this->console("Downloading plugin source from a repository");

                $this->repository = $this->hPluginUpdate->downloadFromRepository(
                    $data,
                    $destination
                );

                $this->hPluginDatabase->install($destination);
            }
        }
    }

    public function recordPluginRepositoryData($plugin)
    {
        $this->hPluginUpdate->setRepository(
            $plugin,
            $this->repository
        );
    }

    public function installFile($file, $pluginPath, $json)
    {
        # @return void

        # @description
        # <h2>Installing Plugin Files</h2>
        # <p>
        #
        # </p>
        # @end

        $this->hFile = $this->library('hFile');
        $this->hUserPermissions = $this->library('hUser/hUserPermissions');
        $this->hFileDatabase = $this->database('hFile');

        //$table = $this->getPluginDatabaseTable($pluginPath);

        $this->console(
            "Found plugin file: {$file->path}\n".
            "Checking existence of file: {$file->path}"
        );

        if (!$this->hFile->exists($file->path) || $this->shellArgumentExists('force', '--force') || $this->shellArgumentExists('update', '--update'))
        {
            $this->console(
                "HtFS File: {$file->path} does not exist, installing this file"
            );

            $directoryPath = dirname($file->path);
            $fileName = basename($file->path);

            if (!$this->hFile->exists($directoryPath))
            {
                $this->console("Creating HtFS directory: {$directoryPath}");
                $directoryId = $this->makePath($directoryPath);
                $this->hUserPermissions->save(2, $directoryId, 'rw', 'r');
            }
            else
            {
                $directoryId = $this->getDirectoryId($directoryPath);
            }

            if (isset($file->directory->properties))
            {
                $this->console("Found configurations for the directory: {$directoryPath}");

                $isApplication = 0;

                if (isset($file->directory->properties->isApplication) && (int) $file->directory->properties->isApplication > 0)
                {
                    $isApplication = 1;
                }

                $this->console("Is the directory an application? ".($isApplication == 1? 'Yes' : 'No'));

                if (isset($file->directory->properties->icon) && isset($file->directory->properties->icon->name))
                {
                    $directoryIconMIME = $file->directory->properties->icon->mime;
                    $directoryIconName = $file->directory->properties->icon->name;
                    $directoryICNS = $file->directory->properties->icon->icns;
                    $directoryIconExtension = nil;

                    if (isset($file->directory->properties->icon->extension))
                    {
                        $directoryIconExtension = $file->directory->properties->icon->extension;
                    }

                    $this->console(
                        "This directory has a custom icon\n".
                        "This directory's pseudo MIME type is: {$directoryIconMIME}\n".
                        "This directory's icon file is: {$directoryIconName}\n".
                        "This directory's ICNS file is: {$directoryICNS}\n".
                        "This directory's extension is: {$directoryIconExtension}"
                    );

                    $this->hFileIconDatabase = $this->database('hFile/hFileIcon');

                    $fileIconId = $this->hFileIconDatabase->save(
                        $directoryIconMIME,
                        $directoryIconName,
                        $directoryICNS,
                        $directoryIconExtension
                    );

                    $this->hFileIconInstall = $this->library('hFile/hFileIcon/hFileIconInstall');

                    $this->hFileIconInstall->copyApplicationIcon(
                        $this->pluginPath.'/'.
                        $file->directory->properties->icon->name
                    );
                }
                else
                {
                    $fileIconId = 0;
                }

                $this->console(
                    "This directory's HtFS label is: ".
                    (isset($file->directory->properties->label)? $file->directory->properties->label : "None Specified")
                );

                $this->hDirectoryProperties->save(
                    array(
                        'hDirectoryId' => (int) $directoryId,
                        'hFileIconId' => (int) $fileIconId,
                        'hDirectoryIsApplication' => $isApplication,
                        'hDirectoryLabel' => isset($file->directory->properties->label)? $file->directory->properties->label : ''
                    )
                );
            }

            $unsetFileDocument = false;

            if (isset($file->importFrom))
            {
                $importFrom = $file->importFrom;

                $isFile = false;

                $isUpload = false;

                if (substr($importFrom, 0, 7) == 'http://' || substr($importFrom, 0, 8) == 'https://' || isset($file->isUpload) && !empty($file->isUpload))
                {
                    $isUpload = true;
                }
                else if (substr($importFrom, 0, 1) == '/')
                {
                    $isFile = true;
                }
                else
                {
                    $importFrom = dirname($this->jsonPath).'/'.$importFrom;
                    $isFile = true;
                }

                if ($isFile && !file_exists($importFrom))
                {
                    $this->console("Import file failed file: '{$importFrom}' does not exist.");
                }
                else
                {
                    $this->console("Importing file from: '{$importFrom}'");

                    if (!$isUpload)
                    {
                        $fileDocument = hString::encodeHTML(file_get_contents($importFrom));
                    }
                    else
                    {
                        $this->hFileSystemAllowDuplicates = true;

                        $temporaryFileName = $this->getRandomString(15);
                        $temporaryPath = $this->hFrameworkTemporaryPath.$temporaryFileName;

                        $this->console("Import file temporary path: ".$temporaryPath);

                        file_put_contents(
                            $temporaryPath,
                            file_get_contents($importFrom)
                        );

                        $extension = $this->getExtension($importFrom);

                        $mimeType = $this->hFileIcons->selectColumn(
                            'hFileMIME',
                            array(
                                'hFileExtension' => $extension
                            )
                        );

                        if (empty($mimeType))
                        {
                            $mimeType = 'application/octet-stream';
                        }

                        $this->hFile->import(
                            $directoryPath,
                            array(
                                array(
                                    'hFileName'         => $fileName,
                                    'hFileReplace'      => true,
                                    'hFileMD5Checksum'  => md5_file($temporaryPath),
                                    'hFileTempPath'     => $temporaryPath,
                                    'hFileMIME'         => $mimeType
                                )
                            )
                        );

                        $this->hFileSystemAllowDuplicates = false;

                        $fileId = $this->getFileIdByFilePath($file->path);

                        $this->rm($temporaryPath, true);

                        $unsetFileDocument = true;
                    }
                }
            }

            $this->console(
                "Creating file: {$fileName} in directory {$directoryPath}\n".
                "The file's owner's hUserId: ".  (isset($file->user->id)?      $file->user->id          : 1)."\n".
                "The file's parent's path: ".    (isset($file->parentPath)?  $file->parentPath  : "None Specified")."\n".
                "The file's name: ".             $fileName."\n".
                "The file's plugin: ".           $pluginPath."\n".
                //"The file's plugin database table: ".$table."\n".
                "The file's sort index: ".       (isset($file->sortIndex)?   $file->sortIndex   : "None Specified")."\n".
                "The file's title: ".            (isset($file->title)?       $file->title       : 'None Specified')."\n".
                "The file's description: ".      (isset($file->description)? $file->description : 'None Specified')."\n".
                "The file's keywords: ".         (isset($file->keywords)?    $file->keywords    : 'None Specified')."\n".
                "The file's document: ".         (isset($file->document)?    $file->document    : 'None Specified')."\n".
                "The file's CSS: ".              (isset($file->css)?         $file->css         : 'None Specified')."\n".
                "The file's JavaScript: ".       (isset($file->js)?          $file->js          : 'None Specified')."\n".
                "The file's HtFS label: ".       (isset($file->label)?       $file->label       : 'None Specified')."\n".
                "The file's domain: ".           (isset($file->domain)?      $file->domain      : 'None Specified')
            );

            $saveFile = array(
                'hFileId'            => !empty($fileId)? $fileId : 0,
                'hLanguageId'        => 1,
                'hDirectoryId'       => $directoryId,
                'hUserId'            => isset($file->user->id)? (int) $file->user->id : 1,
                'hFileParentId'      => isset($file->parentPath)? $this->getFileIdByFilePath($file->parentPath) : 0,
                'hFileName'          => $fileName,
                'hPlugin'            => $pluginPath,
                'hFileSortIndex'     => isset($file->sortIndex)? (int) $file->sortIndex : 0,
                'hFileCreated'       => time(),
                'hFileTitle'         => isset($file->title)?       hString::escapeAndEncode($file->title)       : '',
                'hFileDescription'   => isset($file->description)? hString::escapeAndEncode($file->description) : '',
                'hFileKeywords'      => isset($file->keywords)?    hString::escapeAndEncode($file->keywords)    : '',
                'hFileDocument'      => isset($file->document)?    hString::escapeAndEncode($file->document)    : '',
                'hFileCSS'           => isset($file->css)?         hString::escapeAndEncode($file->css)         : '',
                'hFileJavaScript'    => isset($file->js)?          hString::escapeAndEncode($file->js)          : '',
                'hFileLabel'         => isset($file->label)?       $file->label                                 : ''
            );

            if (isset($file->calendar->id))
            {
                $calendar = array(
                    'hFileCalendarId'           => is_array($file->calendar->id)?       $file->calendar->id         : (int) $file->calendarId,
                    'hFileCalendarCategoryId'   => isset($file->calendar->categoryId)?  $file->calendar->categoryId : 3,
                    'hFileCalendarBegin'        => isset($file->calendar->begin)?       $file->calendar->begin      : nil,
                    'hFileCalendarEnd'          => isset($file->calendar->end)?         $file->calendar->end        : nil,
                    'hFileCalendarRange'        => isset($file->calendar->range)?       $file->calendar->range      : 0,
                    'hFileCalendarDate'         => isset($file->calendar->date)?        $file->calendar->date       : date('m/d/Y'),
                    'hFileCalendarBeginTime'    => isset($file->calendar->beginTime)?   $file->calendar->beginTime  : nil,
                    'hFileCalendarEndTime'      => isset($file->calendar->endTime)?     $file->calendar->endTime    : nil,
                    'hFileCalendarAllDay'       => isset($file->calendar->allDay)?      $file->calendar->allDay     : nil
                );

                $saveFile = array_merge($calendar, $saveFile);
            }

            if (isset($file->contact->id))
            {
                $saveFile['hContactId'] = (int) $file->contact->id;
            }

            if (isset($file->contact->fileCategoryId))
            {
                $saveFile['hContactFileCategoryId'] = (int) $file->contact->fileCategoryId;
            }

            if (isset($file->contact->isProfilePhoto))
            {
                $saveFile['hContactIsProfilePhoto'] = (int) $file->contact->isProfilePhoto;
            }

            if (isset($file->contact->isDefaultProfilePhoto))
            {
                $saveFile['hContactIsDefaultProfilePhoto'] = (int) $file->contact->isDefaultProfilePhoto;
            }

            $saveFile['hFileVariables'] = true;

            if (isset($file->documentSourcePath))
            {
                $this->console("Source path for document: ".dirName($this->jsonPath).'/'.$file->documentSourcePath);

                $importFromSource = isset($file->importFromSource) && !empty($file->importFromSource);

                $fileDocument = file_get_contents(dirName($this->jsonPath).'/'.$file->documentSourcePath);

                if (!$importFromSource)
                {
                    $fileDocument = hString::encodeHTML($fileDocument);
                }
                else
                {
                    $options = array();

                    if ($this->shellArgumentExists('chown', '--chown'))
                    {
                        $options['chown'] = $this->getShellArgumentValue('chown', '--chown');
                    }

                    if ($this->shellArgumentExists('chmod', '--chmod'))
                    {
                        $options['chmod'] = $this->getShellArgumentValue('chown', '--chmod');
                    }

                    $opts = array(
                        'importFrom',
                        'saveFile',
                        'removeHostName',
                        'removeIP',
                        'picturesFolder',
                        'documentsFolder',
                        'moviesFolder',
                        'musicFolder',
                        'chmod',
                        'chown',
                        'userId',
                        'userPermissionsOwner',
                        'userPermissionsWorld',
                        'userPermissionsGroups'
                    );

                    foreach ($opts as $opt)
                    {
                        if (!empty($file->import->$opt))
                        {
                            $options[$opt] = $file->import->$opt;
                        }
                    }

                    $options['saveFile'] = false;

                    $this->hFileImport = $this->library('hFile/hFileImport');

                    $fileDocument = $this->hFileImport->fromSource(
                        $fileDocument,
                        nil,
                        array(),
                        $options
                    );
                }
            }

            if (!empty($fileDocument))
            {
                $saveFile['hFileDocument'] = $fileDocument;
            }

            if ($unsetFileDocument)
            {
                unset(
                    $saveFile['hFileDocument'],
                    $saveFile['hFileCSS'],
                    $saveFile['hFileJavaScript'],
                    $saveFile['hFileLabel']
                );
            }

            if (isset($file->headingTitle))
            {
                $saveFile['hFileHeadingTitle'] = hString::escapeAndEncode($file->headingTitle);
            }

            if (isset($file->breadcrumbTitle))
            {
                $saveFile['hFileBreadcrumbTitle'] = hString::escapeAndEncode($file->breadcrumbTitle);
            }

            if (isset($file->sideboxTitle))
            {
                $saveFile['hFileSideboxTitle'] = hString::escapeAndEncode($file->sideboxTitle);
            }

            if (isset($file->domain))
            {
                $saveFile['hFileDomains'][0] = $file->domain;
            }

            if (isset($file->domains))
            {
                if (is_array($file->domains))
                {
                    $saveFile['hFileDomains'] = $file->domains[0];
                }
            }

            if (isset($file->variables) && is_object($file->variables))
            {
                foreach ($file->variables as $variable => $value)
                {
                    $saveFile[$variable] = $value;
                    $this->console("Setting file variable: {$variable} to value: {$value}");
                }
            }

            $fileId = $this->hFileDatabase->save($saveFile);

            $this->console("Saved fileId: {$fileId}");

            if (isset($file->pathWildcard))
            {
                if (!is_array($file->pathWildcard))
                {
                    $this->console("File's path wildcard: {$file->pathWildcard}");

                    $this->hFilePathWildcards->insert(
                        array(
                            'hFilePathWildcard' => $file->pathWildcard,
                            'hFileId' => (int) $fileId
                        )
                    );
                }
                else
                {
                    foreach ($file->pathWildcard as $wildcardPath)
                    {
                        $this->console("File's path wildcard: {$wildcardPath}");

                        $this->hFilePathWildcards->insert(
                            array(
                                'hFilePathWildcard' => $wildcardPath,
                                'hFileId' => (int) $fileId
                            )
                        );
                    }
                }
            }

            if (isset($file->alias) && is_object($file->alias))
            {
                $this->insertAlias($fileId, $file->alias);
                $this->console("File alias created: ".$file->alias->path);
            }

            if (isset($file->aliases) && is_array($file->aliases))
            {
                foreach ($file->aliases as $alias)
                {
                    $this->insertAlias($fileId, $alias);
                    $this->console("File alias created: ".$alias->path);
                }
            }

            # user: {
            #     permissions : {
            #         owner : "rw",
            #         world: "r",
            #         groups: [
            #             ["Website Administrators", "rw"]
            #         ]
            #     }
            # }

            $owner = isset($file->user->permissions->owner)? $file->user->permissions->owner : 'rw';
            $world = isset($file->user->permissions->world)? $file->user->permissions->world : 'r';

            $this->console(
                "Setting permissions on the file\n".
                "Owner: {$owner}\n".
                "World: {$world}"
            );

            if (isset($file->user->permissions->groups))
            {
                if (isset($file->user->permissions->groups))
                {
                    if (is_array($file->user->permissions->groups))
                    {
                        foreach ($file->user->permissions->groups as $group)
                        {
                            $this->hFiles->setGroup($group[0], $group[1]);
                        }
                    }
                }
            }

            $this->hFiles->savePermissions($fileId, $owner, $world);
        }
        else
        {
            $this->console("File exists: {$file->path}");
        }

        $this->console("\n");
    }

    public function &insertAlias($fileId, $alias)
    {
        # @return void

        # @description
        # <h2>Creating File Aliases</h2>
        # <p>
        #
        # </p>
        # @end

        $aliasExists = $this->hFileAliases->selectExists(
            'hFileAliasId',
            array(
                'hFileId' => $fileId,
                'hFileAliasPath' => $alias->path
            )
        );

        if (!$aliasExists)
        {
            $this->hFileAliases->insert(
                array(
                    'hFileAliasId'       => nil,
                    'hFileId'            => (int) $fileId,
                    'hFileAliasPath'     => $alias->path,
                    'hFileAliasRedirect' => isset($alias->isRedirect)? (int) $alias->isRedirect : 0,
                    'hFileAliasCreated'  => time(),
                    'hFileAliasExpires'  => isset($alias->expires)? strToTime($alias->expires) : nil
                )
            );
        }

        return $this;
    }

    public function makePath($path, $permissions = array())
    {
        # @return integer

        # @description
        # <h2>Creating a Folder</h2>
        # <p>
        #   This method creates specified folder.  If the parent folders in the path
        #   do not exist, these are also created.
        # </p>
        # <p>
        #   Permissions can be set on each folder, or if no permissions are provided,
        #   permissions will be inherited from the first folder in the path that exists.
        # </p>
        # <p>
        #   If permissions are provided, they should be specified as follows:
        # </p>
        # <p>
        # <code>
        #   array(
        #       'hUserPermissionsGroups' => array(
        #           'Website Administrators' => 'rw'
        #       ),
        #       'hUserPermissionsOwner' => 'rw',
        #       'hUserPermissionsWorld' => 'r'
        #   )
        # </code>
        # <p>
        #   All permissions are optional.  Permissions not provided will be set to no
        #   access.
        # </p>
        # @end

        $directoryId = $this->getDirectoryId($path);

        $folders = explode('/', $path);

        $currentPath = '/';

        foreach ($folders as $folder)
        {
            if (!empty($folder))
            {
                $currentPath .= ($currentPath == '/')? $folder : '/'.$folder;

                $this->console("MAKE PATH: {$currentPath}");

                $directoryId = $this->getDirectoryId($currentPath);

                if (empty($directoryId))
                {
                    $parentDirectoryId = $this->getDirectoryId(dirname($currentPath));

                    $directoryId = $this->hDirectories->insert(
                        array(
                            'hDirectoryId'              => nil,
                            'hDirectoryParentId'        => (int) $parentDirectoryId,
                            'hUserId'                   => isset($permissions['hUserId'])? (int) $permissions['hUserId'] : 1,
                            'hDirectoryPath'            => $currentPath,
                            'hDirectoryCreated'         => time(),
                            'hDirectoryLastModified'    => 0
                        )
                    );

                    if (!count($permissions) || !is_array($permissions))
                    {
                        $this->hDirectories->setInherit($directoryId);
                        $this->hDirectories->savePermissions($directoryId);
                    }
                    else
                    {
                        if (isset($permissions['hUserPermissionsGroups']) && is_array($permissions['hUserPermissionsGroups']))
                        {
                            foreach ($permissions['hUserPermissionsGroups'] as $group => $level)
                            {
                                $this->hDirectories->setGroup($group, $level);
                            }
                        }

                        $this->hDirectories->savePermissions(
                            $directoryId,
                            isset($permissions['hUserPermissionsOwner'])? $permissions['hUserPermissionsOwner'] : 'rw',
                            isset($permissions['hUserPermissionsWorld'])? $permissions['hUserPermissionsWorld'] : ''
                        );
                    }
                }
            }
        }

        # Last directory created is returned.
        return $directoryId;
    }

    public function mkdir($path, $name)
    {
        # @return integer

        # @description
        # <h2>Creating a Directory</h2>
        # <p>
        #
        # </p>
        # @end

        # A separate function for making the directories is needed because of
        # checks for special folders in hFile.
        return $this->hDirectories->insert(
            array(
                'hDirectoryId'           => nil,
                'hDirectoryParentId'     => (int) $this->getDirectoryId($path),
                'hUserId'                => 1,
                'hDirectoryPath'         => $this->hFile->getConcatenatedPath($path, $name),
                'hDirectoryCreated'      => time(),
                'hDirectoryLastModified' => 0
            )
        );
    }
}

?>