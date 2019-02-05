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
# <h1>Database Reflection API</h1>
# <p>
#   The <var>hDatabase/hDatabaseTable</var> provides functionality associated with
#   database reflection.  Database reflection creates objects for each table in the
#   database, allowing you to work with the database in a purely object-oriented
#   fashion.  Most of the methods provided here simply map back to methods in the
#   <var>hDatabase</var> plugin, eliminating the need for the <var>$table</var>
#   argument, since the <var>$table</var> argument is provided in the object that
#   you call the method from.  For example, a call using a method defined in
#   <var>hDatabase</var>:
# </p>
# <code>
#   $this-&gt;hDatabase-&gt;select('*', 'hFiles');
# </code>
# <p>
#   The preceding code selects all records in the <var>hFiles</var> database table. The
#   following shows how you'd do that same call using the reflection API:
# </p>
# <code>
#   $this-&gt;hFiles-&gt;select('*');
# </code>
# <p>
#   As you can see, the table <var>hFiles</var> has become the object that you call the
#   method from, rather than an argument in the method call.  You can do the same
#   for any method in the <var>hDatabase</var> object that has a <var>$table</var>
#   argument.  You simply omit the <var>$table</var> argument, and call the method
#   from an object using the same name as the table you wish to use.
# </p>
# @end

class hDatabaseTable {

    private $table;

    private $hDatabase;
    private $hDatabaseEditor;
    private $hContactDatabase;
    private $hFrameworkResource;
    
    private $user;
    private $contact;
    private $hUserPermission;
    private $hFramework;
    private $where = nil;
    private $method = nil;

    private $columns = array();
    private $primaryKey = nil;

    public function __construct($table, &$hDatabase)
    {
        $this->table = $table;
        $this->hDatabase = $hDatabase;
    }

    private function &userPermission()
    {
        # @return hUserPermissionLibrary

        # @description
        # <h2>Including the User Permission Library Object</h2>
        # <p>
        #
        # </p>
        # @end

        if (!is_object($this->hUserPermission))
        {
            $this->hUserPermission = $GLOBALS['hFramework']->library('hUser/hUserPermission');
        }

        if (!$this->frameworkResource()->isResource($this->table))
        {
            $GLOBALS['hFramework']->warning(
                "Table '{$this->table}' is not a resource.  Called by {$this->method}.",
                __FILE__,
                __LINE__
            );
        }

        return $this->hUserPermission;
    }

    private function &editor()
    {
        # @return hDatabaseEditorLibrary

        # @description
        # <h2>Including the Database Editor Library Object</h2>
        # <p>
        #
        # </p>
        # @end


        if (!is_object($this->hDatabaseEditor))
        {
            $this->hDatabaseEditor = $GLOBALS['hFramework']->library('hDatabase/hDatabaseEditor');
        }

        return $this->hDatabaseEditor;
    }

    private function &subscription()
    {
        # @return hSubscriptionLibrary

        # @description
        # <h2>Including the Subscription Library Object</h2>
        # <p>
        #
        # </p>
        # @end

        if (!is_object($this->hSubscription))
        {
            $this->hSubscription = $GLOBALS['hFramework']->library('hSubscription');
        }

        return $this->hSubscription;
    }

    private function &contactDatabase()
    {
        # @return hContactDatabase

        # @description
        # <h2>Including the Contact Database Object</h2>
        # <p>
        #   Includes the <a href='/Hot Toddy/Documentation?hContact.database.php' class='code'>hContactDatabase</a>
        #   object.
        # </p>
        # @end

        if (!is_object($this->hContactDatabase))
        {
            $this->hContactDatabase = $GLOBALS['hFramework']->database('hContact');
        }

        return $this->hContactDatabase;
    }
    
    public function &frameworkResource()
    {
        # @return hFrameworkResourceLibrary
        
        # @description
        # <h2>Using the Framework Resource API</h2>
        # <p>
        #   Intializes the <a href='/Hot Toddy/Documentation?hFrameworkResource.library.php' class='code'>hFrameworkResourceLibrary</a> 
        #   object the first time it's used, and then it returns the 
        #   <a href='/Hot Toddy/Documentation?hFrameworkResource.library.php' class='code'>hFrameworkResourceLibrary</a> 
        #   object.
        # </p>
        # @end

        if (!is_object($this->hFrameworkResource))
        {
            $this->hFrameworkResource = $GLOBALS['hFramework']->library('hFramework/hFrameworkResource');
        }

        return $this->hFrameworkResource;
    }

    public function &renameThisTable($table)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Renaming the Database Table Object</h2>
        # <p>
        #
        # </p>
        # @end

        $this->table = $table;
        $this->columns = array();
        $this->primaryKey = nil;

