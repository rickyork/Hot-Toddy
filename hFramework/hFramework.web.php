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

if (strstr($_SERVER['REQUEST_URI'], '&amp;'))
{
    header('Location: //'.$_SERVER['HTTP_HOST'].str_replace('&amp;', '&', $_SERVER['REQUEST_URI']));
    exit;
}

include_once 'hFramework.setup.php';

$hFramework->plugin('hFramework/hFrameworkEvent');

if (isset($_SERVER['HTTP_REFERER']))
{
    hString::safelyDecodeURL($_SERVER['HTTP_REFERER']);
}

# Make the request is always coming from the same domain.
# example.com and www.example.com will be seen as two different domains
# as far as sesions go.
#
# This check forces all traffic to go to www.example.com, where the hServerHost
# variable is defined as www.example.com.  If hServerHost does not contain "www.",
# this check does not take place.
if (isset($_SERVER['HTTP_HOST']) && strstr($hFramework->hServerHost, 'www.') && $_SERVER['HTTP_HOST'] == str_replace('www.', '',  $hFramework->hServerHost))
{
    header('Location: //'.$hFramework->hServerHost.$_SERVER['REQUEST_URI']);
    exit;
}

@$uri = parse_url($_SERVER['REQUEST_URI']);

if (!is_array($uri))
{
    $uri = array();
}

foreach ($uri as $key => $value)
{
    # Make sure that literal quote characters are encoded.
    hString::safelyDecodeURL($uri[$key]);
}

if (isset($uri['path']))
{
    if ($uri['path'] !== '/' && $hFramework->beginsPath($uri['path'], '/'.$hFramework->hFrameworkSite))
    {
        header('Location: '.$hFramework->getEndOfPath($_SERVER['REQUEST_URI'], '/'.$hFramework->hFrameworkSite));
        exit;
    }

    if ($hFramework->beginsPath($uri['path'], '/Sites'))
    {
        header('Location: '.$hFramework->getEndOfPath($_SERVER['REQUEST_URI'], '/Sites'));
        exit;
    }

    if ($uri['path'] == '/index.php')
    {
        header('Location: /index.html');
        exit;
    }

    if ($uri['path'] == '/phpinfo')
    {
        $hFramework->fire->phpinfo();

        date_default_timezone_set('America/New_York');
        phpinfo();
        exit;
    }

    if ($uri['path'] == '/time')
    {
        echo ((time() - (-date('Z'))) * 1000);
        exit;
    }
}

# URL format: /1
# Forward slash followed by a numeric hFileId
if ($hFramework->hFileShortURLEnabled(true))
{
    if (isset($uri['path']) && substr_count($uri['path'], '/') == 1 && is_numeric(basename($uri['path'])))
    {
        $uri['path'] = $hFramework->getFilePathByFileId(
            basename($uri['path'])
        );
    }
}

# The following lets Hot Toddy, theoretically, live in a directory other
# than DocumentRoot...
if ($hFramework->hPath != '/')
{
    if (isset($uri['path']) && $hFramework->beginsPath($uri['path'], $hFramework->hPath))
    {
        $uri['path'] = $hFramework->getEndOfPath(
            $uri['path'],
            $hFramework->hPath
        );
    }
}

if (isset($uri['path']) && $uri['path'] != '/')
{
    # Trim trailing slashes
    while (substr($uri['path'], -1) == '/')
    {
        $uri['path'] = substr($uri['path'], 0, -1);
    }

    $uri['path'] = preg_replace_callback('/\/+/', 'hFrameworkFixSlashes', $uri['path']);
}

if (!empty($uri['path']))
{
    $extension = $hFramework->getExtension($uri['path']);

    if (!empty($extension))
    {
        $path = $hFramework->getIncludePath($hFramework->hServerDocumentRoot.$uri['path']);

        if (file_exists($path))
        {
            $hFramework->hFileWildcardPath = $uri['path'];
            $hFramework->hFrameworkFilePath = true;
            $hFramework->setPath('/System/Applications/Library.plugin');

            $hFramework->fire->libraryWildcardPath();
        }
    }
}

if ($hFramework->hFileAliasesEnabled(false))
{
    $hFramework->plugin('hFile/hFileAlias');
}

# Don't carry on if the path has been aliased...
if (!$hFramework->hFileAliasPath(null))
{
    $hFramework->plugin('hFile/hFilePathWildcard');
}

$hFramework->execute();

?>