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

class hEditorProperties extends hPlugin {

    private $hForm;
    private $hFile;
    private $hFileIcon;
    private $hFileDatabase;
    private $hCategoryDatabase;
    private $hTidy;

    public function hConstructor()
    {
        if ($this->isLoggedIn())
        {
            if (!isset($_GET['path']))
            {
                $this->warning(
                    'No path was specified.',
                    __FILE__,
                    __LINE__
                );

                return;
            }

            hString::safelyDecodeURL($_GET['path']);

            $this->plugin('hApplication/hApplicationForm');
            $this->plugin('hApplication/hApplicationStatus');

            $this->hForm = $this->library('hForm');
            $this->hFile = $this->library('hFile');
            $this->hFileIcon = $this->library('hFile/hFileIcon');
            $this->hFileDatabase = $this->database('hFile');
            $this->hFileTitle = 'Edit File Properties';

            if (isset($_GET['path']))
            {
                $this->hFile->query($_GET['path']);
                $this->hFileDatabase->setFileId($this->hFile->fileId);
            }

            $this->getPluginFiles();

            $this->getForm();
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function getForm()
    {
        $this->hForm
            ->addDiv('hEditorPropertiesDiv')
            ->addFieldset(
                'Properties',
                '100%',
                '100%',
                'hEditorProperties'
            )

            ->addHiddenInput(
                'hFileId',
                $this->hFileDatabase->hFileId
            )
            ->addHiddenInput(
                'path',
                dirname($_GET['path'])
            )

            ->addTextInput(
                'hFileTitle',
                'Title:',
                25,
                $this->hFileDatabase->get('hFileTitle')
            )
            ->addTextareaInput(
                'hFileKeywords',
                'Keywords:',
                '50,2',
                $this->hFileDatabase->get('hFileKeywords')
            );

        if ($this->hEditorPropertiesEnableDocument(false))
        {
            $document = htmlspecialchars_decode(
                $this->hFileDatabase->get('hFileDocument')
            );

            $document = $this->expandDocumentIds($document);

            $extension = $this->getExtension(
                $this->hFileDatabase->get('hFileName')
            );

            // Save a copy in the file system...
            if (($extension == 'html' || $extension == 'htm') && $this->hEditorPropertiesEnableTidy(false))
            {
                $this->hTidy = $this->library('hTidy');
                $document = $this->hTidy->getHTML($document);
            }

            $document = hString::encodeHTML($document);

            $this->hForm->addWYSIWYGInput(
                'hFileDocument',
                $this->hEditorPropertiesDocumentLabel('Content:'),
                $document, // Value
                '60,15',
                '98%,200px',
                array(),
                'Basic'
            );
        }

        $this->hCategoryDatabase = $this->database('hCategory');

        $this->hCategoryDatabase->setDatabaseReturnFormat('select');

        $categories = $this->hCategoryDatabase->getFileCategories($this->hFile->fileId);

        $html = '';

        foreach ($categories as $category)
        {
            $html .= $this->getTemplate(
                'Category',
                array(
                    'hCategoryId'    => $category['hCategoryId'],
                    'hCategoryName'  => $category['hCategoryName'],
                    'hDirectoryPath' => $this->getCategoryPath($category['hCategoryId']),
                    'hCategoryIsHidden' => substr($category['hCategoryName'], 0, 1) == '.'
                )
            );
        }

        $this->hForm
            ->addWYSIWYGInput(
                'hFileDescription',
                'Description:',
                $this->hFileDatabase->get('hFileDescription'), // Value
                '60,15',
                '98%,100px',
                array(),
                'Basic'
            )
            ->addFieldset(
                'Categories',
                '100%',
                '100%'
            )
            ->addTableCell(
                $this->getTemplate(
                    'Categories',
                    array(
                        'hCategories' => $html
                    )
                )
            )

            ->addFieldset(
                'Uploaded File',
                '100%',
                '150px,',
                'hEditorUploadedFile'
            )
            ->addFileInput(
                'hFileUpload',
                'Replace Uploaded File:',
                50
            )
            ->addDiv('hEditorPermissionsDiv');

        if ($this->hEditorPropertiesSetPermissions(false))
        {
            $this->hForm->addFieldset(
                'Control Access',
                '100%',
                '100%',
                'hEditorPermissions'
            );

            $permissions = $this->hFiles->getPermissions($this->hFile->fileId);

            $this->hForm->addCheckboxInput(
                'hUserPermissionsWorldRead',
                'Make Publicly Accessible?',
                (isset($permissions['hUserPermissionsWorld']) && strstr($permissions['hUserPermissionsWorld'], 'r')? 1 : 0)
            );

            if ($this->hEditorPropertiesGroups(null))
            {
                // Show only a subset of groups... and allow the user to only grant or revoke access to those specific groups.
                // Preserve any access granted to groups outside of the subset.
                //
                // Allow these groups read access only.
                $userGroups = explode(',', $this->hEditorPropertiesGroups);

                $options  = array();
                $selected = array();

                foreach ($userGroups as $userGroup)
                {
                    $userGroupId = $this->getGroupId($userGroup);

                    $options[$userGroupId] = $userGroup;

                    if (isset($permissions['hUserGroups']) && is_array($permissions['hUserGroups']) && array_key_exists($userGroupId, $permissions['hUserGroups']))
                    {
                        $selected[] = $userGroupId;
                    }
                }

                $this->hForm->addSelectInput(
                    ':hUserPermissionsGroups:hUserPermissionsGroups[]',
                    'Or Restrict Access to These Groups:',
                    $options,
                    10,
                    $selected,
                    'multiple'
                );
            }
            else
            {
                // Show and allow all groups.
            }

            // Other Groups...
            if ($this->hEditorPropertiesAdminGroups('Website Administrators'))
            {
                $userGroups = explode(',', $this->hEditorPropertiesAdminGroups('Website Administrators'));

                foreach ($userGroups as $userGroup)
                {
                    $this->hForm->addHiddenInput(
                        'hUserPermissionsWriteGroups[]',
                        $this->getGroupId($userGroup)
                    );
                }
            }
        }

        $this->hForm
            ->addFieldset(
                'Other',
                '100%',
                '100%',
                'hEditorOther'
            )
            ->addData(
                'hFileSize',
                'Size:',
                $this->hFile->getSize()
            )
            ->addData(
                'hFileIcon',
                'Icon:',
                $this->getTemplate(
                    'Icon',
                    array(
                        'hFileIconPath' => $this->hFileIcon->getFileIconPath($this->hFile->fileId, null, null, '128x128')
                    )
                )
            )
            ->setUploadAttributes(
                '/hFile/saveProperties?onSaveProperties='.urlencode('editor.properties.onSaveProperties'),
                'hEditorPropertiesFrame'
            );

        $this->hFileDocument =
            $this->hForm->getForm().
            $this->getTemplate('Buttons');
    }
}

?>