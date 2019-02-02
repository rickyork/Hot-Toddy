<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy User Select Listener
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

class hUserSelectService extends hService {

    private $hUserDatabase;

    public function hConstructor()
    {
        if (!$this->isLoggedIn())
        {
            $this->JSON(-6);
            return;
        }

        if (!$this->inGroup('Employees') && !$this->inGroup('Website Administrators'))
        {
            $this->JSON(-1);
            return;
        }
    }

    public function getUsersByLetter()
    {
        if (!isset($_GET['letter']))
        {
            $this->JSON(-5);
            return;
        }

        if (strlen($_GET['letter']) > 1)
        {
            $this->JSON(0);
            return;
        }

        $this->hUserDatabase = $this->database('hUser');

        if (!isset($_GET['hUserGroups']))
        {
            $contactAddressBookId = isset($_GET['contactAddressBook'])? (int) $_GET['contactAddressBook'] : 1;

            $results = $this->hUserDatabase->queryUsersByWildcard(
                $_GET['letter'].'%',
                'hContacts.hContactLastName',
                $contactAddressBookId
            );
        }
        else
        {
            $results = $this->hUserDatabase->queryGroupsByWildcard($_GET['letter'].'%');
        }

        $html = '';

        foreach ($results as $result)
        {
            $isGroup = !isset($result['hContactFirstName']);

            if (!isset($_GET['hUserGroups']) && !$isGroup || isset($_GET['hUserGroups']) && $isGroup)
            {
                if (strstr($result['hUserName'], '@'))
                {
                    $bits = explode('@', $result['hUserName']);
                    $result['hUserName'] = array_shift($bits);
                }

                $hUserSelectUserResult = '';

                if (!$isGroup)
                {
                    $hUserSelectUserResult = $this->getTemplate(
                        'User Result',
                        array(
                            'hContactFirstName' => hString::entitiesToUTF8($result['hContactFirstName']),
                            'hContactLastName'  => hString::entitiesToUTF8($result['hContactLastName']),
                            'hContactCompany'   => hString::entitiesToUTF8($result['hContactCompany']),
                            'hUserEmail'        => $result['hUserEmail']
                        )
                    );
                }

                $html .= $this->getTemplate(
                    'Result',
                    array(
                        'hUserSelectUserResult' => $hUserSelectUserResult,
                        'hUserId'               => $result['hUserId'],
                        'hUserName'             => hString::entitiesToUTF8($result['hUserName'])
                    )
                );
            }
        }

        $this->HTML(
            $this->getTemplate(
                'Results',
                array(
                    'hUserSelectResults' => $html
                )
            )
        );
    }
}

?>