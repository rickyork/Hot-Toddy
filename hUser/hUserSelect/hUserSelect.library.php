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

class hUserSelectLibrary extends hPlugin {

    public function hConstructor()
    {
        $this->getPluginFiles();
        $this->getPluginCSS('ie6');
    }

    public function get($id, $isGroup = false)
    {
        $contactAddressBookId = 1;

        if (isset($this->hUserSelectConfiguration) && isset($this->hUserSelectConfiguration->$id))
        {
            if (isset($this->hUserSelectConfiguration->$id->hContactAddressBookId))
            {
                $contactAddressBookId = (int) $this->hUserSelectConfiguration->$id->hContactAddressBookId;
            }
        }

        return $this->getTemplate(
            'Select',
            array(
                'userSelectId' => $id,
                'userGroupSwitch' => $isGroup? 'group' : 'user',
                'contactAddressBookId' => $contactAddressBookId
            )
        );
    }
}

?>