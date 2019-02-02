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

# @description
# <h1>Database API</h1>
# <p>
#   The <var>hDatabase</var> object is available in all Hot Toddy plugins as
#   <var>$this-&gt;hDatabase</var>.  Additionally you can call the methods of
#   the database driver defined in <var>hDatabase/hDatabaseDriver</var> via
#   <var>hDatabase</var>.
# </p>
# <p>
#   The <var>hDatabase</var> object provides abstracted APIs that perform
#   various database-related tasking.  Selecting, updating, inserting,
#   deleteing records.  The <var>hDatabase</var> object is also used by
#   Hot Toddy's database reflection API, which exposes each database table
#   as an object, see <var>hDatabase/hDatabaseTable</var> for more documentation
#   regarding the database reflection API.
# </p>
# @end

class hDatabase {

    const autoIncrement     = "int(11) NOT NULL auto_increment";
    const id                = "int(11) NOT NULL default '0'";
    const name              = "varchar(255) NOT NULL default ''";
    const timestamp         = "TIMESTAMP DEFAULT '0000-00-00 00:00:00'";
    const longText          = "LONGTEXT NOT NULL";
    const mediumText        = "MEDIUMTEXT NOT NULL";
    const text              = "TEXT NOT NULL";
    const time              = "int(32) NOT NULL default '0'";
    const is                = "tinyint(1) NOT NULL default '0'";
    const latitudeLongitude = "float(32,12) NOT NULL default '0'";
    const floatTemplate     = "float({size},{precision}) NOT NULL default '{default}'";
    const intTemplate       = "int({size}) NOT NULL default '{default}'";
    const tinyIntTemplate   = "tinyint({size}) NOT NULL default '{default}'";
    const varCharTemplate   = "varchar({size}) NOT NULL default '{default}'";
    const charTemplate      = "char({size}) NOT NULL default '{default}'";

    private $table;
    private $tables = array();
    public $columns;
    private $firstColumns;
    private $primaryKeys;
    private $primaryKeyValue;
    private $primaryIncrementKeys;
    private $useLimit = false;
    private $overloadExceptions = array();
    private $resultCount = 0;
    private $hFileIcon;
    private $defaultResult = 0;
    private $prependResult = array();
    private $resultIndex = nil;
    public $hDB; # The database driver object.
    private $hFramework;
    private $lastQuery = nil;

    private $methods = array(
        'password',
        'find_in_set'
    );

    public static function floatTemplate($size = 11, $precision = 2, $default = 0)
    {
        # @return string

        # @description
        # <h2>Float Data Templates</h2>
        # <p>
        #    This method is used to create or modify columns for float data types.
        #    It returns something like the following, where the size, precision, and
        #    default values are all customizable as parameters to this method.
        # </p>
        # <code>
        #    float({size},{precision}) NOT NULL default '{default}'
        # </code>
        # <p>
        #    This string can then be inserted in a MySQL <var>CREATE TABLE</var>, or
        #    <var>ALTER TABLE</var> statement, or any other SQL statement requiring the
        #    type of the column in this fashion.
        # </p>
        # <p>
        #    <var>$size</var>, <var>$precision</var>, and <var>$default</var> are all
        #    optional parameters.  If not specified, <var>$size</var> is <var>11</var>,
        #    <var>$precision</var> is <var>2</var>, and <var>$default</var> is <var>0</var>
        # </p>
        # @end

        return str_replace(
            array('{size}', '{precision}', '{default}'),
            array($size, $precision, $default),
            self::floatTemplate
        );
    }

    public static function intTemplate($size = 11, $default = 0)
    {
        # @return string

        # @description
        # <h2>Integer Data Templates</h2>
        # <p>
        #    This method is used to create or modify columns for integer data types.
        #    It returns something like the following, where the size and
        #    default values are customizable as parameters to this method.
        # </p>
        # <code>
        #    int({size}) NOT NULL default '{default}'
        # </code>
        # <p>
        #    This string can then be inserted in a MySQL <var>CREATE TABLE</var>, or
        #    <var>ALTER TABLE</var> statement, or any other SQL statement requiring the
        #    type of the column in this fashion.
        # </p>
        # <p>
        #    <var>$size</var> and <var>$default</var> are both optional.  If not
        #    specified <var>$size</var> is <var>11</var> and <var>$default</var> is <var>0</var>
        # </p>
        # @end

        return str_replace(
            array('{size}', '{default}'),
            array($size, $default),
            self::intTemplate
        );
    }

    public static function tinyIntTemplate($size = 1, $default = 0)
    {
        # @return string

        # @description
        # <h2>Tiny Integer Data Templates</h2>
        # <p>
        #    This method is used to create or modify columns for tiny integer data types.
        #    It returns something like the following, where the size and
        #    default values are customizable as parameters to this method.
        # </p>
        # <code>
        #    tinyint({size}) NOT NULL default '{default}'
        # </code>
        # <p>
        #    This string can then be inserted in a MySQL <var>CREATE TABLE</var>, or
        #    <var>ALTER TABLE</var> statement, or any other SQL statement requiring the
        #    type of the column in this fashion.
        # </p>
        # <p>
        #    <var>$size</var> and <var>$default</var> are both optional.  If not
        #    specified <var>$size</var> is <var>1</var> and <var>$default</var> is <var>0</var>
        # </p>
        # @end

        return str_replace(
            array('{size}', '{default}'),
            array($size, $default),
            self::tinyIntTemplate
        );
    }

    public static function varCharTemplate($size = 25, $default = '')
    {
        # @return string

        # @description
        # <h2>Variable Character Data Templates</h2>
        # <p>
        #    This method is used to create or modify columns for <var>varChar</var> data types.
        #    It returns something like the following, where the size and
        #    default values are customizable as parameters to this method.
        # </p>
        # <code>
        #    varchar({size}) NOT NULL default '{default}'
        # </code>
        # <p>
        #    This string can then be inserted in a MySQL <var>CREATE TABLE</var>, or
        #    <var>ALTER TABLE</var> statement, or any other SQL statement requiring the
        #    type of the column in this fashion.
        # </p>
        # <p>
        #    <var>$size</var> and <var>$default</var> are both optional.  If not
        #    specified <var>$size</var> is <var>25</var> and <var>$default</var> is
        #    <var>null</var>
        # </p>
        # @end

        return str_replace(
            array('{size}', '{default}'),
            array($size, $default),
            self::varCharTemplate
        );
    }

    public static function charTemplate($size = 25, $default = '')
    {
        # @return string

        # @description
        # <h2>Character Data Templates</h2>
        # <p>
        #    This method is used to create or modify columns for <var>char</var> data types.
        #    It returns something like the following, where the size and
        #    default values are customizable as parameters to this method.
        # </p>
        # <code>
        #    char({size}) NOT NULL default '{default}'
        # </code>
        # <p>
        #    This string can then be inserted in a MySQL <var>CREATE TABLE</var>, or
        #    <var>ALTER TABLE</var> statement, or any other SQL statement requiring the
        #    type of the column in this fashion.
        # </p>
        # <p>
        #    <var>$size</var> and <var>$default</var> are both optional.  If not
        #    specified <var>$size</var> is <var>25</var> and <var>$default</var> is
        #    <var>null</var>
        # </p>
        # @end

        return str_replace(
            array('{size}', '{default}'),
            array($size, $default),
            self::charTemplate
        );
    }

    public function __construct()
    {
        # Caches an array containing the names of all of the tables in the database
        # for the duration of framework execution.  Tables are stored in $this->tables.
        $this->getTables();
    }

    public function __call($method, $arguments)
    {
        # @return mixed

        # @description

        # <h2>Overloaded Method Calls</h2>
        # <div id='selectColumn'>
        #    <h3>Selecting a Single Column From a Single Row</h3>
        #    <code>public function selectColumn($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # <div id='selectExists'>
        #     <h3>Return a Boolean Value Based on Whether Rows Were Found or Not</h3>
        #     <p>
        #         Run query and return a boolean value based on whether rows were selected by the
        #         query or not.
        #     </p>
        #     <code>public function selectExists($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # <div id='selectAssociative'>
        #     <h3>Return a Multiple Columns From a Single Row as an Associative Array</h3>
        #     <p>
        #         Return a single associative array based on the columns selected in the query.
        #         This will only return an array from the 1st row found, subsequent rows are
        #         discarded and ignored.
        #     </p>
        #     <code>public function selectAssociative($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # <div id='selectResults'>
        #     <h3>Return Multiple Columns From Multiple Rows</h3>
        #     <p>
        #         Alias for <var>select()</var>, returns all columns and rows for the query as a
        #         multidimensional array.
        #     </p>
        #     <code>public function selectResults($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # <div id='selectQuery'>
        #     <h3>Return a Query Resource or Object</h3>
        #     <p>
        #         Returns the query resource handler or object, instead of the actual results.
        #         This can then be used in methods in hDatabaseDriver (which driver depends on
        #         which database API you're using)
        #     </p>
        #     <code>public function selectQuery($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # <div id='selectCount'>
        #     <h3>Return a Result Count (after LIMIT clause is applied)</h3>
        #     <p>
        #         Returns an integer count of the selection.
        #     </p>
        #     <code>public function selectCount($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        #  <div id='selectColumnAsKeyValue'>
        #     <h3>Return a Selection Using the First Column for Keys and the Second Column for Values</h3>
        #     <p>
        #         Makes a selection and returns an associative array where the first column selected is
        #         used for the array keys and the second column selected is used for the array values.
        #     </p>
        #     <code>public function selectColumnAsKeyValue($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # <div id='selectForTemplate'>
        #     <h3>Return an Array Structured for Use in a Hot Toddy Template</h3>
        #     <code>public function selectForTemplate($columns = '*', $table = null, $where = null, $logicalOperator = 'AND', $order = null, $limit = null)</code>
        # </div>
        # @end

        if (substr($method, 0, 6) == 'select')
        {
            switch ($method)
            {
                case 'selectColumn':            $select = 'getColumn';             break;
                case 'selectExists':            $select = 'resultsExist';          break;
                case 'selectAssociative':       $select = 'getAssociativeResults'; break;
                case 'selectResults':           $select = 'getResults';            break;
                case 'selectQuery':             $select = 'query';                 break;
                case 'selectCount':             $select = 'getResultCount';        break;
                case 'selectColumnsAsKeyValue': $select = 'getAssociativeArray';   break;
                case 'selectForTemplate':       $select = 'getResultsForTemplate'; break;
                default:
                {
                    $GLOBALS['hFramework']->warning(
                        "Unimplemented database method '{$method}'.",
                        __FILE__,
                        __LINE__
                    );
                }
            }

            return $this->select(
                isset($arguments[0])? $arguments[0] : '*',   # Columns
                isset($arguments[1])? $arguments[1] : nil,  # Table(s)
                isset($arguments[2])? $arguments[2] : nil,  # Where
                isset($arguments[3])? $arguments[3] : 'AND', # AND, OR
                isset($arguments[4])? $arguments[4] : nil,  # Order
                isset($arguments[5])? $arguments[5] : nil,  # Limit
                $select
            );
        }

        if ($this->tableExists($method))
        {
            return $this->select(
                isset($arguments[0])? $arguments[0] : '*',   # Columns
                $method,                                     # Table(s)
                isset($arguments[1])? $arguments[1] : nil,  # Where
                isset($arguments[2])? $arguments[2] : 'AND', # AND, OR
                isset($arguments[3])? $arguments[3] : nil,  # Order
                isset($arguments[4])? $arguments[4] : nil,  # Limit
                'selectResults'
            );
        }

        if (empty($this->hDB))
        {
            $driver = 'hDatabaseDriver_'.strToUpper(
                $GLOBALS['hFramework']->hDatabaseDriver('MYSQLI')
            );

            $this->hDB = new $driver($GLOBALS['hFramework']);
        }

        if (method_exists($this->hDB, $method))
        {
            return call_user_func_array(
                array(
                    &$this->hDB,
                    $method
                ),
                $arguments
            );
        }

        # Pass method calls back to the framework when the method does not exist here,
        # or in the database driver.

        if (method_exists($GLOBALS['hFramework'], $method))
        {
            return call_user_func_array(
                array(
                    $GLOBALS['hFramework'],
                    $method
                ),
                $arguments
            );
        }

        return $GLOBALS['hFramework']->fuseObjects(
            $method,
            $arguments
        );
    }

