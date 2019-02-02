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

$conf = $installPath.'/Hot Toddy/hFramework/hFrameworkConfiguration/hFramework.conf';

if (!isset($port))
{
    $port = 80;
}

$configurationVariables = array(
    '{$hServerHost}',
    '{$hDatabaseHost}',
    '{$hDatabaseUser}',
    '{$hDatabasePassword}',
    '{$hDatabaseInitial}',
    '{$hFileSystemPath}',
    '{$hDirectoryTemplatePictures}',
    '{$hDirectoryLibrary}',
    '{$hFilePathToPEAR}',
    '{$hFileIconPath}',
    '{$hFrameworkHost}'
);

$configurationValues = array(
    $hostname,
    $dbHost,
    $dbUser,
    $dbPass,
    $db,
    $installPath.'/HtFS',
    $installPath.'/Pictures',
    $installPath.'/Library',
    $installPath.'/Library/PEAR',
    $installPath.'/Icons',
    str_replace('www.', '', $hostname)
);

if (file_exists($conf))
{
    echo "Fetching the template for the hFramework.conf configuration file.\n";

    $conf = file_get_contents($conf);

    $conf = str_replace($configurationVariables, $configurationValues, $conf);

    if (!file_put_contents($installPath.'/Configuration/hFramework.conf', $conf))
    {
        echo "Fatal Error: Creation of the hFramework.conf file at {$installPath}/Configuration/hFramework.conf failed.\n";
        exit;
    }
    else
    {
        echo "hFramework.conf successfully created!\n";
    }
}

$json = $installPath.'/Hot Toddy/hFramework/hFrameworkConfiguration/hFramework.json';

if (file_exists($json))
{
    echo "Fetching the template for the hFramework.json configuration file.\n";

    $json = file_get_contents($json);

    $json = str_replace($configurationVariables, $configurationValues, $json);

    if (!file_put_contents($installPath.'/Configuration/'.$frameworkSite.'.json', $json))
    {
        echo "Fatal Error: Creation of the {$hostname}.json file at {$installPath}/Configuration/{$frameworkSite}.json failed.\n";
        exit;
    }
    else
    {
        echo "{$hostname}.json successfully created!\n";
    }
}

$virtualHost = str_replace(
    array(
        '{$ip}',
        '{$port}',
        '{$installPath}',
        '{$hostname}',
        '{$aliases}'
    ),
    array(
        $ip,
        $port,
        $installPath,
        $hostname,
        $aliases
    ),
    file_get_contents(
        dirname(__FILE__).'/Apache Virtual Host.conf'
    )
);

if (!file_put_contents($installPath.'/Configuration/'.$hostname.'.'.$port.'.conf', $virtualHost))
{
    echo "Fatal Error: Creation of the virtual host configuration file at, {$installPath}/Configuration/{$host}.{$port}.conf, failed.\n";
    exit;
}
else
{
    echo "Virtual Host configuration successfully created.\n";
}

`chown -R {$user} {$installPath}/Configuration`;
`chgrp -R {$group} {$installPath}/Configuration`;
`chmod -R 775 {$installPath}/Configuration`;

if ($appendConf)
{
    if (is_writable($httpdConfLocation))
    {
        $file = file_get_contents($httpdConfLocation);

        if (!file_exists(dirname($httpdConfLocation).'/httpd.backup.conf'))
        {
            # Make a backup of httpd.conf
            if (!file_put_contents(dirname($httpdConfLocation).'/httpd.backup.conf', $file))
            {
                echo "Fatal Error: Unable to make a backup of httpd.conf.\n";
                exit;
            }
            else
            {
                echo "Made a backup of httpd.conf\n";
            }
        }

        if ($nameVirtualHost)
        {
            if (!strstr($file, "NameVirtualHost {$ip}:{$port}") && !strstr($file, "NameVirtualHost *:{$post}") && !strstr($file, "NameVirtualHost {$ip}:*") && !strstr($file, "NameVirtualHost *:*"))
            {
                echo "NameVirtualHost directive added to httpd.conf\n";
                $file .= "\nNameVirtualHost {$ip}:{$port}\n";
            }
        }

        if (!strstr($file, "Include {$installPath}/Configuration/{$hostname}.{$port}.conf"))
        {
            $file .= "\nInclude {$installPath}/Configuration/{$hostname}.{$port}.conf\n";
        }

        if (!file_put_contents($httpdConfLocation, $file))
        {
            echo "Fatal Error: Was unable to append the Include/NameVirtualHost directive(s) to httpd.conf\n";
            exit;
        }
        else
        {
            echo "Include appended to httpd.conf.\n";
        }

        // Restart Apache
        echo `{$apacheRestart}`;
        echo "Apache has been restarted.\n";
    }
    else
    {
        echo "Error: Unable to modify the Apache configuration file located at {$httpdConfLocation}.\n";
    }
}

if ($setEtcHosts)
{
    echo "Appending IP and Hostname to /etc/hosts\n";

    $file = file_get_contents('/etc/hosts');

    if (!strstr($file, $ip.' '.$hostname))
    {
        $file .= $ip.' '.$hostname."\n";
        file_put_contents('/etc/hosts', $file);

        echo "Appended IP and hostname to /etc/hosts\n";
    }
}

?>