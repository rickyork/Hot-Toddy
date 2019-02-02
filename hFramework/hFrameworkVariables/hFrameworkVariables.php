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
# <h1>Framework Variable API</h1>
# <p>
#
# </p>
# @end

class hFrameworkVariables extends hPluginLibrary {

    public $variables = array();

    public $_userAgent;
    public $_user;
    public $_contact;
    public $_on;
    public $_off;
    public $_fire;

    private $hFrameworkApplication;
    private $hFrameworkError;
    private $hFrameworkCommand;
    private $hFilePathCategory;
    private $hFilePathServer;
    private $hFilePathURL;
    private $hJSON;
    private $hShell;

    public function __set($key, $value)
    {
        if (is_object($value))
        {
            $this->notice('Object added to framework variable '.$key, __FILE__, __LINE__);
        }

        $this->variables[$key] = $value;
    }

    public function setUserAgentValue($key, $value)
    {
         # @return void

         # @description
         # <h2>Setting a User Agent Variable</h2>
         # <p>
         #  Sets a user agent variable to the indicated value.
         # </p>
         # @end

        $this->variables['userAgent'][$key] = $value;
    }

    public function &checkArgument($variable, $function)
    {
        # @return hFrameworkVariables

        # @description
        # <h2>Checking an Argument</h2>
        # <p>
        #   Makes sure that the supplied variable meets or does not meet the indicated boolean function.
        # </p>
        # @end

        if (substr($function, 0, 1) == '!')
        {
            $exe = substr($function, 1);

            if ($this->executeMethod($variable, $exe))
            {
                $this->warning('Argument failed to meet condition, '.$function.'.', __FILE__, __LINE__);
            }
        }
        else if (!$this->executeMethod($variable, $function))
        {
            $this->warning('Argument failed to meet condition, '.$function.'.', __FILE__, __LINE__);
        }

        return $this;
    }

    public function executeMethod(&$variable, $function)
    {
        # @return mixed

        # @description
        # <h2>Executing a Method</h2>
        # <p>
        #   Executes any method with a fixed variable value using eval.
        # </p>
        # @end

        # empty and isset are language constructs, not functions, but we need them anyway.
        if (function_exists($function) || $function == 'empty' || $function == 'isset')
        {
            return eval("return $function(\$variable);");
        }
    }

