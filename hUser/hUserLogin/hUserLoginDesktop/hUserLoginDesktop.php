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

class hUserLoginDesktop extends hPlugin {

    private $hDialogue;
    private $hForm;

    public function hConstructor()
    {
        $this->hFileCSS = '';

        $this->plugin('hApplication/hApplicationForm');

        $this->jQuery('Effects/Color');

        $this->hDialogue = $this->library('hDialogue');
        $this->hForm = $this->library('hForm');

        $this->getPluginFiles();

        $this->hDialogue->newDialogue('hUserLoginDesktop');
        $this->hDialogue->setForm($this->hForm);

        $this->hForm
            ->addDiv('hUserLoginDesktopDiv')
            ->addFieldset('', '100%', '80px,150px,')

            ->defineCell(2)
            ->addTextInput(
                'hUserName',
                'Username:',
                25
            )

            ->defineCell(2)
            ->addPasswordInput(
                'hUserPassword',
                'Password:',
                25
            );

        if ($this->hUserLoginDesktopMultipleEnabled(false))
        {
            $this->hForm
                ->defineCell(2)
                ->addTextInput(
                    'hFrameworkNickName',
                    'Nick Name:',
                    25,
                    ''
                )

                ->addTextInput(
                    'hFrameworkServer',
                    'Server:',
                    25,
                    'www.example.com'
                )
                ->addCheckboxInput(
                    'hFrameworkSSLEnabled',
                    'SSL?'
                )

                ->defineCell(2)
                ->addTextInput(
                    'hFrameworkPort',
                    'Port:',
                    4,
                    80
                )

                ->addTableCell('')
                ->defineCell(2)
                ->addCheckboxInput(
                    'hFrameworkKeychain',
                    'Save to Keychain?',
                    1
                );
        }

        $this->hForm
            ->addFieldset('', '100%', '100%', 'hUserDesktopProfiles')

            ->addTableCell(
                $this->getTemplate('Profile')
            );

        $this->hDialogueFullScreen = true;
        $this->hDialogueShadow = false;

        $this->hDialogueContentPrepend = $this->getTemplate(
            'Hot Toddy'
        );

        $this->hFileTitle = 'Hot Toddy';
        $this->hFileTitleAppend = '';
        $this->hFileTitlePrepend = '';
        $this->hFileDocument = $this->hDialogue->getDialogue();


    }
}

?>