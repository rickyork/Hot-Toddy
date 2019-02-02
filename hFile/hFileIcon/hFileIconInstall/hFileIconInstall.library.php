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

class hFileIconInstallLibrary extends hPlugin {

    private $applicationIcons;
    private $icns = array();

    public function hConstructor()
    {
        $this->applicationIcons = $this->hFrameworkIconPath.'/Applications';

        if (!file_exists($this->applicationIcons))
        {
            `mkdir "{$this->applicationIcons}"`;
            `chmod 775 "{$this->applicationIcons}"`;
        }
    }

    public function copyIcns()
    {
        $this->icns = array();

        $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');

        $json = $this->hJSON->getJSON(dirname(__FILE__).'/JSON/icns.json');

        $icons = $json->icns;

        foreach ($icons as $icon)
        {
            if (isset($icon->path) && !empty($icon->path))
            {
                if (file_exists($icon->path))
                {
                    $name = basename($icon->path);

                    $duplicate = '';

                    if (isset($icon->duplicate))
                    {
                        $duplicate = $icon->duplicate;
                    }

                    $name = str_replace('.icns', $duplicate.'.icns', $name);

                    $destination = $this->hFrameworkIconPath.'/Source/'.$name;

                    $this->copy($icon->path, $destination);

                    if (isset($this->icns[$icon->path]))
                    {
                        $this->console("Duplicate Icon: {$icon->path}");
                    }
                    else
                    {
                        $this->icns[$icon->path] = true;
                    }

                    $this->console("Copied {$icon->path} to {$destination}");

                    if (isset($this->icns[$name]))
                    {
                        $this->console("Duplicate Icon: {$icon->path}");
                    }
                    else
                    {
                        $this->icns[$name] = true;
                    }
                }
                else
                {
                    $this->console("Icon {$icon->path} does not exist");
                }
            }
            else
            {
                $this->console("Icon path is empty");
            }
        }
    }

    public function copyApplicationIcon($path)
    {
        if (file_exists($path))
        {
            $name = basename($path);
            `cp "{$path}" "{$this->applicationIcons}/{$name}"`;
        }
    }
}

?>