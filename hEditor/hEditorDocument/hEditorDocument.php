<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Editor Document Plugin
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

class hEditorDocument extends hPlugin {

    private $hForm;
    private $hFile;
    private $hDialogue;
    private $hFileDatabase;
    private $hFileIcon;
    private $hPluginDatabase;
    private $hCalendarDatabase;
    private $hListDatabase;
    private $hTidy;

    public function hConstructor()
    {
        $this->hFileJavaScript = '';
        $this->hFileDocumentExpandIdWithLastModified = false;
        $this->hFileDocumentParseEnabled = false;

        $this->hFileXHTML = false;
        $this->hFileMIME = 'text/html';

        if ($this->inGroup('root') && $this->hEditorDocumentRootTextEditorOverride(true))
        {
            $this->hEditorDocumentEnableTextEditor = true;
        }

        if (isset($_GET['hEditorConf']))
        {
            $conf = $this->hFrameworkConfigurationPath.'/hEditor '.hString::scrubString($_GET['hEditorConf']).'.conf';

            if (file_exists($conf))
            {
                $variables = parse_ini_file($conf);
                $this->setVariables($variables);
            }
        }

        $this->setLite();

        $this->hFileCSS = '';

        if ($this->hEditorDocumentEnableTextEditor(false))
        {
            $this->getPluginJavaScript('/Library/Ace/src-noconflict/ace', true);
        }

        $this->hForm     = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');
        $this->hFileIcon = $this->library('hFile/hFileIcon');
        $this->hFile     = $this->library('hFile');

        $this->hFileDatabase     = $this->database('hFile');
        $this->hCalendarDatabase = $this->database('hCalendar');
        $this->hListDatabase     = $this->database('hList');
        $this->hPluginDatabase   = $this->database('hPlugin');

        $this->getPluginFiles();
        $this->getPluginCSS('ie7');

        if (isset($_GET['path']))
        {
            hString::safelyDecodeURL($_GET['path']);
            hString::safelyDecodeURLPath($_GET['path']);

            $this->hFile->query($_GET['path']);

            $fileId = (int) $this->hFile->fileId;

            $this->hFileDatabase->setFileId($fileId);
        }

        $this->hForm->addDiv('hEditorDocumentContent');
        $this->hForm->addFieldset('Content', '100%', '100%');

        $exclude = array();

        if (!empty($_GET['path']) && $this->hFile->userIsReadAuthorized && $this->hFile->isServer)
        {
            $document = htmlspecialchars(file_get_contents($this->hFile->serverPath));

            $extension = $this->getExtension($this->hFile->serverPath);

            switch (true)
            {
                case $this->hEditorDocumentEnableTextEditor(false):
                {
                    $mode = $this->getMode($extension);

                    $this->hForm->addTableCell(
                        $this->getTemplate(
                            'Text Editor',
                            array(
                                'hFileDocument'  => $document,
                                'mode' => $mode,
                                'isText' => $mode == 'text'
                            )
                        )
                    );

                    break;
                }
                default:
                {
                    $this->hForm
                        ->setAttribute(
                            'wrap',
                            'off'
                        )
                        ->addTextareaInput(
                            'hFileDocument',
                            '',
                            '60,15',
                            $document
                        );
                }
            }

            $this->hForm
                ->addHiddenInput(
                    'hFileId',
                    0
                )
                ->addHiddenInput(
                    'hFileIsServer',
                    1
                )
                ->addHiddenInput(
                    'hDirectoryPath',
                    $this->hFile->parentDirectoryPath
                )
                ->addHiddenInput(
                    'hFileName',
                    $this->hFile->fileName
                )
                ->addHiddenInput(
                    'hFileReplaceExisting',
                    '0'
                );
        }
        else
        {
            $this->hForm
                ->addHiddenInput(
                    'hFileId',
                    $fileId
                )
                ->addHiddenInput(
                    'hFileIsServer',
                    0
                )
                ->addHiddenInput(
                    'hDirectoryPath',
                    $this->hFileDatabase->get('hDirectoryPath')
                )
                ->addHiddenInput(
                    'hFileName',
                    $this->hFileDatabase->get('hFileName')
                )
                ->addHiddenInput(
                    'hFileReplaceExisting',
                    '0'
                );

            $document = hString::decodeHTML($this->hFileDatabase->get('hFileDocument'));
            $document = $this->expandDocumentIds($document);

            $extension = $this->getExtension($this->hFileDatabase->get('hFileName'));

            // Save a copy in the file system...
            if (($extension == 'html' || $extension == 'htm') && $this->hEditorDocumentEnableTidy(false) || !strstr($document, "\n"))
            {
                $this->hTidy = $this->library('hTidy');
                $document = $this->hTidy->getHTML($document);
            }

            $document = htmlspecialchars($document, ENT_QUOTES);

            switch (true)
            {
                case $this->hEditorDocumentEnableTextEditor(false):
                {
                    $mode = $this->getMode($extension);

                    $this->hForm->addTableCell(
                        $this->getTemplate(
                            'Text Editor',
                            array(
                                'hFileDocument'  => $document,
                                'mode' => $mode,
                                'isText' => $mode == 'text'
                            )
                        )
                    );

                    break;
                }
                case $this->hEditorDocumentEnableWYSIWYG(true):
                {
                    $this->hForm->addWYSIWYGInput(
                         'hFileDocument',
                        '',
                        $document,
                        '60,15',
                        '100%,100%',
                        array(),
                        $this->hEditorDocumentWYSIWYGToolbar('Default')
                    );

                    $this->hFileJavaScript .= $this->getTemplate(
                        'WYSIWYG',
                        array(
                            'hEditorWYSIWYGCSS' => $this->hEditorWYSIWYGCSS(nil),
                            'FCKTemplatePath' => $this->FCKTemplatePath(nil)
                        )
                    );

                    break;
                }
                default:
                {
                    $this->hForm
                        ->setAttribute(
                            'wrap',
                            'off'
                        )
                        ->addTextareaInput(
                            'hFileDocument',
                            '',
                            '60,15',
                            $document
                        );
                }
            }

            //$this->hForm->addDiv('hFileProperties');
            if ($this->hEditorDocumentEnableProperties(true))
            {
                $this->hForm
                    ->addDiv(
                        'hEditorDocumentProperties',
                        'Properties'
                    )
                    ->addFieldset(
                        'Meta Data',
                        '100%',
                        '150px,'
                    )
                    ->addTextInput(
                        'hFileTitle',
                        'T:Title:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileTitle'
                        )
                    );

                if ($this->hEditorDocumentEnableSubTitle(true))
                {
                    $this->hForm->addTextInput(
                        'hFileSubTitle',
                        'e:Subtitle:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileSubTitle'
                        )
                    );

                    array_push(
                        $exclude,
                        'hFileSubTitle'
                    );
                }

                $this->hForm
                    ->addTextareaInput(
                        'hFileDescription',
                        'D:Description: -L',
                        '50,3',
                        $this->hFileDatabase->get(
                            'hFileDescription'
                        )
                    )
                    ->addTextareaInput(
                        'hFileKeywords',
                        'K:Keywords: -L',
                        '50,3',
                        $this->hFileDatabase->get(
                            'hFileKeywords'
                        )
                    )
                    ->addFieldset(
                        'Properties',
                        '100%',
                        '150px,'
                    )
                    ->addTextInput(
                        'hFileHeadingTitle',
                        'T:Heading Title:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileHeadingTitle'
                        )
                    );

