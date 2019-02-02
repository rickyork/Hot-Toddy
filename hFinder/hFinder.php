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
# @description
# <h1>Hot Toddy Finder Application</h1>
# <p>
#   Hot Toddy creates a virtual file system in the database, which is called HtFS, or
#   <i>Hot Toddy File System</i>.  It's a mix mash of files and folders from multiple
#   locations, both in the database and on the server's hard disk.  Hot Toddy creates
#   paths to <b>everything</b> on the server, but access to everything is controlled
#   using permissions.  For example, you have to be logged in and have <b>root</b>
#   privileges to be able to access files and folders outside of Hot Toddy, and
#   furthermore, Apache must also have permission to access files and folders outside
#   of Hot Toddy.
# </p>
# <p>
#   Hot Toddy's Finder, or hFinder, is inspired and modeled after Mac OS X's Finder, and its goal
#   is to present a graphical interface for accessing files both in Hot Toddy's
#   virtual file system and the real server file system.  Hot Toddy stores files, folders
#   and meta information in a collection of database tables.  The primary database
#   tables are <var>hFiles</var> and <var>hDirectories</var>.  These two tables store
#   the hierarchy of the HtFS file system.  To faciliate viewing files and folders in
#   the server's file system, Hot Toddy creates dynamic folders in its own file system
#   that act as gateways to the server's file system.  Some examples are:
# </p>
# <ul>
#   <li>
#       <var>/System/Server</var> This folder accesses the root directory of the
#       server's file system, or <var>/</var>
#   </li>
#   <li>
#       <var>/System/Framework</var> This folder accesses the root directory of the
#       Hot Toddy installation, which for this particular installation is
#       <var>{hFrameworkPath}</var> and this is the path specified in the framework
#       variable <var>hFrameworkPath</var>.
#   </li>
#   <li>
#       <var>/Library</var> This folder maps to Hot Toddy's <var>Library</var> folder,
#       which is <var>{hFrameworkLibraryPath}</var>.  The path to this folder is stored in the
#       framework variable <var>hFrameworkLibraryPath</var>.
#   </li>
#   <li>
#       <var>/System/Documents</var> This folder maps to Hot Toddy's <var>HtFS</var> folder,
#       which is <var>{hFrameworkFileSystemPath}</var>.  The <var>HtFS</var> folder is where
#       uploaded documents are stored.  Hot Toddy creates records in the database
#       tables <var>hFiles</var> and <var>hDirectories</var> that remember where users
#       put files in the HtFS file system, while keeping each file's content in a
#       cloned folder heirarchy in the <var>HtFS</var> folder.  For example, if you
#       were to upload <var>image.png</var> to <var>/{hFrameworkSite}</var> the file
#       <var>image.png</var> would be created in HtFS, in the <var>hFiles</var> database
#       table with the parent directory <var>/{hFrameworkSite}</var>, then the actual
#       content of <var>image.png</var> would be created at <var>{hFrameworkFileSystemPath}/{hFrameworkSite}/image.png</var>
#       and whenever <var>image.png</var> is accessed, it will be downloaded from
#       <var>{hFrameworkFileSystemPath}/{hFrameworkSite}/image.png</var>.  Having the
#       file created in the database file system allows for a multitude of meta information
#       to be attached to the file, including Hot Todyd permissions for which framework
#       users you wish to grant access to that document, as well as a plethora of other
#       information.  For example, you can track who's downloaded the file, attach
#       a password for access to the file, and so on.
#   </li>
# </ul>
# <h2>Reusing hFinder</h2>
# <p>
#   hFinder is designed to be a reusable application.  It can be embedded in other plugins,
#   it can be used as a dialogue.  For example, when you would like the user to open a document
#   in the file system, it can be used as an Open Dialogue.  If you would like the user
#   to pick a place to save a document and give that document a name, it can be used as a Save As
#   dialogue.  If you would like the user to pick a folder to be used for something, it can be
#   used as a Choose Folder dialogue.  Fair warning, however, any task involving the file
#   system should be reserved for advanced users that are capable of understanding the
#   concept of a file system.  Applications oriented to average users should completely
#   abstract the concept of the file system whenever possible.
# </p>
# <h2>The Tree View</h2>
# <p>
#   hFinder can be used with both the tree view on the left side, or both the tree and folder
#   views.  The tree view is available alone in the plugin
#   <a href='/Hot Toddy/Documentation?hFinder/hFinderTree' class='code'>hFinder/hFinderTree</a>
# </p>
# <h2>Drag and Drop</h2>
# <p>
#   hFinder supports drag and drop of files and folders between multiple instances of hFinder open
#   in separate browser windows (if the browser supports the feature.  Safari, Chrome, and
#   Firefox do).  hFinder also supports drag and drop of one or more files from the desktop
#   or the desktop file system (again, if the browser supports the feature.  Safari, Chrome and
#   Firefox do).  And finally, hFinder supports drag and drop download of just one file at a
#   time from the browser window to the desktop or desktop file system, in this case, at the
#   time that I'm writing this, only Google Chrome presently supports this feature.
# </p>
# <h2>Database-Driven</h2>
# <p>
#   hFinder is considered to be core, essential funcionality for Hot Toddy, as it is presently
#   the only way to graphically interact with Hot Toddy's file system, and Hot Toddy strives
#   to make as much of its file system as possible database-driven so that files and folders
#   can be easily searched through using queries and database indexes.  When uploading files
#   through hFinder, Hot Toddy will automatically copy the content of office documents, Word,
#   Excel, Pages, Numbers, PDF into the database file system to make those documents
#   searchable via full text indexing in MySQL.  Beyond files names and the actual content of
#   a document, Hot Toddy also makes fields available for specifying descriptions, keywords,
#   and titles, which can be filled with any content you like.
# </p>
# @end

