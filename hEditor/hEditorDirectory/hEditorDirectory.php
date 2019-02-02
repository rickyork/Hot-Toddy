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

class hEditorDirectory extends hPlugin {

    private $hApplicationColumns;
    private $hForm;
    private $hDialogue;
    private $hFile;
    private $hFileIcon;

    private $path;

    public function hConstructor()
    {
        if (!$this->isSSLEnabled())
        {
            header('Location: https://'.$this->hServerHost.$this->href($this->hFilePath, $_GET));
            exit;
        }

        $this->hApplicationColumns = $this->library('hApplication/hApplicationColumns');
        $this->hForm = $this->library('hForm');
        $this->hDialogue = $this->library('hDialogue');
        $this->hFile = $this->library('hFile');

        if (isset($_GET['path']))
        {
            hString::safelyDecodeURL($_GET['path']);

            $this->hFile = $this->library('hFile');
            $this->hFile->query($_GET['path']);

            $hFileId = $this->hFile->fileId;

            $hFileName = basename($_GET['path']);
            $hFilePath = $_GET['path'];

            $this->path = $_GET['path'];
        }
        else
        {
            $this->fatal(
                'Unable to launch editor because no path was provided.',
                __FILE__,
                __LINE__
            );
        }

        $this->hFileTitle = "Editing Files in ".$hFileName;

        $this->hFileDocument = $this->hApplicationColumns->get(
            $this->getFiles(),
            $this->getForm()
        );
    }

    public function getFiles()
    {
        $hFiles = $this->hFile->getFiles($this->path);

        $files = array();

        foreach ($hFiles as $hFile)
        {
            if ($hFile['hFileMIME'] == 'text/html')
            {
                $files['hFileId'][] = $hFile['hFileId'];

                $files['hFileTitle'][] = $this->hFileHeadingTitle(
                    $hFile['hFileTitle'],
                    $hFile['hFileId']
                );

                $files['hFilePath'][] = $hFile['hFilePath'];
            }
        }

        return $this->getTemplate(
            'Files',
            array(
                'files' => $files
            )
        );
    }

    public function getForm()
    {
        $this->hForm
            ->addDiv(
                'hEditorDirectoryFormDivDocument',
                'Document'
            )
            ->addFieldset(
                'Meta Data:',
                '100%',
                '150px,'
            )
            ->addTextInput(
                'hFileTitle',
                'Title:',
                25
            );

        if ($this->hEditorDocumentEnableSubTitle(false))
        {
            $this->hForm->addTextInput(
                'hFileSubTitle',
                'Subtitle:',
                25
            );
        }

        $this->hForm->addTextInput(
            'hFileHeadingTitle',
            'Heading Title:',
            25
        );

        if ($this->hFileBreadcrumbsEnabled(false))
        {
            $this->hForm->addTextInput(
                'hFileBreadcrumbTitle',
                'Breadcrumb Title:',
                25
            );
        }

        if ($this->hEditorDocumentEnableMenuTitle(false))
        {
            $this->hForm->addTextInput(
                'hFileMenuTitle',
                'Menu Title:',
                25
            );
        }

        if ($this->hEditorDocumentEnableSideboxTitle(false))
        {
            $this->hForm->addTextInput(
                'hFileSideboxTitle',
                'Sidebox Title:',
                25
            );
        }

        if ($this->hEditorDocumentEnableTooltipTitle(false))
        {
            $this->hForm->addTextInput(
                'hFileTooltipTitle',
                'Tooltip Title:',
                25
            );
        }

        if ($this->hFileBreadcrumbsEnabled(false))
        {
            $this->hForm->addInputLabel(
                'hFileParentId',
                'Parent Document: -L'
            );

            $this->hForm->addTableCell(

            );
        }
        else
        {
            $this->hForm->addHiddenInput(
                'hFileParentId',
                0
            );
        }

        $this->hForm
            ->addTextareaInput(
                'hFileDescription',
                'Description: -L',
                '50,3'
            )
            ->addTextareaInput(
                'hFileKeywords',
                'K:Keywords: -L',
                '50,3'
            )

            ->addFieldset(
                'Content:',
                '100%',
                '100%',
                'hEditorDirectoryFormFieldsetContent'
            )

            ->addWYSIWYGInput(
                 'hFileDocument',
                '',
                nil,
                '60,15',
                '100%,500px',
                array(),
                $this->hEditorDocumentWYSIWYGToolbar('BasicCMS')
            )

            ->addDiv(
                'hEditorDirectoryFormDivPreview',
                'Preview'
            );

        //$this->hForm->setVariable('hFormAppendInput', $this->getTemplate('Calendar Icon'));
        //$this->hForm->addTextInput('SingleSrcCampaignEnd', 'End Date:', 10);

        $this->hDialogue->newDialogue('hEditorDirectoryDocument');
        $this->hDialogue->setForm($this->hForm);

        $this->hDialogueDisableFocus = true;
        $this->hDialogueShadow = false;
        $this->hDialogueTitlebar = false;

        //$this->hDialogueContentAppend = $this->getTemplate('Document Frame');

        return $this->hDialogue->getDialogue();

    }
}

?>