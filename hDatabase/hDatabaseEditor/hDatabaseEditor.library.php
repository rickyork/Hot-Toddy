<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Database Editor Library
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

class hDatabaseEditorLibrary extends hPlugin {

    public function hConstructor()
    {

    }

    public function addIntegerColumn($column, $length, $default = 0, $table = nil)
    {

    }

    public function renameTable($oldName, $newName)
    {
        $this->hDatabase->whichTable($oldName);

        if (!empty($oldName) && !empty($newName))
        {
            if (!$this->tableExists($newName))
            {
                $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'oldName' => $oldName,
                            'newName' => $newName
                        )
                    )

                );

                $this->hDatabase->renameTableCache($oldName, $newName);
                $this->renameTableObject($oldName, $newName);
            }
            else
            {
                $this->warning(
                    "Unable to rename '{$oldTableName}' to '{$newTableName}'. ".
                    "The table '{$newTableName}' already exists.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function renameColumns($columns, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            foreach ($columns as $column => $newColumn)
            {
                $this->renameColumn($column, $newColumn, $table, false);
            }

            $this->resetTableObject($table);
            $this->hDatabase->refresh();
        }
    }

    public function renameColumn($oldName, $newName, $table = nil, $refresh = true)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if ($this->hDatabase->columnExists($oldName, $table))
            {
                if (!$this->hDatabase->columnExists($newName, $table) || ($oldName != $newName && strtolower($oldName) == strtolower($newName)))
                {
                    $this->hDatabase->query(
                        $this->getTemplateSQL(
                            array(
                                'table' => $table,
                                'oldName' => $oldName,
                                'newName' => $newName,
                                'columnType' => $this->hDatabase->getColumnType(
                                    $oldName,
                                    $table
                                ),
                                'isAutoIncrementColumn' => $this->hDatabase->isAutoIncrementColumn(
                                    $oldName,
                                    $table
                                )
                            )
                        )
                    );

                    $this->hDatabase->renameColumnCache(
                        $oldName,
                        $newName,
                        $table
                    );

                    if ($refresh)
                    {
                        $this->resetTableObject($table);
                    }
                }
                else
                {
                    $this->warning(
                        "Unable to rename column '{$newColumn}' in table '{$table}'. ".
                        "The column '{$newColumn}' already exists.",
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->warning(
                    "Unable to rename column '{$column}' in table '{$table}'. ".
                    "The column '{$column}' does not exist.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function dropTable($table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table
                    )
                )
            );

            $this->deleteTableObject($table);
            $this->hDatabase->deleteTableCache($table);
        }
    }

    public function copyTable($source, $destination)
    {
        $this->hDatabase->whichTable($source);

        if (!empty($source) && !empty($destination))
        {
            $this->hDatabase->query(
                $this->getTemplateSQL(
                    'dropTableIfExists',
                    array(
                        'table' => $destination
                    )
                )
            );

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    'createTableLike',
                    array(
                        'destination' => $destination,
                        'source' => $source
                    )
                )
            );

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    'insertIntoSelectFrom',
                    array(
                        'destination' => $destination,
                        'source' => $source
                    )
                )
            );

            $this->addTableObject($destination);

            $this->hDatabase->addTableToCache($destination);
            $this->hDatabase->getColumns($destination);
        }
    }

    public function dropColumns()
    {
        $columns = func_get_args();

        $table = array_pop($columns);

        foreach ($columns as $column)
        {
            $this->dropColumn($column, $table, false);
        }

        $this->resetTableObject($table);
    }

    public function dropColumn($column, $table = nil, $refresh = true)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if ($this->hDatabase->columnExists($column, $table))
            {
                $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'column' => $column
                        )
                    )
                );

                $this->hDatabase->deleteColumnCache($column, $table);

                if ($refresh)
                {
                    $this->resetTableObject($table);
                }
            }
            else
            {
                $this->warning(
                    "Unable to delete column '{$column}' from table '{$table}'. ".
                    "The column '{$column}' does not exist.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function addColumns(array $columns, $table = nil)
    {
        foreach ($columns as $column)
        {
            $this->addColumn(
                $column['column'],
                $column['type'],
                isset($column['after'])? $column['after'] : nil,
                isset($column['isFirst'])? (bool) $column['isFirst'] : false,
                $table
            );
        }
    }

    public function addColumn($column, $type, $afterColumn = nil, $firstColumn = false, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (!$this->hDatabase->columnExists($column, $table))
            {
                if (!empty($afterColumn) && !$this->hDatabase->columnExists($afterColumn, $table))
                {
                    $this->warning(
                        "Unable to add '{$column}' to '{$table}' after '{$afterColumn}' ".
                        "because '{$afterColumn}' does not exist.",
                        __FILE__,
                        __LINE__
                    );

                    return;
                }

                if (stristr($type, 'auto_increment') && !stristr($type, 'PRIMARY KEY'))
                {
                    $type .= ' PRIMARY KEY';
                }

                $result = $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'column' => $column,
                            'type' => $type,
                            'afterColumn' => $afterColumn,
                            'firstColumn' => $firstColumn
                        )
                    )
                );

                if (isset($this->hDatabase->columns[$table]))
                {
                    $this->hDatabase->columns[$table] = array();
                }

                $this->hDatabase->getColumns($table);
            }
            else
            {
                $this->warning(
                    "Unable to add '{$column}' to '{$table}' because it already exists.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function appendColumn($column, $type, $table = nil)
    {
        return $this->addColumn(
            $column,
            $type,
            nil,
            false,
            $table
        );
    }

    public function prependColumn($column, $type, $table = nil)
    {
        return $this->addColumn(
            $column,
            $type,
            nil,
            true,
            $table
        );
    }

    public function addFullTextIndex($columns, $name = nil, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (!is_array($columns))
            {
                $columns = array($columns);
            }

            if (empty($name))
            {
                $name = implode('_', $columns);
            }

            foreach ($columns as $column)
            {
                if (!$this->hDatabase->columnExists($column, $table))
                {
                    $this->warning(
                        "Unable to add '{$column}' to fulltext index because it doesn't exist in '{$table}'",
                        __FILE__,
                        __LINE__
                    );
                }
            }

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'name' => $name,
                        'columns' => hString::implodeToList($columns, ',', '`')
                    )
                )
            );
        }
    }

    public function addUniqueIndex($columns, $name = nil, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (!is_array($columns))
            {
                $columns = array($columns);
            }

            if (empty($name))
            {
                $name = implode('_', $columns);
            }

            if ($this->hDatabase->columnsExist($columns, $table))
            {
                $this->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'name' => $name,
                            'columns' => hString::implodeToList($columns, ',', '`')
                        )
                    )
                );
            }
            else
            {
                $this->warning(
                    "Unable to add columns to unique index because one or more or the columns ".
                    "specified don't exist in '{$table}'.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function addIndex($columns, $name = nil, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (!is_array($columns))
            {
                $columns = array($columns);
            }

            if (empty($name))
            {
                $name = implode('_', $columns);
            }

            if ($this->hDatabase->columnsExist($columns, $table))
            {
                $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'name' => $name,
                            'columns' => hString::implodeToList($columns, ',', '`')
                        )
                    )
                );
            }
            else
            {
                $this->warning(
                    "Unable to add columns to index because one or more of the columns specified ".
                    "don't exist in '{$table}'.",
                    __FILE__,
                    __LINE__
                );
            }
        }
     }

    public function addKey($columns, $name = nil, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (!is_array($columns))
            {
                $columns = array($columns);
            }

            if (empty($name))
            {
                $name = implode('_', $columns);
            }

            if ($this->hDatabase->columnsExist($columns, $table))
            {
                $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'name' => $name,
                            'columns' => hString::implodeToList($columns, ',', '`')
                        )
                    )
                );
            }
            else
            {
                $this->warning(
                    "Unable to add columns to key because one or more of the columns ".
                    "specified don't exist in '{$table}'.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function dropIndex($name, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (is_array($name))
            {
                $name = implode('_', $name);
            }

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'name' => $name
                    )
                )
            );
        }
    }

    public function dropKey($name, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (is_array($name))
            {
                $name = implode('_', $name);
            }

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'name' => $name
                    )
                )
            );
        }
    }

    public function dropFullTextIndex($name = nil, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (is_array($name))
            {
                $name = implode('_', $name);
            }

            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'name' => $name
                    )
                )
            );
        }
    }

    public function addPrimaryKey($columns, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if (!is_array($columns))
            {
                $columns = array($columns);
            }

            if ($this->hDatabase->columnsExist($columns, $table))
            {
                $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'columns' => hString::implodeToList($columns, ',', '`')
                        )
                    )
                );
            }
            else
            {
                $this->warning(
                    "Unable to add a primary key because one or more or the columns ".
                    "specified don't exist in '{$table}'.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function modifyColumn($column, $type, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            if ($this->hDatabase->columnExists($column, $table))
            {
                $this->hDatabase->query(
                    $this->getTemplateSQL(
                        array(
                            'table' => $table,
                            'column' => $column,
                            'type' => $type
                        )
                    )
                );
            }
            else
            {
                $this->warning(
                    "Unable to modify '{$table}' because '{$column}' does not exist.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    public function dropPrimaryKey($table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table
                    )
                )
            );
        }
    }

    public function setAutoIncrement($counter, $table = nil)
    {
        $this->hDatabase->whichTable($table);

        if (!empty($table))
        {
            $this->hDatabase->query(
                $this->getTemplateSQL(
                    array(
                        'table' => $table,
                        'counter' => $counter
                    )
                )
            );
        }
    }

    public function createTable($table, array $columns, array $options = array())
    {
        # $this->createTable(
        #     'hCategories',
        #     array(
        #         'hCategoryId'           => hDatabase::autoIncrement,
        #         'hUserId'               => hDatabase::id,
        #         'hCategoryName'         => hDatabase::name,
        #         'hFileIconId'           => hDatabase::id,
        #         'hCategoryParentId'     => hDatabase::id,
        #         'hCategoryRootId'       => hDatabase::id,
        #         'hCategoryLastModified' => hDatabase::time
        #     ),
        #     array(
        #         'indexes' => array(
        #             'key' => array(
        #                 array(
        #                     'hCategoryParentId'
        #                 )
        #             )
        #         )
        #     )
        # );

        $columnDefinitions = array();

        $isAutoIncrement = false;
        $autoIncrementColumn = '';

        foreach ($columns as $column => $type)
        {
            if (stristr($type, 'auto_increment'))
            {
                $isAutoIncrement = true;
                $autoIncrementColumn = $column;
            }

            array_push(
                $columnDefinitions,
                "`{$column}` {$type}"
            );
        }

        if ($isAutoIncrement)
        {
            array_push(
                $columnDefinitions,
                "PRIMARY KEY (`{$autoIncrementColumn}`)"
            );
        }

        $this->hDatabase->query(
            $this->getTemplateSQL(
                array(
                    'table'             => $table,
                    'columns'           => implode(",\n", $columnDefinitions),
                    'engine'            => isset($options['engine'])? $options['engine'] : nil,
                    'charset'           => isset($options['charset']) ? $options['charset'] : nil,
                    'isAutoIncrement'   => $isAutoIncrement,
                    'autoIncrement'     => isset($options['autoIncrement'])? $options['autoIncrement'] : nil
                )
            )
        );

        if (isset($options['indexes']) && is_array($options['indexes']))
        {
            foreach ($options['indexes'] as $indexType => $indexTypeColumns)
            {
                $method = '';

                $shouldContinue = false;

                switch ($indexType)
                {
                    case 'key':
                    {
                        $method = 'addKey';
                        break;
                    }
                    case 'unique':
                    {
                        $method = 'addUniqueKey';
                        break;
                    }
                    case 'index':
                    {
                        $method = 'addIndex';
                        break;
                    }
                    case 'primaryKey':
                    {
                        $method = 'addPrimaryKey';
                        break;
                    }
                    default:
                    {
                        $this->warning(
                            "Unable to add index because the type of index '{$indexType}' is unknown.",
                            __FILE__,
                            __LINE__
                        );

                        $shouldContinue = true;
                    }
                }

                if ($shouldContinue)
                {
                    continue;
                }

                if (is_array($indexTypeColumns))
                {
                    foreach ($indexTypeColumns as $indexColumns)
                    {
                        call_user_func_array(
                            array(
                                $this,
                                $method
                            ),
                            array(
                                $indexColumns,
                                nil,
                                $table
                            )
                        );
                    }
                }
                else
                {
                    call_user_func_array(
                        array(
                            $this,
                            $method
                        ),
                        array(
                            $indexTypeColumns,
                            nil,
                            $table
                        )
                    );
                }
            }
        }
    }
}

?>