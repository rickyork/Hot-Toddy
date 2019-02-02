<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Plugin Template
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

class hPluginTemplate extends hPlugin {

    private $hFile;
    private $hForm;
    private $hPluginDatabase;
    private $hPluginTemplate;

    public function hConstructor()
    {
        $this->plugin('hApplication/hApplicationForm');

        $this->hPluginDatabase = $this->database('hPlugin');
        $this->hPluginTemplate = $this->library('hPlugin/hPluginTemplate');

        $this->getPluginFiles();

        // Make a new plugin...
        //
        //
        if ($this->isLoggedIn())
        {
            if (!is_writable($this->hFrameworkPath.'/Hot Toddy'))
            {
                $this->warning('Plugins folder is not writable.', __FILE__, __LINE__);
            }

            if (!is_writable($this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins')))
            {
                $this->warning('Private folder is not writable.', __FILE__, __LINE__);
            }

            if ($this->inGroup('root'))
            {
                if (count($_POST))
                {
                    $this->hPluginTemplate->scaffold($_POST);
                    $this->hFileDocument .= $this->getTemplate('Plugin Created');
                }

                $this->getForm();
            }
            else
            {
                $this->notAuthorized();
            }
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function getForm()
    {
        $this->hForm = $this->library('hForm');

        $this->hForm->addDiv('hPluginTemplateDiv');

        $this->hForm->addFieldset('Plugin Properties', '100%', '200px,');

        $this->hForm->addTextInput('hPluginPath', 'New Plugin Path:');
        $this->hForm->addTextInput('hPluginName', 'New Plugin Name:');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginIsPrivate', 'Private Plugin?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginIsReusable', 'Reusable Plugin?');

        $this->hForm->addFieldset('Plugin Options', '100%', '200px,');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginPlugin', 'Plugin?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginListener', 'Listener?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginLibrary', 'Library?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginShell', 'Shell?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginDaemon', 'Daemon?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginCSS', 'CSS?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginIECSS', 'IE CSS?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginIE6CSS', 'IE6 CSS?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginIE7CSS', 'IE7 CSS?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginIE8CSS', 'IE8 CSS?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginJS', 'JS?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginHTML', 'HTML?');

        $this->hForm->addTableCell('');
        $this->hForm->addCheckboxInput('hPluginDocumentation', 'Documentation?');

        $this->hForm->addFieldset('Plugin Scaffolding', '100%', '200px,');

        $this->hForm->addRadioInput(
            'hPluginExtends',
            'Extends: -L',
            array(
                0 => 'hPlugin (Framework Overloading)',
                1 => 'hFrameworkApplication (Custom Overloading)'
            )
        );

        $this->hForm->addSelectInput(
            'hPluginLibraries',
            'Uses Libraries: -L',
            $this->hPluginDatabase->getLibraries(),
            10,
            'multiple'
        );

        $this->hForm->addSelectInput(
            'hPluginPrivateLibraries',
            'Uses Private Libraries: -L',
            $this->hPluginDatabase->getLibraries(true),
            10,
            'multiple'
        );

        $this->hForm->addFieldset('Listener Methods:', '100%', '200px,', 'hPluginListenerMethods');

        $this->hForm->addTextInput('hPluginListenerMethod:hPluginListenerMethod-0', 'Method:');
        $this->hForm->addTableCell('');
        $this->hForm->addTableCell(
            "<input type='submit' id='hPluginMethodAdd' value='Add Method' />\n".
            "<input type='submit' id='hPluginMethodRemove' value='Remove Method' />\n"
        );

        $this->hForm->addFieldset('Plugin Path', '100%', '200px,');

        $this->hForm->addTextInput('hDirectoryPath', 'Path:', 50);
        $this->hForm->addTextInput('hFileName', 'File Name:', 50);
        $this->hForm->addTextInput('hFileTitle', 'File Title:', 50);
        $this->hForm->addTextInput('hUserId', 'File Owner:', 50);

        $this->hForm->addTextInput('hUserPermissionsOwner', 'Owner Permissions:', 2);
        $this->hForm->addTextInput('hUserPermissionsWorld', 'World Permissions:', 2);

        $this->hForm->addTableCell('');
        $this->hForm->addTableCell(
            "<input type='submit' id='hPluginSave' value='Save' />\n".
            "<input type='submit' id='hPluginReset' value='Reset' />\n"
        );

        $this->hFileDocument .= $this->hForm->getForm();
    }
}

?>