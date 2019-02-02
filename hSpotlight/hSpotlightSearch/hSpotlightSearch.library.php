<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Spotlight Search
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

class hSpotlightSearchLibrary extends hPlugin {

    private $tablesInQuery        = array();
    private $tables               = array();
    private $table                = '';
    private $joinColumns          = array();
    private $ammendTables         = array();
    private $isIrrelevantTable    = false;
    private $defaultSearch        = true;
    private $whereBoolean         = 'OR';
    private $whereConditions      = array();
    private $whereAddendums       = array();
    private $whereAddendumBoolean = array();

    public function &addWhereAddendum($boolean, $sql)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Where Clause Addendum</h2>
        # <p>
        #
        # </p>
        # @end

        array_push($this->whereAddendums, $sql);
        array_push($this->whereAddendumBoolean, $boolean);

        return $this;
    }

    public function &addTable($table, $label)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Table to Search</h2>
        # <p>
        #
        # </p>
        # @end

        $this->tables[$table] = array(
            'label' => $label
        );

        $this->table = $table;
        return $this;
    }

    public function &setColumns(&$columns, &$validation, &$time, &$location, &$sort, &$sortOrientation)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Setting Columns for Search</h2>
        # <p>
        #
        # </p>
        # @end

        if (!empty($_POST['hSpotlightSearchColumns']) && is_array($_POST['hSpotlightSearchColumns']))
        {
            $columns = $_POST['hSpotlightSearchColumns'];
        }

        $validation = $this->validateColumns($columns);

        $time = array();

        if (!empty($_POST['hSpotlightSearchToggleTime']))
        {
            # Make sure the time column is kosher
            if ($this->validateTimeColumn($_POST['hSpotlightSearchTimeColumn']))
            {
                $time = array(
                    'range'  => $_POST['hSpotlightSearchTimeRange'],
                    'start'  => $_POST['hSpotlightSearchDateStart'],
                    'end'    => $_POST['hSpotlightSearchDateEnd'],
                    'column' => $_POST['hSpotlightSearchTimeColumn']
                );
            }
        }

        $this->setSort($sort, $sortOrientation);

        $location = array();

        if (!empty($_POST['hSpotlightSearchToggleLocation']))
        {
            if (!empty($_POST['hSpotlightSearchCountryId']))
            {
                $location['countryId'] = (int) $_POST['hSpotlightSearchCountryId'];
            }

            if (!empty($_POST['hSpotlightSearchStateId']))
            {
                $location['stateId'] = (int) $_POST['hSpotlightSearchStateId'];
            }

            if (!empty($_POST['hSpotlightSearchCity']))
            {
                $location['city'] = $_POST['hSpotlightSearchCity'];
            }

            if (!empty($_POST['hSpotlightSearchPostalCode']))
            {
                $location['postalCode'] = $_POST['hSpotlightSearchPostalCode'];
            }

            if (!empty($_POST['hSpotlightSearchCounty']))
            {
                $location['county'] = $_POST['hSpotlightSearchCounty'];
            }
        }

        return $this;
    }

    public function &setSort(&$sort, &$sortOrientation)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Setting Sort Parameters</h2>
        # <p>
        #
        # </p>
        # @end

        if (!empty($_GET['hSpotlightSortColumn']))
        {
            if ($this->validateSortColumn($_GET['hSpotlightSortColumn']))
            {
                $sort = $_GET['hSpotlightSortColumn'];

                if (!empty($_GET['hSpotlightSortOrientation']))
                {
                    switch ($_GET['hSpotlightSortOrientation'])
                    {
                        case 'ASC':
                        case 'DESC':
                        {
                            $sortOrientation = $_GET['hSpotlightSortOrientation'];
                            break;
                        }
                        default:
                        {
                            $sortOrientation = 'ASC';
                        }
                    }
                }
                else
                {
                    $sortOrientation = 'ASC';
                }
            }
        }

        return $this;
    }

    public function &setWhereBoolean($boolean)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Setting Where Clause Boolean</h2>
        # <p>
        #
        # </p>
        # @end

        switch ($boolean)
        {
            case 'AND':
            case 'OR':
            {
                $this->whereBoolean = $boolean;
                break;
            }
        }

        return $this;
    }

    public function &addColumn($column, $label, $attributes)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Column to the Search</h2>
        # <p>
        #
        # </p>
        # @end

        if (!isset($this->tables[$this->table]['columns']))
        {
            $this->tables[$this->table]['columns'] = array();
        }

        $this->tables[$this->table]['columns'][$column] = array(
            'label' => $label,
            'attributes' => $attributes,
            'joinColumns' => $this->joinColumns
        );

        $this->joinColumns = array();

        return $this;
    }

    public function &defineJoinColumns()
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Defining Join Columns</h2>
        # <p>
        #
        # </p>
        # @end

        $this->joinColumns = func_get_args();

        return $this;
    }

    public function &addAdvancedColumn($column, $label)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding an Column to Advanced Search</h2>
        # <p>
        #
        # </p>
        # @end

        $this->addColumn(
            $column,
            $label,
            array(
                'isDefault'  => false, # Whether or not the field appears in the default extended search list
                'isSortable' => false, # Whether or not the field is available as a sort option
                'isSelected' => false, # Whether or not the field is selected to be included in the results list
                'isTime'     => false, # Whether or not the field is a timestamp, thus appearing in the time constraint options
                                       # of an extended search
                'isAdvanced' => true   # Whether or not the field is an advanced search field
            )
        );

        return $this;
    }

    public function &addAdvancedColumns(array $columns)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding Multiple Columns to Advanced Search</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($columns as $column => $label)
        {
            $this->addAdvancedColumn($column, $label);
        }

        return $this;
    }

    public function &addSortableColumn($column, $label)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Sortable Column</h2>
        # <p>
        #
        # </p>
        # @end

        $this->addColumn(
            $column,
            $label,
            array(
                'isDefault'  => false,
                'isSortable' => true,
                'isSelected' => false,
                'isTime'     => false,
                'isAdvanced' => true
            )
        );

        return $this;
    }

    public function &addDefaultColumn($column, $label)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Default Column</h2>
        # <p>
        #
        # </p>
        # @end

        $this->addColumn(
            $column,
            $label,
            array(
                'isDefault'  => true,
                'isSortable' => true,
                'isSelected' => true,
                'isTime'     => false,
                'isAdvanced' => false
            )
        );

        return $this;
    }

    public function &addDefaultColumns(array $columns)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding Multiple Default Columns</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($columns as $column => $label)
        {
            $this->addDefaultColumn($column, $label);
        }

        return $this;
    }

    public function &addTimeColumn($column, $label)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Time Column</h2>
        # <p>
        #
        # </p>
        # @end

        $this->addColumn(
            $column,
            $label,
            array(
                'isDefault'  => false,
                'isSortable' => true,
                'isSelected' => false,
                'isTime'     => true,
                'isAdvanced' => false
            )
        );

        return $this;
    }

    public function &addTimeColumns(array $columns)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <p>
        #
        # </p>
        # @end

        foreach ($columns as $column => $label)
        {
            $this->addTimeColumn($column, $label);
        }

        return $this;
    }

    public function getSortColumns($table = nil)
    {
        # @return array
        # @description
        # <h2>Getting Sortable Columns</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getColumnsByAttribute($table, 'isSortable');
    }

    public function getSelectColumns($table = nil)
    {
        # @return array

        # @description
        # <h2>Getting Select Columns</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getColumnsByAttribute($table, 'isSelected');
    }

    public function getTimeColumns($table = nil)
    {
        # @return array

        # @description
        # <h2>Getting Time Columns</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getColumnsByAttribute($table, 'isTime');
    }

    public function validateTimeColumn($time)
    {
        # @return boolean

        # @description
        # <h2>Validating Time Columns</h2>
        # <p>
        #
        # </p>
        # @end

        return in_array($time, $this->getTimeColumns());
    }

    public function validateSortColumn($sort)
    {
        # return boolean

        # @description
        # <h2>Validating Sort Columns</h2>
        # <p>
        #
        # </p>
        # @end

        return in_array($sort, $this->getSortColumns());
    }

    public function getDefaultColumns($table = nil)
    {
        # @return array

        # @description
        # <h2>Getting Default Columns</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getColumnsByAttribute($table, 'isDefault');
    }

    public function &setColumnSelected($table, $column)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Setting the Selected Column</h2>
        # <p>
        #
        # </p>
        # @end

        $this->setColumnAttribute(
            $table,
            $column,
            'isSelected',
            true
        );

        return $this;
    }

    public function &setColumnAttribute($table, $column, $attribute, $value)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Setting Column Attribute</h2>
        # <p>
        #
        # </p>
        # @end

        if (isset($this->tables[$table]['columns'][$column]['attributes'][$attribute]))
        {
            $this->tables[$table]['columns'][$column]['attributes'][$attribute] = $value;
        }

        return $this;
    }

    public function getColumnLabel($column)
    {
        # @return string

        # @description
        # <h2>Getting a Column Label</h2>
        # <p>
        #
        # </p>
        # @end

        $column = str_replace('`', '', $column);
        list($table, $column) = explode('.', $column);

        if (isset($this->tables[$table]['columns'][$column]['label']))
        {
            return $this->tables[$table]['columns'][$column]['label'];
        }
        else
        {
            $this->warning(
                'There is no label defined for column, '.$column.', in table, '.$table.'.',
                __FILE__,
                __LINE__
            );
        }
    }

    public function getColumnsByAttribute($getTable, $attribute = false)
    {
        # @return array

        # @description
        # <h2>Getting Columns by Attribute</h2>
        # <p>
        #
        # </p>
        # @end

        if (!empty($getTable))
        {
            if (isset($this->tables[$getTable]))
            {
                $this->getTableColumnsByAttribute(
                    $returnColumns,
                    $getTable,
                    $attribute
                );
            }
            else
            {
                $this->warning(
                    'Table, '.$getTable.' not defined. '.
                    'Unable to select columns by attribute, '.$attribute.'.',
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            foreach ($this->tables as $table => $array)
            {
                $this->getTableColumnsByAttribute(
                    $returnColumns,
                    $table,
                    $attribute
                );
            }
        }

        return $returnColumns;
    }

    private function &getTableColumnsByAttribute(&$returnColumns, $getTable, $attribute)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Getting Table Columns By Attribute</h2>
        # <p>
        #
        # </p>
        # @end

        if (is_array($this->tables[$getTable]['columns']) && count($this->tables[$getTable]['columns']))
        {
            foreach ($this->tables[$getTable]['columns'] as $column => $data)
            {
                if (isset($data['attributes'][$attribute]) && !empty($data['attributes'][$attribute]) || empty($attribute))
                {
                    $this->addTableToQuery("`{$getTable}`");
                    $returnColumns[] = "`{$getTable}`.`{$column}`";
                }
            }
        }
        else
        {
            $this->warning(
                'Table, '.$getTable.', does not have any columns defined. '.
                'Unable to get columns by attribute, '.$attribute.'.',
                __FILE__,
                __LINE__
            );
        }

        return $this;
    }

    # Make sure that only fields allowed to be queried are submitted to
    # be queried.  Otherwise, there is the possibility for arbitrarily
    # accessing database data.
    #
    # Fields that are not allowed to be queried are removed from the
    # submitted set.
    #
    # Function returns false if bad data is detected, true if it believes
    # all is peachy.
    #
    # @param array $submittedColumns
    # @return bool
    public function validateColumns(&$submittedColumns)
    {
        # @return boolean

        # @description
        # <h2>Validating Columns</h2>
        # <p>
        #
        # </p>
        # @end

        if (empty($submittedColumns))
        {
            return true;
        }

        # Make allowed columns more easily searchable...
        $allowedColumns = $this->getDefaultColumns();
        $validation = true;

        foreach ($submittedColumns as $i => $column)
        {
            if (!in_array($column, $allowedColumns))
            {
                unset($submittedColumns[$i]);
                $validation = false;
            }
        }

        return $validation;
    }

    public function &addTableToQuery($table)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Table to a Query</h2>
        # <p>
        #
        # </p>
        # @end

        if (!in_array($table, $this->tablesInQuery))
        {
            array_push($this->tablesInQuery, $table);
        }

        return $this;
    }

    public function &reset()
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Resetting the Tables in the Query</h2>
        # <p>
        #
        # </p>
        # @end

        $this->tablesInQuery = array();
        return $this;
    }

    public function getTablesInQuery()
    {
        # @return array

        # @description
        # <h2>Returning the Tables in the Query</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->tablesInQuery;
    }

    public function getWhereClause($where, $search, $wildcard = nil)
    {
        # @return string

        # @description
        # <h2>Gettign the Where Clause</h2>
        # <p>
        #
        # </p>
        # @end

        if ($search == '*')
        {
            return nil;
        }

        $sql = array();

        $rolodex = false;

        if (strlen($search) == 1)
        {
            $rolodex = true;
        }

        if (is_numeric($search) || $rolodex)
        {
            if ($rolodex && in_array('`hContacts`.`hContactLastName`', $where))
            {
                $where = array(
                    '`hContacts`.`hContactLastName`'
                );
            }

            # Columns must be numeric
            foreach ($where as $column)
            {
                $columnIsNumeric = $this->columnIsNumeric($column);
                $columnIsBoth = false;

                if ($column == '`hProducts`.`hProductPartNumber`')
                {
                    $columnIsBoth = true;
                }

                if ($columnIsNumeric && is_numeric($search) || !$columnIsNumeric && !is_numeric($search) || $columnIsBoth)
                {
                    # Get the default wildcard for a numeric query, exact match.
                    if (strstr($search, ' '))
                    {
                        switch ($column)
                        {
                            case '`hContacts`.`hContactFirstName`':
                            {
                                $bits = explode(' ', $search);
                                $q = array_shift($bits);
                                break;
                            }
                            case '`hContacts`.`hContactLastName`':
                            {
                                $bits = explode(' ', $search);
                                $q = array_pop($bits);
                                break;
                            }
                            default:
                            {
                                $q = $search;
                            }
                        }
                    }
                    else
                    {
                        $q = $search;
                    }

                    $sql[] = $column.$this->getWildcard($q, $wildcard);
                }
            }

            return count($sql)? implode(' '.$this->whereBoolean.' ', $sql) : false;
        }
        else
        {
            # If this is the default search...
            # Columns must not be numeric for a fulltext query
            # Default columns must be optimized with a fulltext index.
            foreach ($where as $column)
            {
                if (!$this->columnIsNumeric($column))
                {
                    $sql[] = $column;
                }
            }

            return count($sql)? ' MATCH ('.implode(',', $sql).") AGAINST ('{$search}' IN BOOLEAN MODE)" : false;
        }
    }

    public function columnIsNumeric($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column is Numeric</h2>
        # <p>
        #
        # </p>
        # @end

        # See if the $column is of a numerical type
        # if it is numeric, don't allow strings to be queried
        # against it.
        list($table, $column) = explode('.', str_replace('`', '', $column));

        return $this->hDatabase->columnIsNumeric(
            $column,
            $table
        );
    }

    public function getWildcard($query, $wildcard)
    {
        # @return string

        # @description
        # <h2>Getting the Comparison Operator or Wildcard</h2>
        # <p>
        #
        # </p>
        # @end

        # Should be double-encoded.
        $wildcard = htmlspecialchars_decode($wildcard);
        $wildcard = htmlspecialchars_decode($wildcard);

        switch ($wildcard)
        {
            case '%.%':   # Contains
            {
                return " LIKE '%{$query}%'";
            }
            case '.%':    # Begins With
            {
                return " LIKE '{$query}%'";
            }
            case '%.':    # Ends With
            {
                return " LIKE '%{$query}'";
            }
            case '=':     # Equals
            {
                return " = '{$query}'";
            }
            case '!=':    # Is Not
            {
                return " NOT LIKE '{$query}'";
            }
            case '>':     # Is Greater Than
            {
                return ' > '.(is_numeric($query)? $query : 0);
            }
            case '<':     # Is Less Than
            {
                return ' < '.(is_numeric($query)? $query : 0);
            }
            case '>=':    # Is Greater Than or Equal To
            {
                return ' >= '.(is_numeric($query)? $query : 0);
            }
            case '<=':    # Is Less Than or Equal To
            {
                return ' <= '.(is_numeric($query)? $query : 0);
            }
            default:
            {
                # No wildcard specified...
                # If the query is a number, default to exact match.
                # Otherwise, default to "contains".
                if (is_numeric($query))
                {
                    # Don't typecast it, it could be an integer or a float.
                    return " = ".$query;
                }
                else
                {
                    return " LIKE '{$query}%'";
                }
            }
        }
    }

    public function isRelevant($columns, $table)
    {
        # @return boolean

        # @description
        # <h2>Determining if Columns Are Relevant</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($columns as $column)
        {
            if (array_shift(explode('.', $column)) == "`{$table}`")
            {
                return true;
            }
        }

        return false;
    }

    public function keysInResult($selectColumns, $data)
    {
        # @return boolean

        # @description
        # <h2>Determining if the Keys are in the Result</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($selectColumns as $column)
        {
            $column = str_replace('`', '', $column);
            list($table, $tableColumn) = explode('.', $column);

            if (!key_exists($column, $data))
            {
                return false;
            }
        }

        return true;
    }

    public function &ammendResults(&$results, $table)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Ammending the Results</h2>
        # <p>
        #
        # </p>
        # @end

        $selectColumns = $this->getSelectColumns($table);

        $select = implode(',', $selectColumns);
        $from   = implode(',', $this->getTablesInQuery());

        foreach ($results as $key => $data)
        {
            if (is_array($data) && !$this->keysInResult($selectColumns, $data) && isset($data[$results['key']]) && is_array($results[$data[$results['key']]]))
            {
                $whereCondition = $this->getWhereCondition();

                $sql =
                    "SELECT {$select}
                       FROM {$from}
                      WHERE ".(!empty($whereCondition)? $whereCondition.' AND' : '')." (`{$table}`.`{$results['key']}` = ". $data[$results['key']].")";

                $query = $this->hDatabase->query($sql);

                if ($this->hDatabase->resultsExist($query))
                {
                    $results[$data[$results['key']]] = array_merge($results[$data[$results['key']]], $this->hDatabase->getAssociativeResults($query));
                }
            }
        }

        return $this;
    }

    public function &ammendTables(&$results)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Ammending the Tables</h2>
        # <p>
        #
        # </p>
        # @end

        foreach ($this->ammendTables as $table)
        {
            $this->ammendResults($results, $table);
        }

        return $this;
    }

    public function getTime($time)
    {
        # @return string

        # @description
        # <h2>Retrieving Time Query</h2>
        # <p>
        #
        # </p>
        # @end

        $bits = explode('.', $time['column']);
        $timeTable = array_shift($bits);

        $this->addTableToQuery($timeTable);

        if (!empty($time['range']))
        {
            switch ($time['range'])
            {
                case -30:
                {
                    $date = strtotime('-1 Month');
                    break;
                }
                case -60:
                {
                    $date = strtotime('-2 Months');
                    break;
                }
                case -90:
                {
                    $date = strtotime('-3 Months');
                    break;
                }
                case -365:
                {
                    $date = strtotime('-1 Year');
                    break;
                }
            }

            return " AND {$time['column']} >= ".$date;
        }
        else
        {
            # Custom
            return
                " AND {$time['column']} >= ".strtotime($time['start']).
                " AND {$time['column']} <= ".strtotime($time['end']);
        }
    }

    public function &addWhereCondition($condition)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Adding a Where Condition</h2>
        # <p>
        #
        # </p>
        # @end

        if (!in_array($condition, $this->whereConditions))
        {
            array_push($this->whereConditions, $condition);
        }

        return $this;
    }

    public function getWhereCondition()
    {
        # @return string

        # @description
        # <h2>Retrieving a Where Condition</h2>
        # <p>
        #
        # </p>
        # @end

        return implode(' AND ', $this->whereConditions);
    }

    public function &query($table, $search, $where, $time, $sort, $sortOrientation, $join, &$results, $wildcard = nil)
    {
        # @return hSpotlightSearchLibrary

        # @description
        # <h2>Executing a Query</h2>
        # <p>
        #
        # </p>
        # @end

        if (!is_array($results))
        {
            $results = array();
        }

        if (!empty($sort))
        {
            $bits = explode('.', $sort);
            $this->addTableToQuery(array_shift($bits));
        }

        # Take the provided results and expand on those with
        # information from the address book.
        if (empty($where))
        {
            $this->defaultSearch = true;
            $where = $this->getDefaultColumns($table);
        }
        else
        {
            $this->defaultSearch = false;
        }

        # No need to continue if there is nothing to search for in
        # this table.
#        if (!$this->isRelevant($where, $table))
#        {
#            $this->isIrrelevantTable = true;
#
#            $key = array_search("`{$table}`", $this->tablesInQuery);
#            unset($this->tablesInQuery[$key]);
#            return $results;
#        }

        # If the columns specified in $where are limited, see whether or not there are any columns
        # that include the hContacts tables, if not, there is no query to be done.

        $select        = $this->getSelectColumns($table);
        $constrainTime = (count($time)? $this->getTime($time) : '');
        $from          = $this->getTablesInQuery();

        $sql = '';

        $rolodex = false;

        if (strlen($search) == 1)
        {
            $rolodex = true;
            $wildcard = '.%';
        }

        if (false !== ($where = $this->getWhereClause($where, $search, $wildcard)))
        {
            $sql =
                 "SELECT DISTINCT ".
                    implode(',', $select).
                 " FROM ";

            if (!empty($join))
            {
                $fromTable = array_shift($from);

                $bits = explode('.', $join);
                $joinTable  = array_shift($bits);
                $bits = explode('.', $join);
                $joinColumn = array_pop($bits);

                $joints = array();

                $sql .= " ".$fromTable." ";

                foreach ($from as $table)
                {
                    $joinToTable = ($table == $joinTable)? $fromTable : $table;

                    $sql .= " LEFT JOIN {$table} ON {$join} = {$joinToTable}.{$joinColumn}";
                }
            }
            else
            {
                $sql .= implode(',', $from);
            }

            $whereSQL = '';

            $fulltext = substr($where, 0, 6) == ' MATCH';

            if ($where)
            {
                if (!$fulltext)
                {
                    $where = '('.$where.')';
                }

                $whereSQL .= "{$where}";
            }

            if (!empty($this->whereAddendums))
            {
                foreach ($this->whereAddendums as $i => $whereAddendum)
                {
                    $whereSQL .= ' '.$this->whereAddendumBoolean[$i].' ('.$whereAddendum.')';
                }
            }

            if (!empty($whereSQL))
            {
                $sql .= " WHERE ({$whereSQL})";
            }

            if (!empty($whereSQL))
            {
                $sql .= ' AND '.$this->getWhereCondition();
            }

            $sql .= $constrainTime;

            if (!empty($sort))
            {
                $sql .= " ORDER BY {$sort} {$sortOrientation}";
            }

            $query = $this->hDatabase->query($sql);

            if ($this->hDatabase->resultsExist($query))
            {
                while ($data = $this->hDatabase->getAssociativeResults($query))
                {
                    # Results already in the set
                    $results[] = $data;
                }
            }
        }

        $this->reset();

        return $this;
    }

    public function advancedQuery()
    {

    }
}

?>