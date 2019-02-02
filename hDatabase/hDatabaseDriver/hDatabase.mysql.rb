
#\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#\\\       \\\\\\\\|
#\\\ @@    @@\\\\\\| Hot Toddy Database MySQL Driver
#\\ @@@@  @@@@\\\\\|
#\\\@@@@| @@@@\\\\\|
#\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#\\\\  \\_   \\\\\\|
#\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#\\\\\  ----  \@@@@| http://www.hframework.com/license
#@@@@@\       \@@@@|
#@@@@@@\     \@@@@@|
#\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class HDatabaseDriverMySQL

    def init
        require 'mysql'

        begin
            $dbh = Mysql.real_connect(
                $framework.hDatabaseHost('localhost'),
                $framework.hDatabaseUser('root'),
                $framework.hDatabasePassword(''),
                $framework.hDatabaseInitial('www')
            )
        rescue Mysql::error => e
            puts "Unable to connect to database: #{e.errno}, #{e.error}"
            Process.exit
        ensure
            $dbh.close if $dbh
        end
    end

    def query(sql, errorReporting = true)
        start = hFrameworkBenchmarkMicrotime()

        result = $dbh.query(sql)

        if !result && errorReporting
            logError()
        end

        stop = FrameworkBenchmarkMicrotime()

        benchmark = ((stop - start).round(3)) * 1000

        $framework.hDatabaseQueryCount += 1

        $framework.hDatabaseQueryBenchmark += benchmark

        if $framework.hDatabaseOptimize(false)
            $databaseBenchmark.push(benchmark)
        end

        if $framework.hDatabaseOptimize(false) || $framework.hDatabaseRememberQueries(false)
            $databaseQueries.push(sql)
        end

        if !result && errorReporting
            $framework.warning "DB Error: Query failed. Query: #{sql} In " + __FILE__ + '@' + __LINE__
        end

        result
    end

#     public function queries($queries, $errorReporting = true)
#     {
#         $start = hFrameworkBenchmarkMicrotime();
# 
#         if (false === ($result = $this->db->multi_query($queries)) && $errorReporting)
#         {
#             $this->logError();
#         }
# 
#         $stop = hFrameworkBenchmarkMicrotime();
#         
#         $benchmark = round($stop - $start, 5);
#         
#         $GLOBALS['hFramework']->hDatabaseQueryCount++;
#         $GLOBALS['hFramework']->hDatabaseQueryBenchmark += $benchmark;
# 
#         if ($GLOBALS['hFramework']->hDatabaseOptimize(false))
#         {
#             $GLOBALS['hDatabaseOptimizeBenchmark'][] = $benchmark;
#             $GLOBALS['hDatabaseOptimize'][]          = $query;
#         }
# 
#         if (false === $result && $errorReporting)
#         {
#             $GLOBALS['hFramework']->warning('DB Error: Query failed. Query: '.$query.'.', __FILE__, __LINE__);
#         }
# 
#         return $result;
#     }

    def getColumn(result, default = nil)
        unless isObject?(result)
            result = query(result)
        end

        if result.num_rows
            data = result.fetch_row
            result.free
            return data.shift
        else
            return default
        end
    end

    def getRow(result, default = [])
        $this->isObject($result);

        if (!empty($result->num_rows))
        {
            $data = $result->fetch_row();
            $result->close();
            return $data;
        }
        else
        {
            return $default;
        }
    end

    public function getResultCount($result, $close = false)
    {
        $this->isObject($result);

        if (!empty($result->num_rows))
        {
            $count = (int) $result->num_rows;
            $this->closeResults($result, $close); 
            return $count;
        }
        else
        {
            return 0;
        }
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
        else
        {
            return false;
        }
    }

    public function getResults($result, $close = false, $default = null)
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

end