    public function __set($key, $value)
    {
        $GLOBALS['hFramework']->__set($key, $value);
    }

    public function __get($key)
    {
        return $GLOBALS['hFramework']->__get($key);
    }

    public function &setDefaultResult($defaultResult)
    {
        # @return hDatabase

        # @description
        # <h2>Setting the Default Result</h2>
        # <p>
        #    Sets the default result that is returned when no results are found when
        #    <a href='#selectColumn' class='code'>selectColumn()</a> is called.
        # </p>
        # @end

        $this->defaultResult = $defaultResult;
        return $this;
    }

    public function &setPrependResult($value)
    {
        # @return hDatabase

        # @description
        # <h2>Setting the Prepended Result</h2>
        # <p>
        #    Sets the label prepended to the beginning of a result set when
        #    <a href='#selectColumnsAsKeyValue' class='code'>selectColumnsAsKeyValue()</a> is called.
        # </p>
        # @end

        $this->prependResult = array($value);
        return $this;
    }

    public function &setResultIndex($index)
    {
        # @return hDatabase

        # @description
        # <h2>Setting the Prepended Result</h2>
        # <p>
        #    Sets the column used for array keys when
        #    <a href='#select' class='code'>select()</a> is called.
        # </p>
        # @end

        $this->resultIndex = $index;
        return $this;
    }

    public function &setWhere($where)
    {
        # @return hDatabase

        # @description
        # <h2>Setting "Where"</h2>
        # <p>
        #    Sets the SQL used for the WHERE clause of supported SQL queries.
        # </p>
        # @end

        $this->where = $where;
        return $this;
    }

