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