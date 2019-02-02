<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Select Library
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