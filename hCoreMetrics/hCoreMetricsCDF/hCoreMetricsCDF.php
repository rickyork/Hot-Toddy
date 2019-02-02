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

class hCoreMetricsCDF extends hPlugin {

    private $hFileIds = array();
    private $counter  = 0;

    public function hConstructor()
    {
        // Generate the Core MEtrics CSV file of site hierarchy.
        $this->hFileDisableCache = true;
        $this->hFileEnableCache  = false;

        // Pick up only HTML files with no parent
        //
        // Omit
        //   .includes.html
        //   .specifications.html
        //
        //
        // CSV:
        //  <client id>,<category id>,<category name>,<parent id>
        $csv = fopen('php://temp/maxmemory:'. (10*1024*1024), 'r+');

        $this->addToCSV($csv, 1, '', 'Home');

        $this->getCategory($csv, 0);

        rewind($csv);

        $this->setDownload('text/x-csv', $this->hCoreMetricsClientId(99999999).'.csv');
        $this->hFileDocument = stream_get_contents($csv);
    }

    public function getCategory(&$csv, $hFileId)
    {
        $query = $this->queryFile($hFileId);

        if ($this->hDatabase->resultsExist($query))
        {
            while ($data = $this->hDatabase->getAssociativeResults($query))
            {
                if ((int) $data['hFileId'] == 1)
                {
                    continue;
                }

                $this->addToCSV($csv, $data['hFileId'], empty($hFileId)? 1 : $hFileId);
                $this->getCategory($csv, $data['hFileId']);
            }
        }
    }

    private function queryFile($hFileId)
    {
        return $this->hDatabase->selectQuery(
            'hFileId',
            'hFiles',
            array(
                'hFileParentId' => (int) $hFileId
            )
        );
    }

    private function addToCSV(&$csv, $hFileId, $hFileParentId, $hFileTitle = '')
    {
        if (!in_array($hFileId, $this->hFileIds))
        {
            if (empty($hFileTitle))
            {
                $hFileTitle = trim($this->hFileMenuTitle($this->getFileTitle($hFileId), $hFileId));

                if (empty($hFileTitle))
                {
                    $hFileTitle = trim($this->getFilePathByFileId($hFileId));
                }
            }

            if (empty($hFileTitle))
            {
                $hFileTitle = 'No Title '.$this->counter;
                $this->counter++;
            }

            fputcsv($csv, array($this->hCoreMetricsClientId(99999999), $hFileId, $hFileTitle, $hFileParentId));

            array_push($this->hFileIds, $hFileId);
        }
    }
}

?>