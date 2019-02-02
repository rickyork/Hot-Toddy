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
# <h1>Calendar Event Form API</h1>
#
# @end

class hCalendarEventFormLibrary extends hPlugin {

    private $hForm;
    private $hCalendarDatabase;
    private $hCategoryDatabase;

    public function hConstructor()
    {
        $this->hForm = $this->library('hForm');
        $this->hCalendarDatabase = $this->database('hCalendar');
        $this->hCategoryDatabase = $this->database('hCategory');

        if ($this->inGroup('root') && $this->hCalendarRootTextEditorOverride(true) && !$this->userAgent->isTrident)
        {
            $this->hCalendarEnableTextEditor = true;
        }

        if ($this->hCalendarEnableTextEditor(false))
        {
            $this->getPluginJavaScript('/Library/Ace/src/ace', true);
            $this->getPluginJavaScript('/Library/Ace/src/mode-html', true);
            $this->getPluginJavaScript('/Library/Ace/src/theme-textmate', true);
        }

        $this->getPluginFiles();
    }

    public function get()
    {
        # @return string

        # @description
        # <h2>Getting the Calendar Event Form</h2>
        # <p>
        #
        # </p>
        # @end

        $optional = $this->getTemplate('Optional');
        $separator = $this->getTemplate('Form Field Separator');

        $this->hForm = $this->library('hForm');
        $form = &$this->hForm;

        $form->hFormElement = false;

        $form
            ->addDiv('hCalendarEventFormContent')
            ->addFieldset('Content', '100%', '100%')

            ->addTextInput('hFileTitle', 'Title:', '')
            ->addTextInput('hFileHeadingTitle', 'Heading/Link Title: <i>Optional</i>', '')

            ->addData(
                'hFileThumbnail',
                'Thumbnail / Facebook Thumbnail:',
                $this->getTemplate(
                    'Thumbnail'
                )
            );

        switch (true)
        {
            case $this->hCalendarEnableTextEditor(false):
            {
                $form
                    ->addInputLabel('hFileDocument', 'Document: (Full Post) -L')
                    ->addTableCell(
                        $this->getTemplate(
                            'Text Editor',
                            array(
                                'id' => 'hFileDocument'
                            )
                        )
                    )
                    ->addTextareaInput('hFileDescription', 'Summary: -L', '60,15');

                break;
            }
            case $this->hCalendarEnableWYSIWYG(true):
            {

                // $attributes, $label, $value = null, $size = null, $dimensions = null, $plugins = null, $toolbar = null, $styles = null, $configuration = null

                $form
                    ->addWYSIWYGInput(
                        array(
                            'id' => 'hFileDocument',
                            'name' => 'hFileDocument'
                        ),
                        'Document: (Full Post): -L',
                        "",
                        '60,15',
                        '100%,300px',
                        array(),
                        $this->hCalendarWYSIWYGToolbarType('BasicCMS')
                    )
                    ->addWYSIWYGInput(
                        array(
                            'id' => 'hFileDescription',
                            'name' => 'hFileDescription'
                        ),
                        'Summary: -L',
                        '',
                        '60,15',
                        '100%,225px',
                        '',
                        $this->hCalendarWYSIWYGToolbarType('BasicCMS')
                    );
                break;
            }
            default:
            {
                $this->setAttribute('wrap', 'off')
                     ->addTextareaInput('hFileDocument', 'Document: (Full Post): -L', '60,15')
                     ->addTextareaInput('hFileDescription', 'Summary: -L', '60,15');
            }
        }

        if ($this->hCalendarLinkEnabled(true))
        {
            $form->addTextInput('hCalendarLink', 'Link:', '');
        }

        #$form->addFieldset('Attached Document', '100%', '100%', 'hCalendarEventFormAttachedDocument');
        #$form->addData('hCalendarEventAttachedDocument', 'Attached Document:', "");
        #$form->addFileInput('hCalendarEventReplaceFile', 'Replace Attached Document:', 25);
        if ($this->hCalendarImportDocumentEnabled(false))
        {
            $form
                ->addFieldset(
                    'Import Document',
                    '100%', '100%',
                    'hCalendarEventFormImportDocument'
                )
                ->addTableCell(
                    $this->getTemplate('Import Document')
                );
        }

        if ($this->hCalendarAttachMovie(false))
        {
            if ($this->hCalendarAttachSWFObject(false))
            {
                $form->addHiddenInput('hCalendarSWFObject', 1);
            }

            $form
                ->addInputLabel('hFileMovie', 'Movie:')
                ->addTableCell(
                    $this->getTemplate(
                        'Movie',
                        array(
                            'hFileMovieId'    => 0,
                            'hFileMovieTitle' => '',
                            'hFileIconPath'   => '',
                            'hFileMoviePath'  => ''
                        )
                    )
                );
        }

        #$form->addFileInput('hCalendarEventUploadFile', 'Attach Document:', 25);

        $form
            ->addDiv('hCalendarEventFormProperties')
            ->addFieldset('Calendar', '100%', '20%,80%');

        if ($this->hCalendarEnabled(true))
        {
            $form
                ->addSelectInput(
                    array(
                        'id' => 'hCalendarId',
                        'name' => 'hCalendarId[]',
                        'multiple' => 'multiple'
                    ),
                    'Calendar: -L',
                    $this->hCalendarDatabase->getCalendars('rw'),
                    5
                )
                ->addTableCell()
                ->addTableCell(
                    $this->getTemplate(
                        'Add Remove Controls',
                        array(
                            'hCalendarControlAdd' => 'hCalendarAdd',
                            'hCalendarControlRemove' => 'hCalendarRemove'
                        )
                    )
                );
        }
        else if ($this->hCalendarId(1))
        {
            $form->addHiddenInput('hCalendarId', $this->hCalendarId(1));
        }

        $form
            ->addHiddenInput('hCalendarCategoryId', $this->hCalendarCategoryId(3))
            ->setVariable(
                'hFormAppendInput',
                $this->getTemplate(
                    'Calendar Icon',
                    array(
                        'hCalendarEventFormIcon' => 'hCalendarDateIcon'
                    )
                )
            )
            ->addTextInput('hCalendarDate', 'Date Posted:', '');

        if ($this->hCalendarTimeEnabled(true))
        {
            $minutes = range(0, 59);
            array_walk($minutes, array($this, 'padMinutes'));

            $form
                ->addFieldset('Time', '100%', '20%,1%,1%,1%,*')
                ->defineCell('white-space: nowrap;')
                ->setVariable(
                    'hFormAppendInput',
                    $this->getTemplate(
                        'Calendar Icon',
                        array(
                            'hCalendarEventFormIcon' => 'hCalendarBeginTimeIcon'
                        )
                    ).
                    ' @ '
                )

                ->addTextInput('hCalendarBeginTime', "Event Begins:", '')

                ->defineCell('white-space: nowrap;')

                ->setVariable('hFormAppendInput', ' :')
                ->setVariable('hFormOptionLabelIsValue', true)
                ->addSelectInput('hCalendarBeginTimeHour', null, range(1, 12))

                ->setVariable('hFormOptionLabelIsValue', true)
                ->addSelectInput('hCalendarBeginTimeMinute', null, $minutes)

                ->addSelectInput(
                    'hCalendarBeginTimeMeridiem',
                    null,
                    array(
                        'AM' => 'AM',
                        'PM' => 'PM'
                    )
                )

                ->defineCell('white-space: nowrap;')

                ->addTextInput('hCalendarEndTime', "Event Ends:", '')

                ->defineCell('white-space: nowrap;')

                ->setVariable('hFormAppendInput', ' :')
                ->setVariable('hFormOptionLabelIsValue', true)
                ->addSelectInput('hCalendarEndTimeHour', null, range(1, 12))

                ->setVariable('hFormOptionLabelIsValue', true)
                ->addSelectInput('hCalendarEndTimeMinute', null, $minutes)
                ->addSelectInput(
                    'hCalendarEndTimeMeridiem',
                    null,
                    array(
                        'AM' => 'AM',
                        'PM' => 'PM'
                    )
                );
        }

        $form
            ->addFieldset('Make Event Available', '100%', '20%,80%')

            ->setVariable(
                'hFormAppendInput',
                $this->getTemplate(
                    'Calendar Icon',
                    array(
                        'hCalendarEventFormIcon' => 'hCalendarBeginIcon'
                    )
                )
            )

            ->addTextInput('hCalendarBegin', "Publish Date:{$optional}", '')

            ->setVariable(
                'hFormAppendInput',
                $this->getTemplate(
                    'Calendar Icon',
                    array(
                        'hCalendarEventFormIcon' => 'hCalendarEndIcon'
                    )
                )
            )

            ->addTextInput('hCalendarEnd', "Expiration Date:{$optional}", '')

            ->addFieldset('File', '100%', '20%,80%');

        if ($this->hCalendarUserNameEnabled(true))
        {
            $form
                ->setVariable('hFormAppendInput', $this->getTemplate('User Icon'))
                ->addTextInput('hUserName', 'Author:', 15, '');
        }

        $this->hCategoryDatabase->setDatabaseReturnFormat('getAssociativeArray');

        if ($this->hCalendarFileCategoryId(null) !== null || $this->hCalendarTagCategoryId(null))
        {
            $form->addTableCell($separator, 2);
        }

        if ((int) $this->hCalendarFileCategoryId(-1) >= 0)
        {
            if ($this->hCalendarFileCategoriesToExclude(null))
            {
                call_user_func_array(
                    array(
                        $this->hCategoryDatabase,
                        'setExclusionCategories'
                    ),
                    is_array($this->hCalendarFileCategoriesToExclude)? $this->hCalendarFileCategoriesToExclude : explode(',', $this->hCalendarFileCategoriesToExclude)
                );
            }

            $categories = $this->hCategoryDatabase->getCategories((int) $this->hCalendarFileCategoryId);

            $filteredCategories = array();

            foreach ($categories as $categoryId => $categoryName)
            {
                if ($this->hCategories->hasPermission($categoryId, 'rw'))
                {
                    $filteredCategories[$categoryId] = $categoryName;
                }
            }

            // Get categories
            $form
                ->addSelectInput(
                    array(
                        'id' =>'hCalendarFileCategories',
                        'name' => 'hCalendarFileCategories[]',
                        'multiple' => 'multiple'
                    ),
                    $this->hCalendarFileCategoriesLabel('Categories: -L'),
                    $filteredCategories,
                    5
                )
                ->addTableCell()
                ->addTableCell(
                    $this->getTemplate(
                        'Add Remove Controls',
                        array(
                            'hCalendarControlAdd' => 'hCalendarFileCategoryAdd',
                            'hCalendarControlRemove' => 'hCalendarFileCategoryRemove'
                        )
                    )
                )
                ->addHiddenInput(
                    'hCalendarFileCategoryId',
                    (string) $this->hCalendarFileCategoryId('0')
                );
        }

        if ((int) $this->hCalendarTagCategoryId(-1) >= 0)
        {
            $form
                ->addSelectInput(
                    array(
                        'id' => 'hCalendarTagCategories',
                        'name' => 'hCalendarTagCategories[]',
                        'multiple' => 'multiple'
                    ),
                    $this->hCalendarCategoryTagsLabel('Tags: -L'),
                    $this->hCategoryDatabase->getCategories((int) $this->hCalendarTagCategoryId),
                    5
                )
                ->addTableCell()
                ->addTableCell(
                    $this->getTemplate(
                        'Add Remove Controls',
                        array(
                            'hCalendarControlAdd' => 'hCalendarTagCategoryAdd',
                            'hCalendarControlRemove' => 'hCalendarTagCategoryRemove'
                        )
                    )
                )
                ->addHiddenInput(
                    'hCalendarTagCategoryId',
                    (string) $this->hCalendarTagCategoryId('0')
                );
        }

        $form
            ->addHiddenInput(
                'hCalendarPathEnabled',
                (int) $this->hCalendarPathEnabled(true)
            )
            ->addHiddenInput('hFileName', '')
            ->addHiddenInput(
                'hDirectoryPath',
                $this->hCalendarDirectoryPath('')
            )
            ->addHiddenInput('hFileReplaceExisting', '');

        if ($this->hCalendarJobCompanyEnabled(true))
        {
            $form->addTextInput('hCalendarJobCompany', 'Job Company:', 50);
        }

        if ($this->hCalendarJobLocationEnabled(true))
        {
            $form->addTextInput('hCalendarJobLocation', 'Job Location:', 50);
        }

        if ($this->hCalendarCommentsEnabled(true))
        {
            $form
                ->addTableCell('')
                ->addCheckboxInput('hFileComments', 'Enable Comments?');
        }

        if ($this->hCalendarWorldReadEnabled(true))
        {
            $form
                ->addTableCell($separator, 2)
                ->addTableCell('')
                ->addCheckboxInput('hUserPermissionWorldRead', 'Make Public?');
        }

        $form->addHiddenInput('hFileId', 0);

        return $form->getForm();
    }

    public function padMinutes(&$item)
    {
        # @return void

        # @description
        # <h2>Padding Minutes</h2>
        # <p>
        #
        # </p>
        # @end

        $item = str_pad($item, 2, '0', STR_PAD_LEFT);
    }
}

?>