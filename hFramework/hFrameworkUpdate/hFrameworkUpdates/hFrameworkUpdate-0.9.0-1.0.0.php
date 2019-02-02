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

class hFrameworkUpdate_090To100 extends hPlugin {

    private $hFile;
    private $hUserDatabase;

    public function hConstructor()
    {
        $this->hFile = $this->library('hFile');

        $hFrameworkSite = $this->hFrameworkSite;
        
        if (!$this->hFrameworkSite)
        {
            $this->fatal("hFrameworkSite not specified");
        }

        $this->hFile->rename('/Sites', $hFrameworkSite);

        $hServerDocumentRoot = $this->hServerDocumentRoot;

        $hServerDocumentRootBase = dirname($hServerDocumentRoot);

        $owner = fileowner($hServerDocumentRoot);
        $group = filegroup($hServerDocumentRoot);

        if (!file_exists("{$hServerDocumentRootBase}/Hot Toddy"))
        {
            `mv "{$hServerDocumentRootBase}/www" "{$hServerDocumentRootBase}/Hot Toddy"`;
        }

        `mv "{$hServerDocumentRootBase}/files" "{$hServerDocumentRootBase}/HtFS"`;

        `mv "{$hServerDocumentRootBase}/conf" "{$hServerDocumentRootBase}/Configuration"`;

        `mv "{$hServerDocumentRootBase}/log" "{$hServerDocumentRootBase}/Log"`;

        `mv "{$hServerDocumentRootBase}/private" "{$hServerDocumentRootBase}/Plugins"`;

        if (!file_exists("{$hServerDocumentRootBase}/www"))
        {
            `mkdir "{$hServerDocumentRootBase}/www"`;
            `chmod 775 "{$hServerDocumentRootBase}/www"`;
            `chown {$owner}:{$group} "{$hServerDocumentRootBase}/www"`;
        }

        `cp "{$hServerDocumentRootBase}/Hot Toddy/default.php" "{$hServerDocumentRootBase}/www/index.php"`;

        `chmod 775 "{$hServerDocumentRootBase}/www/index.php"`;
        `chown -R {$owner}:{$group} "{$hServerDocumentRootBase}/www/index.php"`;

        `cp "{$hServerDocumentRootBase}/Hot Toddy/hShell.php" "{$hServerDocumentRootBase}/hShell"`;
        `chmod 775 "{$hServerDocumentRootBase}/hShell"`;
        `chown -R {$owner}:{$group} "{$hServerDocumentRootBase}/hShell"`;

        $exists = $this->hFileDomains->selectExists(
            'hFileDomainId',
            array(
                'hFileDomain' => str_replace('www.', '', $this->hServerHost)
            )
        );
        
        if (!$exists)
        {
            $this->hFileDomains->insert(
                array(
                    'hFileDomainId'        => null,
                    'hFileDomain'          => str_replace('www.', '', $this->hServerHost),
                    'hFileId'              => $this->getFileIdByFilePath('/'.$hFrameworkSite.'/index.html'),
                    'hFrameworkSite'       => $hFrameworkSite,
                    'hTemplateId'          => 1,
                    'hFileDomainIsDefault' => 1
                )
            );   
        }
        
        $query = $this->hTemplates->select(
            array(
                'hTemplateId',
                'hTemplatePath'
            )
        );

        foreach ($query as $data)
        {
            $this->hTemplates->update(
                array(
                    'hTemplatePath' => str_replace('/private/', '/Plugins/', $data['hTemplatePath'])
                ),
                $data['hTemplateId']
            );
        }

        $query = $this->hFileDocuments->select(
            array(
                'hFileId',
                'hFileDocument'
            ),
            array(
                'hFileDocument' => array('LIKE', '%/Sites%')
            )
        );

        foreach ($query as $data)
        {
            $this->hFileDocuments->update(
                array(
                    'hFileDocument' => str_replace('/Sites', '', $data['hFileDocument'])
                ),
                array(
                    'hFileId' => $data['hFileId']
                )
            );
        }

        if (!file_exists($hServerDocumentRootBase.'/Configuration/'.$hFrameworkSite.'.json'))
        {
            $data = parse_ini_file($hServerDocumentRootBase.'/Configuration/hFramework.conf');

            $conf = '';
            $variables = array();

            foreach ($data as $key => $value)
            {
                switch ($key)
                {
                    case 'hPath':
                    case 'hDatabaseHost':
                    case 'hDatabaseUser':
                    case 'hDatabasePassword':
                    case 'hDatabaseInitial':
                    case 'hDatabaseDriver':
                    case 'hFilePathToPEAR':
                    {
                        $conf .= "{$key} = \"{$value}\"\n";
                        break;
                    }
                    default:
                    {
                        switch ($key)
                        {
                            case 'hFileSystemPath':
                            {
                                $value = $hServerDocumentRootBase.'/HtFS';
                                break;
                            }
                        }
                        
                        if ($value == '0' || $value == '1')
                        {
                            $value = (int) $value;
                        }
                        else
                        {
                            $value = '"'.$value.'"';
                        }

                        $variables[] = "  {$key}: {$value}";
                    }
                }
            }

            file_put_contents(
                $hServerDocumentRootBase.'/Configuration/hFramework.conf',
                $conf
            );

            file_put_contents(
                $hServerDocumentRootBase.'/Configuration/'.$hFrameworkSite.'.json',
                "{\n".
                implode(",\n", $variables)."\n".
                "}"
            );
        }

        $this->hDatabase->query("DROP TABLE `hFileIcons`");

        `php "{$hServerDocumentRootBase}/Hot Toddy/hShell.php" -p hDatabase/hDatabaseStructure -i hFileIcons`;

        // Install icons for Apps and define them as Applications.
        $icns = array(
            '/Applications/Finder' => array(
                'plugins/finder',
                'Finder.png',
                'FinderIcon.icns'
            ),
            '/Applications/Calendar' => array(
                'plugins/calendar',
                'ical.png',
                'App.icns'
            ),
            '/Applications/Editor' => array(
                'plugins/editor',
                'Edit.png',
                'Edit.icns'
            ),
            '/Applications/Search' => array(
                'plugins/search',
                'search.png',
                ''
            ),
            '/Applications/Contacts' => array(
                'plugins/contacts',
                'address_book.png',
                'AppIcon.icns'
            )
        );

        foreach ($icns as $path => $icns)
        {
            $hFileIconId = $this->hDatabase->selectColumn(
                'hFileIconId',
                'hFileIcons',
                array(
                    'hFileMIME' => $icns[0]
                )
            );

            if (empty($hFileIconId))
            {
                $hFileIconId = $this->hDatabase->insert(
                    array(
                        'hFileIconId'    => null,
                        'hFileMIME'      => $icns[0],
                        'hFileName'      => $icns[1],
                        'hFileICNS'      => $icns[2],
                        'hFileExtension' => ''
                    ),
                    'hFileIcons'
                );
            }

            $this->hDatabase->save(
                array(
                    'hDirectoryId' => $this->getDirectoryId($path),
                    'hFileIconId' => $hFileIconId,
                    'hDirectoryIsApplication' => 1
                ),
                'hDirectoryProperties'
            );
        }

        // Create New Groups...
        $groups = array(
            'Administrators'          => 1,
            'Website Administrators'  => 1,
            'Finder Administrators'   => 1,
            'Calendar Administrators' => 1,
            'User Administrators'     => 1,
            'Employees'               => 0,
            'Disabled User Accounts'  => 0,
            'Contact Administrators'  => 1,
            'Contact Address Book'    => 0
        );

        $this->hUserDatabase = $this->database('hUser');

        foreach ($groups as $group => $elevated)
        {
            $this->hUserDatabase->saveGroupProperties(
                $this->hUserDatabase->save(0, $group, str_replace(' ', '', $group).'@localhost', ''), 
                1, $elevated, '', 0
            );

            $this->console("Created group {$group}");
        }

        // Add hFinderDocument as a group...
        if ($this->groupExists('hFinderDocument'))
        {
            $this->addUserToGroup('Website Administrators', 'hFinderDocument');
        }

        $this->addUserToGroup('root', 'Administrators');

        $this->addUserToGroup('Disabled User Accounts', 'Inactive');

        // Install Finder Info
        `php "{$hServerDocumentRootBase}/Hot Toddy/hShell.php" -p hPlugin -i hFinder/hFinderInfo`;
    }
}

?>