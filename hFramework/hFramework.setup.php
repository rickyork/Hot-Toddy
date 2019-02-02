<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Setup
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

define('nil', null);

function hFrameworkFixSlashes($matches)
{
	return '/';
}

function hFrameworkInclude($path)
{
    // $cachedFilePath = dirname($path).'/.'.basename($path);
    //
    // $condition = (
    //     isset($GLOBALS['hFramework']) &&
    //     is_object($GLOBALS['hFramework']) &&
    //     !$GLOBALS['hFramework']->hFrameworkPHPCacheDisabled(false)
    // ) || (
    //     !isset($GLOBALS['hFramework'])
    // );
    //
    // if ($condition && file_exists($cachedFilePath))
    // {
    //     include_once $cachedFilePath;
    //     return;
    // }

    include_once $path;
}

function hFrameworkBenchmarkMicrotime()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float) $usec + (float) $sec);
}

define('hFrameworkBenchmarkStart', hFrameworkBenchmarkMicrotime());

$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__FILE__));

hFrameworkInclude(dirname(__FILE__).'/hFramework.php');
hFrameworkInclude($_SERVER['DOCUMENT_ROOT'].'/hString/hString.php');

# HTML Encode all externally supplied data POST, GET, COOKIE, and SERVER
# Replaces HTML special characters and single quotes with entity equivilents as a
# first line of defense against SQL injection and XSS vulnerabilities.
#
# The ENV array is not encoded.
#
# Also encodes multibyte UTF-8 characters as HTML entities
hString::scrubRequestData();

# In a Hot Toddy mashup, the following function lets you reinitalize the
# framework to a pristine state.  e.g., for simulating calling Hot Toddy's CLI.