interface hFinderDialogueTemplate {
    public function hConstructor();
    public function getControls();
}

class hFinder extends hPlugin {

    private $dialogue = false;
    private $dialogueType = 'custom';
    private $hFile;
    private $hFinder;
    private $hFinderTree;
    private $hDialogue;
    private $hUserPermissions;
    private $hFinderDialogue;
    private $hFinderSideColumn;

    public function hConstructor()
    {
        $this->redirectIfSecureIsEnabled();

        if ($this->inGroup('root') && isset($_GET['hFileSystemAllowDuplicates']))
        {
            $this->hFileSystemAllowDuplicates = (int) $_GET['hFileSystemAllowDuplicates'];
        }

        if ($this->inGroup('root'))
        {
            $this->hFinderCategoriesEnabled = true;
        }

        if (!empty($_GET['hFinderConf']))
        {
            $this->loadConfigurationFile(
                $this->hFrameworkConfigurationPath.'/hFinder '.hString::scrubString($_GET['hFinderConf'])
            );
        }

        if (isset($_GET['path']))
        {
            hString::safelyDecodeURL($_GET['path']);
        }

        if (isset($_GET['hFinderDiskName']))
        {
            hString::safelyDecodeURL($_GET['hFinderDiskName']);
            $this->hFinderDiskName = hString::scrubString($_GET['hFinderDiskName']);
        }

        //echo `system_profiler SPHardwareDataType`;
        $this->hEditorTemplateEnabled = false;

        if ($this->isLoggedIn())
        {
            $this->getFinder();
        }
        else
        {
            $this->notLoggedIn();
        }
    }