    public function &__get($key)
    {
        # @description
        # <h2>Dynamic APIs</h2>
        # <h3>User and Contact APIs</h3>
        # <p>
        #   The two plugin libraries <a href='/Hot Toddy/Documentation?hUser/hUser.library.php' class='code'>hUser</a>
        #   and
        #   <a href='/Hot Toddy/Documentation?hContact/hContact.library.php' class='code'>hContact</a> are set aside
        #   in global variables
        #   because if they are directly  included as member properties of the hFramework object, when
        #   you debug using a backtrace, it creates a rather tricky recursion problem.
        # </p>
        # <p>
        #   First,
        #   <a href='/Hot Toddy/Documentation?hUser/hUser.library.php' class='code'>hUserLibrary</a>
        #   is included.  This has basic methods for retrieving information about a user.
        #   e.g., methods to get a user's real name, screen name, translating a <var>userName</var> to <var>userId</var> as well as
        #   methods to get per-user variables.  Per-user variables are often used to set user preferences.
        # </p>
        # <p>
        #   This plugin is created, by default, in all plugins as <var>$this-&gt;hUser</var>.  <var>$this-&gt;hUser-&gt;getUserName()</var>,
        #   for example, would be what you would use to get a user's <var>userName</var>.
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hUser/hUser.library.php' class='code'>hUser/hUser.library.php</a>
        # </p>
        # <code>$GLOBALS['hUser'] = &amp;$this-&gt;library('hUser');</code>

        # <p>
        #   Second, <a href='/Hot Toddy/Documentation?hContact/hContact.library.php' class='code'>hContactLibrary</a>
        #   is included, which has basic methods for retrieving contact information for
        #   users.  Contact information includes everything in the address book rolodex for a given user.
        #   You can retrieve all contact data associated with a user, or specific bits of data.  Phone numbers, email addresses,
        #   internet accounts, addresses, and so on.
        # </p>
        # <p>
        #   This plugin is created, by default, in all plugins as <var>$this-&gt;hContact</var>.  To get all contact
        #   information for the user presently logged in, you call <var>$this-&gt;hContact-&gt;getRecord();</var>
        # </p>
        # <p>
        #   This method call will return all addresses, email addresses, internet accounts, phone numbers,
        #   and basic contact information.
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hContact/hContact.library.php' class='code'>hContact/hContact.library.php</a>
        # </p>
        # <code>$GLOBALS['hContact'] = &amp;$this-&gt;library('hContact');</code>
        # @end

        switch ($key)
        {
            case 'hPrivateFramework':
            case 'privateFramework':
            {
                return $this->hPrivateFramework;
            }
            case 'hUser':
            case 'user':
            {
                if (empty($this->_user))
                {
                    $this->_user = $this->library('hUser');
                }

                return $this->_user;
            }
            case 'hContact':
            case 'contact':
            {
                if (empty($this->_contact))
                {
                    $this->_contact = $this->library('hContact');
                }

                return $this->_contact;
            }
            case 'userAgent':
            case 'hUserAgent':
            {
                if (empty($this->_userAgent))
                {
                    $this->_userAgent = $this->library('hUser/hUserAgent');
                }

                return $this->_userAgent;
            }
            case 'on':
            {
                if (empty($this->_on))
                {
                    $this->_on = $this->library('hFramework/hFrameworkEvent/hFrameworkEventOn');
                }

                return $this->_on;
            }
            case 'off':
            {
                if (empty($this->_off))
                {
                    $this->_off = $this->library('hFramework/hFrameworkEvent/hFrameworkEventOff');
                }

                return $this->_off;
            }
            case 'fire':
            case 'ricochet':
            {
                if (empty($this->_fire))
                {
                    $this->_fire = $this->library('hFramework/hFrameworkEvent/hFrameworkEventFire');
                }

                return $this->_fire;
            }
        }

        if (isset($this->tables) && is_array($this->tables))
        {
            if (in_array($key, $this->tables))
            {
                if (!isset($this->tableObjects[$key]) || !is_object($this->tableObjects[$key]))
                {
                    $this->tableObjects[$key] = new hDatabaseTable($key, $GLOBALS['hDatabase']);
                }

                return $this->tableObjects[$key];
            }
        }

        $return = isset($this->variables[$key])? $this->variables[$key] : nil;

        return $return;
    }

    public function __isset($key)
    {
        switch ($key)
        {
            case 'hPrivateFramework':
            case 'userAgent':
            case 'hUserAgent':
            case 'contact':
            case 'hContact':
            case 'user':
            case 'hUser':
            {
                return true;
            }
        }

        if (isset($this->tableObjects[$key]))
        {
            return true;
        }

        return isset($this->variables[$key]);
    }

    private function errorObject()
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Creating and Retrieving the Framework Error Object</h2>
        # <p>
        #   If it does not exist, the hFrameworkErrorLibrary object is instantiated and assigned to
        #   the <var>$hFrameworkError</var> property, and then the object is returned by reference.
        # </p>
        # @end

        if (!is_object($this->hFrameworkError))
        {
            $this->hFrameworkError = $this->library('hFramework/hFrameworkError');
        }

