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

if (!isset($argv) || !is_array($argv))
{
    echo "This script must be called from the command line\n";
    exit;
}

if (!file_exists($installPath.'/Configuration'))
{
    echo "Making directory {$installPath}/Configuration\n";
    `mkdir "{$installPath}/Configuration"`;
}
else
{
    echo "Directory {$installPath}/Configuration already exists\n";
}

`chmod -R 775 "{$installPath}/Configuration"`;
`chown -R {$user}:{$group} "{$installPath}/Configuration"`;

if (!file_exists($installPath.'/Pictures'))
{
    echo "Making directory {$installPath}/Pictures\n";
    `mkdir "{$installPath}/Pictures"`;
}
else
{
    echo "Directory {$installPath}/Pictures already exists\n";
}

`chmod -R 777 "{$installPath}/Pictures"`;
`chown -R {$user}:{$group} "{$installPath}/Pictures"`;

if (!file_exists($installPath.'/HtFS'))
{
    echo "Making directory {$installPath}/HtFS\n";
    `mkdir "{$installPath}/HtFS"`;
}
else
{
    echo "Directory {$installPath}/HtFS already exists\n";
}

`chmod -R 775 "{$installPath}/HtFS"`;
`chown -R {$user}:{$group} "{$installPath}/HTFS"`;

if (!file_exists($installPath.'/Compiled'))
{
    echo "Making directory {$installPath}/Compiled\n";
    `mkdir "{$installPath}/Compiled"`;
}
else
{
    echo "Directory {$installPath}/Compiled already exists\n";
}

`chmod -R 775 "{$installPath}/Compiled"`;
`chown -R {$user}:{$group} "{$installPath}/Compiled"`;


if (!file_exists($installPath.'/www'))
{
    echo "Making directory {$installPath}/www\n";
    `mkdir "{$installPath}/www"`;
}
else
{
    echo "Directory {$installPath}/www already exists\n";
}

`chmod -R 775 "{$installPath}/www"`;
`chown -R {$user}:{$group} "{$installPath}/www"`;

`cp "{$installPath}/Hot Toddy/hFramework/hFramework.default.php" "{$installPath}/www/index.php"`;

`cp "{$installPath}/Hot Toddy/hFramework/hFramework.shell.php" "{$installPath}/hot"`;
`chown -R {$user}:{$group} "{$installPath}/hot"`;

if (!file_exists($installPath.'/images/Icons'))
{
    echo "Making directory {$installPath}/Icons\n";
    `mkdir "{$installPath}/Icons"`;
}
else
{
    echo "Directory {$installPath}/Icons already exists\n";
}

$dimensions = array(
    '16x16',
    '24x24',
    '32x32',
    '48x48',
    '96x96',
    'Source',
    'Applications'
);

foreach ($dimensions as $dimension)
{
    if (!file_exists($installPath.'/Icons/'.$dimension))
    {
        `mkdir "{$installPath}/Icons/{$dimension}"`;
    }

    `chmod -R 775 "{$installPath}/Icons/{$dimension}"`;
    `chown -R {$user}:{$group} "{$installPath}/Icons/{$dimension}"`;

    if (!file_exists($installPath.'/Icons/'.$dimension.'/flags'))
    {
        `mkdir "{$installPath}/Icons/{$dimension}/flags"`;
    }
}

if (!file_exists($installPath.'/images/Icons/128x128'))
{
    echo "Checking out 128x128 icons from SVN to {$installPath}/Icons/128x128\n\n";
    echo "svn {$svnOperation} {$svnFilesHost}/Icons/128x128 {$installPath}/Icons/128x128 {$svnUser}\n";
    echo `svn {$svnOperation} {$svnFilesHost}/Icons/128x128 "{$installPath}/Icons/128x128" {$svnUser}`;
}

if (!file_exists($installPath.'/images/Icons/512x512'))
{
    echo "Checking out 512x512 icons from SVN to {$installPath}/Icons/512x512\n\n";
    echo "svn {$svnOperation} {$svnFilesHost}/Icons/512x512 {$installPath}/Icons/512x512 {$svnUser}\n";
    echo `svn {$svnOperation} {$svnFilesHost}/Icons/512x512 "{$installPath}/Icons/512x512" {$svnUser}`;
}

`chmod -R 775 "{$installPath}/Icons"`;
`chown -R {$user}:{$group} "{$installPath}/Icons"`;

if (!file_exists($installPath.'/Log'))
{
    echo "Making directory {$installPath}/Log\n";
    `mkdir "{$installPath}/Log"`;
}
else
{
    echo "Directory {$installPath}/Log already exists\n";
}

