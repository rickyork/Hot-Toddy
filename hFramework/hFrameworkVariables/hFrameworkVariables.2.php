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
    public $ua;
    public $us;
    public $co;

    private $hFrameworkApplication;
    private $hFrameworkError;
    private $hFrameworkCommand;
    private $hFilePathCategory;
    private $hFilePathServer;
    private $hFilePathURL;
    private $hFileVariable;
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
        $this->variables['userAgent'][$key] = $value;
    }

    public function &checkArgument($variable, $function)
    {
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
        # The two plugin libraries <a href='/Hot Toddy/Documentation?hUser/hUser.library.php' class='code'>hUser</a>
        # and
        # <a href='/Hot Toddy/Documentation?hContact/hContact.library.php' class='code'>hContact</a> are set aside
        # in global variables
        # because if they are directly  included as member properties of the hFramework object, when
        # you debug using a backtrace, it creates a rather tricky recursion problem.
        # </p>
        # <p>
        # First,
        # <a href='/Hot Toddy/Documentation?hUser/hUser.library.php' class='code'>hUserLibrary</a>
        # is included.  This has basic methods for retrieving information about a user.
        # e.g., methods to get a user's real name, screen name, translating a <var>userName</var> to <var>userId</var> as well as
        # methods to get per-user variables.  Per-user variables are often used to set user preferences.
        # </p>
        # <p>
        # This plugin is created, by default, in all plugins as <var>$this-&gt;hUser</var>.  <var>$this-&gt;hUser-&gt;getUserName()</var>,
        # for example, would be what you would use to get a user's <var>userName</var>.
        # </p>
        # <p>
        # See: <a href='/Hot Toddy/Documentation?hUser/hUser.library.php' class='code'>hUser/hUser.library.php</a>
        # </p>
        #$GLOBALS['hUser'] = &$this->library('hUser');

        # <p>
        # Second, <a href='/Hot Toddy/Documentation?hContact/hContact.library.php' class='code'>hContactLibrary</a>
        # is included, which has basic methods for retrieving contact information for
        # users.  Contact information includes everything in the address book rolodex for a given user.
        # You can retrieve all contact data associated with a user, or specific bits of data.  Phone numbers, email addresses,
        # internet accounts, addresses, and so on.
        # </p>
        # <p>
        # This plugin is created, by default, in all plugins as <var>$this-&gt;hContact</var>.  To get all contact
        # information for the user presently logged in, you call <var>$this-&gt;hContact-&gt;getRecord();</var>
        # </p>
        # <p>
        # This method call will return all addresses, email addresses, internet accounts, phone numbers,
        # and basic contact information.
        # </p>
        # <p>
        # See: <a href='/Hot Toddy/Documentation?hContact/hContact.library.php' class='code'>hContact/hContact.library.php</a>
        # </p>
        #$GLOBALS['hContact'] = &$this->library('hContact');

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
                if (empty($this->us))
                {
                    $this->us = $this->library('hUser');
                }

                return $this->us;
            }
            case 'hContact':
            case 'contact':
            {
                if (empty($this->co))
                {
                    $this->co = $this->library('hContact');
                }

                return $this->co;
            }
            case 'userAgent':
            case 'hUserAgent':
            {
                if (empty($this->ua))
                {
                    $this->ua = $this->library('hUser/hUserAgent');
                }

                return $this->ua;
            }
            case 'variable':
            {
                if (empty($this->hFileVariable))
                {
                    $this->hFileVariable = $this->library('hFile/hFileVariable');
                }

                return $this->hFileVariable;
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

        $return = isset($this->variables[$key])? $this->variables[$key] : null;

        return $return;
    }

    public function &variables()
    {
        if (empty($this->hFileVariable))
        {
            $this->hFileVariable = $this->library('hFile/hFileVariable');
        }

        return $this->hFileVariable;
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
            case 'variable':
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
        if (!is_object($this->hFrameworkError))
        {
            $this->hFrameworkError = $this->library('hFramework/hFrameworkError');
        }

        return $this->hFrameworkError;
    }

    public function &setToPHP4()
    {
        return $this->errorObject()->setToPHP4();
    }

    public function &setToDefault()
    {
        return $this->errorObject()->setToDefault();
    }

    public function &log($message)
    {
        return $this->errorObject()->log($message);
    }

    public function &verbose($message = null, $file = null, $line = null)
    {
        return (
            $this
                ->errorObject()
                ->errorMessage(
                    'verbose',
                    $message,
                    $file,
                    $line
                )
        );
    }

    public function &console($message = null, $file = null, $line = null)
    {
        return (
            $this
                ->errorObject()
                ->errorMessage(
                    'console',
                    $message,
                    $file,
                    $line
                )
        );
    }

    public function &notice($message = null, $file = null, $line = null)
    {
        return (
            $this
                ->errorObject()
                ->errorMessage(
                    'notice',
                    $message,
                    $file,
                    $line
        );
    }

    public function &warning($message = null, $file = null, $line = null)
    {
        return (
            $this
                ->errorObject()
                ->errorMessage(
                    'warning',
                    $message,
                    $file,
                    $line
                )
        );
    }

    public function &fatal($message = null, $file = null, $line = null)
    {
        return (
            $this
                ->errorObject()
                ->errorMessage(
                    'fatal',
                    $message,
                    $file,
                    $line
                )
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

        return isset($this->variables[$method])? $this->variables[$method] : (isset($arguments[0])? $arguments[0] : '');
    }

    public function fuseObjects(&$method, &$arguments)
    {
        return $this->__call($method, $arguments);
    }

    public function queryVariableInTable(&$method, &$arguments, &$return)
    {
        return $this->variables()->queryVariableInTable($method, $arguments, $return);
    }

    public function getVariableTable($variable)
    {
        return $this->variables()->getVariableTable($variable);
    }

    public function setVariableInTable($fileVariable, $fileValue, $fileId, $table = null)
    {
        return $this->variables()->setVariableInTable($fileVariable, $fileValue, $fileId, $table);
    }

    public function setFileVariables()
    {
        return $this->variables()->setVariablesForThisFile();
    }

    public function getFileVariables($fileId)
    {
        return $this->variables()->getVariablesByFileId($fileId);
    }

    public function setVariables($array, $decodeHTML = true)
    {
        return $this->variables()->setCacheByObjectOrArray($array, $decodeHTML);
    }

    public function setVariable($key, $value, $decodeHTML = true)
    {
        return $this->variables()->setCache($key, $value, $decodeHTML);
    }

    public function getPluginFilesByVariable()
    {
        if ($this->hPluginIncludeCSS(null) || $this->hPluginIncludeJavaScript(null) || $this->hPluginIncludeFiles(null))
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

                $value = $this->$variable(null);

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
        return $this->variables()->unsetCacheByPrefix($prefix);
    }

    public function variableExists($key)
    {
        return $this->variables()->cacheExists($key);
    }

    public function unsetVariable($key)
    {
        return $this->variables()->unsetCache($key);
    }

    public function queryVariable($fileVariable, $fileId)
    {
        return $this->variables()->query($fileVariable, $fileId);
    }

    public function getVariables()
    {
        return $this->variables()->getCache();
    }
}

?>