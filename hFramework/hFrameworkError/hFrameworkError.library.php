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
# <h1>Error API</h1>
# <p>
#   Custom error handling for most built-in PHP errors, and framework errors.
# </p>
# <p>
#   Attempt to log and mail errors, as appropriate.
# </p>
# @end

class hFrameworkErrorLibrary extends hPlugin {

    public function &setToPHP4()
    {
        $level = $this->hFrameworkErrorReportingPHP4("E_ALL");
        error_reporting(is_numeric($level)? $level : constant($level));
        return $this;
    }

    public function &setToDefault()
    {
        error_reporting($this->hFrameworkErrorReportingDefault);
        return $this;
    }

    public function &log($message)
    {
        error_log($message."\n", 3, $this->hFrameworkLogPath.'/Hot Toddy.log');
        return $this;
    }

    public function &errorMessage($type, $message = nil, $file = nil, $line = nil, $override = false)
    {       
        $reportingErrorLevel = 0;

        switch ($type)
        {
            case 'verbose': # Level 0
            {
                $reportingErrorLevel = 0;
                break;
            }
            case 'console': # Level 1
            {
                $reportingErrorLevel = 1;
                break;
            }
            case 'notice':  # Level 2
            {
                $reportingErrorLevel = 2;
                break;
            }
            case 'error':   # Alias for warning
            case 'warning': # Level 3
            {
                $reportingErrorLevel = 3;
                break;
            }
            case 'fatal':   # Level 4
            {
                $reportingErrorLevel = 4;
                break;
            }
        }

        if ($reportingErrorLevel >= 0)
        {
            # Console text is always output if this is the shell.
            if ($this->hShellCLI(false) && $reportingErrorLevel >= 1)
            {
                echo $message;

                if ($file !== false)
                {
                    echo "\n";
                }

                return $this;
            }

            # Set error level
            $errorLevel = $this->hFrameworkErrorReportingLevel('warning');

            if (!is_numeric($errorLevel))
            {
                switch ($errorLevel)
                {
                    case 'fatal':
                    {
                        $errorLevel = 4;
                        break;
                    }
                    case 'warning':
                    {
                        $errorLevel = 3;
                        break;
                    }
                    case 'notice':
                    {
                        $errorLevel = 2;
                        break;
                    }
                    case 'console':
                    {
                        $errorLevel = 1;
                        break;
                    }
                    case 'verbose':
                    {
                        $errorLevel = 0;
                        break;
                    }
                    default:
                    {
                        $errorLevel = 3;
                    }
                }
            }
            else if ($errorLevel < 0 || $errorLevel > 4)
            {
                $errorLevel = 3;
            }

            if ($reportingErrorLevel >= $errorLevel || $override)
            {
                $value = ucwords($type).': '.$message;

                if ($this->hShellCLI(false))
                {
                    echo $message."\n";

                    if ($file && $line)
                    {
                        echo "Originated from ".$file.':'.$line."\n";
                    }

                    if ($reportingErrorLevel == 4)
                    {
                        exit;
                    }

                    return $this;
                }

                if ($this->hFrameworkErrorReporting(false))
                {
                    echo $message."\n";

                    if ($file && $line)
                    {
                        echo "Originated from ".$file.':'.$line."\n";
                    }

                    echo "\n";

                    if ($this->hFrameworkErrorBacktrace(false))
                    {
                        debug_print_backtrace();
                        exit;
                    }
                }

                if ($this->hFrameworkErrorLog(true))
                {
                    $backtrace = nil;

                    if ($this->hFrameworkErrorBacktrace(false))
                    {
                        ob_end_flush();
                        ob_start();

                        debug_print_backtrace();

                        $backtrace = ob_get_contents();

                        ob_end_clean();
                        ob_start();
                    }

                    $this->errorConsole($message, $backtrace, $file, $line);
                }

                if ($reportingErrorLevel == 4)
                {
                    exit;
                }
            }
        }

        return $this;
    }

    public function errorConsole($errorText, $errorBacktrace = nil, $file = nil, $line = nil)
    {
        if ($this->hFrameworkErrorLogFile(false))
        {
            $this->log(
                "Date: ".date('m/d/Y h:i:s A')."\n".
                "Username: ".(!empty($_SESSION['hUserName'])? $_SESSION['hUserName'] : '')."\n".
                "User Agent: ".(!empty($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT'] : '')."\n".
                "IP: ".(!empty($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR'] : '')."\n".
                "Request URI: ".(!empty($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI'] : '')."\n".
                "Referrer: ".(!empty($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : '')."\n".
                $errorText."\n"
            );
        }

        if ($this->hFrameworkErrorLogDatabase(true))
        {
            $this->hFrameworkErrors->insert(
                array(
                    'hUserId'             => !empty($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 0,
                    'hFrameworkError'     => hString::escapeAndEncode($errorText),
                    'hFilePath'           => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : nil,
                    'hUserAgentReferrer'  => !empty($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : '',
                    'hPluginPath'         => hString::escapeAndEncode($file),
                    'hPluginLine'         => (int) $line,
                    'hFrameworkBackTrace' => hString::escapeAndEncode($errorBacktrace),
                    'hFrameworkErrorDate' => time(),
                    'hUserAgent'          => !empty($_SERVER['HTTP_USER_AGENT'])? hString::escapeAndEncode($_SERVER['HTTP_USER_AGENT']) : '',
                    'hUserRemoteIP'       => !empty($_SERVER['REMOTE_ADDR'])?     hString::escapeAndEncode($_SERVER['REMOTE_ADDR'])     : ''
                )
            );

            $logExpiration = $this->hFrameworkErrorLogExpiration('-1 month');

            if ($logExpiration && (1 == rand(1, 100)))
            {
                $this->hFrameworkErrors->delete(
                    array(
                        'hFrameworkErrorDate' => array(
                            '<',
                            strtotime($logExpiration)
                        )
                    )
                );
            }
        }
    }
}

?>