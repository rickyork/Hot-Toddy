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

class hDatabaseDriver_MYSQL {

    private $hFramework;
    private $db;
    private $lastResult;

    public function __construct($hFramework)
    {
        $this->db = mysql_pconnect(
            $hFramework->hDatabaseHost('localhost'),
            $hFramework->hDatabaseUser('root'),
            $hFramework->hDatabasePassword('')
        );

        mysql_select_db($hFramework->hDatabaseInitial('www'), $this->db);

        if (!$this->db)
        {
            echo "Unable to connect to database: ".mysql_error();
            exit;
        }
    }

    public function query($query, $errorReporting = true)
    {
        $start = hFrameworkBenchmarkMicrotime();

        if (false === ($result = mysql_query($query, $this->db)) && $errorReporting)
        {
            $this->logError();
        }

        $stop = hFrameworkBenchmarkMicrotime();

        $benchmark = round($stop - $start, 3) * 1000;

        $GLOBALS['hFramework']->hDatabaseQueryCount++;
        $GLOBALS['hFramework']->hDatabaseQueryBenchmark += $benchmark;

        if ($GLOBALS['hFramework']->hDatabaseOptimize(false))
        {
            if ($GLOBALS['hFramework']->isLoggedIn() && $GLOBALS['hFramework']->hDatabaseOptimizeUserId(false) == $_SESSION['hUserId'] || !$GLOBALS['hFramework']->hDatabaseOptimizeUserId(false))
            {
                $GLOBALS['hDatabaseOptimizeBenchmark'][] = $benchmark;
            }
        }

        if ($GLOBALS['hFramework']->hDatabaseOptimize(false) || $GLOBALS['hFramework']->hDatabaseRememberQueries(false))
        {
            if ($GLOBALS['hFramework']->isLoggedIn() && $GLOBALS['hFramework']->hDatabaseOptimizeUserId(false) == $_SESSION['hUserId'] || !$GLOBALS['hFramework']->hDatabaseOptimizeUserId(false))
            {
                $GLOBALS['hDatabaseQueries'][] = $query;
            }
        }

        if (false === $result && $errorReporting)
        {
            $GLOBALS['hFramework']->warning('DB Error: Query failed. Query: '.$query, __FILE__, __LINE__);
        }

        $this->lastResult = $result;

        return $result;
    }

    public function getColumn($result, $default = '')
    {
        $this->isResource($result);

        if (is_resource($result) && mysql_num_rows($result))
        {
            $data = mysql_result($result, 0);
            mysql_free_result($result);
            return $data;
        }

        return $default;
    }

    public function getRow($result, $default = array())
    {
        $this->isResource($result);

        if (is_resource($result) && mysql_num_rows($result))
        {
            $data = mysql_fetch_assoc($result);
            mysql_free_result($result);
            return $data;
        }

        return $default;
    }

    public function getResultCount($result, $close = false)
    {
        $this->isResource($result);

        if (is_resource($result))
        {
            $count = (int) mysql_num_rows($result);
            $this->closeResults($result, $close);
            return $count;
        }

        return 0;
    }

    public function getAffectedCount($result, $close = false)
    {
        $this->isResource($result);

        if (is_resource($result))
        {
            $count = (int) mysql_affected_rows($result);
            $this->closeResults($result);
            return $count;
        }

        return 0;
    }

    public function resultsExist($result, $close = false)
    {
        $this->isResource($result);
        $count = (bool) (is_resource($result) && mysql_num_rows($result));
        $this->closeResults($result, $close);
        return $count;
    }

    public function getColumnCount($result = nil, $close = false)
    {
        if (empty($result))
        {
            $result = $this->lastResult;
        }

        if (!empty($result))
        {
            $this->isResource($result);
        }

        $count = is_resource($result)? mysql_num_fields($result) : 0;

        if (!empty($result))
        {
            $this->closeResults($result, $close);
        }

        return $count;
    }

    public function getResults($result, $close = false, $default = nil)
    {
        $this->isResource($result);
        $data = is_resource($result) && mysql_num_rows($result)? mysql_fetch_object($result) : (object) $default;
        $this->closeResults($result, $close);
        return $data;
    }

    public function getAssociativeResults($result, $close = false, $default = array())
    {
        $this->isResource($result);
        $data = is_resource($result) && mysql_num_rows($result)? mysql_fetch_assoc($result) : $default;
        $this->closeResults($result, $close);
        return $data;
    }

    public function getNumberedResults($result, $close = false, $default = array())
    {
        $this->isResource($result);
        $data = is_resource($result) && mysql_num_rows($result)? mysql_fetch_row($result) : $default;
        $this->closeResults($result, $close);
        return $data;
    }

    public function getArrayResults($result, $close = false, $default = array())
    {
        $this->isResource($result);
        $data = is_resource($result) && mysql_num_rows($result)? mysql_fetch_array($result) : $default;
        $this->closeResults($result, $close);
        return $data;
    }

    private function isResource(&$result)
    {
        if (!is_resource($result) && is_string($result))
        {
            $result = $this->query($result);
        }
    }

    public function closeResults($result, $close = true)
    {
        if ($close && is_resource($result))
        {
            mysql_free_result($result);
        }
    }

    public function getInsertedId()
    {
        return mysql_insert_id($this->db);
    }

    public function logError()
    {
        $GLOBALS['hFramework']->warning("DB Error: ".mysql_error($this->db), __FILE__, __LINE__);
    }

    public function close()
    {
        mysql_close($this->db);
    }
}

?>