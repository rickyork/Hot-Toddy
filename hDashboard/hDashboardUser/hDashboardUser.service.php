<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Service
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

class hDashboardUserService extends hService {

    private $hUserDatabase;
    private $hContactDatabase;
    private $hDashboardUser;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('Website Administrators') && !$this->inGroup('Contact Administrators'))
        {
            $this->JSON(-1);
            return;
        }

        $this->hDashboardUser = $this->library('hDashboard/hDashboardUser');
        $this->hUserDatabase = $this->database('hUser');
        $this->hContactDatabase = $this->database('hContact');
    }

    public function get()
    {
        $this->JSON(
            $this->hDashboardUser->getUsers(
                isset($_GET['letter'])? $_GET['letter'] : nil,
                isset($_GET['group'])? $_GET['group'] : nil
            )
        );
    }

    public function getUser()
    {
        if (!isset($_GET['userId']))
        {
            $this->JSON(-5);
            return;
        }

        $userId = (int) $_GET['userId'];
        $contactId = $this->user->getContactId($userId);
        $contact = $this->contact->getRecord($contactId);

        $this->JSON(
            array_merge(
                $contact,
                array(
                    'hUserName' => $this->user->getUserName($userId),
                    'hUserEmail' => $this->user->getUserEmail($userId),
                    'hUserGroups' => $this->hUserDatabase->getUserGroups($userId, 'select')
                )
            )
        );
    }

    public function save()
    {
        $userId = (int) $_GET['userId'];

        $userId = $this->hUserDatabase->save(
            $userId,
            $_POST['hUserName'],
            $_POST['hUserEmail'],
            !empty($_POST['hUserPassword'])? $_POST['hUserPassword'] : nil
        );

        if ($userId <= 0)
        {
            $this->JSON($userId);
            return;
        }

        $contactId = $this->hContactDatabase->save(
            array(
                'hContactFirstName' => $_POST['hContactFirstName'],
                'hContactLastName' => $_POST['hContactLastName'],
                'hContactCompany' => $_POST['hContactCompany'],
                'hContactTitle' => $_POST['hContactTitle'],
                'hContactDepartment' => $_POST['hContactDepartment'],
                'hContactWebsite' => $_POST['hContactWebsite'],
                'hContactGender' => $_POST['hContactGender']
            ),
            1, $userId, $this->user->getContactId($userId)
        );

        $this->hUserDatabase->removeUserFromGroups($userId);

        if (isset($_POST['hUserGroups']) && is_array($_POST['hUserGroups']))
        {
            $this->hUserDatabase->addUserToGroups($_POST['hUserGroups'], $userId);
        }

        $this->JSON(
            array(
                'userId' => $userId,
                'contactId' => $contactId
            )
        );
    }

    public function delete()
    {
        if (!isset($_GET['userId']))
        {
            $this->JSON(-5);
            return;
        }

        $userId = (int) $_GET['userId'];
        $contactId = $this->user->getContactId($userId);

        $this->hContactDatabase->delete($contactId);
        $this->hUserDatabase->delete($userId);

        $this->JSON(1);
    }

    public function enable()
    {
        if (!isset($_GET['userId']))
        {
            $this->JSON(-5);
            return;
        }

        $userId = (int) $_GET['userId'];

        $this->hUserDatabase->removeUserFromGroup(
            'Disabled User Accounts',
            $userId
        );

        $this->JSON(1);
    }

    public function disable()
    {
        if (!isset($_GET['userId']))
        {
            $this->JSON(-5);
            return;
        }

        $userId = (int) $_GET['userId'];

        $this->hUserDatabase->addUserToGroup(
            'Disabled User Accounts',
            $userId
        );

        $this->JSON(1);
    }

    public function getGroup()
    {
        if (!isset($_GET['userId']))
        {
            $this->JSON(-5);
            return;
        }

        $userId = (int) $_GET['userId'];

        $this->JSON(
            array(
                'hUserName' => $this->user->getUserName($userId),
                'hUserEmail' => $this->user->getUserEmail($userId)
            )
        );
    }

    public function saveGroup()
    {
        if (!isset($_GET['userId']))
        {
            $this->JSON(-5);
            return;
        }

        $userId = $this->hUserDatabase->save(
            $userId,
            $_POST['hUserName'],
            $_POST['hUserEmail'],
            $this->getRandomString(25, true, true)
        );

        $this->JSON();
    }
}

?>