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

class hPluginUpdateLibrary extends hPlugin {

    private $hFileUtilities;

    public function hConstructor()
    {
        //$this->hFileUtilities = $this->library('hFile/hFileUtilities');


    }

    public function downloadFromRepository($data, $destination)
    {
        if (!isset($data['software']))
        {
            $data['software'] = $this->hPluginDefaultSourceRepositorySoftware('');
        }

        $this->console("Plugin repository software: {$data['software']}");

        if (!isset($data['user']))
        {
            $data['user'] = $this->hPluginDefaultSourceRepositoryUser('');
        }

        $this->console("Plugin repository user: {$data['user']}");

        if (!isset($data['password']))
        {
            $data['password'] = $this->hPluginDefaultSourceRepositoryPassword('');
        }

        $this->console("Plugin repository password: {$data['password']}");

        if (!isset($data['baseURI']))
        {
            $this->repository['baseURI'] = $this->hPluginDefaultSourceRepositoryBaseURI('');
        }

        $this->console("Plugin repository base URI: {$data['baseURI']}");

        if (!isset($data['path']))
        {
            $data['path'] = $this->hPluginDefaultSourceRepositoryPath('');
        }

        $this->console("Plugin repository path: {$data['path']}");

        if (!isset($data['checkout']))
        {
            $data['checkout'] = $this->hPluginDefaultSourceRepositoryCheckout(false);
        }

        $this->console("Plugin repository checkout: {$data['checkout']}");

        if (!isset($data['readonly']))
        {
            $data['readonly'] = $this->hPluginDefaultSourceRepositoryReadonly(true);
        }

        $this->console("Plugin repository readonly: {$data['readonly']}");

        if (!isset($data['revision']))
        {
            $data['revision'] = $this->hPluginDefaultSourceRepositoryRevision(nil);
        }

        $this->console("Plugin repository revision: {$data['revision']}");

        if ($data['checkout'])
        {
            $data['readonly'] = false;
        }

        if (!$data['readonly'])
        {
            $data['checkout'] = true;
        }

        switch ($data['software'])
        {
            case 'cvs':
            {

                break;
            }
            case 'svn':
            {
                if ($data['checkout'])
                {
                    $command = 'checkout';
                }
                else if ($data['readonly'])
                {
                    $command = 'export';
                }

                if ($data['revision'])
                {
                    $command .= ' --revision '.escapeShellArg($data['revision']);
                }

                if ($data['user'])
                {
                    $command .= ' --username '.escapeShellArg($data['user']);
                }

                if ($data['password'])
                {
                    $command .= ' --password '.escapeShellArg($data['password']);
                }

                if ($data['baseURI'])
                {
                    $command .= ' '.escapeShellArg($data['baseURI'].$data['path']);
                }

                $command .= ' '.escapeShellArg($destination);

                $this->console("Plugin repository command: {$command}");

                $this->pipeCommand(
                    '/usr/bin/svn',
                    trim($command)
                );

                break;
            }
            case 'git':
            default:
            {

            }
        }

        $this->updateDatabase();

        return $data;
    }

    public function updateDatabase()
    {
        //echo $this->hot('database install');
        //echo $this->hot('database versions');
        //echo $this->hot('database update');
    }

    public function setRepository($plugin, array $data)
    {
        $this->console("Setting plugin repository data for plugin: {$plugin}");

        if (count($data))
        {
            $table = $this->getPluginDatabaseTable($plugin);

            $this->console("Plugin database table: {$table}");

            $pluginType = nil;

            switch ($table)
            {
                case 'hPlugins':
                {
                    $pluginType = 'Public';
                    break;
                }
                case 'hPluginPrivate':
                {
                    $pluginType = 'Private';
                    break;
                }
                case 'hPluginApplication':
                {
                    $pluginType = 'Application';
                    break;
                }
            }

            $this->console("Plugin type: {$pluginType}");

            $recordExists = $this->hPluginRepository->selectExists(
                array(
                    'hPlugin'
                ),
                array(
                    'hPlugin' => $plugin,
                    'hPluginType' => $pluginType
                )
            );

            if ($recordExists)
            {
                $this->hPluginRepository->update(
                    array(
                        'hPluginRepositoryUser'     => $data['user'],
                        'hPluginRepositoryPassword' => $data['password'],
                        'hPluginRepositoryCheckout' => (int) $data['checkout'],
                        'hPluginRepositoryReadonly' => (int) $data['readonly'],
                        'hPluginRepositoryRevision' => $data['revision'],
                        'hPluginRepositorySoftware' => $data['software'],
                        'hPluginRepositoryBaseURI'  => $data['baseURI'],
                        'hPluginRepositoryPath'     => $data['path']
                    ),
                    array(
                        'hPlugin'     => $plugin,
                        'hPluginType' => $pluginType
                    )
                );
            }
            else
            {
                $this->hPluginRepository->insert(
                    array(
                        'hPlugin'                   => $plugin,
                        'hPluginType'               => $pluginType,
                        'hPluginRepositoryUser'     => $data['user'],
                        'hPluginRepositoryPassword' => $data['password'],
                        'hPluginRepositoryCheckout' => (int) $data['checkout'],
                        'hPluginRepositoryReadonly' => (int) $data['readonly'],
                        'hPluginRepositoryRevision' => $data['revision'],
                        'hPluginRepositorySoftware' => $data['software'],
                        'hPluginRepositoryBaseURI'  => $data['baseURI'],
                        'hPluginRepositoryPath'     => $data['path']
                    )
                );
            }
        }
    }

    public function updateFromRepository()
    {
        $this->console("Updating plugins from repositories");

        $query = $this->hPluginRepository->select();

        foreach ($query as $data)
        {
            $this->console("Plugin: ".$data['hPlugin']);
            $this->console("Plugin Type: ".$data['hPluginType']);

            $path = $this->hFrameworkPath;

            switch ($data['hPluginType'])
            {
                case 'Public':
                {
                    $path .= $this->hFrameworkRoot;
                    break;
                }
                case 'Private':
                {
                    $path .= $this->hFrameworkPluginPath;
                    break;
                }
                case 'Application':
                {
                    $path .= $this->hFrameworkApplicationPath;
                    break;
                }
            }

            switch ($data['hPluginRepositorySoftware'])
            {
                case 'cvs':
                {

                    break;
                }
                case 'svn':
                {
                    if ($data['hPluginRepositoryCheckout'])
                    {
                        $command = 'update';
                    }
                    else if ($data['hPluginRepositoryReadonly'])
                    {
                        $command = 'export';
                    }

                    if ($data['hPluginRepositoryRevision'])
                    {
                        $command .=
                            ' --revision '.
                            escapeShellArg($data['hPluginRepositoryRevision']);
                    }

                    if ($data['hPluginRepositoryUser'])
                    {
                        $command .=
                            ' --username '.
                            escapeShellArg($data['hPluginRepositoryUser']);
                    }

                    if ($data['hPluginRepositoryPassword'])
                    {
                        $command .=
                            ' --password '.
                            escapeShellArg($data['hPluginRepositoryPassword']);
                    }

                    $command .= ' '.escapeShellArg($path);

                    $this->console("Repository Update Command: svn ".$command);

                    $this->pipeCommand(
                        '/usr/bin/svn',
                        trim($command)
                    );

                    break;
                }
                case 'git':
                {

                    break;
                }
            }
        }

        $this->database();
    }
}

?>