    private function getFinder()
    {
        $this->hFileCSS = '';
        $this->hFileJavaScript = '';

        if ($this->hDesktopApplication(false))
        {
            $this->getPluginFiles('hFinder/hFinderDesktop');
        }

        $this->hFileDocument = '';

        $this->hFinder = $this->library('hFinder');
        $this->hFile = $this->library('hFile');

        $this->hFileFavicon = '/hFinder/Pictures/Finder.ico';

        $this->hFileTitle = $this->hServerHost.' Finder';
        $this->hFileTitlePrepend = '';
        $this->hFileTitleAppend  = '';

        //$this->jQuery('Draggable', 'Droppable');

        if (isset($_GET['dialogue']))
        {
            $this->dialogue = true;
            $this->dialogueType = $_GET['dialogue'];
        }

        $this->hFileDisableCache = true;
        $this->hFileEnableCache  = false;

        $this->hTemplatePath = '/hFinder/hFinder.template.php';

        if ($this->dialogue)
        {
            switch ($this->dialogueType)
            {
                case 'SaveAs':
                #case 'Open' :
                #case 'Custom':

                # Link and Image are used by WYSIWYG editors, these will be eliminated eventually,
                # and the 'Choose' dialogue will be used instead.
                #
                # To use the Choose dialogue in a situation where you want the user to pick
                # something to link to, the best option is to use Choose with the
                # &types=folders,files
                #
                # To use the Choose dialogue in a situation where you want the user to pick
                # images, the best option is to use Choose with the
                # &types=jpg,jpe,jpeg,png,gif,sag  (i.e., any image type that is acceptable for
                # web use)
                #
                case 'Image':
                case 'Link':

                # Directory dialogue type is deprecated, please use the Choose dialogue instead.
                #
                # That said, Directory provides a UI for picking a folder via a tree view and
                # does not show files at all.
                case 'Directory':

                # Choose can be used when you want the user to pick a folder or a file.
                #
                # What the user can choose can be controlled by providing a 'types' argument
                # in the URL.
                #
                # Allow Folders
                # &types=folders
                #
                # Allow Files
                # &types=files
                #
                # Allow Word Doc and PDF by extensions
                # &types=doc,pdf
                #
                # Allow jpg and png by MIME types
                # &types=image/jpeg,image/jpg,image/png
                #
                # Allow Word Doc, JPG, and Folders
                # &types=doc,image/jpeg,image/jpg,folders
                #
                # If types is not provided all files will be allowed by default (but not
                # folders).
                #
                # The Choose dialogue also requires a JavaScript callback function to be
                # specified in the URL, this function will be called when the user makes
                # a selection, and takes two arguments.
                #
                # &onChooseFile=myGreatObject.myGreatCallbackFunction
                #
                # The callback function should have two arguments:
                # onChooseFile : function(fileId, filePath)

                case 'Choose':
                {
                    $this->hFinderDialogue = $this->plugin('hFinder/hFinderDialogue/hFinderDialogue'.$this->dialogueType);
                    break;
                }
                default:
                {
                    $this->warning('Invalid type of dialogue.', __FILE__, __LINE__);
                }
            }

            $this->hFinderHasTree = true;
            $this->hFinderBodyId = 'hFinderDialogue'.$this->dialogueType;
        }

        hString::scrubArray($_GET);

        if (isset($_GET['path']))
        {
            if (isset($_GET['setDefaultPath']))
            {
                $this->hFinderDefaultPath = $_GET['path'];
            }
            else
            {
                $path = $_GET['path'];
            }
        }

        if ($this->hFinderDefaultPath)
        {
            if ($this->inGroup('root') && $this->hFinderRootOverrideDefaultPath(true))
            {

            }
            else
            {
                $path = $this->hFinderDefaultPath;
            }
        }

        if (empty($path))
        {
            $path = $this->user->getVariable('hFinderPath', '/');
        }

        if ($this->hFinderHasFiles(true) && $this->hFinderEnableContextMenu(true))
        {
            $this->plugin('hFinder/hFinderContextMenu');
        }

        $view = ucwords($this->user->getVariable('hFinderView', 'Icons'));

        $this->user->hFinderPath($path, $this->user->getUserId(), true);

        if ($this->beginsPath($path, '/Categories'))
        {
            $view = 'List';
        }

        $this->hFinderPath = $path;
        $this->hFinderView = $view;

        $this->getPluginCSS('ie6');
        $this->getPluginCSS('ie7');

        $html = '';

        if ($this->hFinderHasFiles(true))
        {
            $html .= $this->getTemplate(
                'Configuration',
                array(
                    'hFinderLocation' => urlencode($path),
                    'hFinderView'     => $view
                )
            );

            if (isset($_GET['hFinderUploadPlugin']))
            {
                $this->hFinderUploadPlugin = $_GET['hFinderUploadPlugin'];
            }

            $this->plugin('hFinder/hFinderPasswords');
            $this->plugin('hFinder/hFinderToolbar');

            if (!isset($_GET['hFinderUploadOverride']))
            {
                $this->plugin($this->hFinderUploadPlugin('hFinder/hFinderUpload'));
            }
            else if ($this->inGroup('root'))
            {
                $this->plugin('hFinder/hFinderUpload');
            }

            $this->plugin($this->hFinderEditFilePlugin('hFinder/hFinderEditFile'));
            $this->plugin('hFinder/hFinderCategories');
        }

        if ($this->hFinderHasSideColumn(true))
        {
            $this->hFinderSideColumn = $this->plugin('hFinder/hFinderSideColumn');
            $html .= $this->hFinderSideColumn->get();
        }
        else if ($this->hFinderHasTree(false))
        {
            $this->hFinderTree = $this->plugin('hFinder/hFinderTree');
            $html .= $this->hFinderTree->getTree();
        }

        if ($this->hFinderHasFiles(true))
        {
            if ($this->hDesktopApplication(false))
            {
                $html .= $this->getTemplate(
                    'Finder Files',
                    array(
                        'hFinderView'  => 'Icons',
                        'hFinderColumnsView' => ($view == 'Columns'),
                        'hFinderListView' => ($view == 'List'),
                        'hFinderFiles' => '',
                        'hFinderPath' => $this->hFinder->getEncodedPath($this->hFinderPath)
                    )
                );
            }
            else
            {
                $html .= $this->getTemplate(
                    'Finder Files',
                    array(
                        'hFinderView'  => $view,
                        'hFinderColumnsView' => ($view == 'Columns'),
                        'hFinderListView' => ($view == 'List'),
                        'hFinderFiles' => $this->hFinder->getDirectory($path, $view),
                        'hFinderPath' => $this->hFinder->getEncodedPath($this->hFinderPath)
                    )
                );
            }
        }

        if ($this->hFinderDialogue)
        {
            $html .= $this->hFinderDialogue->getControls();
        }

        $this->hFileDocument = $this->getTemplate(
            'Finder',
            array(
                'hFinder' => $this->hFileDocument.$html,
                'hFinderContextMenu' => $this->hFinderContextMenu(nil)
            )
        );

        if ($this->hFinderDialogue && method_exists($this->hFinderDialogue, 'getDialogues'))
        {
            $this->hFileDocument .= $this->hFinderDialogue->getDialogues();
        }

        if (isset($_GET['hFinderButtons']) || $this->hFinderButtons(false))
        {
            $this->plugin('hFinder/hFinderButtons');
        }
    }

    public static function defaultDialogueControls($input, $buttons)
    {
        return $this->getTemplate(
            'Dialogue Controls',
            array(
                'input' => $input,
                'buttons' => $buttons
            )
        );
    }
}

?>