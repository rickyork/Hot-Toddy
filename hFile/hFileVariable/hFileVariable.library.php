<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework Variables
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
# @description
# <h1>Framework Variable API</h1>
# <p>
#
# </p>
# @end

class hFileVariableLibrary extends hPlugin {

    private $variables;

    public function hConstructor()
    {
        $return = false;

        $value = $this->queryVariableInTable(
            $method,
            $arguments,
            $return
        );

        if ($return)
        {
            return $value;
        }

        if (isset($arguments[1]))
        {
            $query = $this->queryVariable($method, $arguments[1]);

            $rtn = $arguments[0];

            if ($this->hDatabase->resultsExist($query))
            {
                return $this->hDatabase->getColumn($query);
            }

            return $rtn;
        }

        return isset($this->variables[$method])? $this->variables[$method] : (isset($arguments[0])? $arguments[0] : '');
    }

    public function &save($fileVariable, $fileValue, $fileId, $table = nil)
    {
        if (empty($table))
        {
            $table = $this->getDatabaseTable($fileVariable);
        }

        $variableExists = $this->$table->selectQuery(
            $fileVariable,
            array(
                'hFileId' => (int) $fileId
            )
        );

        if ($variableExists)
        {
            $set = array();
            $set[$fileVariable] = $fileValue;

            $this->$table->update(
                $set,
                array(
                    'hFileId' => (int) $fileId
                )
            );
        }
        else
        {
            $insert = array();
            $insert['hFileId'] = (int) $fileId;
            $insert[$fileVariable] = $fileValue;

            $this->$table->insert($insert);
        }

        return $this;
    }

    public function get($fileVariable, $defaultValue = nil, $fileId = 0)
    {
        if (empty($fileId))
        {
            if (isset($this->variables[$fileVariable]))
            {
                return $this->variables[$fileVariable];
            }

            return $defaultValue;
        }
        else
        {

        }
    }

    public function delete($fileVariable, $fileId = 0)
    {
        unset($this->variables[$fileVariable];

        if (empty($fileId))
        {
            $fileId = $this->hFileId;
        }
        else
        {

        }
    }

    public function exists()
    {

    }

    public function query($fileVariable, $fileId)
    {
        return $this->hFileVariables->selectQuery(
            'hFileValue',
            array(
                'hFileId'       => (int) $fileId,
                'hFileVariable' => $fileVariable
            )
        );
    }

    public function queryVariableInTable(&$method, &$arguments, &$return)
    {
        $table = $this->getDatabaseTable($method);

        if (!empty($table) && isset($arguments[1]))
        {
            $query = $this->$table->selectQuery(
                $method,
                array(
                    'hFileId' => (int) $arguments[1]
                )
            );

            if (isset($arguments[2]) && $this->hDatabase->resultsExist($query))
            {
                $this->save($method, $arguments[0], $arguments[1], $table);
                return $arguments[0];
            }

            $return = true;
            return ($this->hDatabase->resultsExist($query)? $this->hDatabase->getColumn($query) : $arguments[0]);
        }
        else
        {
            $return = false;
        }
    }

    public function getDatabaseTable($variable)
    {
        switch ($variable)
        {
            case 'hFileCSS':
            case 'hFileJavaScript':
            {
                return 'hFileHeaders';
            }
            case 'hFileIconId':
            case 'hFileMIME':
            case 'hFileSize':
            case 'hFileDownload':
            case 'hFileSystem':
            case 'hFileSystemPath':
            case 'hFileMD5Checksum':
            case 'hFileLabel':
            {
                return 'hFileProperties';
            }
            default:
            {
                return nil;
            }
        }
    }

    public function &setVariableInTable($fileVariable, $fileValue, $fileId, $table = nil)
    {
        if (empty($table))
        {
            $table = $this->getVariableTable($fileVariable);
        }

        $set = array();
        $set[$fileVariable] = $fileValue;

        $this->$table->update(
            $set,
            array(
                'hFileId' => (int) $fileId
            )
        );

        return $this;
    }

    public function &setVariablesForThisFile()
    {
        $this->setCacheByObjectOrArray(
            $this->getVariablesByFileId(
                $this->hFileId
            )
        );

        return $this;
    }

    public function getVariablesByFileId($fileId)
    {
        return $this->hFileVariables->selectColumnsAsKeyValue(
            array(
                'hFileVariable',
                'hFileValue'
            ),
            array(
                'hFileId' => (int) $fileId
            )
        );
    }

    public function &setCache($key, $value, $decodeHTML = true)
    {
        $key = trim($key);

        switch ($key)
        {
            case 'hFileCSS':
            case 'hFileJavaScript':
            {
                if (isset($this->variables[$key]))
                {
                    $this->variables[$key] .= $decodeHTML? hString::decodeHTML($value) : $value;
                    break;
                }
            }
            default:
            {
                $this->variables[$key] = is_string($value) && $decodeHTML? hString::decodeHTML($value) : $value;
            }
        }

        return $this;
    }

    public function &setCacheByObjectOrArray($array, $decodeHTML = true)
    {
        if (is_array($array) || is_object($array))
        {
            foreach ($array as $key => $value)
            {
                $this->setCache($key, $value, $decodeHTML);
            }

            $this->getPluginFilesByVariable();
        }

        return $this;
    }

    public function &unsetCacheByPrefix($prefix)
    {
        foreach ($this->variables as $key => $value)
        {
            if (substr($key, 0, strlen($prefix)) == $prefix)
            {
                unset($this->variables[$key]);
            }
        }

        return $this;
    }

    public function cacheExists($key)
    {
        return isset($this->variables[$key]);
    }

    public function &unsetCache($key)
    {
        unset($this->variables[$key]);
        return $this;
    }

    public function getCache()
    {
        return $this->variables;
    }
}

?>