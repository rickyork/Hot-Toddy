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
# <h1>Hot Toddy Plugin API</h1>
# <p>
#
# </p>
# @end

class hPlugin extends hFrameworkApplication {

    public function __set($key, $value)
    {
        # @return void

        # @description
        # <h2>Plugin Setter</h2>
        # <p>
        #
        # </p>
        # @end

        if (method_exists($GLOBALS['hFramework'], '__set'))
        {
            $GLOBALS['hFramework']->__set($key, $value);
        }
    }

    public function &__get($key)
    {
        # @return mixed

        # @description
        # <h2>Plugin Getter</h2>
        # <p>
        #
        # </p>
        # @end

        if (method_exists($GLOBALS['hFramework'], '__get'))
        {
            return $GLOBALS['hFramework']->__get($key);
        }

        $variable = nil;

        return $variable;
    }

    /**
    * Overloading is used to make framework methods and variables available within
    * a plugin, and simplifies references to these methods and variables.
    */
    public function __call($method, $arguments)
    {
        # @return mixed

        # @description
        # <h2>Plugin Overloading</h2>
        # <p>
        #
        # </p>
        # @end

        if (substr($method, 0, 11) == 'getTemplate')
        {
            if (isset($arguments[0]) && is_array($arguments[0]) || !isset($arguments[0]))
            {
                if (isset($arguments[0]))
                {
                    $arguments[1] = $arguments[0];
                }

                $backtrace = debug_backtrace();

                $i = 2;

                if ($backtrace[1]['function'] == '__call')
                {
                    $i = 3;
                }

                $arguments[0] = $backtrace[$i]['function'];
                # echo $arguments[0]."\n";

                # for ($i = 0; true; $i++)
                # {
                #     if (isset($backtrace[$i]['function']))
                #     {
                #         echo $backtrace[$i]['function']."\n";
                #     }
                #     else
                #     {
                #         break;
                #     }
                # }
            }

            if ($method != 'getTemplate')
            {
                $arguments[0] .= '.'.strtolower(
                    substr($method, 11)
                );

                $method = 'getTemplate';
            }
        }

        if ($method == 'getTemplate')
        {
            $template = $arguments[0];

            if (!$this->beginsPath($arguments[0], $this->hServerDocumentRoot))
            {
                if (strstr($arguments[0], ':'))
                {
                    list($frameworkVariable, $templateName) = explode(':', $arguments[0]);

                    $frameworkVariableValue = $this->$frameworkVariable(nil);

                    if (!empty($frameworkVariableValue) && $frameworkVariableValue != $templateName)
                    {
                        $arguments[0] = $frameworkVariableValue;
                    }
                    else
                    {
                        $arguments[0] = $templateName;
                    }
                }

                if (!strstr($arguments[0], '.'))
                {
                    $arguments[0] .= '.html';
                }

                $folder = strtoupper(
                    $this->getExtension($arguments[0])
                );

                if (substr($arguments[0], 0, 1) != '/')
                {
                    $arguments[0] = (
                        $this->hServerDocumentRoot.
                        dirname($this->hPluginPath).'/'.
                        $folder.'/'.
                        $arguments[0]
                    );
                }

                $arguments[0] = $this->getIncludePath($arguments[0]);

                if (empty($arguments[0]) || !file_exists($arguments[0]))
                {
                    $this->warning(
                        "Template '{$template}' does not exist.",
                        __FILE__,
                        __LINE__
                    );

                    return nil;
                }
            }
        }

        if ($method == 'sendMail')
        {
            if (isset($arguments[0]))
            {
                $hMail = $this->library('hMail');

                if (!class_exists('hJSONLibrary'))
                {
                    hFrameworkInclude(
                        $this->hServerDocumentRoot.'/hJSON/hJSON.library.php'
                    );
                }

                $hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');

                $plugin = explode(
                    '/',
                    $this->hPluginPath
                );

                $name = array_pop($plugin);

                $pluginName = substr(
                    $name,
                    0,
                    strpos($name, '.')
                );

                $templateName = isset($arguments[0])? $arguments[0] : $pluginName;

                $mailJSON = $this->getIncludePath(
                    $this->hServerDocumentRoot.
                    dirname($this->hPluginPath).'/'.
                    $templateName.'.mail.json'
                );

                if (file_exists($mailJSON))
                {
                    $templateVariables = isset($arguments[1])? $arguments[1] : array();
                    $mailConfiguration = $hJSON->getJSON($mailJSON);

                    $this->fire->mail(
                        $templateName,
                        $templateVariables,
                        $this->hPluginPath,
                        $mailConfiguration
                    );

                    // Template.mail.json
                    return $hMail->sendMail(
                        $templateName,
                        $templateVariables,
                        $this->hPluginPath,
                        $mailConfiguration,
                        filemtime($mailJSON)
                    );
                }
                else
                {
                    $this->warning(
                        'The mail configuration file '.$mailJSON.' does not exist.',
                        __FILE__,
                        __LINE__
                    );
                }
            }
            else
            {
                $this->warning(
                    'The name of the mail template was not specified.',
                    __FILE__,
                    __LINE__
                );
            }

            return false;
        }

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

        return $GLOBALS['hFramework']->__call(
            $method,
            $arguments
        );
    }

    public function __isset($key)
    {
        return $GLOBALS['hFramework']->__isset($key);
    }
}

?>