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

$configurationFilePath = $installPath.'/Configuration/'.$hostname.'.'.$port.'.conf';

if (!$macOSXServer)
{
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
    
    if (!file_put_contents($configurationFilePath, $virtualHost))
    {
        echo "Fatal Error: Creation of the virtual host configuration file at, {$configurationFilePath}, failed.\n";
        exit;
    }
    else
    {
        echo "Virtual Host configuration successfully created.\n";
    }
}
else
{
    $apacheConfPath = '';

    $macOSXApacheConfPath = '/Library/Server/Web/Config/apache2/sites/';
    
    if ($ip == '*')
    {    
        $apacheConfFile = '0000_any_80_'.$hostname.'.conf';
        
        if (file_exists($macOSXApacheConfPath.$apacheConfFile))
        {
            $apacheConfPath = $macOSXApacheConfPath.$apacheConfFile;
        }
    }

    if (!$apacheConfPath && $ip == '*')
    {
        $apacheConfFile = '0000_any_80_.conf';

        if (file_exists($macOSXApacheConfPath.$apacheConfFile))
        {
            $apacheConfPath = $macOSXApacheConfPath.$apacheConfFile;
        }
    }

    if (!$apacheConfPath && ($ip =='*' || $ip == '127.0.0.1'))
    {   
        $apacheConfFile = '0000_127.0.0.1_'.($port == 80? '345' : '').$port.'_'.$hostname.'.conf';

        if (file_exists($macOSXApacheConfPath.$apacheConfFile))
        {
            $apacheConfPath = $macOSXApacheConfPath.$apacheConfFile;
        }
    }
    
    if (!$apacheConfPath)
    {
        $apacheConfFile = '0000_'.$ip.'_'.($port == 80? '345' : '').$port.'_'.$hostname.'.conf';
        
        if (file_exists($macOSXApacheConfPath.$apacheConfFile))
        {
            $apacheConfPath = $macOSXApacheConfPath.$apacheConfFile;
        }
    }
    
    if (!$apacheConfPath)
    {
        // Try a default
        $apacheConfFile = '0000_'.$ip.'_'.($port == 80? '345' : '').$port.'_.conf';
        
        if (file_exists($macOSXApacheConfPath.$apacheConfFile))
        {
            $apacheConfPath = $macOSXApacheConfPath.$apacheConfFile;
        }
    }
    
    if (!$apacheConfPath)
    {
        echo "Fatal Error: Mac OS X Server.app could not be configured automatically.\n";
        exit;
    }
    else
    {
        $apacheConfiguration = file_get_contents(dirname(__FILE__).'/Mac OS X Server Apache.conf');
        
        if (!file_put_contents($configurationFilePath, $apacheConfiguration))
        {
            echo "Fatal Error: Creation of the virtual host configuration file at, {$installPath}/Configuration/{$host}.{$port}.conf, failed.\n";
            exit;
        }
        else
        {
            $apacheConf = file_get_contents($apacheConfPath);
            
            # Include "{$configurationFilePath}"
           
            if (!stristr($apacheConf, "Include \"{$configurationFilePath}\""))
            {
                $apacheConf = str_replace(
                    '</VirtualHost>',
 
                    "        Include \"{$configurationFilePath}\"\n".
                    ($aliases? "        ServerAlias ".$aliases."\n" : '').
                    "</VirtualHost>", 
                    
                    $apacheConf
                );
                
                if (file_put_contents($apacheConfPath, $apacheConf))
                {
                    echo "Mac OS X Server.app Apache configuration successfully modified.\n";
                }
                else
                {
                    echo "Fatal Error: Unable to modify Mac OS X Server.app Apache configuration located at {$apacheConfPath}.\n";
                    exit;
                }
            }

            echo "Mac OS X Server.app Virtual Host configuration successfully modified.\n";
        }
    }
}

echo `chown -R {$user} {$installPath}/Configuration`;
echo `chgrp -R {$group} {$installPath}/Configuration`;
echo `chmod -R 775 {$installPath}/Configuration`;

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