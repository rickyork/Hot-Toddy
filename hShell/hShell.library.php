<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Shell
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

class hShellLibrary extends hPlugin {

    public function shellArgumentExists($short, $long = nil)
    {
        if (empty($long))
        {
            $long = '-'.$short;
        }

        # @return boolean

        # @description
        # <h2>Detecting the Existence of a Shell Argument</h2>
        # <p>
        # <var>shellArgumentExists()</var> accepts both a long and short version of a shell argument.<br />
        # For example, <var>-b</var> and <var>--brains</var>, and returns a boolean response as to
        # whether or not that shell argument was passed in the command.
        # </p>
        # @end

        if (isset($GLOBALS['argv']))
        {
            $args = func_get_args();

            foreach ($args as $arg)
            {
                if (in_array($arg, $GLOBALS['argv']))
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function getShellArgumentValue($short, $long = nil, $default = nil, $required = false)
    {
        # @return string

        # @description
        # <h2>Getting the Value of a Shell Argument</h2>
        # <p>
        # <var>getShellArgumentValues()</var> accepts both a long and short version of a shell argument.<br />
        # For example, <var>-b</var> and <var>--brains</var>, and returns the value passed for that
        # shell argument.
        # </p>
        # @end

        if (is_array($short))
        {
            $required = $default;
            $default = $long;
            unset($long);
        }

        if (!is_array($short))
        {
            $short = array($short);
        }

        if (isset($GLOBALS['argv']))
        {
            foreach ($short as $arg)
            {
                $key = array_search($arg, $GLOBALS['argv']);

                if ($key !== false)
                {
                    break;
                }
            }

            if ($key === false)
            {
                if (isset($long))
                {
                    if (false == ($key = array_search($long, $GLOBALS['argv'])))
                    {
                        if (!$required)
                        {
                            return $default;
                        }
                        else
                        {
                            $this->fatal("Required shell argument '{$short}' or '{$long}' was not specified.", __FILE__, __LINE__);
                        }
                    }
                }
                else if (!$required)
                {
                    return $default;
                }
                else
                {
                    $this->fatal("Required shell argument, which can be any one of the following '".implode(', ', $short)."' was not specified.", __FILE__, __LINE__);
                }
            }

            return isset($GLOBALS['argv'][$key+1])? $GLOBALS['argv'][$key + 1] : nil;
        }

        return nil;
    }
}

?>