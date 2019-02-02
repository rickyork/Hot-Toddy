<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Database Benchmark
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

class hDatabaseBenchmark extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabaseOptimize = false;

        if ($this->isLoggedIn() && $this->hDatabaseOptimizeUserId(false) == $_SESSION['hUserId'] || !$this->hDatabaseOptimizeUserId(false))
        {
            $this->hDatabase->uses('hDatabaseBenchmark');

            if (isset($GLOBALS['hDatabaseQueries']) && is_array($GLOBALS['hDatabaseQueries']) && count($GLOBALS['hDatabaseQueries']) > 1)
            {
                foreach ($GLOBALS['hDatabaseQueries'] as $i => $query)
                {
                    $results = $this->hDatabase->getResults("EXPLAIN ".$query);

                    foreach ($results as $data)
                    {
                        $this->hDatabase->insert(
                            array(
                                'hDatabaseBenchmark'             => (float) $GLOBALS['hDatabaseOptimizeBenchmark'][$i],
                                'hDatabaseBenchmarkTable'        => $data['table'],
                                'hDatabaseBenchmarkType'         => $data['type'],
                                'hDatabaseBenchmarkPossibleKeys' => $data['possible_keys'],
                                'hDatabaseBenchmarkKey'          => $data['key'],
                                'hDatabaseBenchmarkKeyLen'       => $data['key_len'],
                                'hDatabaseBenchmarkRef'          => $data['ref'],
                                'hDatabaseBenchmarkRows'         => $data['rows'],
                                'hDatabaseBenchmarkExtra'        => $data['Extra'],
                                'hDatabaseBenchmarkQuery'        => hString::encodeHTML($query)
                            ),
                            'hDatabaseBenchmark'
                        );
                    }
                }
            }
        }
    }
}

?>