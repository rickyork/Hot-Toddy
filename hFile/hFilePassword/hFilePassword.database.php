<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File Password Database
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

class hFilePasswordDatabase extends hPlugin {

    private $query;

    public function queryPasswords($fileId, $passwordIsRequired = true)
    {
        $where['hFileId'] = (int) $fileId;

        if ($passwordIsRequired !== null)
        {
            $where['hFilePasswordRequired'] = (int) $passwordIsRequired;
        }

        return $this->hFilePasswords->selectQuery(
            array(
                'hFilePassword',
                'hFilePasswordLifetime',
                'hFilePasswordExpirationAction',
                'hFilePasswordRequired',
                'hFilePasswordCreated',
                'hFilePasswordExpires'
            ),
            $where
        );
    }

    public function hasRequiredPasswords($fileId)
    {
        $this->query = $this->queryPasswords($fileId);

        return (bool) $this->hDatabase->resultsExist($this->query);
    }

    public function hasOptionalPasswords($fileId)
    {
        $this->query = $this->queryPasswords($fileId, false);

        return (bool) $this->hDatabase->resultsExist($this->query);
    }

    private function getPasswords($fileId, $passwordIsRequired)
    {
        if (empty($this->query))
        {
            $this->query = $this->queryPasswords(
                $fileId,
                $passwordIsRequired
            );
        }

        $results = array();

        while ($data = $this->hDatabase->getAssociativeResults($this->query))
        {
            $results[] = $data;
        }

        $this->hDatabase->closeResults($this->query);

        return $results;
    }

    public function getRequiredPasswords($fileId)
    {
        return $this->getPasswords($fileId, true);
    }

    public function getOptionalPasswords($fileId)
    {
        return $this->getPasswords($fileId, false);
    }
}

?>