`chmod -R 775 "{$installPath}/Log"`;
`chown -R {$user}:{$group} "{$installPath}/Log"`;

if (!file_exists($installPath.'/Log/PHP.log'))
{
    `touch "{$installPath}/Log/PHP.log"`;
    `chmod 775 "{$installPath}/Log/PHP.log"`;
    `chown {$user}:{$group} "{$installPath}/Log/PHP.log"`;
}

if (!file_exists($installPath.'/Log/httpd.log'))
{
    `touch "{$installPath}/Log/httpd.log"`;
    `chmod 775 "{$installPath}/Log/httpd.log"`;
    `chown {$user}:{$group} "{$installPath}/Log/httpd.log"`;
}

if (!file_exists($installPath.'/Temporary'))
{
    echo "Making directory {$installPath}/Temporary";
    `mkdir "{$installPath}/Temporary"`;
}
else
{
    echo "Directory {$installPath}/Temporary already exists\n";
}

`chmod -R 775 "{$installPath}/Temporary"`;
`chown -R {$user}:{$group} "{$installPath}/Temporary"`;

if (!file_exists($installPath.'/Plugins'))
{
    echo "Making directory {$installPath}/Plugins\n";
    `mkdir "{$installPath}/Plugins"`;
}
else
{
    echo "Directory {$installPath}/Plugins already exists\n";
}

`chmod -R 775 "{$installPath}/Plugins"`;
`chown -R {$user}:{$group} "{$installPath}/Plugins"`;

if (!file_exists($installPath.'/Library'))
{
    echo "Checking out 3rd-Party Libraries from SVN to {$installPath}/Library\n\n";
    echo "svn {$svnOperation} {$svnFilesHost}/Library/ {$installPath}/Library {$svnUser}\n";
    echo `svn {$svnOperation} {$svnFilesHost}/Library/ "{$installPath}/Library" {$svnUser}`;
}
else
{
    echo "Path {$installPath}/Library already exists, delete to reinstall this folder\n";
}

`chmod -R 775 "{$installPath}/Library"`;
`chown -R {$user}:{$group} "{$installPath}/Library"`;

$createDB = $installPath.'/Hot Toddy/hDatabase/hDatabaseInstall/hDatabaseInstall.php';

if (file_exists($createDB))
{
    include $createDB;
}
else
{
    echo "Error: The setup script was unable to locate the database creation script, {$createDB}, generation of the database has failed\n";
}

if (isset($tables) && is_array($tables) && count($tables))
{
    $conf = $installPath.'/Hot Toddy/hFramework/hFrameworkConfiguration/hFrameworkConfiguration.php';

    // A database has been successfully created,
    // now let's generate a conf file, and initialize the framework
    // installation, and pass off the rest of the installation tasks
    // to the framework itself.
    if (file_exists($conf))
    {
        include $conf;
    }
    else
    {
        echo "Error: The setup script was unable to location the configuration script, {$conf}, generation of the configuration files has failed\n";
    }
}
else
{
    // Something went wrong with the database installation.
    exit;
}

$hot = $installPath.'/Hot Toddy/hFramework/hFramework.shell.php';

if (file_exists($hot))
{
    if (!isset($pathToPHP))
    {
        $pathToPHP = isset($_SERVER['_'])? $_SERVER['_'] : '/usr/bin/php';
    }

    // Now let the framework be brought to life to finish its own installation.
    echo "{$pathToPHP} {$hot} plugin hFile/hFileInstall --hFrameworkInstall\n";
    echo `{$pathToPHP} "{$hot}" plugin hFile/hFileInstall --hFrameworkInstall`;

    echo "{$pathToPHP} {$hot} plugin hTemplate/hTemplateInstall --hFrameworkInstall\n";
    echo `{$pathToPHP} "{$hot}" plugin hTemplate/hTemplateInstall --hFrameworkInstall`;

    echo "{$pathToPHP} {$hot} plugin hUser/hUserInstall --hFrameworkInstall\n";
    echo `{$pathToPHP} "{$hot}" plugin hUser/hUserInstall --hFrameworkInstall`;

    echo "{$pathToPHP} {$hot} database versions\n";
    echo `{$pathToPHP} "{$hot}" database versions`;

    if (PHP_OS == 'Darwin')
    {
        echo "{$pathToPHP} {$hot} plugin hFile/hFileIcon/hFileIconInstall icns\n";
        echo `{$pathToPHP} "{$hot}" plugin hFile/hFileIcon/hFileIconInstall icns`;
    }
}
else
{
    echo "Unable to locate the Hot Toddy Command Line Interface, hShell: {$hShell}\n";
}

?>