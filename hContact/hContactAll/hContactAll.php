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

class hContactAll extends hPlugin implements hContactApplication {

    private $hContactAddressBookName;
    private $hSpotlightSearch;

    public function hConstructor()
    {
        $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');
        $this->getSearchColumns();
        //$this->hSpotlightSearch->addTableCondition('hContact',  ' `hContacts`.`hContactAddressBookId` NOT LIKE 41 AND ');

        if (isset($_GET['hContactAddressBookId']))
        {
            $this->hSpotlightSearch->addWhereCondition(
                '`hContacts`.`hContactAddressBookId` = '. (int) $_GET['hContactAddressBookId']
            );
        }
    }

    public function setAddressBookName($addressBookName)
    {
        $this->hContactAddressBookName = $addressBookName;
    }

    public function getSearchColumns()
    {
    }

    public function queryLocation($search, $location, $where, $time, $sort, $sortOrientation, &$results)
    {
        //$this->hSpotlightSearch->setColumnSelected('hContact', 'hUserId');
        $this->hSpotlightSearch->setColumnSelected('hContact', 'hContactId');

        if (empty($where))
        {
            $where = $this->hSpotlightSearch->getDefaultColumns($table);
        }

        if (!empty($sort))
        {
            $this->hSpotlightSearch->addTableToQuery(
                array_shift($bits = explode('.', $sort))
            );
        }

        $select = $this->hSpotlightSearch->getSelectColumns();

        $constrainTime = (count($time)? $this->hSpotlightSearch->getTime($time, $join) : '');

        if (false !== ($where = $this->hSpotlightSearch->getWhereClause($where, $search)))
        {
            $this->hSpotlightSearch->addTableToQuery("`hContactAddresses`");

            if (!empty($location['county']))
            {
                $this->hSpotlightSearch->addTableToQuery("`hLocationCounties`");
            }

            $from = $this->hSpotlightSearch->getTablesInQuery();

            $query = $thi->hDatabase->query(
                $this->getTemplateSQL(
                    array_merge(
                        array(
                            'select' => implode(',', $select),
                            'from' => implode(',', $from),
                            'where' => $where,
                            'constrainTime' => $constrainTime,
                            'sort' => $sort,
                            'sortOrientation' => $sortOrientation
                        ),
                        $location
                    )
                )
            );

            if ($this->hDatabase->resultsExist($query))
            {
                while ($data = $this->hDatabase->getAssociativeResults($query))
                {
                    $results[$data['hContactId']] = $data;
                }
            }
        }
    }

    public function query($search, &$where, $time, $sort, $sortOrientation, &$results)
    {
        $this->hSpotlightSearch->query(
            'hContacts',
            $search,
            $where,
            $time,
            $sort,
            $sortOrientation,
            nil,
            $results
        );
    }
}

?>