    private function singleRecordOperation($key, $value = nil, $update = false)
    {
        # @return string

        # @description
        # <h2>Single Record Operation</h2>
        # <p>
        #   This method performs a query within the context of a single database record.
        #   If the <var>$update</var> argument is false, then a <var>SELECT</var> operation
        #   is performed. If the <var>$update</var> argument is true, then an <var>UPDATE</var>
        #   operation is performed.
        # </p>
        # <p>
        #   The operation is carried out within the context of the table set in the <var>$table</var>
        #   property, on the column <var>$key</var>. When updating, the value of the column is set to
        #   the value of the <var>$value</var> argument.
        # </p>
        # @end

        if ($this->hasPrimaryKey())
        {
            if (!empty($this->primaryKeyValue))
            {
                if (empty($update))
                {
                    $this->updateSingle($key, $value);
                }
                else
                {
                    return $this->selectSingle($key);
                }
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    "There is no primary key value set, unable to select/update a ".
                    "single record in column '{$key}' in table '{$this->table}'.",
                    __FILE__, __LINE__
                );
            }
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                "The table '{$this->table}' does not have a primary key defined.",
                __FILE__, __LINE__
            );
        }
    }

    public function columnExists($column, $table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Exists</h2>
        # <p>
        # Determines if the specified <var>$column</var> exists in the specified <var>$table</var>.
        # If <var>$table</var> is not specified, the value of <var>$table</var> is determined
        # automatically using <a href='#whichTable' class='code'>whichTable()</a>.
        # </p>
        # @end

        if (preg_match('/^[A-Z|a-z|0-9|\s|\-|\_|\.]+$/', $column) >= 1 && preg_match('/^[A-Z|a-z|0-9|\s|\-|\_|\.]+$/', $table) >= 1)
        {
            $this->whichTable($table);

            if (!empty($table))
            {
                $this->getColumns($table);

                if (!empty($column) && isset($this->columns[$table]) && is_array($this->columns[$table]))
                {
                    foreach ($this->columns[$table] as $columnInTable => $data)
                    {
                        if (strToLower($columnInTable) == strToLower($column))
                        {
                            return true;
                        }
                    }

                    return false;
                }
                else
                {
                    $GLOBALS['hFramework']->warning(
                        "Unable to determine if the column exists in table '{$table}'.",
                        __FILE__, __LINE__
                    );

                    return false;
                }
            }
        }

        return false;
    }

    public function columnsExist(Array $columns, $table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Exists Within a Table</h2>
        # <p>
        #    Returns <var>true</var> or <var>false</var> depending on whether the
        #    column specified in <var>$column</var> exists within the table specified
        #    in <var>$table</var>.
        # </p>
        # @end

        $this->whichTable($table);

        if (!empty($table))
        {
            foreach ($columns as $column)
            {
                if (!$this->columnExists($column, $table))
                {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public function getXML()
    {

    }

    public function select($columns = '*', $tables = nil, $where = nil, $logicalOperator = 'AND', $order = nil, $limit = 0, $method = 'getResults')
    {
        # @return array

        # @description
        # <h2>Selecting API</h2>
        # <p>
        #   Most of the following arguments apply to any of the <var>select*</var> methods.
        # </p>
        # <h3>Selecting Columns</h3>
        # <p>
        #   Columns are selecting using any of the following:
        # </p>
        # <ul>
        #   <li>one column, naming a column in a string.  Example: <var>'hFileId'</var></li>
        #   <li>an asterisk (all columns).  Example: <var>'*'</var></li>
        #   <li>an array, multiple columns.  Example:
        #       <code>
        #           array(
        #               'hFileId',
        #               'hFileTitle',
        #               'hFileDocument'
        #           )
        #       </code>
        #   </li>
        #   <li>multiple arrays, one or more columns from multiple tables.  Example:
        #       <code>
        #           array(
        #               'hFiles' => array(
        #                   'hFileId',
        #                   'hFileLastModified'
        #               ),
        #               'hFileDocuments' => array(
        #                   'hFileTitle',
        #                   'hFileDocument',
        #                   'hFileKeywords',
        #                   'hFileDescription'
        #               )
        #           )
        #       </code>
        #   </li>
        #   <li>
        #       Keywords: <var><i>DISTINCT, SQL_CALC_FOUND_ROWS, COUNT</i></var>
        #       <p>
        #           <var>DISTINCT</var> simply adds the <var>DISTINCT</var> keyword to the query.
        #       </p>
        #       <p>
        #           Example:
        #       </p>
        #       <code>
        #           array(
        #               'DISTINCT',
        #               'hFileId',
        #               'hFileTitle',
        #               'hFileDocument'
        #           )
        #       </code>
        #       <p>
        #           The preceding removes duplicate entries.
        #       </p>
        #       <p>
        #           <var>COUNT</var> and <var>SQL_CALC_FOUND_ROWS</var> both add the
        #           <var>SQL_CALC_FOUND_ROWS</var> keyword to the query.
        #       </p>
        #   </li>
        # </ul>
        # <h3>Selecting Tables</h3>
        # <p>
        #   <var>$tables</var> can be one or many tables, specified in either a string
        #   or an array.  Example of a single table in a string: <var>'hFiles'</var>.  Example of
        #   many tables in an array:
        # </p>
        # <code>
        #   array(
        #       'hFiles',
        #       'hFileDocuments'
        #   )
        # </code>
        # <p>
        #   If the table is already specified, as in the following example:
        # </p>
        # <code>$this-&gt;hFiles-&gt;selectColumn('hFileId');</code>
        # <p>
        #   The <var>$tables</var> argument is not specified in this situation because the table
        #   is specified in the object, <var>hFiles</var>, that the method <var>selectColumn()</var>
        #   is called from.
        # </p>
        # <h3>Specifying WHERE</h3>
        # <p>
        #
        # @end

        if (!is_array($tables) && !is_array($where))
        {
            $this->whichWhere($where, $tables)
                 ->setTable($tables)
                 ->whichTable($tables);
        }

        $formattedColumns = array();
        $distinct = false;
        $count = false;

        if (is_array($columns))
        {
            foreach ($columns as $i => $column)
            {
                if ($column == 'SQL_CALC_FOUND_ROWS' || $column == 'COUNT')
                {
                    $count = true;
                    continue;
                }

                if ($column == 'DISTINCT')
                {
                    $distinct = true;
                    continue;
                }

                if ($column == 'hFilePath')
                {
                    array_push(
                        $formattedColumns,
                        "REPLACE(".
                            "CONCAT(".
                                "(".
                                    "SELECT `hDirectoryPath` ".
                                      "FROM `hDirectories` ".
                                     "WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`".
                                "), ".
                                "'/', ".
                                "`hFiles`.`hFileName`".
                            "), ".
                            "'//', ".
                            "'/'".
                        ") AS `hFilePath`"
                    );

                    continue;
                }

                if ($column == '@hFilePath')
                {
                    $column = 'hFilePath';
                }

                if (is_array($column))
                {
                    foreach ($column as $n)
                    {
                        array_push(
                            $formattedColumns,
                            "`{$i}`.`{$n}`"
                        );
                    }
                }
                else
                {
                    if (!is_numeric($i))
                    {
                        array_push(
                            $formattedColumns,
                            "`{$i}`.`{$column}`"
                        );
                    }
                    else
                    {
                        array_push(
                            $formattedColumns,
                            "`{$column}`"
                        );
                    }
                }
            }
        }
        else
        {
            array_push(
                $formattedColumns,
                $columns != '*' ? "`{$columns}`" : '*'
            );
        }

        if (is_array($tables))
        {
            foreach ($tables as $i => $table)
            {
                $tables[$i] = "`{$table}`";
            }
        }
        else
        {
            $tables = "`{$tables}`";
        }

        if (is_array($where))
        {
            $where = $this->where($where, $logicalOperator, nil);
        }

        $sql =
            "SELECT ".
               ($count? ' SQL_CALC_FOUND_ROWS ' : '').
               ($distinct? 'DISTINCT ' : '').
               implode(', ', $formattedColumns)."
               FROM ".(is_array($tables)?  implode(', ', $tables)  : $tables);

        if (!empty($where))
        {
            $sql .= " WHERE ".$where;
        }

        if (!empty($order))
        {
            $sql .= " ORDER BY ";
            $direction = 'ASC';

            $random = false;

            if (is_array($order))
            {
                $sortByColumns = array();

                foreach ($order as $i => $column)
                {
                    if ($column == 'ASC' || $column == 'DESC')
                    {
                        $direction = $column;
                        continue;
                    }

                    array_push(
                        $sortByColumns,
                        (is_numeric($i)? '' : "`{$i}`.")."`{$column}`"
                    );
                }

                $sql .= implode(', ', $sortByColumns);
            }
            else if ($order == 'RAND' || $order == 'random')
            {
                $sql .= " RAND()";
                $random = true;
            }
            else if (strstr($order, '.'))
            {
                $bits = explode('.', $order);
                $sql .= "`{$bits[0]}`.`{$bits[1]}`";
            }
            else
            {
                $sql .= "`{$order}`";
            }

            if (!$random)
            {
                $sql .= ' '.$direction;
            }
        }

        if (!empty($limit))
        {
            $sql .= ' LIMIT '.$limit;
        }

        $defaultResult = $this->defaultResult;
        $this->defaultResult = 0;

        $prependResult = $this->prependResult;
        $this->prependResult = array();

        $resultIndex = $this->resultIndex;
        $this->resultIndex = nil;

        $this->lastQuery = $sql;

        switch ($method)
        {
            case 'resultsExist':
            case 'getAssociativeResults':
            case 'getResultCount':
            {
                return $this->hDB->$method($sql, true);
            }
            case 'getAssociativeArray':
            {
                return $this->getAssociativeArray(
                    $sql,
                    isset($prependResult[0]),
                    isset($prependResult[0]) ? $prependResult[0] : nil
                );
            }
            case 'getColumn':
            {
                return $this->hDB->getColumn($sql, $defaultResult);
            }
            case 'getResults':
            {
                return $this->getResults($sql, $resultIndex);
            }
            default:
            {
                return $this->$method($sql);
            }
        }
    }

    public function getLastQuery()
    {
        # @return string

        # @description
        # <h2>Debugging: Getting the SQL Text of the Last Query Executed</h2>
        # <p>
        #   This method returns the value of the <var>$lastQuery</var> property, which
        #   ideally, contains the complete text of the last SQL query executed.
        # </p>
        # @end

        return $this->lastQuery;
    }

    public function selectSingle($column, $value = nil, $table = nil)
    {
        # @return mixed

        # @description
        # <h2>Selecting a Single Column's Value From a Single Row</h2>
        # <p>
        #   Returns the value found in <var>$column</var> WHERE the primary
        #   key of that table is the value found in <var>$value</var>.
        # </p>
        # <p>
        #   Optionally, you can specifiy the table in the <var>$table</var> argument.
        #   If no <var>$table</var> is specified, then the value of the internal <var>$table</var>
        #   property is used.
        # </p>
        # @end
        $this->whichTable($table)->whichValue($value);

        $sql =
            "SELECT `{$column}`
               FROM `{$this->hDatabaseInitial}`.`{$table}`
              WHERE `{$this->primaryKeys[$table]}` = '{$value}'";

        $this->lastQuery = $sql;

        return $this->getResult($sql);
    }

    public function update(array $columns, $where = nil, $table = nil, $logicalOperator = 'AND', $key = nil, $quoteColumns = true)
    {
        # @return integer

        # @description
        # <h2>Updating Database Content</h2>
        #
        #
        # @end

        $this->whichTable($table)
             ->setTable($table)
             ->whichWhere($where, $table);

        if (is_array($where))
        {
            $where = $this->where($where, $logicalOperator, $key);
        }

        if (!empty($where))
        {
            $columnNames = $this->getColumnsForOperation($columns, $table);

            $this->expandNumericColumns($columns, $columnNames);

            $set = array();

            foreach ($columns as $column => $value)
            {
                if (isset($this->primaryKeys[$table]) && $this->primaryKeys[$table] != $column || !isset($this->primaryKeys[$table]))
                {
                    if ($quoteColumns)
                    {
                        $set[] = $this->getColumnValueSQL(nil, $column, '=', $value);
                    }
                    else
                    {
                        $set[] = "`{$column}` = {$value}";
                    }
                }
            }

            $sql =
              "UPDATE `{$this->hDatabaseInitial}`.`{$table}`
                  SET ".implode(',', $set)."
                WHERE {$where}".
               ($this->useLimit? ' LIMIT 1' : '');

            $this->lastQuery = $sql;

            return $this->getAffectedCount($sql);
        }

        return 0;
    }

    public function where(array $columns, $logicalOperator = 'AND', $key = nil)
    {
        # @return string

        # @description
        # <h2>Assembling a WHERE Clause</h2>
        #
        #
        #
        # @end

        $where = array();
        $statements = array();

        foreach ($columns as $column => $value)
        {
            if (is_numeric($column))
            {
                $column = $value;
                $value  = $key;
            }

            if (strstr($column, '.'))
            {
                list($table, $column) = explode('.', $column);
            }

            if (!is_array($value))
            {
                $columnResult = $this->checkValueForColumns($value);

                if ($columnResult !== false)
                {
                    list($table2, $column2) = $columnResult;
                }

                if (strstr($column, ' '))
                {
                    list($column, $operator) = explode(' ', $column);
                }
                else
                {
                    $operator = '=';
                }
            }
            else
            {
                if (is_array($value[0]))
                {
                    $operators = array();
                    $values = array();

                    foreach ($value as $c => $colValue)
                    {
                        array_push($operators, $colValue[0]);
                        array_push($values, $colValue[1]);
                    }
                }
                else
                {
                    $operator = $value[0];
                    $value    = $value[1];
                }
            }

            if (!isset($operators) && !isset($values))
            {
                array_push(
                    $where,
                    $this->getColumnValueSQL(
                        isset($table)? $table : nil,
                        $column,
                        $operator,
                        $value,
                        isset($table2)? $table2 : nil,
                        isset($column2)? $column2 : nil
                    )
                );
            }
            else
            {
                foreach ($operators as $v => $operator)
                {
                    $columnResult = $this->checkValueForColumns($values[$v]);

                    if ($columnResult !== false)
                    {
                        list($table2, $column2) = $columnResult;
                    }

                    array_push(
                        $where,
                        $this->getColumnValueSQL(
                            isset($table)? $table : nil,
                            $column,
                            $operator,
                            $values[$v],
                            isset($table2)? $table2 : nil,
                            isset($column2)? $column2 : nil
                        )
                    );

                    unset($table2, $column2);
                }

                unset($operators, $values);
            }

            unset($table2, $column2);
        }

        return implode(' '.$logicalOperator.' ', $where);
    }

    private function checkValueForColumns($value)
    {
        # @return array | boolean

        # @description
        # <h2>Sniffing Database Table/Columns From a Value</h2>
        # <p>
        # This method Looks at a value and determines whether the value is intended to be a
        # column.  This is used to do simple joins using the <var>select*</var> API.  The
        # value is determined to be a column if the column matches a regular expression that
        # looks for a value consistent with the column names used by Hot Toddy.  If the
        # regular expression matches, a further check is then done to see if the value
        # matches to a database table and column that exists.
        # </p>
        # <p>
        # If the value is a column, an array with the table and column is returned.  If the
        # value is not a column, the function returns false.
        # </p>
        # @end

        # The value might be table.column, indicating that the user
        # wishes to do an implied join
        if (strStr($value, '.'))
        {
            # Do a sanity check to rule out values that obviously
            # are not table.column references.
            if (preg_match('/^[A-Z|a-z|0-9|\s|\-|\_|\.]+$/', $value) > 0)
            {
                # Split so that the table and column bits can be validated.
                list($table, $column) = explode('.', $value);

                # The check for the existence of a table is very inexpensive and
                # should rule out the majority of false positives that make it
                # past the first check
                if ($this->tableExists($table))
                {
                    # Last ditch verification that the portion after the dot,
                    # is, in fact, a reference to a column.  This will cause
                    # errors to be logged if the string in question is not a column
                    return $this->columnExists($column, $table)? array($table, $column) : false;
                }
                else
                {
                    return false;
                }
            }
        }

        return false;
    }

    private function getColumnValueSQL($table, $column, $operator, $value, $table2 = nil, $column2 = nil)
    {
        $sql = '';

        if (substr($value, 0, 12) == 'FIND_IN_SET(')
        {
            # No column and no operator...
            $value = str_replace(')', ", `{$column}`)");
            $operator = false;
        }

        if ($operator != false)
        {
            if (!empty($table))
            {
                $sql .= "`{$table}`.";
            }

            $sql .= "`{$column}` {$operator} ";
        }

        if (!empty($table2) && !empty($column2))
        {
            $sql .= "`{$table2}`.`{$column2}`";
        }
        else
        {
            if (!is_numeric($value) && $value !== 0)
            {
                # Some edge cases..
                # using a MySQL method on the column value (only the methods specified in $this->methods
                # are presently supported).
                # strings that are already quoted should not be quoted again
                # incrementing a column value
                $isMethod = false;

                foreach ($this->methods as $method)
                {
                    if (substr($value, 0, strlen($method.'(') ) == $method.'(')
                    {
                        $isMethod = true;
                        break;
                    }
                }

                if (subStr($value, 0, 1) == "'" && subStr($value, -1) == "'" || $isMethod || subStr($value, 0, 1) == "`" && subStr($value, -1) == "`")
                {
                    $sql .= $value;
                }
                else if (strStr($value, '+') || strStr($value, '-')) # Increment/Decrement column
                {
                    switch (true)
                    {
                        case strStr($value, '+'):
                        {
                            $operator = '+';
                            break;
                        }
                        case strStr($value, '-'):
                        {
                            $operator = '-';
                            break;
                        }
                    }

                    list($first, $second) = explode($operator, $value);

                    $first = trim($first);
                    $second = trim($second);

                    $sql .= ($first == $column)? "`{$first}` {$operator} {$second}" : "'{$value}'";
                }
                else
                {
                    $sql .= "'{$value}'";
                }
            }
            else
            {
                if (subStr($value, 0, 1) == '0')
                {
                    $sql .= "'{$value}'";
                }
                else
                {
                    $sql .= empty($value)? '0' : $value;
                }
            }
        }

        return $sql;
    }

    public function truncate($table)
    {
        # @return integer

        # @description
        # <h2>Truncating a Database Table</h2>
        # <p>
        # This method executes the SQL <var>TRUNCATE <i>$table</i></var>,
        # which removes all data from the database table and resets the
        # auto incrementing primary key, if there is one.
        # </p>
        # <p>
        # This method is just a shortcut to the following:
        # </p>
        # <code>
        # $this-&gt;hDatabase-&gt;delete($table);
        # </code>
        # <p>
        # Which, provides the same results.
        # </p>
        # @end

        return $this->delete($table);
    }

    public function delete($tables, $columns = nil, $key = nil, $logicalOperator = 'AND')
    {
        # @return integer

        # @description
        # <h2>Deleting Records</h2>
        # <h3>Truncating Tables</h3>
        # <p>
        #    If no <var>$columns</var> or <var>$key</var> is specified, then the
        #    table(s) specified will be <b>TRUNCATED</b> (all data in the table will be
        #    deleted and this cannot be undone).  Example:
        # </p>
        # <code>
        #    $this-&gt;hFiles-&gt;delete();
        # </code>
        # <p>
        #    This is the same as calling:
        # </p>
        # <code>
        #    $this-&gt;hFiles-&gt;truncate();
        # </code>
        # <h3>Using a Flat Query</h3>
        # <p>
        #    Record(s) in a single table can be deleted with a call like this:
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete('hFiles', 'hFileId', 1);
        # </code>
        # <p>
        #    The preceding builds a query like this:
        # </p>
        # <code>
        #    DELETE FROM `hFiles` WHERE `hFileId` = 1;
        # </code>
        # <h3>Using Multiple Columns</h3>
        # <p>
        #    That simple query can be expanded to include multiple columns.
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete(
        #        'hFiles',
        #        array(
        #            'hFileId' =&gt; 1,
        #            'hUserId' =&gt; 1
        #        )
        #    );
        # </code>
        # <p>
        #    This query now includes multiple columns.
        # </p>
        # <code>
        #    DELETE FROM `hFiles` WHERE `hFileId` = 1 AND `hUserId` = 1;
        # </code>
        # <p>
        #    Whether or not 'AND' or 'OR' are used in the query can be controlled using
        #    the <var>$logicalOperator</var> argument (which is set to <var>'AND'</var> by default).
        #    Another example:
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete(
        #        'hFiles',
        #        array(
        #            'hFileId' =&gt; 1,
        #            'hUserId' =&gt; 1
        #        ),
        #        nil,
        #        'OR'
        #    );
        # </code>
        # <p>
        #    The preceding becomes the following query:
        # </p>
        # <code>
        #    DELETE FROM `hFiles` WHERE `hFileId` = 1 OR `hUserId` = 1;
        # </code>
        # <p>
        #    Another way to write this query would be this:
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete(
        #        'hFiles',
        #        array(
        #            'hFileId',
        #            'hUserId'
        #        ),
        #        1,
        #        'OR'
        #    );
        # </code>
        # <p>
        #    This is the SAME query.
        # </p>
        # <code>
        #    DELETE FROM `hFiles` WHERE `hFileId` = 1 OR `hUserId` = 1;
        # </code>
        # <h3>Deleting Records in Multiple Tables</h3>
        # <p>
        #    Records in multiple tables can be deleted if you provide an array in the
        #    <var>$tables</var> argument.  Example:
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete(
        #        array(
        #            'hFiles',
        #            'hFileVariables'
        #        ),
        #        'hFileId',
        #        1
        #    );
        # </code>
        # <p>
        #    In the preceding, the records where <var>`hFileId` = 1</var> are deleted from both
        #    the <var>hFiles</var> and <var>hFileVariables</var> tables.
        # </p>
        # <h4>Using Multiple Columns</h4>
        # <p>
        #    It is also possible to construct more complicated deletion queries.
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete(
        #        array(
        #            'hFiles',
        #            'hFileVariables'
        #        ),
        #        array(
        #            'hFileId' => 1,
        #            'hUserId' => 1
        #        ),
        #        1
        #    );
        # </code>
        # <p>
        #    The preceding translates to the following queries:
        # </p>
        # <code>
        #    DELETE FROM `hFiles` WHERE `hFileId` = 1 AND `hUserId` = 1;
        #    DELETE FROM `hFileVariables` WHERE `hFileId` = 1 AND `hUserId` = 1;
        # </code>
        # <p>
        #    The <var>$logicalOperator</var> can be used to modify the grammar of the query.  If you
        #    set <var>$logicalOperator</var> in the preceding to <var>'OR'</var> like this:
        # </p>
        # <code>
        #    $this-&gt;hDatabase-&gt;delete(
        #        array(
        #            'hFiles',
        #            'hFileVariables'
        #        ),
        #        array(
        #            'hFileId' => 1,
        #            'hUserId' => 1
        #        ),
        #        1,
        #        'OR'
        #    );
        # </code>
        # <p>
        #    This results in the following queries:
        # </p>
        # <code>
        #    DELETE FROM `hFiles` WHERE `hFileId` = 1 OR `hUserId` = 1;
        #    DELETE FROM `hFileVariables` WHERE `hFileId` = 1 OR `hUserId` = 1;
        # </code>
        # @end

        $this->lastQuery = nil;

        if (empty($columns) && empty($key))
        {
            if (!is_array($tables))
            {
                if (!empty($tables) && $this->tableExists($tables))
                {
                    return $this->getAffectedCount("TRUNCATE `{$tables}`");
                }
                else
                {
                    $GLOBALS['hFramework']->warning(
                        "Unable to truncate '{$tables}' because it does not exist.",
                        __FILE__, __LINE__
                    );

                    return 0;
                }
            }
            else
            {
                $counter = 0;

                foreach ($tables as $table)
                {
                    if (!empty($table) && $this->tableExists($table))
                    {
                        $counter += $this->getAffectedCount("TRUNCATE `{$table}`");
                    }
                    else
                    {
                        $GLOBALS['hFramework']->warning(
                            "Unable to truncate '{$table}' because it does not exist.",
                            __FILE__, __LINE__
                        );
                    }
                }
            }

            return $counter;
        }

        if (!empty($tables))
        {
            if (!empty($columns))
            {
                if (is_array($tables))
                {
                    $counter = 0;

                    foreach ($tables as $column => $table)
                    {
                        if ($this->tableExists($table))
                        {
                            if (is_numeric($column))
                            {
                                if (is_array($columns))
                                {
                                    $counter += $this->deleteSingle(
                                        $this->where(
                                            $columns,
                                            $logicalOperator
                                        ),
                                        $table,
                                        $key
                                    );
                                }
                                else
                                {
                                    $counter += $this->deleteSingle(
                                        "`{$columns}` = '{$key}'",
                                        $table
                                    );
                                }
                            }
                            else
                            {
                                $counter += $this->deleteSingle(
                                    "`{$columns}` = '{$key}'",
                                    $table
                                );
                            }
                        }
                        else
                        {
                            $GLOBALS['hFramework']->warning(
                                "Table '{$table}' does not exist.",
                                __FILE__, __LINE__
                            );
                        }
                    }

                    return $counter;
                }
                else
                {
                    if ($this->tableExists($tables))
                    {
                        if (is_array($columns))
                        {
                            return $this->deleteSingle(
                                $this->where(
                                    $columns,
                                    $logicalOperator
                                ),
                                $tables
                            );
                        }
                        else
                        {
                            return $this->deleteSingle(
                                "`{$columns}` = '{$key}'",
                                $tables
                            );
                        }
                    }
                    else
                    {
                        $GLOBALS['hFramework']->warning(
                            "Table '{$tables}' does not exist.",
                            __FILE__, __LINE__
                        );
                    }
                }
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    '2nd Argument must be an array of columns, a single column, '.
                    'or a single key value.',
                    __FILE__, __LINE__
                );
            }
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                '1st Argument must be an array of tables and columns, an array '.
                'of tables or a single table.',
                __FILE__, __LINE__
            );
        }

        return 0;
    }

    public function deleteSingle($where = nil, $table = nil)
    {
        # @return boolean | integer

        # @description
        # <h2>Deleting a Single Row</h2>
        # <p>
        #
        # </p>
        # @end

        $this->whichTable($table)
             ->whichWhere($where, $table);

        if (!empty($where) && !empty($table))
        {
            $sql =
                "DELETE
                   FROM `{$this->hDatabaseInitial}`.`{$table}`
                  WHERE {$where}".
                 ($this->useLimit? ' LIMIT 1' : '');

            $this->lastQuery = $sql;

            return $this->getAffectedCount($sql);
        }

        return 0;
    }

    public function updateSingle($column, $value, $primaryKeyValue = nil, $table = nil)
    {
        # @return integer

        # @description
        # <h2>Updating a Single Record in a Table</h2>
        # <p>
        # Updates the value of the column specified in <var>$column</var> to the
        # value specified in <var>$value</var>.
        # </p>
        # <p>
        # The table, specified in <var>$table</var>, or determined from a
        # call to <a href='#whichTable' class='code'>whichTable()</a> if not
        # specified, must have a primary key defined.
        # </p>
        # <p>
        # The value of the row to be updated is determined by the value passed in
        # <var>$primaryKeyValue</var>.  If no <var>$primaryKeyValue</var> is provided,
        # a call to <a href='#whichValue' class='code'>whichValue()</a> determines
        # which value to use for <var>$primaryKeyValue</var>
        # </p>
        # <p>
        # For debugging, you can print the full SQL query used in this method by calling:
        # </p>
        # <code>
        # echo $this-&gt;hDatabase-&gt;getLastQuery();
        # </code>
        # <p>
        # After calling <var>updateSingle()</var>
        # </p>
        # @end

        $this->whichValue($primaryKeyValue)->whichTable($table);

        if (empty($table) || empty($primaryKeyValue))
        {
            $GLOBALS['hFramework']->warning(
                "Call to updateSingle() failed, either the \$table, '{$table}' was ".
                "empty or the \$primaryKeyValue '{$primaryKeyValue}' was empty.",
                __FILE__, __LINE__
            );

            return false;
        }

        if (!is_numeric($primaryKeyValue))
        {
            $primaryKeyValue = "'{$primaryKeyValue}'";
        }

        $sql =
            "UPDATE `{$this->hDatabaseInitial}`.`{$table}`
                SET `{$column}` = '{$value}'
              WHERE `{$this->primaryKeys[$table]}` = {$primaryKeyValue}";

        $this->lastQuery = $sql;

        return $this->getAffectedCount($sql);
    }

    private function expandNumericColumns(array &$columns, array &$columnNames)
    {
        # @return void

        # @description
        # <h2>Getting Column Names for Numerically Offset Column References</h2>
        #
        #
        #
        # @end

        foreach ($columns as $column => $value)
        {
            # You may pass just the column values to be inserted as
            # long as the column count of the table matches the
            # number of values passed.  This automatically
            # converts the numeric keys into the actual column names.
            if (is_numeric($column))
            {
                unset($columns[$column]);
                $columns[$columnNames[$column]] = $value;
            }
        }
    }

    public function insert(array $columns, $table = nil)
    {
        # @return integer

        # @description
        # <h2>Inserting Records</h2>
        # <p>
        # This method executes an SQL <var>INSERT</var> statement.  The columns are
        # specified in the <var>$columns</var> argument as an <var>array</var>.  The
        # array provided is expected to match one of two conditions:
        # </p>
        # <ol>
        #   <li>
        #       Each key of the array is expected to be a column name,
        #       while each value of the array is expected to be the value
        #       inserted into that column.
        #   </li>
        #   <li>
        #       If numeric offset values are provided instead of column
        #       names, the column names will be filled in, in order.
        #       If there are too few, or too many columns provided, you
        #       may see unexpected results. Therefore, it is recommended
        #       that you <b>always</b> fill in the column names and do
        #       not rely on them getting filled in for you automatically.
        #   </li>
        # </ol>
        # <p>
        # If the <var>$table</var> is not provided, the value used for <var>$table</var>
        # is provided by <a href='#whichTable' class='code'>whichTable()</a>.
        # </p>
        # <p>
        # Whatever the value provided in <var>$table</var> becomes the value of the
        # object's <var>table</var> property, which is used by default, when no table
        # name is provided explicitly.
        # </p>
        # <p>
        # If the <var>INSERT</var> was successful, this method returns the value of
        # last inserted auto increment key, otherwise this method returns zero.
        # </p>
        # <p>
        # For debugging, call <a href='#getLastQuery' class='code'>getLastQuery()</a>
        # which will return the string of the last executed SQL statement.
        # </p>
        # <p>
        # If the primary increment key is empty or nil, it implies that you wish
        # that field to be automatically incremented.
        # </p>
        # @end

        $this->whichTable($table)
             ->setTable($table);

        $columnNames = $this->getColumnsForOperation($columns);

        $this->expandNumericColumns(
            $columns,
            $columnNames
        );

        foreach ($columns as $column => $value)
        {
            if ($this->hasIncrementKey() && $this->primaryIncrementKeys[$table] == $column)
            {
                $columns[$column] = 'null';
            }
            else
            {
                $columns[$column] = $this->getColumnValueSQL(nil, $column, false, $value);
            }
        }

        $sql =
            "INSERT INTO `{$this->hDatabaseInitial}`.`{$table}` (".
                hString::implodeToList($columnNames, ',', '`').
            ") VALUES (".
                implode(',', $columns).
            ")";

        if ($table != 'hErrorLog')
        {
            $this->lastQuery = $sql;
        }

        $this->query($sql);

        return $this->hasIncrementKey($table)? $this->getInsertedId() : 0;
    }

    public function save($columns, $table = nil)
    {
        $this->whichTable($table)
             ->setTable($table);

        # @return integer | void
        # <p>
        #    If the table has a primary key and a record was inserted, the value of the
        #    newly inserted primary key is returned.  If the table has a primary key and
        #    the record was updated, the value of the primary key is returned.
        # </p>
        # <p>
        #    If there is no primary key, the <var>INSERT</var> or <var>UPDATE</var> query
        #    is carried out based on the value of the first column, and the value of the
        #    first column is returned.
        # </p>
        # @end

        # @argument $columns array
        # <p>
        #   An associative array of columns, the key is the column name in the table,
        #   the value is the value to be inserted or updated in that column.  The value
        #   of the primary key column determines whether an update or insert operation
        #   is performed.
        # </p>
        # @end

        # @argument $table string
        # <p>
        #   The database table to perform the operation on.
        # </p>
        # @end

        # @description
        # <h2>Saving a Record</h2>
        # <p>
        #     This method integrates the functionality of both the <a href='#insert' class='code'>insert()</a>
        #     and <a href='#update' class='code'>update()</a> methods, and successfully replaces
        #     the need for those methods entirely in tables that either have a primary key, or
        #     where the first column in the table is unique.
        # </p>
        # <p>
        #     If a table has a primary column, which is either unique or auto incrementing,
        #     this method executes an <var>INSERT</var> or <var>UPDATE</var> SQL statement
        #     automatically depending on whether or not the value provided for the primary
        #     column exists.  If the value does not exist, an <var>INSERT</var> is done.  If
        #     the value does exist, an <var>UPDATE</var> is done.  The primary column is
        #     automatically detected based on the table's definition.
        # </p>
        # <p>
        #    Providing an empty value for the primary key column is an automatic trigger
        #    for an <var>INSERT</var> query.  This is typically done by passing the primary
        #    column's value as <var>0</var>.
        # </p>
        # <p>
        #     The <var>$columns</var> argument is automatically validated to make sure the
        #     columns specified actually exist in the <var>$table</var>.  If the array of
        #     columns is associative, the values used for the keys are expected to be the
        #     column names.  If the array of
        #     <var>$columns</var> is numerically offset, instead of associative, the count
        #     of the columns provided must match the count of the columns of the target table.
        # </p>
        # @end

        $columns = $this->filterTableColumns($columns);

        $primaryColumn = $this->getPrimaryKey();

        if (!empty($primaryColumn))
        {
            if (empty($columns[$primaryColumn]))
            {
                return $this->insert($columns);
            }
            else
            {
                $where = array($primaryColumn => $columns[$primaryColumn]);

                if ($this->selectExists($primaryColumn, $table, $where))
                {
                    $this->update($columns, $where);
                    return $columns[$primaryColumn];
                }
                else
                {
                    return $this->insert($columns);
                }
            }
        }
        else
        {
            $firstColumn = $this->getFirstColumn();

            if (!empty($firstColumn))
            {
                $where = array($firstColumn => $columns[$firstColumn]);

                # Take the first column and make a decision to insert based on
                # whether the value of the first column exists in the table.
                if ($this->selectExists($firstColumn, $table, $where))
                {
                    $this->update($columns, $where);
                }
                else
                {
                    $this->insert($columns);
                }

                return $columns[$firstColumn];
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    "Error retrieving the first column for table '{$table}'.",
                    __FILE__, __LINE__
                );
            }
        }
    }

    private function getColumnsForOperation(array &$columns, $table = nil)
    {
        $this->whichTable($table)->setTable($table)->getColumns($table);

        $columnNames = array_keys($columns);

        if (!$this->columnsInTable($columnNames) && isset($this->columns[$table]) && count($columnNames) == count($this->columns[$table]))
        {
            $columnNames = array_keys($this->columns[$table]);
        }

        return $columnNames;
    }

    public function filterTableColumns(array $columns, $table = nil)
    {
        # @return array

        # @description
        # <h2>Filtering Table Columns</h2>
        # <p>
        #   This method takes an array of table column names
        #   and returns a filtered array containing only the
        #   names of columns in the specified table. Columns
        #   not in the table are discarded.
        # </p>
        # @end

        $this->whichTable($table);

        $columnsInTable = $this->getColumnNames($table);

        $filteredColumns = array();

        if (is_array($columnsInTable) && count($columnsInTable))
        {
            foreach ($columnsInTable as $columnInTable)
            {
                if (array_key_exists($columnInTable, $columns))
                {
                    $filteredColumns[$columnInTable] = $columns[$columnInTable];
                }
            }

            if (!count($filteredColumns))
            {
                if (count($columns) == count($columnsInTable))
                {
                    # Maybe the array passed has numeric indices, see if we can match it to the column count
                    $filteredColumns = array_combine(
                        $columnsInTable,
                        $columns
                    );
                }
            }

            return $filteredColumns;
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                "Unable to retrieve columns for table '{$table}'.",
                __FILE__, __LINE__
            );
        }
    }

    public function columnsInTable(array $columns, $table = nil)
    {
        $this->whichTable($table);
        $tracker = true;

        foreach($columns as $column)
        {
            if (!empty($column))
            {
                if (!$this->columnExists($column, $table))
                {
                    $tracker = false;
                }
            }
        }

        return $tracker;
    }

    public function columnInTable($column, $table = nil)
    {
        $this->whichTable($table)
             ->getColumns($table);

        return array_key_exists(
            $column,
            $this->columns[$table]
        );
    }

    private function &whichTable(&$table)
    {
        if (empty($table))
        {
            if (!empty($this->table))
            {
                $table = $this->table;
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    'No default table is set.',
                    __FILE__, __LINE__
                );
            }
        }

        if (!empty($table))
        {
            if (!$this->tableExists($table))
            {
                $backtrace = debug_backtrace();

                $GLOBALS['hFramework']->warning(
                    "Table '{$table}' does not exist, called in hDatabase::{$backtrace[2]['function']}(). ",
                    __FILE__, __LINE__
                );
            }
            else
            {
                $this->getColumns($table);
            }
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                'No table could be selected.',
                __FILE__, __LINE__
            );
        }

        return $this;
    }

    public function &setTable($table, $value = nil)
    {
        $this->table = $table;

        if (!empty($value))
        {
            $this->whichValue($value);
        }

        return $this;
    }

    public function refresh()
    {
        return $this->getTables(true);
    }

    public function getTables($refresh = false)
    {
        if (!count($this->tables) || $refresh)
        {
            $this->tables = array();
            $this->firstColumns = array();
            $this->primaryKeys = array();
            $this->columns = array();
            $this->primaryIncrementKeys = array();

            $query = $this->query(
                "SHOW TABLES FROM `{$this->hDatabaseInitial}`"
            );

            while ($data = $this->getNumberedResults($query))
            {
                if (!in_array($data[0], $this->tables))
                {
                    array_push($this->tables, $data[0]);
                }
            }
        }

        return $this->tables;
    }

    public function tableExists($table)
    {
        return in_array($table, $this->tables);
    }

    private function &whichValue(&$value)
    {
        # @return hDatabase

        # @description
        # <h2>Determining Which Value to Use</h2>
        # <p>
        #    Methods that call for a primary key value call upon this method to determine
        #    whether to use a parameter passed to that method, or whether to use the
        #    value of the internal <var>primaryKeyValue</var> property.
        # </p>
        # @end

        if (empty($value))
        {
            if (!empty($this->primaryKeyValue))
            {
                $value = $this->primaryKeyValue;
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    'No default primary key value is set.',
                    __FILE__, __LINE__
                );
            }
        }

        return $this;
    }

    public function &setPrimaryKeyValue($value)
    {
        # @return hDatabase

        # @description
        # <h2>Setting the Value of the Primary Key</h2>
        # <p>
        #    Sets the value of the internal <var>primaryKeyValue</var> property.  Methods that
        #    call upon a the method <a href='#whichValue' class='code'>whichValue()</a> use the
        #    value of <var>primaryKeyValue</var> if the <var>$value</var> parameter is not specified.
        #    This makes it possible to perform multiple operations with the same primary key row, without
        #    having to respecify the value of the primary key.
        # </p>
        # @end

        $this->primaryKeyValue = $value;
        return $this;
    }

    public function hasPrimaryKey($table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Table has a Primary Key</h2>
        # <p>
        #    Returns <var>true</var> or <var>false</var> depending on whether the
        #    table specified in <var>$table</var> has a primary key.
        # </p>
        # @end

        $this->whichTable($table);
        return isset($this->primaryKeys[$table]);
    }

    public function isPrimaryKey($column, $table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column is the Primary Key</h2>
        # <p>
        #    Returns <var>true</var> or <var>false</var> depending on whether the specified
        #    <var>$column</var> in <var>$table</var> is a pimary key.
        # </p>
        # @end

        $this->whichTable($table);

        if ($this->hasPrimaryKey($table) && $this->primaryKeys[$table] == $column)
        {
            return true;
        }

        return false;
    }

    public function hasIncrementKey($table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Table has an Auto Increment Column</h2>
        # <p>
        #    Returns <var>true</var> or <var>false</var> depending on whether the
        #    table specified in <var>$table</var> has an auto-incrementing column.
        # </p>
        # @end

        $this->whichTable($table);

        return isset($this->primaryIncrementKeys[$table]);
    }

    public function getPrimaryKey($table = nil)
    {
        # @return string

        # @description
        # <h2>Getting the Primary Key Column</h2>
        # <p>
        #    Returns the name of the column that presently acts as the primary key
        #    for the specified <var>$table</var>.
        # </p>
        # @end

        $this->whichTable($table);

        return isset($this->primaryKeys[$table])? $this->primaryKeys[$table] : '';
    }

    public function getFirstColumn($table = nil)
    {
        # @return false | string

        # @description
        # <h2>Getting the First Column's Name</h2>
        # <p>
        #    Returns the name of the first column in the specified <var>$table</var>.
        # </p>
        # @end

        $this->whichTable($table);

        if (isset($this->firstColumns[$table]))
        {
            return $this->firstColumns[$table];
        }
        else
        {
            $query = $this->getResults(
                "SHOW COLUMNS FROM `{$table}`"
            );

            if (count($query))
            {
                foreach ($query as $data)
                {
                    $this->firstColumns[$table] = $data['Field'];

                    return $this->firstColumns[$table];
                }
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    "Query Failed '{$query}'.",
                    __FILE__, __LINE__
                );

                return false;
            }
        }

        return false;
    }

    public function &whichWhere(&$where, $table = nil)
    {
        $this->whichTable($table);

        if (empty($where))
        {
            if (empty($this->where))
            {
                if ($this->hasPrimaryKey($table))
                {
                    if ($this->primaryKeyValue)
                    {
                        $where = "`{$this->primaryKeys[$table]}` = '{$value}'";
                    }
                }
            }
            else
            {
                $where = $this->where;
            }
        }
        else
        {
            if (is_numeric($where))
            {
                if ($this->hasPrimaryKey($table))
                {
                    $where = "`{$this->primaryKeys[$table]}` = ".$where;
                }
                else
                {
                    $firstColumn = $this->getFirstColumn($table);

                    // Make the statement start with the first column of the table...
                    if (!empty($firstColumn))
                    {
                        $where = "`{$firstColumn}` = ".$where;
                    }
                    else
                    {
                        $GLOBALS['hFramework']->warning(
                            "Unable to determine a column to use for table '{$table}' ".
                            "with value '{$where}'.",
                            __FILE__, __LINE__
                        );
                    }
                }
            }
        }

        return $this;
    }

    private function &decideResultCount($sql)
    {
        # @return hDatabase

        # @description
        # <h2>Deciding How to Set the Result Count</h2>
        # <p>
        #    When a query contains <var>SQL_CALC_FOUND_ROWS</var>, the total number of
        #    results found are stored in <var>resultCount</var>.  <var>SQL_CALC_FOUND_ROWS</var>
        #    provides the total number of found rows, even when a query is limited with
        #    a <var>LIMIT</var> clause. If a query does not contain <var>SQL_CALC_FOUND_ROWS</var>,
        #    the value stored in <var>resultCount</var> is the result count (the number of rows AFTER the
        #    <var>LIMIT</var> clause has been applied).
        # </p>
        # @end

        if (is_string($sql))
        {
            $this->countType = strStr($sql, 'SQL_CALC_FOUND_ROWS')? '' : 'numRows';
        }

        return $this;
    }

    private function &setResultCount($query = nil)
    {
        # @return hDatabase

        # @description
        # <h2>Setting the Result Count</h2>
        # <p>
        #    When a query contains <var>SQL_CALC_FOUND_ROWS</var>, the total number of
        #    results found are stored in <var>resultCount</var>.  <var>SQL_CALC_FOUND_ROWS</var>
        #    provides the total number of found rows, even when a query is limited with
        #    a <var>LIMIT</var> clause. If a query does not contain <var>SQL_CALC_FOUND_ROWS</var>,
        #    the value stored in <var>resultCount</var> is the result count (the number of rows AFTER the
        #    <var>LIMIT</var> clause has been applied).
        # </p>
        # @end

        $this->resultCount = $this->countType ?
            $this->getResultCount($query) : $this->getColumn("SELECT FOUND_ROWS()");

        return $this;
    }

    public function getResultCount()
    {
        # @return integer

        # @description
        # <h2>Getting the Result Count</h2>
        # <p>
        #    When a query contains <var>SQL_CALC_FOUND_ROWS</var>, the total number of
        #    results found are stored in <var>resultCount</var>.  <var>SQL_CALC_FOUND_ROWS</var>
        #    provides the total number of found rows, even when a query is limited with
        #    a <var>LIMIT</var> clause. If a query does not contain <var>SQL_CALC_FOUND_ROWS</var>,
        #    the value stored in <var>resultCount</var> is the result count (the number of rows AFTER the
        #    <var>LIMIT</var> clause has been applied). Other ways to
        #    get a result count include: <var>getResultCount()</var>, <var>getAffectedCount()</var>,
        #    or simply <var>count()</var>.
        # </p>
        # @end

        return (int) $this->resultCount;
    }

    public function getAssociativeArray($sql, $prependValue = false, $prependString = '')
    {
        # @return array

        # @description
        # <h2>Getting an Associative Array</h2>
        # <p>
        #    Returns query results where the first column in the query is used to create the keys
        #    of the array and the second column in the query is used for the corresponding value.
        #    If <var>$prependValue</var> is true, the value of <var>$prependString</var> becomes the
        #    first value in the array returned.
        # </p>
        # <p>
        #    Imagine this query:
        # </p>
        # <code>
        #    SELECT `hLocationStateId`,
        #           `hLocationStateCode`
        #      FROM `hLocationStates`
        #     WHERE `hLocationCountryId` = 223 # The U.S.A.
        #  ORDER BY `hLocationStateCode` ASC
        # </code>
        # <p>
        #    The results of this query will look something like this:
        # </p>
        # <code>
        #    +---------------------+-----------------------+
        #    | hLocationStateId    | hLocationStateCode    |
        #    +---------------------+-----------------------+
        #    | 7                   | AA                    |
        #    | 8                   | AC                    |
        #    | 9                   | AE                    |
        #    | 6                   | AF                    |
        # </code>
        # <p>
        #    This method will return, by default, an array that looks like this:
        # </p>
        # <code>
        #    array(
        #        7 => 'AA',
        #        8 => 'AC',
        #        9 => 'AE',
        #        6 => 'AF'
        #    );
        # </code>
        # <p>
        #    And the <var>$prependValue</var> and <var>$prependString</var> arguments can be
        #    used like this:
        # </p>
        # <code>
        #    array(
        #        0 => "Please select a state",
        #        7 => 'AA',
        #        8 => 'AC',
        #        9 => 'AE',
        #        6 => 'AF'
        #    );
        # </code>
        # <p>
        #    The value associated with <var>$prependString</var> is always <var>0</var>.
        # </p>
        # @end

        $this->decideResultCount($sql);
        $query = $this->query($sql);
        $this->setResultCount($query);

        $options = array();

        if (!empty($prependValue))
        {
            $options[0] = $prependString;
        }

        while ($data = $this->getNumberedResults($query))
        {
            $options[$data[0]] = $data[1];
        }

        $this->closeResults($query);

        return $options;
    }

    public function getResultsAsArray($sql, $index = nil)
    {
        # @return array

        # @description
        # <h2>Getting Query Results</h2>
        # <p>
        #    Deprecated.  Use <a href='#getResults'>getResults()</a> instead.
        # </p>
        # @end

        return $this->getResults($sql, $index);
    }

    public function getResults($sql, $index = nil)
    {
        # @return array

        # @description
        # <h2>Getting Query Results</h2>
        # <p>
        #    This method takes the provided SQL (which is executed and converted
        #    into an array) and structures the returned array in one of the following
        #    ways.
        # </p>
        # <ol>
        #    <li>
        #        If <var>$index</var> is specified, the column in the result
        #        set with the name matching <var>$index</var> is used to
        #        set the value of each array key to the value of that column.
        #        The entire result set is then assigned to that array key.
        #    </li>
        #    <li>
        #        If only a single column is selected in the query, then a
        #        numerically offset array is returned, each corresponding value
        #        to each numeric indice is the value of the selected column's row.
        #    </li>
        #    <li>
        #        If there are multiple columns selected and no <var>$index</var> is
        #        specified, then the results are returned in a numerically offset
        #        array, where each indice contains an array representing a row of
        #        results, this method matches the <var>select()</var> method.
        #    </li>
        # </ol>
        # @end

        if (empty($sql))
        {
            return false;
        }

        $this->decideResultCount($sql);

        if (is_string($sql))
        {
            $query = $this->query($sql);
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                'Unable to get database results because $sql was not a string. '.
                'You probably meant to call select(). Now attempting to '.
                'automatically reconcile this problem.', __FILE__, __LINE__
            );

            if (is_array($sql))
            {
                $arguments = func_get_args();

                return call_user_func_array(
                    array(
                        $this,
                        'select'
                    ),
                    $arguments
                );
            }

            return $sql;
        }

        $columnCount = $this->getColumnCount();
        $this->setResultCount($query);

        $results = array();

        if (!empty($index))
        {
            while ($data = $this->getAssociativeResults($query))
            {
                $results[isset($data[$index])? $data[$index] : count($results)] = $data;
            }
        }
        else if ($columnCount > 1)
        {
            while ($data = $this->getAssociativeResults($query))
            {
                array_push($results, $data);
            }
        }
        else
        {
            while ($data = $this->getNumberedResults($query))
            {
                array_push($results, $data[0]);
            }
        }

        $this->closeResults($query);
        return $results;
    }

    public function getResultsForTemplate($results)
    {
        # @return array

        # @description
        # <h2>Getting Query Results For a Template</h2>
        # <p>
        #    This method takes the provided array, or provided SQL (which is first executed and converted
        #    into an array) and structures the array so that it can be properly used inside of a Hot Toddy
        #    Template.
        # </p>
        # @end

        if (is_array($results))
        {
            if (count($results))
            {
                $rtn = array();

                foreach ($results as $i => $result)
                {
                    if (is_array($result))
                    {
                        foreach ($result as $key => $value)
                        {
                            $rtn[$key][] = $value;
                        }
                    }
                    else
                    {
                        $GLOBALS['hFramework']->warning(
                            "Get results for template failed!  Item '{$i}' is not an array.",
                            __FILE__, __LINE__
                        );
                    }
                }

                if (isset($key) && isset($rtn[$key]))
                {
                    $i = count($rtn[$key]);

                    if (!empty($i))
                    {
                        for ($c = 0; $c < $i; $c++)
                        {
                            $rtn['isOdd'][] = !($c & 1);
                        }
                    }
                }

                if ($GLOBALS['hFramework']->hFileGetMetaData(false) && (isset($rtn['hFileId']) || isset($rtn['hFileId'])))
                {
                    return $this->getFileMetaDataForTemplate($rtn);
                }

                return $rtn;
            }

            return array();
        }
        else if (!empty($results))
        {
            return $this->getResultsForTemplate($this->getResults($results));
        }
        else
        {
            return array();
        }
    }

    public function implodeResults($sql, $glue = '')
    {
        # @return string

        # @description
        # <h2>Imploding Database Results</h2>
        # <p>
        #    Executes the provided SQL in <var>$sql</var> and returns a string where
        #    results from every found row are joined together using the string specified in
        #    <var>$glue</var>.
        # </p>
        # @end

        $query = $this->query($sql);

        $rtn = array();

        while ($data = $this->getAssociativeResults($query))
        {
            foreach ($data as $key => $value)
            {
                $rtn[] = $value;
            }
        }

        $this->closeResults($query);

        return implode($glue, $rtn);
    }

    public function getResult($sql, $default = '')
    {
        # @return mixed

        # @description
        # <h2>Getting a Single Row From a Single Column's Value</h2>
        # <p>
        #    Executes the provided SQL in <var>$sql</var> and returns a single row from a
        #    single column.  If no results are found, then the value provided in <var>$default</var>
        #    is returned.
        # </p>
        # @end

        return $this->getColumn($sql, $default);
    }

    public function getResultByKey($field, $table, $key, $keyValue, $default = '')
    {
        # @return mixed

        # @description
        # <h2>Getting Results By Key Value</h2>
        # <p>
        #    Selects <var>$field</var> from <var>$table</var> where <var>$key</var> is <var>$keyValue</var>.
        #    If no record is found, then the value provided in <var>$default</var> is returned.
        # </p>
        # @end

        return $this->getColumn(
            "SELECT `{$field}`
               FROM `{$table}`
              WHERE `{$key}` = ". (int) $keyValue,
            $default
        );
    }

    public function getColumnType($column, $table = nil)
    {
        # @return string

        # @description
        # <h2>Getting a Column's Type</h2>
        # <p>
        #    Returns a string containing the column's type, such as: <var>int(11) NOT NULL default '0'</var>
        # </p>
        # @end

        $this->whichTable($table)->getColumns($table);

        if (isset($this->columns[$table][$column]['Type']))
        {
            return $this->columns[$table][$column]['Type'];
        }
        else
        {
            $GLOBALS['hFramework']->warning(
                "No type is defined for column '{$column}' in table '{$table}'.",
                __FILE__, __LINE__
            );
        }
    }

    public function columnIsNumeric($column, $table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining If a Column Is Numeric</h2>
        # <p>
        #    This method determines if the provided <var>$column</var> in the provided <var>$table</var>
        #    is a numeric data type.  The following data types are checked:
        # </p>
        # <ul>
        #    <li class='code'>tinyint</li>
        #    <li class='code'>smallint</li>
        #    <li class='code'>mediumint</li>
        #    <li class='code'>int</li>
        #    <li class='code'>bigint</li>
        #    <li class='code'>integer</li>
        #    <li class='code'>dec</li>
        #    <li class='code'>decimal</li>
        #    <li class='code'>numeric</li>
        #    <li class='code'>float</li>
        #    <li class='code'>real</li>
        # </ul>
        # @end

        $this->whichTable($table);

        $bits = explode('(', $this->getColumnType($column, $table));

        return in_array(
            strToLower(
                array_shift($bits)
            ),
            array(
                'tinyint',
                'smallint',
                'mediumint',
                'int',
                'bigint',
                'integer',
                'dec',
                'decimal',
                'numeric',
                'float',
                'real'
            )
        );
    }

    public function getColumns($table)
    {
        # @return array

        # @description
        # <h2>Getting and Caching Column Data for Each Table</h2>
        # <p>
        #    This method creates several member properties that contain cached information about
        #    the columns and the specified table, this information is gathered by calling
        #    <var>SHOW COLUMNS FROM `{table}`</var>.
        # </p>
        # @end

        $table = trim($table);

        if (empty($table))
        {
            $GLOBALS['hFramework']->warning(
                'Unable to get columns for table because no table was provided.',
                __FILE__, __LINE__
            );

            return;
        }
        else
        {
            if (!isset($this->columns[$table]) || empty($this->columns[$table]) || !is_array($this->columns[$table]) || !count($this->columns[$table]))
            {
                $query = $this->getResults("SHOW COLUMNS FROM `{$table}`");

                if (count($query))
                {
                    $i = 0;

                    foreach ($query as $data)
                    {
                        if (!$i)
                        {
                            $this->firstColumns[$table] = $data['Field'];
                        }

                        $this->columns[$table][$data['Field']] = $data;

                        if ($data['Key'] == 'PRI')
                        {
                            $this->primaryKeys[$table] = $data['Field'];
                        }

                        if ($data['Extra'] == 'auto_increment')
                        {
                            $this->primaryIncrementKeys[$table] = $data['Field'];
                        }

                        $i++;
                    }
                }
                else
                {
                    $GLOBALS['hFramework']->warning(
                        "Query Failed '{$query}'.",
                        __FILE__, __LINE__
                    );
                }
            }
        }

        return $this->columns[$table];
    }

    public function &uses()
    {
        # @return hDatabase

        # @description
        # <h2>Creating Multiple Tables From SQL Files</h2>
        # <p>
        #    This method takes one or more names of tables in its parameters, if each table
        #    is defined in Hot Toddy's hDatabase/hDatabaseStructure folder and does not already exist,
        #    then it will be installed.  The file must exist at:
        # </p>
        # <code>
        #    {hFrameworkPath}/Hot Toddy/hDatabase/hDatabaseStructure/{table}/{$table}.sql
        # </code>
        # @end

        $arguments = func_get_args();

        foreach ($arguments as $table)
        {
            if (!$this->tableExists($table))
            {
                $this->createTableFromFile($table);
            }
        }

        return $this;
    }

    public function &createTableFromFile($table)
    {
        # @return hDatabase

        # @description
        # <h2>Creating a Table From SQL File</h2>
        # <p>
        #    This method takes the name of a table in the <var>$table</var> argument, if that table
        #    is defined in Hot Toddy's hDatabase/hDatabaseStructure folder and does not already exist,
        #    then it will be installed.  The file must exist at:
        # </p>
        # <code>
        #    {hFrameworkPath}/Hot Toddy/hDatabase/hDatabaseStructure/{table}/{$table}.sql
        # </code>
        # @end

        if (!$this->tableExists($table))
        {
            $path = $GLOBALS['hFramework']->hFrameworkPath.'/Hot Toddy/hDatabase/hDatabaseStructure/'.$table.'/'.$table.'.sql';

            if (file_exists($path))
            {
                // Attempt to automatically create each database table.
                $this->query(
                    file_get_contents($path)
                );

                $GLOBALS['hFramework']->addTableObject($table);

                array_push($this->tables, $table);
            }
        }

        return $this;
    }

    public function getPostDataByColumnName($table, array $columns = array())
    {
        # @return array

        # @description
        # <h2>Retrieving POST Data By Table Column Names</h2>
        # <p>
        #    This method returns data in the <var>POST</var> array based on the provided
        #    <var>$table</var> and <var>$columns</var>.  If only a <var>$table</var> is provided,
        #    any <var>POST</var> data with the same name as any column in that table will be
        #    returned.  If <var>$columns</var> is specified, only the specified columns will be
        #    returned, and anything else will be ignored.
        # </p>
        # @end

        //$this->checkArgument($table, '!empty');

        $columnsInTable = $this->getColumnNames($table);

        $data = array();

        foreach ($columnsInTable as $column)
        {
            if (isset($_POST[$column]) && (count($columns) && in_array($column, $columns) || !count($columns)))
            {
                $data[$column] = $_POST[$column];
            }
        }

        return $data;
    }

    public function getColumnNames($table)
    {
        # @return array

        # @description
        # <h2>Retrieving Table Column Names</h2>
        # <p>
        #    This method returns the names of the columns for the specified <var>$table</var>
        # </p>
        # @end

        if (!isset($this->columns[$table]))
        {
            $this->getColumns($table);
        }

        return array_keys($this->columns[$table]);
    }

    public function &deleteTableCache($table)
    {
        # @return hDatabase

        # @description
        # <h2>Deleting a Table from the Database's Table Data Cache</h2>
        # <p>
        #    When the <var>hDatabase</var> object calls some methods, the query
        #    <var>SHOW COLUMNS FROM `{table}`</var> is ran and information about each table and the
        #    columns within each table is gathered and cached in various member properties for
        #    later use.  This method deletes data from the various properties that store
        #    cached data for tables.  This method is called automatically by existing methods
        #    that delete tables.
        # </p>
        # @end

        unset($this->firstColumns[$table]);
        unset($this->primaryKeys[$table]);
        unset($this->columns[$table]);
        unset($this->primaryIncrementKeys[$table]);

        foreach ($this->tables as $i => $tableName)
        {
            if ($tableName == $table)
            {
                unset($this->tables[$i]);
            }
        }

        return $this;
    }

    public function &addTableToCache($table)
    {
        # @return hDatabase

        # @description
        # <h2>Adding a Table to the Database's Table Data Cache</h2>
        # <p>
        #    When the <var>hDatabase</var> object calls some methods, the query
        #    <var>SHOW COLUMNS FROM `{table}`</var> is ran and information about each table and the
        #    columns within each table is gathered and cached in various member properties for
        #    later use.  This method adds data to the various properties that store
        #    cached data for tables.  This method is called automatically by existing methods
        #    that create tables.
        # </p>
        # @end

        if (!in_array($table, $this->tables))
        {
            array_push($this->tables, $table);
        }

        return $this;
    }

    public function &renameTableCache($oldName, $newName)
    {
        # @return hDatabase

        # @description
        # <h2>Renaming the Database's Table Data Cache</h2>
        # <p>
        #    When the <var>hDatabase</var> object calls some methods, the query
        #    <var>SHOW COLUMNS FROM `{table}`</var> is ran and information about each table and the
        #    columns within each table is gathered and cached in various member properties for
        #    later use.  This method renames data stored in the various properties that store
        #    cached data for tables.  This method is called automatically by existing methods
        #    that rename tables.
        # </p>
        # @end

        foreach ($this->tables as $i => $table)
        {
            if ($tableName == $oldName)
            {
                $this->tables[$i] = $newName;
                break;
            }
        }

        if (isset($this->columns[$oldName]))
        {
            $this->columns[$newName] = $this->columns[$oldName];
            unset($this->columns[$oldName]);
        }

        if (isset($this->firstColumns[$oldName]))
        {
            $this->firstColumns[$newName] = $this->firstColumns[$oldName];
            unset($this->firstColumns[$oldName]);
        }

        if (isset($this->primaryKeys[$oldName]))
        {
            $this->primaryKeys[$newName] = $this->primaryKeys[$oldName];
            unset($this->primaryKeys[$oldName]);
        }

        if (isset($this->primaryIncrementKeys[$oldName]))
        {
            $this->primaryIncrementKeys[$newName] = $this->primaryIncrementKeys[$oldName];
            unset($this->primaryIncrementKeys[$oldName]);
        }

        return $this;
    }

    public function &deleteColumnCache($column, $table)
    {
        # @return hDatabase

        # @description
        # <h2>Deleting the Database's Column Data Cache</h2>
        # <p>
        #    When the <var>hDatabase</var> object calls some methods, the query
        #    <var>SHOW COLUMNS FROM `{table}`</var> is ran and information about each table and the
        #    columns within each table is gathered and cached in various member properties for
        #    later use.  This method deletes data stored in the various properties that store
        #    cached data.  This method is called automatically by existing methods that delete
        #    columns or tables.
        # </p>
        # @end

        if (isset($this->columns[$table]))
        {
            if (isset($this->columns[$table][$column]))
            {
                unset($this->columns[$table][$column]);
            }
        }

        if (isset($this->firstColumns[$table]))
        {
            if ($this->firstColumns[$table] == $column)
            {
                unset($this->firstColumns[$table]);
                $this->getFirstColumn($table);
            }
        }

        if (isset($this->primaryKeys[$table]))
        {
            if ($this->primaryKeys[$table] == $column)
            {
                unset($this->primaryKeys[$table]);
            }
        }

        if (isset($this->primaryIncrementKeys[$table]))
        {
            if ($this->primaryIncrementKeys[$table] == $column)
            {
                unset($this->primaryIncrementKeys[$table]);
            }
        }

        return $this;
    }

    public function &renameColumnCache($oldName, $newName, $table)
    {
        # @return hDatabase

        # @description
        # <h2>Renaming the Database's Column Data Cache</h2>
        # <p>
        #    When the <var>hDatabase</var> object calls some methods, the query
        #    <var>SHOW COLUMNS FROM `{table}`</var> is ran and information about each table and the
        #    columns within each table is gathered and cached in various member properties for
        #    later use.  This method renames data stored in the various properties storing
        #    cached data.  It's called automatically by existing methods that rename
        #    columns or tables.
        # </p>
        # @end

        if (isset($this->columns[$table]))
        {
            if (isset($this->columns[$table][$oldName]))
            {
                $copy = array();

                # This keeps the column in the correct position.
                foreach ($this->columns[$table] as $column => $data)
                {
                    if ($column == $oldName)
                    {
                        $copy[$newName] = $data;
                    }
                    else
                    {
                        $copy[$column] = $data;
                    }
                }

                $this->columns[$table] = $copy;
            }
        }

        if (isset($this->firstColumns[$table]))
        {
            if ($this->firstColumns[$table] == $oldName)
            {
                $this->firstColumns[$table] = $newName;
            }
        }

        if (isset($this->primaryKeys[$table]))
        {
            if ($this->primaryKeys[$table] == $oldName)
            {
                $this->primaryKeys[$table] = $newName;
            }
        }

        if (isset($this->primaryIncrementKeys[$table]))
        {
            if ($this->primaryIncrementKeys[$table] == $oldName)
            {
                $this->primaryIncrementKeys[$table] = $newName;
            }
        }

        return $this;
    }

    public function isAutoIncrementColumn($column, $table = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining If a Column Is Auto Increment</h2>
        # <p>
        #    This method takes a <var>$column</var> and optional <var>$table</var> argument,
        #    and determines if the column is an auto increment column.  This is determined
        #    by consulting a cache of data about each table and column gathered from the
        #    query <var>SHOW COLUMNS FROM `{table}`</var>
        # </p>
        # @end

        $this->whichTable($table);

        if (!empty($table))
        {
            if (!isset($this->primaryIncrementKeys[$table]))
            {
                $this->getColumns($table);
            }

            if (isset($this->primaryIncrementKeys[$table]))
            {
                return strToLower($this->primaryIncrementKeys[$table]) == strToLower($column);
            }
        }

        return false;
    }

    private function whichColumnExists($column, $table)
    {

    }
}

?>