        return $this->hFrameworkError;
    }

    public function &setToPHP4()
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Setting PHP4 Error Handling</h2>
        # <p>
        #   Resets PHP error handling to a PHP4 compatible state.
        # </p>
        # @end

        return
            $this->errorObject()
                ->setToPHP4();
    }

    public function &setToDefault()
    {
        # @return hFrameworkErrorLibrary
        # @description
        # <h2>Setting Default Error Handling</h2>
        # <p>
        #   Resets PHP error handling to the default.
        # </p>
        # @end

        return
            $this->errorObject()
                ->setToDefault();
    }

    public function &log($message)
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Logging a Hot Toddy Error Message</h2>
        # <p>
        #
        # </p>
        # @end

        return
            $this->errorObject()
                ->log($message);
    }

    public function &verbose($message = nil, $file = nil, $line = nil)
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Verbose Messages</h2>
        # <p>
        #   Writes a verbose level message to Hot Toddy's error console, or log.
        # </p>
        # @end

        return
            $this->errorObject()
                ->errorMessage(
                    'verbose',
                    $message,
                    $file,
                    $line
                );
    }

    public function &console($message = nil, $file = nil, $line = nil)
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Console Messages</h2>
        # <p>
        #   Writes a console level message to Hot Toddy's error console, or log.
        # </p>
        # @end

        return
            $this->errorObject()
                ->errorMessage(
                    'console',
                    $message,
                    $file,
                    $line
                );
    }

    public function &notice($message = nil, $file = nil, $line = nil)
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Notice Messages</h2>
        # <p>
        #   Writes a notice level message to Hot Toddy's error console, or log.
        # </p>
        # @end

        return
            $this->errorObject()
                ->errorMessage(
                    'notice',
                    $message,
                    $file,
                    $line
                );
    }

    public function &warning($message = nil, $file = nil, $line = nil)
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Warning Messages</h2>
        # <p>
        #   Writes a warning level message to Hot Toddy's error console, or log.
        # </p>
        # @end

        return
            $this->errorObject()
                ->errorMessage(
                    'warning',
                    $message,
                    $file,
                    $line
                );
    }

    public function &fatal($message = nil, $file = nil, $line = nil)
    {
        # @return hFrameworkErrorLibrary

        # @description
        # <h2>Fatal Messages</h2>
        # <p>
        #   Writes a fatal level message to Hot Toddy's error console, or log.
        # </p>
        # @end

        return
            $this->errorObject()
                ->errorMessage(
                    'fatal',
                    $message,
                    $file,
                    $line
                );
    }

    public function __call($method, $arguments)
    {
        switch ($method)
        {
            case 'isCategoryPath':
            case 'isHomeCategoryPath':
            case 'getCategoryPath':
            case 'getCategoryIdFromPath':
            case 'categoryExists':
            {
                if (!is_object($this->hFilePathCategory))
                {
                    $this->hFilePathCategory = $this->library('hFile/hFilePath/hFilePathCategory');
                }

                return call_user_func_array(
                    array(
                        $this->hFilePathCategory,
                        $method
                    ),
                    $arguments
                );
            }
            case 'isServerPath':
            case 'getVirtualFileSystemPath':
            case 'getServerFileSystemPath':
            {
                if (!is_object($this->hFilePathServer))
                {
                    $this->hFilePathServer = $this->library('hFile/hFilePath/hFilePathServer');
                }

                return call_user_func_array(
                    array(
                        $this->hFilePathServer,
                        $method
                    ),
                    $arguments
                );
            }
            case 'shellArgumentExists':
            case 'getShellArgumentValue':
            {
                if (!is_object($this->hShell))
                {
                    $this->hShell = $this->library('hShell');
                }

                return call_user_func_array(
                    array(
                        $this->hShell,
                        $method
                    ),
                    $arguments
                );
            }
            case 'absolutePathToSelf':
            case 'getURL':
            case 'getURLByFileId':
            {
                if (!is_object($this->hFilePathURL))
                {
                    $this->hFilePathURL = $this->library('hFile/hFilePath/hFilePathURL');
                }

                return call_user_func_array(
                    array(
                        $this->hFilePathURL,
                        $method
                    ),
                    $arguments
                );
            }
            case 'command':
            case 'pipeCommand':
            case 'rename':
            case 'mkdir':
            case 'chmod':
            case 'chown':
            case 'chgrp':
            case 'touch':
            case 'copy':
            case 'move':
            case 'getMIMEType':
            case 'rm':
            case 'hot':
            {
                if (!is_object($this->hFrameworkCommand))
                {
                    $this->hFrameworkCommand = $this->library('hFramework/hFrameworkCommand');
                }

                return call_user_func_array(
                    array(
                        $this->hFrameworkCommand,
                        $method
                    ),
                    $arguments
                );
            }
            case 'prepareApplication':
            {
                if (!is_object($this->hFrameworkApplication))
                {
                    $this->hFrameworkApplication = $this->library('hFramework/hFrameworkApplication');
                }

                return call_user_func_array(
                    array(
                        $this->hFrameworkApplication,
                        $method
                    ),
                    $arguments
                );
            }
        }

        if (isset($this->fusePlugins) && is_array($this->fusePlugins))
        {
            foreach ($this->fusePlugins as $plugin)
            {
                if (isset($GLOBALS['hPlugins'][$plugin]) && method_exists($GLOBALS['hPlugins'][$plugin], $method))
                {
                    return @call_user_func_array(
                        array(
                            $GLOBALS['hPlugins'][$plugin],
                            $method
                        ),
                        $arguments
                    );
                }
            }
        }

        if ($method == 'activity')
        {
            # Activity logging is turned off.
            return false;
        }

        $return = false;
        $value = $this->queryVariableInTable($method, $arguments, $return);

        if ($return)
        {
            return $value;
        }

        if (isset($arguments[1]))
        {
            $query = $this->queryVariable($method, $arguments[1]);

            $rtn = $arguments[0];

            if (!empty($arguments[2]))
            {
                if ($this->hDatabase->resultsExist($query))
                {
                    $rtn = $this->hDatabase->getColumn($query);

                    $this->hFileVariables->update(
                        array(
                            'hFileValue' => $arguments[0]
                        ),
                        array(
                            'hFileId' => (int) $arguments[1],
                            'hFileVariable' => $method
                        )
                    );
                }
                else
                {
                    $this->hFileVariables->insert(
                        array(
                            'hFileId'       => (int) $arguments[1],
                            'hFileVariable' => $method,
                            'hFileValue'    => $arguments[0]
                        )
                    );
                }

                return $arguments[0];
            }
            else if ($this->hDatabase->resultsExist($query))
            {
                return $this->hDatabase->getColumn($query);
            }

            return $rtn;
        }

        if (isset($this->variables[$method]))
        {
            return $this->variables[$method];
        }

        if (isset($arguments[0]))
        {
            return $arguments[0];
        }

        return '';
    }

    public function fuseObjects(&$method, &$arguments)
    {
        return $this->__call($method, $arguments);
    }

    protected function queryVariableInTable(&$method, &$arguments, &$return)
    {
        $table = $this->getVariableTable($method);

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
                $this->setVariableInTable($method, $arguments[0], $arguments[1], $table);
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

    public function getVariableTable($variable)
    {
        # @return string

        # @description
        # <h2>Determining a File Variable's Database Table</h2>
        # <p>
        #   Returns the database table for the specified file variable.
        # </p>
        # @end

        switch ($variable)
        {
            case 'hLanguageId':
            case 'hDirectoryId':
            case 'hUserId':
            case 'hFileParentId':
            case 'hFileName':
            case 'hPlugin':
            case 'hFileSortIndex':
            case 'hFileCreated':
            case 'hFileLastModified':
            case 'hFileLastModifiedBy':
            {
                return 'hFiles';
            }
            case 'hFileDescription':
            case 'hFileKeywords':
            case 'hFileTitle':
            case 'hFileDocument':
            case 'hFileComments':
            case 'hFileDocumentCreated':
            case 'hFileDocumentLastModified':
            {
                return 'hFileDocuments';
            }
            case 'hFileCSS':
            case 'hFileJavaScript':
            {
                return 'hFileHeaders';
            }
            case 'hFileIconId':
            case 'hFileMIME':
            case 'hFileSize':
            case 'hFileDownload':
            case 'hFileIsSystem':
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

    public function setVariableInTable($fileVariable, $fileValue, $fileId, $table = nil)
    {
        # @return void

        # @description
        # <h2>Setting a Framework Variable in a Database Table</h2>
        # <p>
        #   Sets a framework variable in the <var>hFileVariables</var>, <var>hFileHeaders</var>, <var>hFileProperties</var>
        #   and other tables warehousing data related to files.
        # </p>
        # @end

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

        #return $this;
    }

    public function setFileVariables()
    {
        # @return void
        # @description
        # <h2>Setting File Variables</h2>
        # <p>
        #   Retrieves and sets framework variables for the current Hot Toddy document.
        # </p>
        # @end

        $this->setVariables(
            $this->getFileVariables($this->hFileId)
        );

        #return $this;
    }

    public function getFileVariables($fileId)
    {
        # @return array

        # @description
        # <h2>Retrieving File Variables</h2>
        # <p>
        #   Returns all variables stored in the <var>hFileVariables</var> database table for the
        #   specified <var>$fileId</var>.
        # </p>
        # @end

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

    public function setVariables($array, $decodeHTML = true)
    {
        # @return void

        # @description
        # <h2>Setting Multiple Framework Variables</h2>
        # <p>
        #   Sets a single framework variable.
        # </p>
        # @end

        if (is_array($array) || is_object($array))
        {
            foreach ($array as $key => $value)
            {
                $this->setVariable($key, $value, $decodeHTML);
            }

            $this->getPluginFilesByVariable();
        }
    }

    public function setVariable($key, $value, $decodeHTML = true)
    {
        # @return void

        # @description
        # <h2>Setting a Framework Variable</h2>
        # <p>
        #   Sets a single framework variable.
        # </p>
        # @end

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

        #return $this;
    }

    public function getPluginFilesByVariable()
    {
        # @return void

        # @description
        # <h2>Getting Plugin Files By Variable</h2>
        # <p>
        #
        # </p>
        # @end

        if ($this->hPluginIncludeCSS(nil) || $this->hPluginIncludeJavaScript(nil) || $this->hPluginIncludeFiles(nil))
        {
            $variables = array(
                'hPluginIncludeCSS',
                'hPluginIncludeJavaScript',
                'hPluginIncludeFiles'
            );

            foreach ($variables as $variable)
            {
                $types = array('CSS', 'JavaScript', 'Files');

                foreach ($types as $oneType)
                {
                    if (strstr($variable, $oneType))
                    {
                        $type = $oneType;
                        break;
                    }
                }

                $value = $this->$variable(nil);

                if (!empty($value))
                {
                    if (strstr($value, ','))
                    {
                        $plugins = explode(',', $value);

                        foreach ($plugins as $plugin)
                        {
                            $this->{"getPlugin{$type}"}(trim($plugin));
                        }
                    }
                    else
                    {
                        $this->{"getPlugin{$type}"}(trim($value));
                    }
                }
            }

            unset($this->variables['hPluginIncludeCSS']);
            unset($this->variables['hPluginIncludeJavaScript']);
            unset($this->variables['hPluginIncludeFiles']);
        }
    }

    public function unsetVariables($prefix)
    {
        # @return void

        # @description
        # <h2>Unsetting Framework Variables By Prefix</h2>
        # <p>
        #   Allows you to unset a variable via its framework prefix, for example to unset
        #   all variables associated with the plugin 'hSpotlight'.  Each variable for that
        #   plugin is prefixed with the name of the plugin, so the prefix 'hSpotlight'
        #   is provided to this method to unset all variables with that prefix.
        # </p>
        # @end

        foreach ($this->variables as $key => $value)
        {
            if (substr($key, 0, strlen($prefix)) == $prefix)
            {
                unset($this->variables[$key]);
            }
        }

        #return $this;
    }

    public function variableExists($key)
    {
        # @return boolean

        # @description
        # <h2>Checking for Framework Variable Existence</h2>
        # <p>
        #   Checks to see if the specified variable exists.
        # </p>
        # @end

        return isset($this->variables[$key]);
    }

    public function unsetVariable($key)
    {
        # @return void

        # @description
        # <h2>Unsetting a Framework Variable</h2>
        # <p>
        #   Unsets a framework variable.
        # </p>
        # @end

        unset($this->variables[$key]);
        #return $this;
    }

    public function queryVariable($fileVariable, $fileId)
    {
        # @return query

        # @description
        # <h2>Query for a File Variable</h2>
        # <p>
        #   Queries for a file variable's value.
        # </p>
        # @end

        return $this->hFileVariables->selectQuery(
            'hFileValue',
            array(
                'hFileId'       => (int) $fileId,
                'hFileVariable' => $fileVariable
            )
        );
    }

    public function getVariables()
    {
        return $this->variables;
    }

}

?>