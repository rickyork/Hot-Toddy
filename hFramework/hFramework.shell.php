#!/Server/bin/php -q
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

// #!/usr/bin/php -q
// hShell

if (isset($argv) && is_array($argv))
{
    if (in_array('--hFrameworkInstall', $argv))
    {
        define('hFrameworkInstall', true);
    }

    $path = dirname(__FILE__).'/hFramework.setup.php';

    if (!file_exists($path))
    {
        $path = dirname(__FILE__).'/Hot Toddy/hFramework/hFramework.setup.php';
    }

    include_once $path;

    $hFramework->hShellExecutable  = isset($_SERVER['_'])?       $_SERVER['_'] : '';
    $hFramework->hShellHomePath    = isset($_SERVER['HOME'])?    $_SERVER['HOME'] : '';
    $hFramework->hShellUser        = isset($_SERVER['LOGNAME'])? $_SERVER['LOGNAME'] : '';
    $hFramework->hShellCLI         = true;
    $hFramework->hShellApplication = isset($_SERVER['SHELL'])?   $_SERVER['SHELL'] : '';

    $hFramework->setPath('/');

    $hFramework->execute();
}
else
{
    echo "Error: No arguments passed.  Rolling over and playing dead now.\n";
}

?>