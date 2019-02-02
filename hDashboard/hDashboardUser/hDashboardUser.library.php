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

class hDashboardUserLibrary extends hPlugin {

    private $hUserDatabase;
    private $hPagination;

    public function hConstructor()
    {
        $this->hUserDatabase = $this->database('hUser');
        $this->hPagination = $this->library('hPagination');

        $this->hPagination->setResultsPerPage(20);
    }

    public function getUsers($letter, $group = nil)
    {
        $sql = $this->getTemplateSQL(
            array(
                'limit' => $this->hPagination->getLimit(),
                'letter' => $letter,
                'group' => $group
            )
        );

        $results = $this->hDatabase->getResults($sql);

        $count = $this->hDatabase->getResultCount();

        $this->hPagination->setResultCount($count);

        $enabled = array();
        $disabled = array();

        foreach ($results as $result)
        {
            if ($this->inGroup('Disabled User Accounts', $result['hUserId'], false))
            {
                $disabled['userId'][] = $result['hUserId'];
                $disabled['userName'][] = $result['hUserName'];
                $disabled['email'][] = $result['hUserEmail'];
                $disabled['name'][] = $result['hContactFirstName'].' '.$result['hContactLastName'];
            }
            else
            {
                $enabled['userId'][] = $result['hUserId'];
                $enabled['userName'][] = $result['hUserName'];
                $enabled['email'][] = $result['hUserEmail'];
                $enabled['name'][] = $result['hContactFirstName'].' '.$result['hContactLastName'];
            }
        }

        return array(
            'users' => $this->getTemplate(
                'Users',
                array(
                    'enabled' => $enabled,
                    'disabled' => $disabled
                )
            ),
            'pagination' => $this->hPagination->getNavigationTemplate('/Hot Toddy/Dashboard/User.html')
        );
    }

    public function getUsersByGroup($group)
    {
        $members = $this->hUserDatabase->getGroupMemberUsers($group);

        return array(
            'enabled' => $this->getTemplateData($members, 'enabled'),
            'disabled' => $this->getTemplateData($members, 'disabled')
        );
    }

    public function getTemplateData($members, $set)
    {
        $data = nil;

        if (isset($members[$set]) && is_array($members[$set]) && count($members[$set]))
        {
            $data = array(
                'name' => array(),
                'userName' => array(),
                'email' => array()
            );

            foreach ($members[$set] as $userId)
            {
                $data['name'][]     = $this->user->getDisplayName($userId);
                $data['userName'][] = $this->user->getUserName($userId);
                $data['email'][]    = $this->user->getUserEmail($userId);
            }
        }

        return $data;
    }
}

?>