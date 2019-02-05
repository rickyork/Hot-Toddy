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
#
# This plugin installs the default Hot Toddy file system.
#

class hFileInstallShell extends hShell {

    private $hFile;
    private $hFileUtilities;
    private $hUserPermission;
    private $hFileDatabase;
    private $hForumDatabase;
    private $hCalendarDatabase;
    private $hPluginDatabase;
    private $hFrameworkVariables;

    public function hConstructor()
    {
        $this->console('hFileInstallShell loaded...');
        
        if (!$this->hFrameworkSite)
        {
            $this->hFrameworkSite = $this->hServerHost;
        }

        $this->hFile = $this->library('hFile');
        $this->hUserPermission = $this->library('hUser/hUserPermission');

        // Insert the root directory.
        $this->hDirectories->insert(
            array(
                'hDirectoryId'           => 1,
                'hDirectoryParentId'     => 0,
                'hUserId'                => 1,
                'hDirectoryPath'         => '/',
                'hDirectoryCreated'      => time(),
                'hDirectoryLastModified' => 0
            )
        );

        $this->hUserPermission->save(2, 1, 'rw', 'r');

        $this->console("HtFS root directory created.");

        // Create the default directory structure.
        $directories = array(
            'Applications',
            'Hot Toddy',
            'Categories',
            'Library',
            $this->hFrameworkSite,
            'System',
            'Template',
            'Users'
        );

        foreach ($directories as $directory)
        {
            $this->hUserPermission->save(
                2,
                $this->mkdir('/', $directory),
                'rw',
                'r'
            );

            $this->console("HtFS directory /{$directory} created.");
        }

        if (!$this->hFile->exists('/Applications/Utilities'))
        {
            $this->hUserPermission->save(
                2,
                $this->mkdir('/Applications', 'Utilities'),
                'rw',
                'r'
            );

            $this->console("HtFS directory /Applications/Utilities created.");
        }

        if (!$this->hFile->exists('/'.$this->hFrameworkSite.'/Pictures'))
        {
            $this->hUserPermission->save(
                2,
                $this->mkdir('/'.$this->hFrameworkSite, 'Pictures'),
                'rw',
                'r'
            );

            $this->console("HtFS directory /{$this->hFrameworkSite}/Pictures created.");
        }

        if (!$this->hFile->exists('/Template/Pictures'))
        {
            $this->hUserPermission->save(
                2,
                $this->mkdir('/Template', 'Pictures'),
                'rw',
                'r'
            );

            $this->console("HtFS directory /Template/Pictures created.");
        }

        if (!$this->hFile->exists('/'.$this->hFrameworkSite.'/Events'))
        {
            $this->hUserPermission->save(
                2,
                $this->mkdir('/'.$this->hFrameworkSite, 'Events'),
                'rw',
                'r'
            );

            $this->console("HtFS directory /{$this->hFrameworkSite}/Events created.");
        }

        $directories = array(
            'Applications',
            'Documents',
            'Library',
            'Server'
        );

        foreach ($directories as $directory)
        {
            $this->hUserPermission->save(
                2,
                $this->mkdir('/System', $directory),
                'rw',
                'r'
            );

            $this->console("HtFS directory /System/{$directory} created.");
        }

        // Next, all of the plugin XML documents need to be located, and parsed,
        // to create file system objects for default Applications.

        $this->console("Rounding up framework files...");

        $this->console("Reading plugin definitions and installing default applications, this could take a while...");

        $this->hFileDatabase = $this->database('hFile');

        $hFileId = $this->hFileDatabase->save(
            array(
                'hFileId'            => 0,
                'hLanguageId'        => 1,
                'hDirectoryId'       => $this->getDirectoryId('/'.$this->hFrameworkSite),
                'hUserId'            => 1,
                'hFileParentId'      => 0,
                'hFileName'          => 'index.html',
                'hFileTitle'         => 'Welcome to Hot Toddy',
                'hFileDocument'      => "&lt;p&gt;Hot Toddy installation was successful!&lt;/p&gt;",
                'hPlugin'            => ''
            )
        );

        $this->hUserPermission->save(1, $hFileId, 'rw', 'r');

/*
        echo "Making a Forum...\n";

        $this->plugin('hForum', false, false, false, false);

        $hFileId = $this->hFileDatabase->save(
            array(
                'hFileId'            => 0,
                'hLanguageId'        => 1,
                'hDirectoryId'       => $this->getDirectoryId('/'.$this->hFrameworkSite),
                'hUserId'            => 1,
                'hFileParentId'      => 0,
                'hFileName'          => 'Forum.html',
                'hFileTitle'         => 'Hot Toddy Forum',
                'hPlugin'            => 'hForum',
                'hFileSortIndex'     => 0,
                'hFileCreated'       => time()
            )
        );

        $this->hUserPermission->save(1, $hFileId, 'rw', 'r');

        $this->hForumDatabase = $this->database('hForum');

        $hForumId = $this->hForumDatabase->save(0, $hFileId, 'Forum', 0, 1);
        $this->hUserPermissions->save(3, $hForumId, 'rw', 'r');

        $hForumTopicId = $this->hForumDatabase->saveTopic(
            array(
                'hForumTopicId' => 0,
                'hForumId' => $hForumId,
                'hForumTopic' => 'Hot Toddy',
                'hForumTopicDescription' => '',
                'hForumTopicSortIndex' => 0,
                'hForumTopicIsLocked' => 0,
                'hForumTopicIsModerated' => 0,
                'hForumTopicLastResponse' => 0,
                'hForumTopicLastResponseBy' => 0,
                'hForumTopicResponseCount' => 0,
                'hUserId' => 1
            )
        );

        $this->hUserPermission->setGroup(1, 'rw');
        $this->hUserPermission->save(4, $hForumTopicId, 'rw', 'r');
*/

        $this->console("Making a blog...");

        $this->plugin('hCalendar/hCalendarBlog', false, false, false, false);

        $this->hCalendarDatabase = $this->database('hCalendar');

        $hCalendarId = $this->hCalendarDatabase->saveCalendar(0, 1, 'Hot Toddy');

        $hFileId = $this->hFileDatabase->save(
            array(
                'hFileId'             => 0,
                'hDirectoryId'        => $this->getDirectoryId('/'.$this->hFrameworkSite),
                'hUserId'             => 1,
                'hFileParentId'       => 0,
                'hFileName'           => 'Blog.html',
                'hFileTitle'          => 'Hot Toddy Blog',
                'hPlugin'             => 'hCalendar/hCalendarBlog',
                'hCalendarId'         => $hCalendarId,
                'hCalendarCategoryId' => 3,
                'hRSSTitle'           => 'Hot Toddy Blog'
            )
        );

        $this->hUserPermission->save(1, $hFileId, 'rw', 'r');

        $this->console("Adding a Blog Post...");

        $hFileId = $this->hFileDatabase->save(
            array(
                'hFileId'                 => 0,
                'hDirectoryId'            => $this->getDirectoryId('/'.$this->hFrameworkSite.'/Events'),
                'hUserId'                 => 1,
                'hFileParentId'           => $hFileId,
                'hFileName'               => 'Hot Toddy.html',
                'hFileTitle'              => 'Welcome to Your Hot Toddy Blog',
                'hFileDocument'           => hString::escapeAndEncode(
                    file_get_contents(
                        dirname(__FILE__).'/HTML/Default Blog Post.html'
                    )
                ),
                'hFileDescription'        => hString::escapeAndEncode(
                    file_get_contents(
                        dirname(__FILE__).'/HTML/Default Blog Post Description.html'
                    )
                ),
                'hPlugin'                 => 'hCalendar/hCalendarBlog',
                'hFileCalendarId'         => $hCalendarId,
                'hFileCalendarCategoryId' => 3,
                'hFileCalendarDate'       => time()
            )
        );

        $this->hUserPermission->save(1, $hFileId, 'rw', 'r');

        $this->hFileUtilities = $this->library('hFile/hFileUtilities');
        $files = $this->hFileUtilities->getFiles();

        // Wonder if it can install itself.
        $this->hPluginDatabase = $this->database('hPlugin');

        $this->hPluginInstallFiles = true;

        foreach ($files as $file)
        {
            if (substr($file, -4) == '.xml' || substr($file, -5) == '.json')
            {
                $this->console("Reading from: {$file}.");

                $plugin     = str_replace($this->hServerDocumentRoot.'/', '', $file);
                $pluginBits = explode('/', $plugin);
                array_pop($pluginBits);
                $plugin = implode('/', $pluginBits);

                $this->hPluginDatabase->register($plugin);

                $this->console("\n");
            }
        }

        // Fix Default Category Id
        // can't get this to work with "update" method.
        $this->hDatabase->query(
            "UPDATE `hCategories`
                SET `hCategoryId` = 0
              WHERE `hCategoryId` = 1"
        );

/*
        $name = basename($path);

        switch ($name)
        {
            case '':               return 'directory/root';
            case 'Applications':   return 'directory/applications';
            case 'Users':          return 'directory/users';
            case 'Library':        return 'directory/library';
            case 'Documents':      return 'directory/documents';
            case 'Movies':         return 'directory/movies';
            case 'Music':          return 'directory/music';
            case 'Pictures':       return 'directory/pictures';
            case 'Sites':          return 'directory/sites';
            case 'Categories':     return 'directory/categories';
            case 'Network':        return 'directory/network';
            case 'System':         return 'directory/system';
            case 'Products':       return 'directory/products';
            case '/Users/'.$this->user->getUserName(): return 'directory/home';
        }
*/
        $folders = array(
            '/Applications' => 'directory/applications',
            '/Users'        => 'directory/users',
            '/Categories'   => 'directory/categories',
            '/System'       => 'directory/system',
            '/Library'      => 'directory/library'
        );

        $folders['/'.$this->hFrameworkSite] = 'directory/sites';

        foreach ($folders as $path => $mime)
        {
            $hFileIconId = $this->hFileIcons->selectColumn(
                'hFileIconId',
                array(
                    'hFileMIME' => $mime
                )
            );

            $this->hDirectoryProperties->insert(
                array(
                    'hDirectoryId' => (int) $this->getDirectoryId($path),
                    'hFileIconId'  => (int) $hFileIconId,
                    'hDirectoryIsApplication' => 0,
                    'hDirectoryLabel' => ''
                )
            );
        }

        // Record the framework's version at install time
        $version = file_get_contents($this->hServerDocumentRoot.'/hFramework/hFrameworkUpdate/hFrameworkVersion.txt');
        $this->hFrameworkVariables = $this->library('hFramework/hFrameworkVariables');
        $this->hFrameworkVariables->save('hFrameworkVersion', $version);
    }

    public function mkdir($path, $name)
    {
        // A separate function for making the directories is needed because of
        // checks for special folders in hFile.
        return $this->hDirectories->insert(
            array(
                'hDirectoryId'           => nil,
                'hDirectoryParentId'     => (int) $this->getDirectoryId($path),
                'hUserId'                => 1,
                'hDirectoryPath'         => $this->getConcatenatedPath($path, $name),
                'hDirectoryCreated'      => time(),
                'hDirectoryLastModified' => 0
            )
        );
    }
}

?>