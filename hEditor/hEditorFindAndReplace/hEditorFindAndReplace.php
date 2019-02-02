<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Editor Find and Replace
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

class hEditorFindAndReplace extends hPlugin {

    private $hApplicationForm;
    private $hForm;

    private $fileTypes = array(
        'conf'     => 'conf',
        'css'      => 'css',
        'htm'      => 'htm',
        'html'     => 'html',
        'js'       => 'js',
        'json'     => 'json',
        'php'      => 'php',
        'rb'       => 'rb',
        'sql'      => 'sql',
        'xml'      => 'xml'
    );

    public function hConstructor()
    {
        $this->prepareApplication(
            array(
                'title' => $this->hServerHost.' Find and Replace',
                'formTemplate' => true
            )
        );

        $this->getPluginFiles();

        $this->hForm = $this->library('hForm');

        $this->hFileDocument =
            $this->getForm().
            $this->getTemplate('Find and Replace');
    }

    private function getForm()
    {
        $this->hForm
            ->addDiv('hEditorFindAndReplaceDiv')
            ->addFieldset(
                'Find and Replace:',
                '100%',
                '200px,'
            )
            ->addTextInput(
                ':hEditorFind:find',
                'Find:',
                75
            )
            ->addRadioInput(
                ':hEditorFindType:type',
                'Match: -L',
                array(
                    'regexp'   => 'Regular Expression (preg)',
                    'strstr'  => 'Substring (Case-Sensitive)',
                    'stristr' => 'Substring (Case-Insensitive)'
                ),
                'stristr'
            )
            ->addTextInput(
                ':hEditorReplace:replace',
                'Replace:',
                75
            )

            ->addFieldset(
                'Target Folder(s):',
                '100%',
                '200px,'
            )

            ->addData(
                'hEditorFindAndReplaceFolder',
                'Select Folder(s): -L',
                $this->getTemplate('Folder')
            )

            ->addFieldset(
                'Limit to File Names Matching:',
                '100%',
                '200px,'
            )

            ->addTextInput(
                ':hEditorFileName:matchFileName',
                'File Name:',
                75
            )
            ->addRadioInput(
                'matchFileNameType',
                'Match File Name: -L',
                array(
                    'exactly'  => 'Exactly',
                    'regexp'   => 'Regular Expression (preg)',
                    'strstr'   => 'Substring (Case-Sensitive)',
                    'stristr'  => 'Substring (Case-Insensitive)'
                ),
                'stristr'
            )

            ->addFieldset(
                'Limit to File Types:',
                '100%',
                '200px,'
            )

            ->addSelectInput(
                ':hEditorIncludeFileType:includeFileTypes[]',
                'Include File Type(s): -L',
                $this->fileTypes,
                'multiple',
                5
            )
            ->addSelectInput(
                ':hEditorExcludeFileType:excludeFileTypes[]',
                'Exclude File Type(s): -L',
                $this->fileTypes,
                'multiple',
                5
            )
            ->addTableCell(
                $this->getTemplate('Buttons'),
                2
            );

        return $this->hForm->getForm('hEditorFindAndReplace');
    }
}

?>