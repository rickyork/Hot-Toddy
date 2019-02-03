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
    echo `mkdir "{$installPath}/Configuration"`;
}
else
{
    echo "Directory {$installPath}/Configuration already exists\n";
}

echo `chmod -R 775 "{$installPath}/Configuration"`;
echo `chown -R {$user}:{$group} "{$installPath}/Configuration"`;

if (!file_exists($installPath.'/Pictures'))
{
    echo "Making directory {$installPath}/Pictures\n";
    echo `mkdir "{$installPath}/Pictures"`;
}
else
{
    echo "Directory {$installPath}/Pictures already exists\n";
}

echo `chmod -R 777 "{$installPath}/Pictures"`;
echo `chown -R {$user}:{$group} "{$installPath}/Pictures"`;

if (!file_exists($installPath.'/HtFS'))
{
    echo "Making directory {$installPath}/HtFS\n";
    echo `mkdir "{$installPath}/HtFS"`;
}
else
{
    echo "Directory {$installPath}/HtFS already exists\n";
}

echo `chmod -R 775 "{$installPath}/HtFS"`;
echo `chown -R {$user}:{$group} "{$installPath}/HTFS"`;

if (!file_exists($installPath.'/Compiled'))
{
    echo "Making directory {$installPath}/Compiled\n";
    echo `mkdir "{$installPath}/Compiled"`;
}
else
{
    echo "Directory {$installPath}/Compiled already exists\n";
}

echo `chmod -R 775 "{$installPath}/Compiled"`;
echo `chown -R {$user}:{$group} "{$installPath}/Compiled"`;

if (!file_exists($installPath.'/www'))
{
    echo "Making directory {$installPath}/www\n";
    echo `mkdir "{$installPath}/www"`;
}
else
{
    echo "Directory {$installPath}/www already exists\n";
}

echo `chmod -R 775 "{$installPath}/www"`;
echo `chown -R {$user}:{$group} "{$installPath}/www"`;

echo `cp "{$installPath}/Hot Toddy/hFramework/hFramework.default.php" "{$installPath}/www/index.php"`;

echo `cp "{$installPath}/Hot Toddy/hFramework/hFramework.shell.php" "{$installPath}/hot"`;
echo `chown {$user}:{$group} "{$installPath}/hot"`;
echo `chmod 775 '{$installPath}/hot'`;

if (!file_exists($installPath.'/images/Icons'))
{
    echo "Making directory {$installPath}/Icons\n";
    echo `mkdir "{$installPath}/Icons"`;
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
    '128x128',
    '512x512',
    'Source',
    'Applications'
);

foreach ($dimensions as $dimension)
{
    if (!file_exists($installPath.'/Icons/'.$dimension))
    {
        echo `mkdir "{$installPath}/Icons/{$dimension}"`;
    }

    echo `chmod -R 775 "{$installPath}/Icons/{$dimension}"`;
    echo `chown -R {$user}:{$group} "{$installPath}/Icons/{$dimension}"`;

    if (!file_exists($installPath.'/Icons/'.$dimension.'/flags'))
    {
        echo `mkdir "{$installPath}/Icons/{$dimension}/flags"`;
    }
}

/*
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
*/

echo `chmod -R 775 "{$installPath}/Icons"`;
echo `chown -R {$user}:{$group} "{$installPath}/Icons"`;

if (!file_exists($installPath.'/Log'))
{
    echo "Making directory {$installPath}/Log\n";
    echo `mkdir "{$installPath}/Log"`;
}
else
{
    echo "Directory {$installPath}/Log already exists\n";
}

echo `chmod -R 775 "{$installPath}/Log"`;
echo `chown -R {$user}:{$group} "{$installPath}/Log"`;

if (!file_exists($installPath.'/Log/PHP.log'))
{
    echo `touch "{$installPath}/Log/PHP.log"`;
    echo `chmod 775 "{$installPath}/Log/PHP.log"`;
    echo `chown {$user}:{$group} "{$installPath}/Log/PHP.log"`;
}

if (!file_exists($installPath.'/Log/Apache Errors.log'))
{
    echo `touch "{$installPath}/Log/httpd.log"`;
    echo `chmod 775 "{$installPath}/Log/httpd.log"`;
    echo `chown {$user}:{$group} "{$installPath}/Log/Apache Errors.log"`;
}

if (!file_exists($installPath.'/Temporary'))
{
    echo "Making directory {$installPath}/Temporary";
    echo `mkdir "{$installPath}/Temporary"`;
}
else
{
    echo "Directory {$installPath}/Temporary already exists\n";
}

echo `chmod -R 775 "{$installPath}/Temporary"`;
echo `chown -R {$user}:{$group} "{$installPath}/Temporary"`;

if (!file_exists($installPath.'/Plugins'))
{
    echo "Making directory {$installPath}/Plugins\n";
    echo `mkdir "{$installPath}/Plugins"`;
}
else
{
    echo "Directory {$installPath}/Plugins already exists\n";
}

echo `chmod -R 775 "{$installPath}/Plugins"`;
echo `chown -R {$user}:{$group} "{$installPath}/Plugins"`;

if (!file_exists($installPath.'/Plugins/HotToddy'))
{
    echo "Making directory {$installPath}/Plugins/HotToddy\n";
    echo `mkdir "{$installPath}/Plugins/HotToddy"`;
}
else
{
    echo "Directory {$installPath}/Plugins/HotToddy already exists\n";
}

if (!file_exists($installPath.'/Library'))
{
    echo "Checking out 3rd-Party Libraries from git to {$installPath}/Library\n\n";
    echo "git {$gitOperation} {$gitLibraryHost} '{$installPath}/Library'\n";
    echo `git {$gitOperation} {$gitLibraryHost} '{$installPath}/Library'`;
}
else
{
    echo "Path {$installPath}/Library already exists, delete to reinstall this folder\n";
}

echo `chmod -R 775 "{$installPath}/Library"`;
echo `chown -R {$user}:{$group} "{$installPath}/Library"`;

$createDB = $installPath.'/Hot Toddy/hDatabase/hDatabaseInstall/hDatabaseInstall.php';

if (file_exists($createDB))
{
    include $createDB;
}
else
{
    echo "Fatal Error: The setup script was unable to locate the database creation script, {$createDB}, generation of the database has failed\n";
    exit;
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
        echo "Fatal Error: The setup script was unable to location the configuration script, {$conf}, generation of the configuration files has failed\n";
        exit;
    }
}
else
{
    // Something went wrong with the database installation.
    echo "Fatal Error: Database tables do not exist. Something went wrong with the database installation.\n";
    exit;
}

$hot = $installPath.'/hot';

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
    
    echo "{$pathToPHP} {$hot} plugin hDocumentation/hDocumentationParser tokenize all --hFrameworkInstall\n";
    echo `{$pathToPHP} {$hot} plugin hDocumentation/hDocumentationParser tokenize all --hFrameworkInstall`;

    if (PHP_OS == 'Darwin')
    {
        echo "{$pathToPHP} {$hot} plugin hFile/hFileIcon/hFileIconInstall icns\n";
        echo `{$pathToPHP} "{$hot}" plugin hFile/hFileIcon/hFileIconInstall icns`;
    }

    echo
        "\n\n\n\n".
        "Installation complete.\n".
        "The default user is:\n".
        "   Username: administrator\n".
        "   Password: password\n\n";
}
else
{
    echo "Fatal Error: Unable to locate the Hot Toddy Command Line Interface, hShell: {$hShell}\n";
    exit;
}

?>