                array_push(
                    $exclude,
                    'hFileHeadingTitle'
                );

                if ($this->hFileBreadcrumbsEnabled(false))
                {
                    $this->hForm->addTextInput(
                        'hFileBreadcrumbTitle',
                        'B:Breadcrumb Title:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileBreadcrumbTitle'
                        )
                    );

                    array_push(
                        $exclude,
                        'hFileBreadcrumbTitle'
                    );
                }

                if ($this->hEditorDocumentEnableMenuTitle(false))
                {
                    $this->hForm->addTextInput(
                        'hFileMenuTitle',
                        'M:Menu Title:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileMenuTitle'
                        )
                    );

                    array_push(
                        $exclude,
                        'hFileMenuTitle'
                    );
                }

                if ($this->hEditorDocumentEnableSideboxTitle(false))
                {
                    $this->hForm->addTextInput(
                        'hFileSideboxTitle',
                        'S:Sidebox Title:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileSideboxTitle'
                        )
                    );

                    array_push(
                        $exclude,
                        'hFileSideboxTitle'
                    );
                }

                if ($this->hEditorDocumentEnableTooltipTitle(false))
                {
                    $this->hForm->addTextInput(
                        'hFileTooltipTitle',
                        'l:Tooltip Title:',
                        25,
                        $this->hFileDatabase->get(
                            'hFileTooltipTitle'
                        )
                    );

                    array_push(
                        $exclude,
                        'hFileTooltipTitle'
                    );
                }

                $fileParentId = (int) $this->hFileDatabase->get('hFileParentId');

                if ($this->hFileBreadcrumbsEnabled(false))
                {
                    $this->hForm
                        ->addInputLabel(
                            'hFileParentId',
                            'Parent Document: -L'
                        )
                        ->addTableCell(
                            $this->getTemplate(
                                'Parent Document',
                                array(
                                    'hFileParentId'    => $fileParentId,
                                    'hFileParentTitle' => $this->getFileTitle($fileParentId),
                                    'hFileIconPath'    => $this->hFileIcon->getFileIconPath($fileParentId),
                                    'hFileParentPath'  => $this->getFilePathByFileId($fileParentId)
                                )
                            )
                        );
                }
                else
                {
                    $this->hForm->addHiddenInput(
                        'hFileParentId',
                        $fileParentId
                    );
                }

                if ($this->hEditorDocumentEnableGoogleSitemap(false))
                {
                    array_push(
                        $exclude,
                        'hGoogleSitemapPriority'
                    );

                    array_push(
                        $exclude,
                        'hGoogleSitemapChangeFrequency'
                    );

                    $this->hForm
                        ->addFieldset(
                            'Google Sitemap',
                            '100%',
                            '150px,'
                        )
                        ->addSelectInput(
                            'hGoogleSitemapPriority',
                            'y:Priority:',
                            array(
                                '' => 'none',
                                '1' => '1',
                                '0.9' => '0.9',
                                '0.8' => '0.8',
                                '0.7' => '0.7',
                                '0.6' => '0.6',
                                '0.5' => '0.5',
                                '0.4' => '0.4',
                                '0.3' => '0.3',
                                '0.2' => '0.2',
                                '0.1' => '0.1',
                                '0.0' => '0.0'
                            ),
                            1,
                            $this->hFileDatabase->get(
                                'hGoogleSitemapPriority'
                            )
                        )
                        ->addSelectInput(
                            'hGoogleSitemapChangeFrequency',
                            'q:Update Frequency:',
                            array(
                                ''        => 'none',
                                'always'  => 'always',
                                'hourly'  => 'hourly',
                                'daily'   => 'daily',
                                'weekly'  => 'weekly',
                                'monthly' => 'monthly',
                                'yearly'  => 'yearly',
                                'never'   => 'never'
                            ),
                            1,
                            $this->hFileDatabase->get(
                                'hGoogleSitemapChangeFrequency'
                            )
                        );


                }
            }

            if ($this->hEditorDocumentEnableLists(true))
            {
                $this->hForm
                    ->addDiv(
                        'hEditorDocumentLists',
                        'Lists'
                    )
                    ->addFieldset(
                        'Lists',
                        '100%',
                        '150px,200px,'
                    )
                    ->addInputLabel(
                        'hListFiles',
                        'List Files: -L'
                    )
                    ->addTableCell(
                        $this->getTemplate(
                            'List',
                            array(
                                'hLists' => $this->hListDatabase->getListsForTemplate(0, $fileId)
                            )
                        )
                    )
                    ->addTableCell(
                        $this->getTemplate('List File Buttons')
                    );
            }

            if ($this->hEditorDocumentEnableCalendar(true))
            {
                array_push(
                    $exclude,
                    'hCalendarId'
                );

                array_push(
                    $exclude,
                    'hCalendarCategoryId'
                );

                $date = (int) $this->hCalendarDatabase->getFileDate($fileId);

                $this->hForm
                    ->addDiv(
                        'hEditorDocumentCalendar',
                        'Calendar'
                    )
                    ->addFieldset(
                        'Calendar',
                        '100%',
                        '150px,'
                    )
                    ->addSelectInput(
                        'hFileCalendarId',
                        'c:Calendar(s): -L',
                        $this->hCalendarDatabase->getCalendars(),
                        5,
                        'multiple',
                        $this->hCalendarDatabase->getFileCalendars($fileId)
                    )
                    ->addRadioInput(
                        'hFileCalendarCategoryId',
                        'y:Calendar Category: -L',
                        $this->hCalendarDatabase->getCategories(),
                        $this->hCalendarDatabase->getFileCategories($fileId)
                    )
                    ->addTextInput(
                        'hFileCalendarDate',
                        'a:Date:',
                        25,
                        $date > 0? date('m/d/y h:i:s a', $date) : ''
                    );
            }

            $plugin = $this->hFileDatabase->get('hPlugin');

            if ($this->hEditorDocumentEnablePlugins(true))
            {
                $this->hForm
                    ->addDiv(
                        'hEditorDocumentPlugins',
                        'Plugin'
                    )
                    ->addFieldset(
                        'Plugin',
                        '100%',
                        '150px,auto'
                    )
                    ->addSelectInput(
                        'hPlugin',
                        'n:Plugin: -L',
                        $this->hPluginDatabase->getPlugins(),
                        10,
                        $plugin
                    )
                    ->addFieldset(
                        'Private Plugin',
                        '100%',
                        '150px,auto'
                    )
                    ->addSelectInput(
                        'hPluginPrivate',
                        'v:Private Plugin: -L',
                        $this->hPluginDatabase->getPlugins(true),
                        10,
                        $plugin
                    );
            }
            else
            {
                $this->hForm
                    ->addHiddenInput(
                        'hPlugin',
                        $plugin
                    )
                    ->addHiddenInput(
                        'hPluginPrivate',
                        $plugin
                    );
            }

            if ($this->hEditorDocumentEnableHeaders(true))
            {
                $this->hForm
                    ->addDiv(
                        'hEditorDocumentHeaders',
                        'Headers'
                    )
                    ->addFieldset(
                        'Headers',
                        '100%',
                        '150px,'
                    )
                    ->addTextareaInput(
                        'hFileCSS',
                        'c:CSS: -L',
                        '60,10',
                        $this->hFileDatabase->get('hFileCSS')
                    )
                    ->addTextareaInput(
                        'hFileJavaScript',
                        'j:JavaScript: -L',
                        '60,10',
                        $this->hFileDatabase->get('hFileJavaScript')
                    );
            }
            else
            {
                $this->hForm
                    ->addHiddenInput(
                        'hFileCSS',
                        $this->hFileDatabase->get('hFileCSS')
                    )
                    ->addHiddenInput(
                        'hFileJavaScript',
                        $this->hFileDatabase->get('hFileJavaScript')
                    );
            }

            if ($this->hEditorDocumentEnableAdvancedProperties(true))
            {
                $this->hForm
                    ->addDiv(
                        'hEditorDocumentAdvancedProperties',
                        'Advanced'
                    )
                    ->addFieldset(
                        'File',
                        '100%',
                        '150px,auto'
                    )
                    ->addTextInput(
                        'hFileMIME',
                        'MIME Type:',
                        25,
                        $this->hFileDatabase->get('hFileMIME')
                    );

                if ($this->hEditorDocumentEnableOwner(true))
                {
                    $this->hForm->addTextInput(
                        'hFileOwner',
                        'w:Owner:',
                        10,
                        $this->user->getUserName(
                            $this->hFileDatabase->get('hUserId')
                        )
                    );
                }

                if ($this->hEditorDocumentEnableSortIndex(true))
                {
                    $this->hForm->addTextInput(
                        'hFileSortIndex',
                        'u:Menu Position:',
                        2,
                        $this->hFileDatabase->get('hFileSortIndex')
                    );
                }

                $this->hForm
                    ->addTableCell('')
                    ->addCheckboxInput(
                        'hFileIsSystem',
                        'Is Framework File?',
                        (int) $this->hFileDatabase->get('hFileIsSystem') == 1
                    )
                    ->addTextInput(
                        'hFileSystemPath',
                        'File System Path:',
                        50,
                        $this->hFileDatabase->get('hFileSystemPath')
                    )
                    ->addTableCell('')
                    ->addCheckboxInput(
                        'hFileDownload',
                        'Force Download Dialogue?',
                        (int) $this->hFileDatabase->get('hFileDownload') == 1
                    );


    //            $this->hForm->addFieldset('Aliases', '100%', '150px,auto');
    //            $this->hForm->addTextInput('hFileAlias:', 's:Alias:', 25);
                if ($this->hEditorDocumentEnableTemplate(true))
                {
                    array_push($exclude, 'hTemplatePath');

                    $this->hForm
                        ->addFieldset(
                            'Template',
                            '100%',
                            '150px,'
                        )
                        ->addTextInput(
                            'hTemplatePath',
                            'm:Template Path:',
                            25,
                            $this->hFileDatabase->get('hTemplatePath')
                        )
                        ->addTableCell('')
                        ->addCheckboxInput(
                            'hFileExcludeTemplate',
                            'No Template?',
                            $this->hFileDatabase->variableExists('hTemplatePath') && is_null($this->hFileDatabase->get('hTemplatePath'))
                        );
                }

                $this->hForm->addFieldset(
                    'Variables',
                    '100%',
                    '150px,200px,auto',
                    'hEditorVariables'
                );

                $variables = $this->hFileDatabase->getVariables($exclude);

                if (count($variables))
                {
                    $variableCounter = 0;

                    foreach ($variables as $variable => $value)
                    {
                        $this->hForm
                            ->addTextInput(
                                'hFileVariable:hFileVariable-'.$variableCounter,
                                's:Variable:',
                                25,
                                $variable
                            )
                            ->addTextInput(
                                'hFileValue:hFileValue-'.$variableCounter,
                                nil,
                                25,
                                $value
                            );

                        $variableCounter++;
                    }
                }
                else
                {
                    $this->hForm
                        ->addTextInput(
                            'hFileVariable:hFileVariable-0',
                            's:Variable:',
                            25
                        )
                        ->addTextInput(
                            'hFileValue:hFileValue-0',
                            nil,
                            25
                        );
                }

                $this->hForm
                    ->addTableCell('')
                    ->addTableCell(
                        $this->getTemplate('Variable Buttons'),
                        2
                    );
            }
            else
            {
                $variables = $this->hFileDatabase->getVariables($exclude);

                // Dump variables in hidden fields...
                if (count($variables))
                {
                    $variableCounter = 0;

                    foreach ($variables as $variable => $value)
                    {
                        $this->hForm
                            ->addHiddenInput(
                                'hFileVariable:hFileVariable-'.$variableCounter,
                                $variable
                            )
                            ->addHiddenInput(
                                'hFileValue:hFileValue-'.$variableCounter,
                                $value
                            );

                        $variableCounter++;
                    }
                }
            }
        }

        $this->hDialogue->newDialogue('hEditorDocument');
        $this->hDialogueFullScreen = true;
        $this->hDialogue->setForm($this->hForm);

        //$this->hDialogueAction = $this->hFilePath;

        if ($this->hEditorDocumentDisableTabs(false))
        {
            $this->hDialogueAutoTabs = false;
            $this->hDialogueEnableTabs = false;

            $this->hFileCSS .= $this->getTemplate('Disable Tabs');
        }

        $this->hFileDocument .=
            $this->hDialogue->getDialogue().
            $this->getTemplate('Buttons');

        $this->hFileDocumentExpandIdWithLastModified = true;
    }

    private function getMode($extension)
    {
        switch ($extension)
        {
            case 'php':
            {
                return 'php';
            }
            case 'json':
            case 'js':
            {
                return 'javascript';
            }
            case 'rb':
            {
                return 'ruby';
            }
            case 'css':
            {
                return 'css';
            }
            case 'htm':
            case 'html':
            {
                return 'html';
            }
            case 'xml':
            {
                return 'xml';
            }
            default:
            {
                return 'text';
            }
        }
    }

    private function setLite()
    {
        if (!$this->inGroup('root') && $this->hEditorDocumentLite(true) || $this->hEditorDocumentForceLite(false))
        {
            // Disable if not explicitly enabled.
            if (!$this->hEditorDocumentEnableLists(false))
            {
                $this->hEditorDocumentEnableLists = false;
            }

            if (!$this->hEditorDocumentEnableCalendar(false))
            {
                $this->hEditorDocumentEnableCalendar = false;
            }

            if (!$this->hEditorDocumentEnableHeaders(false))
            {
                $this->hEditorDocumentEnableHeaders = false;
            }

            if (!$this->hEditorDocumentEnablePlugins(false))
            {
                $this->hEditorDocumentEnablePlugins = false;
            }

            if (!$this->hEditorDocumentEnableAdvancedProperties(false))
            {
                $this->hEditorDocumentEnableAdvancedProperties = false;
            }

            if (!$this->hEditorDocumentEnableSubTitle(false))
            {
                $this->hEditorDocumentEnableSubTitle = false;
            }

            if (!$this->hEditorDocumentEnableBreadcrumbTitle(false))
            {
                $this->hEditorDocumentEnableBreadcrumbTitle = false;
            }

            if (!$this->hEditorDocumentEnableParentId(false))
            {
                $this->hEditorDocumentEnableParentId = false;
            }

            if (!$this->hEditorDocumentEnableMenuTitle(false))
            {
                $this->hEditorDocumentEnableMenuTitle = false;
            }

            if (!$this->hEditorDocumentEnableSideboxTitle(false))
            {
                $this->hEditorDocumentEnableSideboxTitle = false;
            }

            if (!$this->hEditorDocumentEnableTooltipTitle(false))
            {
                $this->hEditorDocumentEnableTooltipTitle = false;
            }
        }
    }
}

?>