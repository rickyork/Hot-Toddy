<?php
  class hFileInstallShell extends hShell { private $hFile; private $hFileUtilities; private $hUserPermissions; private $hFileDatabase; private $hForumDatabase; private $hCalendarDatabase; private $hPluginDatabase; private $hFrameworkVariables; public function hConstructor() { if (!$this->hFrameworkSite) { $this->hFrameworkSite = $this->hServerHost; } $this->hFile = $this->library('hFile'); $this->hUserPermissions = $this->library('hUser/hUserPermissions');  $this->hDirectories->insert( array( 'hDirectoryId' => 1, 'hDirectoryParentId' => 0, 'hUserId' => 1, 'hDirectoryPath' => '/', 'hDirectoryCreated' => time(), 'hDirectoryLastModified' => 0 ) ); $this->hUserPermissions->save(2, 1, 'rw', 'r'); $this->console("HtFS root directory created.");  $directories = array( 'Applications', 'Hot Toddy', 'Categories', 'Library', $this->hFrameworkSite, 'System', 'Template', 'Users' ); foreach ($directories as $directory) { $this->hUserPermissions->save( 2, $this->mkdir('/', $directory), 'rw', 'r' ); $this->console("HtFS directory /{$directory} created."); } if (!$this->hFile->exists('/Applications/Utilities')) { $this->hUserPermissions->save( 2, $this->mkdir('/Applications', 'Utilities'), 'rw', 'r' ); $this->console("HtFS directory /Applications/Utilities created."); } if (!$this->hFile->exists('/'.$this->hFrameworkSite.'/Pictures')) { $this->hUserPermissions->save( 2, $this->mkdir('/'.$this->hFrameworkSite, 'Pictures'), 'rw', 'r' ); $this->console("HtFS directory /{$this->hFrameworkSite}/Pictures created."); } if (!$this->hFile->exists('/Template/Pictures')) { $this->hUserPermissions->save( 2, $this->mkdir('/Template', 'Pictures'), 'rw', 'r' ); $this->console("HtFS directory /Template/Pictures created."); } if (!$this->hFile->exists('/'.$this->hFrameworkSite.'/Events')) { $this->hUserPermissions->save( 2, $this->mkdir('/'.$this->hFrameworkSite, 'Events'), 'rw', 'r' ); $this->console("HtFS directory /{$this->hFrameworkSite}/Events created."); } $directories = array( 'Applications', 'Documents', 'Library', 'Server' ); foreach ($directories as $directory) { $this->hUserPermissions->save( 2, $this->mkdir('/System', $directory), 'rw', 'r' ); $this->console("HtFS directory /System/{$directory} created."); }   $this->console("Rounding up framework files..."); $this->console("Reading plugin definitions and installing default applications, this could take a while..."); $this->hFileDatabase = $this->database('hFile'); $hFileId = $this->hFileDatabase->save( array( 'hFileId' => 0, 'hLanguageId' => 1, 'hDirectoryId' => $this->getDirectoryId('/'.$this->hFrameworkSite), 'hUserId' => 1, 'hFileParentId' => 0, 'hFileName' => 'index.html', 'hFileTitle' => 'Welcome to Hot Toddy', 'hFileDocument' => "&lt;p&gt;Hot Toddy installation was successful!&lt;/p&gt;", 'hPlugin' => '' ) ); $this->hUserPermissions->save(1, $hFileId, 'rw', 'r');  $this->console("Making a blog..."); $this->plugin('hCalendar/hCalendarBlog', false, false, false, false); $this->hCalendarDatabase = $this->database('hCalendar'); $hCalendarId = $this->hCalendarDatabase->saveCalendar(0, 1, 'Hot Toddy'); $hFileId = $this->hFileDatabase->save( array( 'hFileId' => 0, 'hDirectoryId' => $this->getDirectoryId('/'.$this->hFrameworkSite), 'hUserId' => 1, 'hFileParentId' => 0, 'hFileName' => 'Blog.html', 'hFileTitle' => 'Hot Toddy Blog', 'hPlugin' => 'hCalendar/hCalendarBlog', 'hCalendarId' => $hCalendarId, 'hCalendarCategoryId' => 3, 'hRSSTitle' => 'Hot Toddy Blog' ) ); $this->hUserPermissions->save(1, $hFileId, 'rw', 'r'); $this->console("Adding a Blog Post..."); $hFileId = $this->hFileDatabase->save( array( 'hFileId' => 0, 'hDirectoryId' => $this->getDirectoryId('/'.$this->hFrameworkSite.'/Events'), 'hUserId' => 1, 'hFileParentId' => $hFileId, 'hFileName' => 'Hot Toddy.html', 'hFileTitle' => 'Welcome to Your Hot Toddy Blog', 'hFileDocument' => hString::escapeAndEncode( file_get_contents( dirname(__FILE__).'/HTML/Default Blog Post.html' ) ), 'hFileDescription' => hString::escapeAndEncode( file_get_contents( dirname(__FILE__).'/HTML/Default Blog Post Description.html' ) ), 'hPlugin' => 'hCalendar/hCalendarBlog', 'hFileCalendarId' => $hCalendarId, 'hFileCalendarCategoryId' => 3, 'hFileCalendarDate' => time() ) ); $this->hUserPermissions->save(1, $hFileId, 'rw', 'r'); $this->hFileUtilities = $this->library('hFile/hFileUtilities'); $files = $this->hFileUtilities->getFiles();  $this->hPluginDatabase = $this->database('hPlugin'); $this->hPluginInstallFiles = true; foreach ($files as $file) { if (substr($file, -4) == '.xml' || substr($file, -5) == '.json') { $this->console("Reading from: {$file}."); $plugin = str_replace($this->hServerDocumentRoot.'/', '', $file); $pluginBits = explode('/', $plugin); array_pop($pluginBits); $plugin = implode('/', $pluginBits); $this->hPluginDatabase->register($plugin); $this->console("\n"); } }   $this->hDatabase->query( "UPDATE `hCategories`
                SET `hCategoryId` = 0
              WHERE `hCategoryId` = 1" );  $folders = array( '/Applications' => 'directory/applications', '/Users' => 'directory/users', '/Categories' => 'directory/categories', '/System' => 'directory/system', '/Library' => 'directory/library' ); $folders['/'.$this->hFrameworkSite] = 'directory/sites'; foreach ($folders as $path => $mime) { $hFileIconId = $this->hFileIcons->selectColumn( 'hFileIconId', array( 'hFileMIME' => $mime ) ); $this->hDirectoryProperties->insert( array( 'hDirectoryId' => (int) $this->getDirectoryId($path), 'hFileIconId' => (int) $hFileIconId, 'hDirectoryIsApplication' => 0, 'hDirectoryLabel' => '' ) ); }  $version = file_get_contents($this->hServerDocumentRoot.'/hFramework/hFrameworkUpdate/hFrameworkVersion.txt'); $this->hFrameworkVariables = $this->library('hFramework/hFrameworkVariables'); $this->hFrameworkVariables->save('hFrameworkVersion', $version); } public function mkdir($path, $name) {   return $this->hDirectories->insert( array( 'hDirectoryId' => nil, 'hDirectoryParentId' => (int) $this->getDirectoryId($path), 'hUserId' => 1, 'hDirectoryPath' => $this->getConcatenatedPath($path, $name), 'hDirectoryCreated' => time(), 'hDirectoryLastModified' => 0 ) ); } } ?>