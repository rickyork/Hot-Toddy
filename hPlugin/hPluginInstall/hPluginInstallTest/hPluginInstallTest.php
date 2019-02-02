<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Plugin Install Test
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

class hPluginInstallTest extends hPlugin {

    private $hForm;
    private $hPluginInstsall;

    public function hConstructor()
    {
        $this->plugin('hApplication');
        $this->getPluginCSS('hApplication');

        $this->hForm = $this->library('hForm');

        if (!isset($_POST['hPluginInstallTestForm']))
        {
            $this->hForm
                ->addDiv('hPluginInstallTestDiv')
                ->addFieldset(
                    'Plugin Test',
                    '100%',
                    '200px,'
                )
                ->addFileInput(
                    'hPluginInstallTestFile',
                    'Plugin:'
                )
                ->addTableCell('')
                ->addSubmitButton(
                    'hPluginInstallTestSubmit',
                    'Submit'
                );

            $this->hFileDocument = $this->hForm->getForm('hPluginInstallTestForm');
        }
        else
        {
            $this->hPluginInstall = $this->library(
                'hPlugin/hPluginInstall',
                array(
                    'path' => $_FILES['hPluginInstallTestFile']['tmp_name'],
                    'name' => $_FILES['hPluginInstallTestFile']['name'],
                    'mime' => $_FILES['hPluginInstallTestFile']['type']
                )
            );
        }
    }
}

?>