function hFrameworkReset()
{
    # Reset the framework to a pristine state.
    $GLOBALS['hPlugins'] = array();
    $GLOBALS['hFramework'] = new hFramework();
    $GLOBALS['hPluginConfiguration'] = array();
    $GLOBALS['hPluginCache'] = array();
    $GLOBALS['hPluginData'] = array();
    $GLOBALS['hDatabaseQueries'] = array();
    $GLOBALS['hDatabaseOptimizeBenchmark'] = array();

    $frameworkPath = dirname($_SERVER['DOCUMENT_ROOT']);

    # private $pluginConf = array();
    # private $pluginCache = array();
    # private $plugins = array();
    if (defined('hFrameworkInstall'))
    {
        $GLOBALS['hFramework']->hPluginInstallFiles = false;
    }

    # Sibling to document root.
    $GLOBALS['hConfLocation'] = $frameworkPath.'/Configuration/hFramework.conf';
    $GLOBALS['hConf'] = array();

    $GLOBALS['hConf'] = parse_ini_file($GLOBALS['hConfLocation']);

    # See if the hostname is an IP address...
    # If so, reset the Hot Toddy hostname to the IP to preserve session cookies
    if (isset($_SERVER['HTTP_HOST']))
    {
        if (substr_count($_SERVER['HTTP_HOST'], '.') == 3)
        {
            # IPv4
            $bits = explode('.', $_SERVER['HTTP_HOST']);

            $isIP = true;

            foreach ($bits as $bit)
            {
                if (!is_numeric($bit) || $bit < 0 || $bit > 255)
                {
                    $isIP = false;
                }
            }

            if ($isIP)
            {
                $GLOBALS['hFramework']->hServerHost = $_SERVER['HTTP_HOST'];
                $GLOBALS['hFramework']->hServerHostIsIP = true;
            }
        }
        else if (strstr($_SERVER['HTTP_HOST'], ':') && substr_count($_SERVER['HTTP_HOST'], ':') > 1)
        {
            # IPv6
            $GLOBALS['hFramework']->hServerHost = $_SERVER['HTTP_HOST'];
            $GLOBALS['hFramework']->hServerHostIsIP = true;
        }
    }

    $GLOBALS['hFramework']->setVariables(
        array_merge(
            array(
                'hPluginPath'               => $_SERVER['DOCUMENT_ROOT'],
                'hOS'                       => PHP_OS,
                # The main host (for SSL, and anywhere a hard-coded host name is needed)
                'hServerRequestURI'         => isset($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI'] : '',
                'hServerUserAgent'          => isset($_SERVER['HTTP_USER_AGENT'])? $_SERVER['HTTP_USER_AGENT'] : '',
                'hServerRequestQueryString' => isset($_SERVER['QUERY_STRING'])? $_SERVER['QUERY_STRING'] : '',
                'hServerDocumentRoot'       => $_SERVER['DOCUMENT_ROOT'],
                'hFrameworkPath'            => $frameworkPath,
                'hDatabaseAssoc'            => MYSQL_ASSOC,
                'hDatabaseNum'              => MYSQL_NUM,
                'hDatabaseBoth'             => MYSQL_BOTH,
                'hDatabaseLinkName'         => 'dblink'
            ), $GLOBALS['hConf']
        ),
        false
    );

    if ($GLOBALS['hFramework']->hFilePathToPEAR)
    {
        set_include_path($GLOBALS['hFramework']->hFilePathToPEAR);
    }
    else
    {
        set_include_path(dirname($_SERVER['DOCUMENT_ROOT']).'/Library/PEAR');
    }

    define('hFrameworkPathToPHP', $GLOBALS['hFramework']->hFrameworkPathToPHP('/usr/bin/php'));

    # Copy to global variables for stand-alone reuse
    $GLOBALS['hDatabaseHost']     = $GLOBALS['hFramework']->hDatabaseHost;
    $GLOBALS['hDatabaseUser']     = $GLOBALS['hFramework']->hDatabaseUser;
    $GLOBALS['hDatabasePassword'] = $GLOBALS['hFramework']->hDatabasePassword;
    $GLOBALS['hDatabaseInitial']  = $GLOBALS['hFramework']->hDatabaseInitial;

    date_default_timezone_set($GLOBALS['hFramework']->hCalendarTimezone('America/New_York'));

    $driver = 'hDatabaseDriver_'.strtoupper($GLOBALS['hFramework']->hDatabaseDriver('MYSQLI'));
    $driverLowerCase = strtolower($GLOBALS['hFramework']->hDatabaseDriver('MYSQLI'));

    if (!class_exists($driver))
    {
        hFrameworkInclude($_SERVER['DOCUMENT_ROOT'].'/hDatabase/hDatabaseDriver/hDatabaseDriver.'.$driverLowerCase.'.php');
    }

    if (!class_exists('hDatabase'))
    {
        hFrameworkInclude($_SERVER['DOCUMENT_ROOT'].'/hDatabase/hDatabase.php');
    }

    if (!class_exists('hDatabaseTable'))
    {
        hFrameworkInclude($_SERVER['DOCUMENT_ROOT'].'/hDatabase/hDatabaseTable/hDatabaseTable.php');
    }

    $GLOBALS['hDB'] = new $driver($GLOBALS['hFramework']);

    $GLOBALS['hFramework']->hDB = &$GLOBALS['hDB'];

    $GLOBALS['hDatabase'] = new hDatabase($GLOBALS['hFramework']);

    $GLOBALS['hFramework']->hDatabase = &$GLOBALS['hDatabase'];

    $GLOBALS['hFramework']->defineTableObjects();

    $GLOBALS['hFramework']->plugin('hFile/hFileDomain');

    if (!isset($GLOBALS['hUserSessionLoaded']))
    {
        $GLOBALS['hFramework']->plugin('hUser/hUserSession');
    }

    if ($GLOBALS['hFramework']->hUserAgentEnabled(true))
    {
        $GLOBALS['hFramework']->plugin('hUser/hUserAgent');
    }
}

# CMS Group!
define('RFC822', 'D, d M Y H:i:s e');

hFrameworkReset();

define('hFrameworkErrorReportingDefault', E_STRICT);

if (ini_get('register_globals'))
{
    $hFramework->warning('The PHP configuration directive "register_globals" must be turned off.', __FILE__, __LINE__);
}

?>