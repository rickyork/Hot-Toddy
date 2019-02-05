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

class hUserPermissionService extends hService {

    private $hUserPermission;

    public function hConstructor()
    {
        $this->hUserPermission = $this->library('hUser/hUserPermission');
    }

    public function save()
    {
        # This method hands off to saveForm, which iteself performs
        # authentication and validation.

        $resourceId = (int) $this->get('resourceId');
        $resourceKey = $this->get('resourceKey');

        $owner = $this->post('owner', '');
        $world = $this->post('world', '');

        if (empty($resourceId) || empty($resourceKey))
        {
            $this->JSON(-5);
            return;
        }

        $users = array();

        if (isset($_POST['users']) && is_array($_POST['users']))
        {
            foreach ($_POST['users'] as $userName => $level)
            {
                $users[addslashes($userName)] = $level;
            }
        }

        $groups = array();

        if (isset($_POST['groups']) && is_array($_POST['groups']))
        {
            foreach ($_POST['groups'] as $userName => $level)
            {
                $groups[addslashes($userName)] = $level;
            }
        }

        $this->JSON(
            $this->hUserPermission->saveForm(
                $resourceId,
                $resourceKey,
                $owner,
                $world,
                $users,
                $groups
            )
        );
    }
}

?>