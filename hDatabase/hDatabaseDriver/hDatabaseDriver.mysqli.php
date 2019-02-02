<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Database MySQLi Driver
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


class hDatabaseDriver_MYSQLI {

    private $hFramework;
    private $db;

    // /Library/StartupItems/MySQLCOM/MySQLCOM start

    public function __construct($hFramework)
    {
        $this->db = new mysqli(
            $hFramework->hDatabaseHost('localhost'),
            $hFramework->hDatabaseUser('root'),
            $hFramework->hDatabasePassword(''),
            $hFramework->hDatabaseInitial('www')
        );

        if (!empty($this->db->connect_error))
        {
            echo "Unable to connect to database: ".$this->db->connect_errno.", ".$this->db->connect_error;
            exit;
        }
    }

    public function selectDatabase($database)
    {
        if (!$this->db->select_db($database))
        {
            $this->logError();
        }
    }

    public function query($query, $errorReporting = true)
    {
        $start = hFrameworkBenchmarkMicrotime();

        #echo $query."\n\n";

        # if (empty($query))
        # {
        #     var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        #     exit;
        # }

        if (false === ($result = $this->db->query($query)) && $errorReporting)
        {
            $this->logError();
            //debug_print_backtrace();
            //exit;
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

        return $result;
    }

    public function queries($queries, $errorReporting = true)
    {
        $start = hFrameworkBenchmarkMicrotime();

        if (false === ($result = $this->db->multi_query($queries)) && $errorReporting)
        {
            $this->logError();
        }

        $stop = hFrameworkBenchmarkMicrotime();

        $benchmark = round($stop - $start, 5);

        $GLOBALS['hFramework']->hDatabaseQueryCount++;
        $GLOBALS['hFramework']->hDatabaseQueryBenchmark += $benchmark;

        if ($GLOBALS['hFramework']->hDatabaseOptimize(false))
        {
            $GLOBALS['hDatabaseOptimizeBenchmark'][] = $benchmark;
            $GLOBALS['hDatabaseOptimize'][]          = $query;
        }

        if (false === $result && $errorReporting)
        {
            $GLOBALS['hFramework']->warning('DB Error: Query failed. Query: '.$query, __FILE__, __LINE__);
        }

        return $result;
    }

    public function getDBObject()
    {
        return $this->db;
    }

    public function getColumn($result, $default = '')
    {
        $this->isObject($result);

        if (!empty($result->num_rows))
        {
            $data = $result->fetch_row();
            $result->close();
            return $data[0];
        }

        return $default;
    }

    public function getRow($result, $default = array())
    {
        $this->isObject($result);

        if (!empty($result->num_rows))
        {
            $data = $result->fetch_row();
            $result->close();
            return $data;
        }

        return $default;
    }

    public function getResultCount($result, $close = false)
    {
        $this->isObject($result);

        if (!empty($result->num_rows))
        {
            $count = (int) $result->num_rows;
            $this->closeResults($result, $close);
            return $count;
        }

        return 0;
    }

    public function getAffectedCount($result, $close = false)
    {
        $this->isObject($result);

        if (isset($this->db->affected_rows))
        {
            $count = (int) $this->db->affected_rows;
            $this->closeResults($result, $close);
            return $count;
        }

        return 0;
    }

    public function resultsExist($result, $close = false)
    {
        $this->isObject($result);

        if (!empty($result->num_rows))
        {
            $bool = (bool) $result->num_rows;
            $this->closeResults($result, $close);
            return $bool;
        }

        return false;
    }

    public function getColumnCount($result = nil, $close = false)
    {
        if (!empty($result))
        {
            $this->isObject($result);
        }

        $count = isset($this->db->field_count)? $this->db->field_count : 0;

        if (!empty($result))
        {
            $this->closeResults($result, $close);
        }

        return $count;
    }

    public function getResults($result, $close = false, $default = nil)
    {
        $this->isObject($result);
        $data = !empty($result->num_rows)? $result->fetch_object() : (object) $default;
        $this->closeResults($result, $close);
        return $data;
    }

    public function getAssociativeResults($result, $close = false, $default = array())
    {
        $this->isObject($result);
        $data = !empty($result->num_rows)? $result->fetch_assoc() : $default;
        $this->closeResults($result, $close);
        return $data;
    }

    public function getNumberedResults($result, $close = false, $default = array())
    {
        $this->isObject($result);
        $data = !empty($result->num_rows)? $result->fetch_array(MYSQLI_NUM) : $default;
        $this->closeResults($result, $close);
        return $data;
    }

    public function getArrayResults($result, $close = false, $default = array())
    {
        $this->isObject($result);
        $data = !empty($result->num_rows)? $result->fetch_array(MYSQLI_BOTH) : $default;
        $this->closeResults($result, $close);
        return $data;
    }

    private function isObject(&$result)
    {
        if (!is_object($result))
        {
            $result = $this->query($result);
        }
    }

    public function closeResults($result, $close = true)
    {
        if ($close && is_object($result))
        {
            $result->close();
            unset($result);
        }
    }

    public function getInsertedId()
    {
        return isset($this->db->insert_id)? $this->db->insert_id : 0;
    }

    public function logError()
    {
        $error = $this->db->error;

        if ($error == "Commands out of sync; you can't run this command now")
        {
            echo "<b>Fatal Database Error</b>: {$error}\n";
            exit;
        }

        $GLOBALS['hFramework']->warning("DB Error: ".$error, __FILE__, __LINE__);
    }

    public function close()
    {
        $this->db->close();
    }
}

?>