        return $this;
    }

    public function __get($key)
    {
        // Returns all values of a column, by default
        if (!empty($this->primaryKey))
        {
            return $this->hDatabase->select(
                $key,
                $this->table,
                $this->columns[$this->primaryKey],  // Where
                'AND',
                nil,  // Order
                nil,  // Limit
                'getColumn'
            );
        }
        else
        {
            // No primary key has been previously set, therefore, there is no context.
            return $this->hDatabase->select($key, $this->table);
        }
    }

    public function __set($key, $value)
    {
        if ($this->hDatabase->columnExists($key, $this->table))
        {
            $this->columns[$key] = $value;

            if ($this->hDatabase->isPrimaryKey($key, $this->table))
            {
                $this->primaryKey = $key;
            }
        }
    }

    public function __isset($key)
    {
        // In this context, $key refers to a column in the table.
        return $this->hDatabase->columnExists($key, $this->table);
    }

    public function __unset($key)
    {
        // Remove the specified column from the table
        // if ($this->hDatabase->columnExists($key, $this->table))
        // {
        //     $this->hDatabase->query("ALTER TABLE `{$this->table}` DROP COLUMN `{$key}`");
        // }
    }

    public function &reset()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Resetting the Database Table Object</h2>
        # <p>
        #
        # </p>
        # @end

        $this->columns = array();
        $this->primaryKey = nil;

        return $this;
    }

    public function __call($method, $arguments)
    {
        if (!is_object($this->user) && isset($GLOBALS['hFramework']->user) && is_object($GLOBALS['hFramework']->user))
        {
            $this->user = &$GLOBALS['hFramework']->user;
        }

        if (!is_object($this->contact) && isset($GLOBALS['hFramework']->contact) && is_object($GLOBALS['hFramework']->contact))
        {
            $this->contact = &$GLOBALS['hFramework']->contact;
        }

        $this->method = $method;

        if (substr($method, 0, 6) == 'select')
        {
            $GLOBALS['hFramework']->warning(
                "Unimplemented method '{$method}' called from the context of database table '{$this->table}'.",
                __FILE__,
                __LINE__
            );

            return;
        }

        if ($this->hDatabase->columnExists($method, $this->table))
        {
            // $this->hFiles->hFileId($where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil);
            //
            // --> SELECT `hFileId` FROM `hFiles`;

            $data = $this->hDatabase->select(
                $method,
                $this->table,
                isset($arguments[0])? $arguments[0] : nil,  // Where
                isset($arguments[1])? $arguments[1] : 'AND', // AND, OR
                isset($arguments[2])? $arguments[2] : nil,  // Order
                isset($arguments[3])? $arguments[3] : nil,  // Limit
                'getResults'
            );

            if (is_array($data) && count($data) == 1)
            {
                return !is_array($data[0])? $data[0] : $data;
            }

            return $data;
        }
    }

    public function select($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return array

        # @description
        # <h2>Selecting Results</h2>
        # <p>
        #    The select* series of methods provide API replacements for most things that would
        #    have to be written using SQL syntax.  These methods provide a programmatic, API
        #    equivalent that allow you to avoid directly writing SQL.
        # </p>
        # <p>
        #    This method returns all results based on the supplied criteria.  See:
        #    <a href='/Hot Toddy/Documentation?hDatabase#getResults'>hDatabase::getResults()</a> to see
        #    the various ways that results can be returned depending on the supplied criteria.
        # </p>
        # <p>
        #    This method is passed to
        #    <a href='/Hot Toddy/Documentation?hDatabase#select'>hDatabase::select()</a>, where a
        #    SQL is built from the supplied arguments.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getResults'
        );
    }

    public function selectResults($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return array

        # @description
        # <h2>Selectin Results</h2>
        # <p>
        #    This method is an alias of: <a href='#select'>select()</a>
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getResults'
        );
    }

    public function selectColumn($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return mixed

        # @description
        # <h2>Selecting a Single Result</h2>
        # <p>
        #    Returns a single column's value from a single row.  If multiple columns or rows are
        #    returned in the query, the first column from the first row of results is returned.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getColumn'
        );
    }

    public function selectExists($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Result Exists</h2>
        # <p>
        #    Determines if the query turns up a result, if so, it returns <var>true</var>, if no results are
        #    found, it returns false.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'resultsExist'
        );
    }

    public function selectAssociative($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return boolean

        # @description
        # <h2>Returning a Single Row of Results</h2>
        # <p>
        #    Returns all selected columns from a single row of results.  If multiple rows are returned
        #    in the query, the first row of results is returned.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getAssociativeResults'
        );
    }

    public function selectQuery($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return object | resource

        # @description
        # <h2>Returning a Query</h2>
        # <p>
        #    Returns a query object or resource, depending on which driver the database is using.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'query'
        );
    }

    public function selectCount($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return integer

        # @description
        # <h2>Returning a Count</h2>
        # <p>
        #    Returns a count of the total results in a query (after the <var>LIMIT</var> clause has
        #    been applied)
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getResultCount'
        );
    }

    public function selectColumnsAsKeyValue($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return array

        # @description
        # <h2>Returning an Array Using One Columns for Keys and Another as Values</h2>
        # <p>
        #    Returns an array where the first column in the selection is used for the keys
        #    of the returned array, and the second column in the selection is used as the
        #    corresponding values for those keys.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getAssociativeArray'
        );
    }

    public function selectForTemplate($columns = '*', $where = nil, $logicalOperator = 'AND', $order = nil, $limit = nil)
    {
        # @return array

        # @description
        # <h2>Selecting Results for a Template</h2>
        # <p>
        #    Returns an array structured so that it can be directory used with Hot Toddy template syntax.
        # </p>
        # @end

        return $this->hDatabase->select(
            $columns,
            $this->table,
            $where,
            $logicalOperator,
            $order,
            $limit,
            'getResultsForTemplate'
        );
    }

    public function insert()
    {
        # @return integer

        # @description
        # <h2>Inserting Records</h2>
        # <p>
        #    Inserts a row in the database.  If the table has a primary <var>auto_increment</var> key,
        #    the newly inserted key is returned.
        # </p>
        # @end

        $arguments = func_get_args();

        // Column1, Column2, Column3
        // Array
        if (!isset($arguments[0]))
        {
            if (count($this->columns))
            {
                return $this->hDatabase->insert($this->columns, $this->table);
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    "Unable to insert into table '{$this->table}' because no columns were defined.",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else
        {
            if (isset($arguments[0]) && is_array($arguments[0]))
            {
                if (count($arguments) > 1)
                {
                    foreach ($arguments as $insert)
                    {
                        $id = $this->hDatabase->insert($insert, $this->table);
                    }

                    return $id;
                }
                else
                {
                    return $this->hDatabase->insert($arguments[0], $this->table);
                }
            }
            else
            {
                return call_user_func_array(
                    array($this->hDatabase, 'insert'),
                    array(
                        $arguments,
                        $this->table
                    )
                );
            }
        }
    }

    public function update($columns, $where = nil, $logicalOperator = 'AND', $key = nil, $quoteColumns = true)
    {
        # @return integer

        # @description
        # <h2>Updating Records</h2>
        # <p>
        #    Updates records in the database based on the supplied criteria.  If successful, the
        #    number of affected rows is returned.
        # </p>
        # @end

        return $this->hDatabase->update(
            $columns,
            $where,
            $this->table,
            $logicalOperator,
            $key,
            $quoteColumns
        );
    }

    public function save()
    {
        # @return integer

        # @description
        # <h2>Saving Records</h2>
        # <p>
        #    Inserts or updates records in a table based on the presence of a unique or primary key.
        #    If the primary key is empty, a new record is inserted using <a href='#insert'>insert()</a>.
        #    If the primary key is not empty, the existing record is updated using <a href='#update'>update()</a>.
        #    If the table has no primary key or unique key, the value of the first column is used
        #    to determine whether or not a record should be inserted or updated.  Whether a record is
        #    created or updated, the value of the primary key is returned.
        # </p>
        # @end

        // $columns, $table = nil
        $arguments = func_get_args();

        if (!isset($arguments[0]))
        {
            if (count($this->columns))
            {
                return $this->hDatabase->save($this->columns, $this->table);
            }
            else
            {
                $GLOBALS['hFramework']->warning(
                    "Unable to save {$this->table}, no columns were defined.",
                    __FILE__,
                    __LINE__
                );
            }
        }
        else if (isset($arguments[0]) && is_array($arguments[0]))
        {
            return $this->hDatabase->save(
                $arguments[0],
                $this->table
            );
        }
        else
        {
            return call_user_func_array(
                array($this->hDatabase, 'save'),
                array(
                    $arguments,
                    $this->table
                )
            );
        }
    }

    public function delete($columns = array(), $key = nil, $logicalOperator = 'AND')
    {
        # @return integer

        # @description
        # <h2>Deleting Records From a Table</h2>
        # <p>
        #
        # </p>
        # @end

        if ((empty($columns) || !count($columns)) && count($this->columns))
        {
            return $this->hDatabase->delete(
                $this->table,
                $this->columns
            );
        }
        else
        {
            return $this->hDatabase->delete(
                $this->table,
                $columns,
                $key,
                $logicalOperator
            );
        }
    }

    public function isResource()
    {
        # @return boolean

        # @description
        # <h2>Determining if a Database Table is a Framework Resource</h2>
        # <p>
        #    Framework resources are database tables that can be verbed.  Owned, subscribed, etc.
        #    See: <a href='/Hot Toddy/Documentation?hFramework/hFrameworkResources#isResource'>hFrameworkResources::isResource()</a>
        # </p>
        # <p>
        #    Example:
        # </p>
        # <code>$this-&gt;hFiles-&gt;isResource();</code>
        # <p>
        #    The preceding returns <var>true</var> because <var>hFiles</var> is a resource.
        # </p>
        # @end
        return $this->frameworkResource()->isResource($this->table);
    }

    public function getResourceId()
    {
        # @return integer

        # @description
        # <h2>Getting a Framework Resource Id</h2>
        # <p>
        #    Framework resources are database tables that can be verbed.  Owned, subscribed, etc.
        #    See: <a href='/Hot Toddy/Documentation?hFramework/hFrameworkResources#isResource'>hFrameworkResources::getResourceId()</a>
        # </p>
        # <p>
        #    This method returns the permanent <var>hFrameworkResourceId</var> for a given resource.
        # </p>
        # <code>$this-&gt;hFiles-&gt;getResourceId();</code>
        # <p>
        #    The preceding returns <var>1</var>, <var>hFiles</var> always has a frameworkResourceId
        #    of <var>1</var>.
        # </p>
        # @end
        return (int) $this->frameworkResource()->getResourceId($this->table);
    }

    public function getResource()
    {
        # @return array

        # @description
        # <h2>Getting a Framework Resource</h2>
        # <p>
        #    Framework resources are database tables that can be verbed.  Owned, subscribed, etc.
        #    See: <a href='/Hot Toddy/Documentation?hFramework/hFrameworkResources#getResource'>hFrameworkResources::getResource()</a>
        # </p>
        # <p>
        #    This method returns resource data for a given resource.
        # </p>
        # <code>$this-&gt;hFiles-&gt;getResource();</code>
        # <p>
        #    The preceding returns the following array:
        # </p>
        # <code>
        #    array(
        #        'hFrameworkResourceTable' =&gt; 'hFiles',
        #        'hFrameworkResourcePrimaryKey' =&gt; 'hFileId',
        #        'hFrameworkResourceNameColumn' =&gt; 'hFileName',
        #        'hFrameworkResourceLastModifiedColumn' =&gt; 'hFileLastModified'
        #    );
        # </code>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Returned Data</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>hFrameworkResourceTable</td>
        #           <td>The resource table</td>
        #       </tr>
        #       <tr>
        #           <td>hFrameworkResourcePrimaryKey</td>
        #           <td>The resourse table's primary key column</td>
        #       </tr>
        #       <tr>
        #           <td>hFrameworkResourceNameColumn</td>
        #           <td>The resource table's column containing name infotmation.</td>
        #       </tr>
        #       <tr>
        #           <td>hFrameworkResourceLastModifiedColumn</td>
        #           <td>The resource table's column containing last modified time information.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end
        return $this->frameworkResource()->getResource($this->table);
    }

    public function getResourceName($frameworkResourceKey)
    {
        # @return array

        # @description
        # <h2>Getting a Resource's Name</h2>
        # <p>
        #    Framework resources are database tables that can be verbed.  Owned, subscribed, etc.
        #    See: <a href='/Hot Toddy/Documentation?hFramework/hFrameworkResources#getResource'>hFrameworkResources::getResourceName()</a>
        # </p>
        # <p>
        #    This method returns a resource's name.
        # </p>
        # <code>$this-&gt;hFiles-&gt;getResourceName(1);</code>
        # <p>
        #    The preceding returns the <var>hFileName</var> for <var>hFileId = 1</var>, since
        #    <var>hFileName</var> is defined as the column housing a framework resource's name.
        #    In other tables, the framework resource's name will be different.
        # </p>
        # @end
        return $this->frameworkResource()->getResourceName(
            $this->table,
            $frameworkResourceKey
        );
    }

    public function getLastModified($frameworkResourceKey = 0)
    {
        # @return integer

        # @description
        # <h2>Getting a Resource's Last Modified Time</h2>
        # <p>
        #    Framework resources are database tables that can be verbed.  Owned, subscribed, etc.
        #    See: <a href='/Hot Toddy/Documentation?hFramework/hFrameworkResources#getResourceLastModified'>hFrameworkResources::getResourceLastModified()</a>
        # </p>
        # <p>
        #    Returns a unix timestamp representing a framework resource's last modified time.  If no
        #    <var>$frameworkResourceKey</var> is specified, the last modified time
        #    returned is for the whole table.  If a <var>$frameworkResourceKey</var> is
        #    provided, the last modified time is for just that row.
        # </p>
        # @end

        return $this->frameworkResource()->getResourceLastModified(
            $this->table,
            $frameworkResourceKey
        );
    }

    public function getResourceLastModified($frameworkResourceKey = 0)
    {
        # @return integer

        # @description
        # <h2>Getting a Resource's Last Modified Time</h2>
        # <p>
        #    Alias for: <a href='#getLastModified'>getLastModified()</a>
        # </p>
        # @end
        return $this->frameworkResource()->getResourceLastModified(
            $this->table,
            $frameworkResourceKey
        );
    }

    public function &modifyResource($frameworkResourceKey = 0)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Modifying a Resource</h2>
        # <p>
        #    Alias for: <a href='#modify'>modify()</a>
        # </p>
        # @end

        $this->frameworkResource()->modifyResource(
            $this->table,
            $frameworkResourceKey
        );

        return $this;
    }

    public function &modify($frameworkResourceKey = 0)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Modifying a Resource</h2>
        # <p>
        #    Framework resources are database tables that can be verbed.  Owned, subscribed, etc.
        #    See: <a href='/Hot Toddy/Documentation?hFramework/hFrameworkResources#modifyResource'>hFrameworkResources::modifyResource()</a>
        # </p>
        # <p>
        #    Modifies the specified resource (updates the last modified Unix Timestamp to now).
        #    If no <var>$frameworkResourceKey</var> is specified, the last modified time
        #    is updated for the whole table.  If a <var>$frameworkResourceKey</var> is specified,
        #    only the specified row is updated.
        # </p>
        # @end
        $this->frameworkResource()->modifyResource(
            $this->table,
            $frameworkResourceKey
        );

        return $this;
    }

    public function &truncate()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Truncating a Table</h2>
        # <p>
        #    Truncates (deletes all data) the specified table.
        # </p>
        # @end

        $this->hDatabase->truncate($this->table);
        return $this;
    }

    public function &truncateAndInsert()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Truncating and Re-inserting</h2>
        # <p>
        #    Truncates (deletes all data) the specified data, and then reinserts the contents of
        #    the <var>{/hDatabaseTable}.insert.sql</var> record from the <var>/Hot Toddy/hDatabase/hDatabaseStructure</var>
        #    folder.
        # </p>
        # @end

        $this->hDatabase->truncate($this->table);

        $insertPath = $GLOBALS['hFramework']->hFrameworkPath.'/Hot Toddy/hDatabase/hDatabaseStructure/'.$this->table.'/'.$this->table.'.insert.sql';

        if (file_exists($insertPath))
        {
            $this->hDatabase->query(
                $GLOBALS['hFramework']->getTemplate($insertPath)
            );
        }

        return $this;
    }

    public function &activity($activity)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Logging Activity</h2>
        # <p>
        #    Logs activity on the specified table.
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserActivityLog'>hUser/hUserActivityLog</a>
        # </p>
        # @end

        $GLOBALS['hFramework']->activity(
            $this->table,
            $activity
        );

        return $this;
    }

    public function &set()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Setting a Database Table</h2>
        # <p>
        #    Sets a persistent database table to <var>hDatabase</var>'s internal <var>table</var>
        #    property.  Once a persistent table is set, the <var>$table</var> argument can
        #    be ommited from most of <var>hDatabase</var>'s methods, and those methods will be
        #    carried out in the context of that table.
        # </p>
        # <p>
        #    Note: This method has no effect on <var>hDatabaseTable</var>, it only
        #    applies to <var>hDatabase</var>.  Each <var>hDatabaseTable</var> object
        #    is created on demand, and copied once for each table in the database. The
        #    table set in it is permanent for the entire session.  i.e. calling a
        #    method like <var>$this-&gt;hFiles-&gt;set();</var> returns <var>hDatabaseTable</var>
        #    the <var>hDatabaseTable</var> returned is also in the context of <var>hFiles</var>,
        #    and cannot be changed.
        # </p>
        # @end

        $this->hDatabase->setTable($this->table);
        return $this;
    }

    public function &uses()
    {
        # @return hDatabaseTable

        # @description
        # <h2>"Using" a Database Table</h2>
        # <p>
        #    Calling this method prior to using a database table will ensure that that
        #    table is installed and available.
        # </p>
        # @end

        $this->hDatabase->uses($this->table);
        return $this;
    }

    public function &createFromFile()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Creating a Database Table From File</h2>
        # <p>
        #    If a database table does not exist, it is created from the database definition file
        #    stored in <var>/Hot Toddy/hDatabase/hDatabaseStructure</var>
        # </p>
        # @end

        $this->hDatabase->createTableFromFile($this->table);
        return $this;
    }

    public function hasPermission($frameworkResourceKey, $level = 'r', $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining User Permissions</h2>
        # <p>
        #    Tells you whether the user has read or read/write permission to the
        #    specified framework resource in the specified table.
        # </p>
        # <p>
        #    If no <var>$userId</var> is specified, the current user is assumed.
        # </p>
        # <p>
        #    See also: <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#hasPermission'>hUserAuthenticationLibrary::hasPermission()</a>
        # </p>
        # @end
        return $GLOBALS['hFramework']->hasPermission(
            $this->table.':'.$frameworkResourceKey.':'.$level,
            $userId
        );
    }

    public function hasReadPermission($frameworkResourceKey, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining Read Permission for a User</h2>
        # <p>
        #    Tells you whether the user has read permission to the
        #    specified framework resource in the specified table.
        # </p>
        # <p>
        #    If no <var>$userId</var> is specified, the current user is assumed.
        # </p>
        # <p>
        #    See also: <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#hasPermission'>hUserAuthenticationLibrary::hasPermission()</a>
        # </p>
        # @end
        return $GLOBALS['hFramework']->hasPermission(
            $this->table.':'.$frameworkResourceKey.':r',
            $userId
        );
    }

    public function hasWritePermission($frameworkResourceKey, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining Write Permission for a User</h2>
        # <p>
        #    Tells you whether the user has write permission to the
        #    specified framework resource in the specified table.
        # </p>
        # <p>
        #    If no <var>$userId</var> is specified, the current user is assumed.
        # </p>
        # <p>
        #    Example:
        # </p>
        # <code>$this-&gt;hFiles-&gt;hasWritePermission(10);</code>
        # <p>
        #    The preceding looks to see if the current user has write permission to a file in the
        #    <var>hFiles</var> table with <var>hFileId</var> 10.
        # </p>
        # <p>
        #    See also: <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#hasPermission'>hUserAuthenticationLibrary::hasPermission()</a>
        # </p>
        # @end
        return $GLOBALS['hFramework']->hasPermission(
            $this->table.':'.$frameworkResourceKey.'rw',
            $userId
        );
    }

    public function &setInherit($frameworkResourceKey)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Inheriting Permissions</h2>
        # <p>
        #    Alias for: <a href='#inheritPermissionsFrom'>inheritPermissionsFrom()</a>
        # </p>
        # @end

        return $this->inheritPermissionsFrom($frameworkResourceKey);
    }

    public function isAuthorized($frameworkResourceKey, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Determining if a User Is Authorized to Modify Permissions</h2>
        # <p>
        #    Checks to see if the current user or the user specified in <var>$userId</var> is
        #    authorized to modifed permissions on a resource.
        # </p>
        # <p>
        #    A user is authorized to modify the permissions of a resource if:
        # </p>
        # <ul>
        #    <li>The user is a member of <i>root</i></li>
        #    <li>The user is a member of <i>Website Administrators</i></li>
        #    <li>The user is the owner of a resource</li>
        #    <li>The user has <b>read &amp; write</b> access to a resource</li>
        # </ul>
        # <p>
        #    This limitation applies to a user attempting to directly modify
        #    a resource's permissions.  Applications obviously, can be designed
        #    to modify permissions on a user's behalf without these limitations.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#isAuthorized'>hUserPermissionLibrary::isAuthorized()</a>
        # </p>
        # @end

        return $this->userPermission()->isAuthorized($this->table, $frameworkResourceKey, $userId);
    }

    public function isResourceOwner($frameworkResourcePrimaryKey, $frameworkResourceKey, $userId = 0)
    {
         # @return boolean

         # @description
         # <h2>Determining the Resource Owner</h2>
         # <p>
         #    Reports whether or not the current user or the specified <var>$userId</var> is a resource
         #    owner.  The resource owner is the user associated with the <var>hUserId</var> field of
         #    a record.
         # </p>
         # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#isResourceOwner'>hUserPermissionLibrary::isResourceOwner()</a>
        # </p>
         # @end

        return $this->userPermission()->isResourceOwner(
            $this->table,
            $frameworkResourcePrimaryKey,
            $frameworkResourceKey,
            $userId
        );
    }

    public function getPermissions($frameworkResourceKey)
    {
        # @return array

        # @description
        # <h2>Getting the Existing Permissions of a Resource</h2>
        # <p>
        #    Returns an associative array of permissions applied to the specified
        #    resource.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#getPermissions'>hUserPermissionLibrary::getPermissions()</a>
        # </p>
        # @end
        return $this->userPermission()->getPermissions($this->table, $frameworkResourceKey);
    }

    public function &inheritPermissionsFrom($frameworkResourceKey)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Inheriting Permissions</h2>
        # <p>
        #    Sets up <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php'>hUser/hUserPermission/hUserPermission.library.php</a>
        #    so that a resource will inherit permissions from the specified table and specified key referencing a primary row'
        #    in <var>$from</var>.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#setInherit'>hUserPermissionLibrary::setInherit()</a>
        # </p>
        # @end

        $this->userPermission()->setInherit($this->table, $frameworkResourceKey);
        return $this;
    }

    public function &addGroup($group, $level = 'r')
    {
        # @return hDatabaseTable

        # @description
        # <h2>Setting a Single Group for Permissions</h2>
        # <p>
        #    Alias of <a href='#setGroup'>setGroup()</a>
        # </p>
        # @end

        return $this->setGroup($group, $level);
    }

    public function &setGroup($group, $level = 'r')
    {
        # @return hDatabaseTable

        # @description
        # <h2>Setting a Single Group for Permissions</h2>
        # <p>
        #    Adds the <var>$group</var> with permissions <var>$level</var> to the framework resource.
        #    Changes made are not final until <a href='#savePermissions'>savePermissions()</a> is called.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#setGroup'>hUserPermissionLibrary::setGroup()</a>
        # </p>
        # @end

        $this->userPermission()->setGroup($group, $level = 'r');
        return $this;
    }

    public function &addGroups($groups)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Setting a Multiple Groups for Permissions</h2>
        # <p>
        #    Alias of <a href='#setGroups'>setGroups()</a>
        # </p>
        # @end

        return $this->setGroups($groups);
    }

    public function &setGroups($groups)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Setting a Multiple Groups for Permissions</h2>
        # <p>
        #    Adds the <var>$groups</var> to the framework resource.  <var>$groups</var> is provided
        #    as an array that is structured like this:
        # </p>
        # <code>
        #    array(
        #        'Website Administrators' =&gt; 'rw',
        #        'Calendar Administrators' =&gt; 'rw',
        #        'Employees' =&gt; 'r'
        #    )
        # </code>
        # <p>
        #    In the preceding example, you see that groups are used as the keys of the array, and the
        #    corresponding level of permissions for that group is set as the values of the array.
        # </p>
        # </p>
        #    Changes made are not final until <a href='#savePermissions'>savePermissions()</a> is called.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#setGroups'>hUserPermissionLibrary::setGroups()</a>
        # </p>
        # @end
        $this->userPermission()->setGroups($groups);
        return $this;
    }

    public function &savePermissions($frameworkResourceKey, $owner = 'rw', $world = '')
    {
        # @return hDatabaseTable

        # @description
        # <h2>Saving a Resource's Permissions</h2>
        # <p>
        #    Once you have set group or user permissions, or set a resource to inherit permissions from
        #    another resource, or even if you have done none of those things, this method
        #    saves modifications to permissions.  In addition, it also allows you to set permissions on
        #    <var>$owner</var> and <var>$world</var> (world access is synonomous with public access).
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#save'>hUserPermissionLibrary::save()</a>
        # </p>
        # @end
        $this->userPermission()->save(
            $this->table,
            $frameworkResourceKey,
            $owner,
            $world
        );

        return $this;
    }

    public function hasWorldRead($frameworkResourceKey)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Resource Has World Read Permission</h2>
        # <p>
        #   Returns whether or not the <i>read</i> attribute on the specified
        #   resource is set.
        # </p>
        # @end

        return (bool) $GLOBALS['hFramework']->hasWorldRead(
            $this->table.':'.$frameworkResourceKey
        );
    }

    public function &saveWorldPermissions($frameworkResourceKey, $world = '')
    {
        # @return hDatabaseTable

        # @description
        # <h2>Saving Only Modifications to World Permissions</h2>
        # <p>
        #    This method saves only modifications to world permissions (public access).
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#saveWorldAccess'>hUserPermissionLibrary::saveWorldAccess()</a>
        # </p>
        # @end
        $this->userPermission()->saveWorldAccess(
            $this->table,
            $frameworkResourceKey,
            $world
        );

        return $this;
    }

    public function &deletePermissions($frameworkResourceKey)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Deleting Permissions</h2>
        # <p>
        #    Removes all permissions from the specified resource, and only the permissions.
        #    The resource itself is untouched.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#delete'>hUserPermissionLibrary::delete()</a>
        # </p>
        # @end
        $this->userPermission()->delete(
            $this->table,
            $frameworkResourceKey
        );

        return $this;
    }

    public function &deletePermissionsCache($frameworkResourceKey, $userPermissionType = 'hUserPermissions')
    {
        # @return hDatabaseTable

        # @description
        # <h2>Deleting Cached Permissions</h2>
        # <p>
        #    Clears all cached permissions for the specified resource.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#deleteCache'>hUserPermissionLibrary::deleteCache()</a>
        # </p>
        # @end
        $this->userPermission()->deleteCache(
            $this->table,
            $frameworkResourceKey,
            $userPermissionType
        );

        return $this;
    }

    public function &chown($frameworkResourceKey, $userId = 0)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Changing the Owner of a Resource</h2>
        # <p>
        #    Changing the owner of the specified resource to the current user (if no <var>$userId</var> is provided),
        #    or the user specified in <var>$userId</var>, if a user is provided.
        # </p>
        # <p>
        #    See: <a href='/Hot Toddy/Documentation?hUser/hUserPermission/hUserPermission.library.php#chown'>hUserPermissionLibrary::chown()</a>
        # </p>
        # @end
        $this->userPermission()->chown(
            $this->table,
            $frameworkResourceKey,
            $userId
        );

        return $this;
    }

    public function filterTableColumns(array $columns)
    {
        # @return array

        # @description
        # <h2>Filtering Table Columns</h2>
        # <p>
        #   An alias of <a href='#filterColumns' class='code'>filterColumns()</a>
        # </p>
        # @end

        return $this->hDatabase->filterTableColumns(
            $columns,
            $this->table
        );
    }

    public function filterColumns(array $columns)
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

        return $this->hDatabase->filterTableColumns(
            $columns,
            $this->table
        );
    }

    public function getPostDataByColumnName(array $columns = array())
    {
        # @return array

        # @description
        # <h2>Getting Filtered POST Data</h2>
        # <p>
        #   Returns data from <var>$_POST</var> where the name
        #   of POST data matches the names of columns in a table.
        #   Only the specified columns are returned, and any
        #   additional POST data is discarded.
        # </p>
        # <p>
        #   This method also validates the specified column names
        #   and ensures the specified column names exist in the
        #   specified table.
        # </p>
        # @end

        return $this->hDatabase->getPostDataByColumnName(
            $this->table,
            $columns
        );
    }

    public function getResultByKey($field, $key, $keyValue, $default = '')
    {
        # @return mixed

        # @description
        # <h2>Getting a Single Column's Value</h2>
        # <p>
        #   Returns the value of column <var>$field</var> from
        #   the table <var>$table</var>, where the column <var>$key</var>
        #   is the value <var>$keyValue</var>. If the field isn't
        #   found the default value specified in <var>$default</var>
        #   is returned.
        # </p>
        # <p>
        #   The following is the SQL template used by this method:
        # </p>
        # <code>
        # SELECT `{/$field}`
        #   FROM `{/$table}`
        #  WHERE `{/$key}` = ". (int) $keyValue
        # </cope>
        # @end

        return $this->hDatabase->getResultByKey(
            $field,
            $this->table,
            $key,
            $keyValue,
            $default
        );
    }

    public function isAutoIncrementColumn($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Is Auto Increment</h2>
        # <p>
        #   Returns <var>true</var> if the specified <var>$column</var>
        #   is an auto-increment column.
        # </p>
        # @end

        return (bool) $this->hDatabase->isAutoIncrementColumn(
            $column,
            $this->table
        );
    }

    public function exists()
    {
        # @return boolean

        # @description
        # <h2>Determining if a Table Exists</h2>
        # <p>
        #   Returns <var>true</var> if the specified table
        #   exists.
        # </p>
        # @end

        return (bool) $this->hDatabase->tableExists($this->table);
    }

    public function tableExists()
    {
        # @return boolean

        # @description
        # <h2>Determining if a Table Exists</h2>
        # <p>
        #  An alias of <a href='#exists' class='code'>exists()</a>
        # </p>
        # @end

        return (bool) $this->hDatabase->tableExists($this->table);
    }

    public function columnExists($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Columns Exists</h2>
        # <p>
        #   Returns <var>true</var> if the specified <var>$column</var>
        #   exists in the specified table. The name of the column
        #   is also validated according to Hot Toddy naming conventions,
        #   which is done to validate that the string is both a
        #   valid column name, as well as existing within a table.
        # </p>
        # @end

        return (bool) $this->hDatabase->columnExists(
            $column,
            $this->table
        );
    }

    public function columnsInTable(array $columns)
    {
        # @return boolean

        # @description
        # <h2>Determining if Multiple Columns Exists Within a Table</h2>
        # <p>
        #   Returns <var>true</var> if all of the <var>$columns</var>
        #   specified as an array are found in the specified table.
        # </p>
        # @end

        return (bool) $this->hDatabase->columnsInTable(
            $columns,
            $this->table
        );
    }

    public function columnsIn(array $columns)
    {
        # @return boolean

        # @description
        # <h2>Determining if Multiple Columns Exists Within a Table</h2>
        # <p>
        #   An alias of <a href='#columnsInTable' class='code'>columnsInTable()</a>
        # </p>
        # @end

        return (bool) $this->hDatabase->columnsInTable(
            $columns,
            $this->table
        );
    }

    public function columnInTable($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Exists Within a Table</h2>
        # <p>
        #   A simpler determination if a column exists within a table,
        #   without the column name validation used in
        #   <a href='#columnExists' class='code'>columnExists()</a>
        # </p>
        # @end

        return (bool) $this->hDatabase->columnInTable(
            $column,
            $this->table
        );
    }

    public function columnIn($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Exists Within a Table</h2>
        # <p>
        #   An alias of <a href='#columnInTable' class='code'>columnInTable</a>
        # </p>
        # @end

        return (bool) $this->hDatabase->columnInTable(
            $column,
            $this->table
        );
    }

    public function getColumnType($column)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Getting the a Column's Type</h2>
        # <p>
        #   Returns the column's type.
        # </p>
        # @end

        return $this->hDatabase->getColumnType(
            $column,
            $this->table
        );
    }

    public function columnIsNumeric($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Is Numeric</h2>
        # <p>
        #   Returns <var>true</var> if the column type is
        #   a numeric column type. For example, integer, float, etc.
        # </p>
        # @end

        return (bool) $this->hDatabase->columnIsNumeric(
            $column,
            $this->table
        );
    }

    public function hasPrimaryKey()
    {
        # @return boolean

        # @description
        # <h2>Determining if a Table Has a Primary Key</h2>
        # <p>
        #   Returns <var>true</var> if the specified table
        #   has a primary key.
        # </p>
        # @end

        return (bool) $this->hDatabase->hasPrimaryKey(
            $this->table
        );
    }

    public function isPrimaryKey($column)
    {
        # @return boolean

        # @description
        # <h2>Determining if a Column Is a the Primary Key</h2>
        # <p>
        #   Returns <var>true</var> if the specified <var>$column</var>
        #   is the primary key.
        # </p>
        # @end

        return (bool) $this->hDatabase->isPrimaryKey(
            $column,
            $this->table
        );
    }

    public function hasIncrementKey()
    {
        # @return boolean

        # @description
        # <h2>Determining if a Table Has an Incrementing Key</h2>
        # <p>
        #   Returns <var>true</var> if the specified table has
        #   an auto-increment column.
        # </p>
        # @end

        return (bool) $this->hDatabase->hasIncrementKey(
            $this->table
        );
    }

    public function getFirstColumn()
    {
        # @return string

        # @description
        # <h2>Getting the Name of the First Column in a Table</h2>
        # <p>
        #   Returns the name of the first column in the specified table.
        # </p>
        # @end

        return $this->hDatabase->getFirstColumn(
            $this->table
        );
    }

    public function getColumns()
    {
        # @return array

        # @description
        # <h2>Getting the Columns in a Table</h2>
        # <p>
        #   Returns all of the columns contained in a table.
        #   The data returned includes each column's type.
        # </p>
        # @end

        return $this->hDatabase->getColumns(
            $this->table
        );
    }

    public function getColumnNames()
    {
        # @return array

        # @description
        # <h2>Getting the Names of Columns in a Table</h2>
        # <p>
        #   Returns all of the column names in a table as
        #   an array. Only column names are returned, no
        #   additional data is returned.
        # </p>
        # @end

        return $this->hDatabase->getColumnNames(
            $this->table
        );
    }

    public function &rename($to)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Renaming a Table</h2>
        # <p>
        #   Renames the specified table to the name specified
        #   in <var>$to</var>.
        # </p>
        # @end

        $this->editor()->renameTable(
            $this->table,
            $to
        );

        return $this;
    }

    public function &renameColumns(array $columns)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Renaming Multiple Columns</h2>
        # <p>
        #   Renames the specified <var>$columns</var>, renaming
        #   is done by specifying the old column name as the array key
        #   and the new column name as the array value.
        # </p>
        # @end

        $this->editor()->renameColumns(
            $columns,
            $this->table
        );

        return $this;
    }

    public function &renameColumn($from, $to)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Renaming a Column</h2>
        # <p>
        #   Renames the specified column from the value specified
        #   in <var>$from</var> to the value specified in <var>$to</var>.
        # </p>
        # @end

        $this->editor()->renameColumn(
            $from,
            $to,
            $this->table
        );

        return $this;
    }

    public function &dropColumns(array $columns)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping Multiple Columns</h2>
        # <p>
        #   Deletes the columns specified in <var>$columns</var> from
        #   the specified table. <var>$columns</var> must be an array.
        # </p>
        # @end

        if (!count($columns))
        {
            $GLOBALS['hFramework']->warning(
                "Unable to delete columns from table '{$this->table}', ".
                "because no columns were specified.", 
                __FILE__, 
                __LINE__
            );

            return $this;
        }

        array_push($columns, $this->table);

        call_user_method_array(
            'dropColumns',
            $this->editor(),
            $columns
        );

        return $this;
    }

    public function &dropColumn($column)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping a Column</h2>
        # <p>
        #   Deletes the specified <var>$column</var> from the specified table.
        # </p>
        # @end

        $this->editor()->dropColumn(
            $column,
            $this->table
        );

        return $this;
    }

    public function &addColumn($column, $type, $afterColumn = nil, $firstColumn = false)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Adding a Column to a Table</h2>
        # <p>
        #   Creates a new column by the name of <var>$column</var> of type <var>$type</var>.
        #   The column is created after the column specified in <var>$afterColumn</var>.
        #   Alternatively, if the new column is to be the first column in the table,
        #   the <var>$afterColumn</var> argument can be left <var>nil</var>, and the
        #   <var>$firstColumn</var> argument can be set to <var>true</var>.
        # </p>
        # @end

        $this->editor()->addColumn(
            $column,
            $type,
            $afterColumn,
            $firstColumn,
            $this->table
        );

        return $this;
    }

    public function &addColumns(array $columns)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Adding Columns to a Table</h2>
        # <p>
        #   Adds the specified columns to the table. Each column is specified
        #   as an array of information.
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Index</th>
        #           <th>Purpose</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>column</td>
        #           <td>The name of the column</td>
        #       </tr>
        #       <tr>
        #           <td>type</td>
        #           <td>The column's type</td>
        #       </tr>
        #       <tr>
        #           <td>after</td>
        #           <td>The name of the column this column should appear after.</td>
        #       </tr>
        #       <tr>
        #           <td>isFirst</td>
        #           <td>Whether or not the column should be the first column in the table</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p>
        #   An example of the array:
        # </p>
        # <code>
        #   $columns = array(
        #       array(
        #           'column' =&gt; 'nameOfTheColumn',
        #           'type' =&gt; 'mediumtext NOT NULL',
        #           'after' =&gt; 'nameOfPrecedingColumn',
        #           'isFirst' =&gt; false
        #       )
        #   );
        # </code>
        # @end

        $this->editor()->addColumns(
            $columns,
            $this->table
        );

        return $this;
    }

    public function &appendColumn($column, $type)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Appending a Column to a Table</h2>
        # <p>
        #   Adds a new column to the end of the table.
        #   The name of the column is specified in the <var>$column</var>
        #   argument, and the type is specified in the <var>$type</var>
        #   argument.
        # </p>
        # @end

        $this->editor()->appendColumn(
            $column,
            $type,
            $this->table
        );

        return $this;
    }

    public function &prependColumn($column, $type)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Prepending a Column to a Table</h2>
        # <p>
        #   Adds a new column to the beginning of the table.
        #   The name of the column is specified in the <var>$column</var>
        #   argument, and the type is specified in the <var>$type</var>
        #   argument.
        # </p>
        # @end

        $this->editor()->prependColumn(
            $column,
            $type,
            $this->table
        );

        return $this;
    }

    public function &modifyColumn($column, $type)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Modifying a Column</h2>
        # <p>
        #   Changes the column specified in <var>$column</var> to the
        #   type specified in <var>$type</var>.
        # </p>
        # @end

        $this->editor()->modifyColumn(
            $column,
            $type,
            $this->table
        );

        return $this;
    }

    public function &addFullTextIndex($columns, $name = nil)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Creating a Full Text Index</h2>
        # <p>
        #   Creates a full text index comprised of the columns specified in
        #   <var>$columns</var>. The index is named by joining the column
        #   names, unless a name is specified in the <var>$name</var> argument.
        # </p>
        # @end

        $this->editor()->addFullTextIndex(
            $columns,
            $name,
            $this->table
        );

        return $this;
    }

    public function &dropFullTextIndex($name)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping a Full Text Index</h2>
        # <p>
        #   Removes the full text index named in the <var>$name</var>
        #   argument.
        # </p>
        # @end

        $this->editor()->dropFullTextIndex(
            $name,
            $this->table
        );

        return $this;
    }

    public function &addUniqueIndex($columns, $name = nil)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Adding a Unique Index</h2>
        # <p>
        #    Creates a unique index in the specified table.  The unique index's name can be passed
        #    explicitly as a string in the <var>$name</var> argument, or if the unique index's
        #    name should be based on the columns it contains, the <var>$name</var>
        #    argument can be omitted and only an array of the columns can be provided.
        # </p>
        # @end

        $this->editor()->addUniqueIndex(
            $columns,
            $name,
            $this->table
        );

        return $this;
    }

    public function &addIndex($columns, $name = nil)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Adding an Index</h2>
        # <p>
        #    Creates an index in the specified table.  The index's name can be passed
        #    explicitly as a string in the <var>$name</var> argument, or if the index's
        #    name should be based on the columns it contains, the <var>$name</var>
        #    argument can be omitted and only an array of the columns can be provided.
        # </p>
        # @end

        $this->editor()->addIndex(
            $columns,
            $name,
            $this->table
        );

        return $this;
    }

    public function &addKey($columns, $name = nil)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Adding a Key</h2>
        # <p>
        #    Creates a key in the specified table.  The key's name can be passed
        #    explicitly as a string in the <var>$name</var> argument, or if the key's
        #    name should be based on the columns it contains, the <var>$name</var>
        #    argument can be omitted and only an array of the columns can be provided.
        # </p>
        # @end

        $this->editor()->addKey(
            $columns,
            $name,
            $this->table
        );

        return $this;
    }

    public function &dropIndex($columns)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping an Index</h2>
        # <p>
        #    Drops the specified index from the table.  The index's name can be passed
        #    explicitly as a string, or if the index's name is based on the columns it contains,
        #    an array of the columns can be provided (in the same order as created).
        # </p>
        # @end

        $this->editor()->dropIndex(
            $columns,
            $this->table
        );

        return $this;
    }

    public function &dropKey($columns)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping a Key</h2>
        # <p>
        #    Drops the specified key from the table.  The key's name can be passed
        #    explicitly as a string, or if the key's name is based on the columns it contains,
        #    an array of the columns can be provided (in the same order as created).
        # </p>
        # @end

        $this->editor()->dropKey(
            $columns,
            $this->table
        );

        return $this;
    }

    public function &copyTo($to)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Copying a Table</h2>
        # <p>
        #    Makes an exact copy of the specified table to <var>$to</var>.
        # </p>
        # @end

        $this->editor()->copyTable(
            $this->table,
            $to
        );

        return $this;
    }

    public function &addPrimaryKey($columns)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Adding a Primary Key</h2>
        # <p>
        #    Adds a primary key index comprised of the specified column(s).
        #    <var>$columns</var> can be passed as a string if there is just one column,
        #    or an array if there are multiple (numeric offset).
        # </p>
        # @end

        $this->editor()->addPrimaryKey(
            $columns,
            $this->table
        );

        return $this;
    }

    public function &dropPrimaryKey()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping a Primary Key</h2>
        # <p>
        #    Removes the primary key index from the specified table.
        # </p>
        # @end

        $this->editor()->dropPrimaryKey(
            $this->table
        );

        return $this;
    }

    public function &drop()
    {
        # @return hDatabaseTable

        # @description
        # <h2>Dropping a Table</h2>
        # <p>
        #     Permanently deletes the specified table and all data contained within it.
        # </p>
        # @end

        $this->editor()->dropTable(
            $this->table
        );

        return $this;
    }

    public function &setAutoIncrement($counter)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Setting a Table's Auto Increment Counter</h2>
        # <p>
        #    Sets the table's auto increment counter to the value specified in
        #    <var>$counter</var>.
        # </p>
        # @end

        $this->editor()->setAutoIncrement(
            $counter,
            $this->table
        );

        return $this;
    }

    public function getFields()
    {
        # @return array

        # @description
        # <h2>Getting Contact Databases Field Types</h2>
        # <p>
        #   Returns an array of <var>hContactFieldId</var>
        #   and <var>hContactField</var> values for the specified
        #   table (framework resource).
        # </p>
        # <p>
        #   Returned Data:
        # </p>
        # <h4>hContactAddresses</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>1</td>
        #           <td>Home</td>
        #       </tr>
        #       <tr>
        #           <td>2</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>3</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>hContactEmailAddresses</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>19</td>
        #           <td>Personal</td>
        #       </tr>
        #       <tr>
        #           <td>20</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>37</td>
        #           <td>Facebook</td>
        #       </tr>
        #       <tr>
        #           <td>38</td>
        #           <td>Gmail</td>
        #       </tr>
        #       <tr>
        #           <td>39</td>
        #           <td>Microsoft Hotmail</td>
        #       </tr>
        #       <tr>
        #           <td>40</td>
        #           <td>Windows Live</td>
        #       </tr>
        #       <tr>
        #           <td>41</td>
        #           <td>iCloud</td>
        #       </tr>
        #       <tr>
        #           <td>43</td>
        #           <td>Microsoft Exchange</td>
        #       </tr>
        #       <tr>
        #           <td>44</td>
        #           <td>Aol.</td>
        #       </tr>
        #       <tr>
        #           <td>21</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>hContactInternetAccounts</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>30</td>
        #           <td>Apple Id</td>
        #       </tr>
        #       <tr>
        #           <td>31</td>
        #           <td>iMessages</td>
        #       </tr>
        #       <tr>
        #           <td>32</td>
        #           <td>iCloud</td>
        #       </tr>
        #       <tr>
        #           <td>33</td>
        #           <td>Game Center</td>
        #       </tr>
        #       <tr>
        #           <td>34</td>
        #           <td>iTunes</td>
        #       </tr>
        #       <tr>
        #           <td>35</td>
        #           <td>Mac App Store</td>
        #       </tr>
        #       <tr>
        #           <td>29</td>
        #           <td>Facebook</td>
        #       </tr>
        #       <tr>
        #           <td>36</td>
        #           <td>Windows Live</td>
        #       </tr>
        #       <tr>
        #           <td>42</td>
        #           <td>Google</td>
        #       </tr>
        #       <tr>
        #           <td>12</td>
        #           <td>Aol.</td>
        #       </tr>
        #       <tr>
        #           <td>45</td>
        #           <td>Playstation Network</td>
        #       </tr>
        #       <tr>
        #           <td>46</td>
        #           <td>Xbox Live</td>
        #       </tr>
        #       <tr>
        #           <td>13</td>
        #           <td>Yahoo!</td>
        #       </tr>
        #       <tr>
        #           <td>15</td>
        #           <td>ICQ</td>
        #       </tr>
        #       <tr>
        #           <td>16</td>
        #           <td>iChat</td>
        #       </tr>
        #       <tr>
        #           <td>17</td>
        #           <td>Jabber</td>
        #       </tr>
        #       <tr>
        #           <td>18</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # <h4>hContactPhoneNumbers</h4>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>hContactFieldId</var>
        #           <th>hContactField</var>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td>4</td>
        #           <td>Home</td>
        #       </tr>
        #       <tr>
        #           <td>5</td>
        #           <td>Mobile</td>
        #       </tr>
        #       <tr>
        #           <td>6</td>
        #           <td>Work</td>
        #       </tr>
        #       <tr>
        #           <td>7</td>
        #           <td>Extension</td>
        #       </tr>
        #       <tr>
        #           <td>8</td>
        #           <td>Company</td>
        #       </tr>
        #       <tr>
        #           <td>9</td>
        #           <td>Fax</td>
        #       </tr>
        #       <tr>
        #           <td>10</td>
        #           <td>Pager</td>
        #       </tr>
        #       <tr>
        #           <td>22</td>
        #           <td>Main</td>
        #       </tr>
        #       <tr>
        #           <td>23</td>
        #           <td>Toll-Free</td>
        #       </tr>
        #       <tr>
        #           <td>24</td>
        #           <td>Appointments</td>
        #       </tr>
        #       <tr>
        #           <td>47</td>
        #           <td>Scheduling</td>
        #       </tr>
        #       <tr>
        #           <td>25</td>
        #           <td>iPhone</td>
        #       </tr>
        #       <tr>
        #           <td>26</td>
        #           <td>Home Fax</td>
        #       </tr>
        #       <tr>
        #           <td>27</td>
        #           <td>Work Fax</td>
        #       </tr>
        #       <tr>
        #           <td>28</td>
        #           <td>Other Fax</td>
        #       </tr>
        #       <tr>
        #           <td>11</td>
        #           <td>Other</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        return $this->contactDatabase()->getFields(
            $this->table
        );
    }

    public function isSubscribed($frameworkResourceKey, $userId = 0)
    {
        # @return boolean

        # @description
        # <h2>Checking a User's Subscription Status</h2>
        # <p>
        #   Determines if a user is subscribed to the framework resource.
        #   A framework resource is comprised of a database table name
        #   and an auto-incrementing primary key index.
        # </p>
        # @end

        return (bool) $this->subscription()->isSubscribed(
            $this->table,
            $frameworkResourceKey,
            $userId
        );
    }

    public function &toggleSubscription($frameworkResourceKey, $userId = 0)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Toggling a Subscription On or Off</h2>
        # <p>
        #
        # </p>
        # @end

        $this->subscription()->toggleSubscription(
            $this->table,
            $frameworkResourceKey,
            $userId
        );

        return $this;
    }

    public function getSubscriptions($frameworkResourceKey)
    {
        # @return array

        # @description
        # <h2>Getting Subscriptions</h2>
        # <p>
        #   Returns all the users subscribed to a given resource. If there
        #   are groups subscribed, then the users within that group are 
        #   returned too. 
        # </p>
        # @end

        return $this->subscription()->getSubscriptions(
            $this->table,
            $frameworkResourceKey
        );
    }

    public function getSubscriptionId($frameworkResourceKey)
    {
        # @return integer

        # @description
        # <h2>Getting a subscriptionId For a Resource</h2>
        # <p>
        #   Returns the <var>subscriptionId</var> for the provided 
        #   <var>frameworkResourceKey</var>. For example <var>$this-&gt;hForums-&gt;getSubscriptionId(1)</var>
        #   will return the <var>subscriptionId</var> for <var>forumId = 1</var>. 
        #   The <var>frameworkResourceKey</var> represents the unique id of
        #   the thing being subscribed to. Another example: <var>$this-&gt;hForumTopics-&gt;getSubscriptionId(1)</var>
        #   will return the <var>subscriptionId</var> for <var>forumTopicId = 1</var>.
        # </p>
        # <p>
        #   See: <a href='#subscribe' class='code'>subscribe()</a> for more information.
        # </p>
        # @end

        return $this->subscription()->getSubscriptionId(
            $this->table,
            $frameworkResourceKey
        );
    }

    public function &deleteSubscription($frameworkResourceKey)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Deleting a Subscription Resource</h2>
        # <p>
        #   This method deletes a subscription resource. 
        # </p>
        # <p>
        #   See: <a href='#subscribe' class='code'>subscribe()</a> for more information.
        # </p>
        # @end

        $this->subscription()->delete(
            $this->table,
            $frameworkResourceKey
        );

        return $this;
    }

    public function saveSubscription($frameworkResourceKey)
    {
        # @return integer

        # @description
        # <h2>Saving a Subscription Resource</h2>
        # <p>
        #   This method inserts or updates a subscription, it returns 
        #   the <var>subscriptionId</var>.
        # </p>
        # <p>
        #   See: <a href='#subscribe' class='code'>subscribe()</a> for more information.
        # </p>
        # @end

        return (int) $this->subscription()->save(
            $this->table,
            $frameworkResourceKey
        );
    }

    public function &subscribe($frameworkResourceKey, $userId = 0)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Subscribing a User to a Resource</h2>
        # <p>
        #   
        # </p>
        # <p>
        #   A resource is
        #   defined as a database table that has all the components necessary
        #   for a database table to be handled a certain way. When a database
        #   table is designated a resource, its records each have a unique id. 
        #   There is a name column. There is a last modified 
        #   column. There is a last modified by column. There is a userId 
        #   column. The userId column means that the record can be owned by 
        #   someone, which means that permissions can be assigned to the 
        #   resource. The resource can be tracked as to who owns it, who last
        #   modified it. Who has permission to see it. When it was created
        #   When it was last modified. A subscription can also be made to a resource.
        #   You can create something that people can subscribe to. For example, 
        #   the table <var>hForums</var> is a resource, someone can own a forum based, 
        #   on the <var>$forumId</var>. Permission can be assigned to a forum.
        #   You can track what users or groups are able to either view the forum
        #   or which users or groups are able to modify the forum (moderators). 
        #   Finally, you can assign users and groups a subscription to a forum.
        # </p>
        # <p>
        #   Resources are tracked in the <var>hFrameworkResources</var> database
        #   table. Subscription resources are tracked in the <var>hSubscriptions</var>
        #   database table. To create a subscription resource, first you 
        #   create a resource in <var>hFrameworkResources</var>, then once you have 
        #   the <var>frameworkResourceKey</var> you can create a subscription. The 
        #   <var>frameworkResourceKey</var> is simply the unique id assigned to a 
        #   row in the resource table. For example, <var>$this-&gt;hForums-&gt;deleteSubscription(1)</var>
        #   would delete all the subscriptions and the subscription resource itself for
        #   <var>$forumId = 1</var>. <var>hForums</var> in the method call is the 
        #   <var>frameworkResource</var>. The <var>frameworkResourceId</var> is 
        #   automatically retrieved based on the name of the database table.
        # </p>
        # <p>
        #   If I pass <var>$forumId</var> as the <var>$frameworkResourceKey</var> then
        #   this method deletes from <var>hSubscriptionUsers</var> all subscriptions 
        #   associated with <var>$forumId</var>, then the subscription resource itself
        #   is deleted from <var>hSubscriptions</var>. 
        # </p>
        # @end

        # @end

        $this->subscription()->subscribe(
            $this->table,
            $frameworkResourceKey,
            $userId
        );

        return $this;
    }

    public function &unsubscribe($frameworkResourceKey, $userId = 0)
    {
        # @return hDatabaseTable

        # @description
        # <h2>Unsubscribing a User From a Resource</h2>
        # <p>
        #
        # </p>
        # @end

        $this->subscription()->unsubscribe(
            $this->table,
            $frameworkResourceKey,
            $userId
        );

        return $this;
    }
}

?>