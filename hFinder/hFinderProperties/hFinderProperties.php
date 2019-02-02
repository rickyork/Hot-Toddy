<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Finder Properties
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

class hFinderProperties extends hPlugin {

    private $path;
    private $hDialogue;
    private $hFile;
    private $hForm;

    public function hConstructor()
    {
        $this->hFileCSS = '';
        $this->hFileJavaScript = '';
        $this->getPluginFiles();

        if (isset($_GET['path']))
        {
            hString::safelyDecodeURL($_GET['path']);

            hString::scrubArray($_GET);

            $this->hTemplatePath = '/hFinder/templates/hFinder.template.php';

            $this->hFile     = $this->library('hFile');
            $this->hDialogue = $this->library('hDialogue');
            $this->hForm     = $this->library('hForm');

            $this->hFile->query($_GET['path']);
            $this->hFileTitle = $this->hFile->filePath;
            $this->hFileTitleAppend = '';
            $this->hFileTitlePrepend = '';

            $this->properties();
        }
        else
        {
            $this->warning('Unable to retrieve properties because no path was supplied.', __FILE__, __LINE__);
        }
    }

    private function properties()
    {
        $hUserId = $this->hDatabase->selectColumn(
            'hUserId',
            $this->hFile->isDirectory? 'hDirectories' : 'hFiles',
            $this->hFile->isDirectory?
                array('hDirectoryId' => (int) $this->hFile->directoryId) : array('hFileId' => (int) $this->hFile->fileId)
        );

        // Calculating the size of a directory can take a while.
        // ini_set('MAX_EXECUTION_TIME', 0);
        $symbolicLink = $this->hFileSymbolicLinkTo(0, $this->hFile->fileId);

        // General, Path, Document, Aliases, Configuration, Permissions, Plugin
        $this->hDialogue->newDialogue('hFinderProperties');
        $this->hDialogue->setForm($this->hForm);

        $this->hForm
            ->addDiv('hFinderPropertiesGeneral', 'General')
            ->addFieldset('Properties', '100%', '150px,auto')

            ->addData(
                'hFinderPropertiesUserFullName',
                'Owner Name:',
                $this->user->getFullName($hUserId)
            )
            ->addData(
                'hFinderPropertiesUserName',
                'Owner User Name:',
                $this->user->getUserName($hUserId)
            )
            ->addData(
                'hFinderPropertiesUserId',
                'Owner User Id:',
                ((int) $hUserId)
            );

        if (!$this->hFile->isDirectory)
        {
            $this->hForm->addData(
                'hFinderPropertiesFileId',
                'File Id:',
                $this->hFile->fileId
            );
        }

        $this->hForm
            ->addData(
                'hFinderPropertiesDirectoryId',
                'Directory Id:',
                $this->hFile->directoryId
            )
            ->addData(
                'hFinderPropertiesPath',
                'Full Path:',
                "<a href='".$this->hFile->filePath."'>".$this->hFile->filePath."</a>"
            );

        $hasPlugin = $this->hasPlugin($symbolicLink? $symbolicLink : $this->hFile->fileId);

        $size = ($hasPlugin)? "<i>Not Available (Dynamically Generated Content)</i>" : $this->hFile->getSize();

        $this->hForm->addData(
            'hFinderPropertiesSize',
            'Total Size:',
            $size
        );

        if ($hasPlugin)
        {
            $this->hForm->addData(
                'hFinderPropertiesDocumentSize',
                'Document Size:',
                $this->hFile->getSize()
            );
        }

        $lastModified = $this->hFile->getLastModified();

        $this->hForm
            ->addData(
                'hFinderPropertiesMIMEType',
                'MIME Type:',
                $this->hFile->getMIMEType()
            )
            ->addData(
                'hFinderPropertiesDescription',
                'Description: -L',
                $this->hFile->getDescription()
            )
            ->addData(
                'hFinderPropertiesCreated',
                'Created:',
                date('m/d/Y h:i:s A', $this->hFile->getCreated())
            )
            ->addData(
                'hFinderPropertiesLastModified',
                'Last Modified:',
                $lastModified? date('m/d/Y h:i:s A', $lastModified) : '<i>Never</i>'
            );

        //$this->hForm->addDiv('hFinderPropertiesPath');

        $hFileParentId = $this->getFileParentId($this->hFile->fileId);

        if (!empty($hFileParentId))
        {
            $this->hForm->addFieldset(
                'Parent Document',
                '100%',
                '150px,auto'
            );

            $path = $this->getFilePathByFileId($hFileParentId);

            $this->hForm
                ->addData(
                    'hFinderPropertiesParentFileId',
                    'Parent File Id:',
                    $hFileParentId
                )
                ->addData(
                    'hFinderPropertiesParentTitle',
                    'Parent Title:',
                    $this->getFileTitle($hFileParentId)
                )
                ->addData(
                    'hFinderPropertiesParentPath',
                    'Parent Path:',
                    "<a href='{$path}'>{$path}</a>"
                );
        }

        if ($symbolicLink)
        {
            $path = $this->getFilePathByFileId($symbolicLink);
            $this->hForm
                ->addFieldset(
                    'Symbolic Link',
                    '100%',
                    '150px,auto'
                )
                ->addData(
                    'hFinderPropertiesSymbolicLinkId',
                    'Symbolic Link File Id:',
                    $symbolicLink
                )
                ->addData(
                    'hFinderPropertiesSymbolicLinkPath',
                    'Symbolic Link Path:',
                    "<a href='{$path}'>{$path}</a>"
                )
                ->addData(
                    'hFinderPropertiesSymbolicLinkTitle',
                    'Symbolic Link Title:',
                    $this->getFileTitle($symbolicLink)
                );
        }

        if (!$this->hFile->isDirectory)
        {
            $this->hForm
                ->addDiv(
                    'hFinderPropertiesAliases',
                    'Aliases'
                )
                ->addFieldset(
                    'Aliases',
                    '100%',
                    'auto,auto,auto,auto'
                );

            $query = $this->hDatabase->select(
                array(
                    'hFileAliasPath',
                    'hFileAliasRedirect',
                    'hFileAliasCreated',
                    'hFileAliasExpires'
                ),
                'hFileAliases',
                array(
                    'hFileId' => (int) $this->hFile->fileId
                )
            );

            if (!count($query))
            {
                $this->hForm->addTableCell(
                    "<div id='hFinderPropertiesNoAliases'>There are no aliases associated with this file.</div>",
                    4
                );
            }
            else
            {
                $this->hForm
                    ->addTableHeading('Path')
                    ->addTableHeading('301 Redirect')
                    ->addTableHeading('Created')
                    ->addTableHeading('Expires');

                foreach ($query as $data)
                {
                    $this->hForm
                        ->addTableCell("<a href='{$data['hFileAliasPath']}'>{$data['hFileAliasPath']}</a>")
                        ->addTableCell(empty($data['hFileAliasRedirect'])? 'No' : 'Yes')
                        ->addTableCell(date('m/d/Y h:i:s A', $data['hFileAliasCreated']))
                        ->addTableCell(empty($data['hFileAliasExpires'])? 'No' : 'Yes');
                }
            }

            if ($hasPlugin)
            {
                $this->hForm->addDiv('hFinderPropertiesPlugin', 'Plugin');

                $plugin = $this->hFiles->selectColumn(
                    'hPlugin',
                    (int) $this->hFile->fileId
                );

                if (!empty($plugin))
                {
                    $plugin = $this->queryPlugin($plugin);

                    $this->hForm
                        ->addFieldset(
                            'Plugin Properties',
                            '100%',
                            '150px,auto'
                        )
                        ->addData(
                            'hFinderPropertiesPluginIdIsPrivate',
                            'Private Plugin:',
                            $plugin['isPrivate']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginName',
                            'Plugin Name:',
                            $plugin['name']
                        )
                        ->addData(
                            'hFinderPropertiesPluginPath',
                            'Plugin Path:',
                            $plugin['path']
                        )
                        ->addData(
                            'hFinderPropertiesPluginIsService',
                            'Service Plugin:',
                            (bool) $plugin['isService']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginIsListener',
                            'Listener Plugin:',
                            (bool) $plugin['isListener']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginIsLibrary',
                            'Library Plugin:',
                            (bool) $plugin['isLibrary']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginIsDatabase',
                            'Database Plugin:',
                            (bool) $plugin['isDatabase']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginIsDaemon',
                            'Daemon Plugin:',
                            (bool) $plugin['isDaemon']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginIsShell',
                            'Shell Plugin:',
                            (bool) $plugin['isShell']? 'Yes' : 'No'
                        )
                        ->addData(
                            'hFinderPropertiesPluginBaseName',
                            'Name:',
                            $plugin['name']
                        )
                        ->addData(
                            'hFinderPropertiesPluginBaseName',
                            'Base Name:',
                            $plugin['baseName']
                        )
                        ->addData(
                            'hFinderPropertiesPluginPath',
                            'Path:',
                            $plugin['path']
                        )
                        ->addData(
                            'hFinderPropertiesPluginBasePath',
                            'Base Path:',
                            $plugin['basePath']
                        );
                }
            }
        }

        $this->hDialogueFullScreen = true;

        $this->hFileDocument = $this->hDialogue->getDialogue('', 'Properties');
    }
}

?>