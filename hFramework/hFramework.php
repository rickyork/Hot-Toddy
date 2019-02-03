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
# <h1>Hot Toddy Framework Core API</h1>
# <p>
#   The <var>hFramework</var> object is core functionality for Hot Toddy,
#   it contains methods that allow file-system access to Hot Toddy's file
#   system, HtFS to function, as well as the command line API.
# </p>
# @end

interface hPrivatePlugin {

    # The following are used to get private, replacement themes for Hot Toddy components.
    public function getPrivateHeaders(&$plugin, &$method, &$file, &$path);

    # Snapin your own CSS or JS for front-end Hot Toddy form components.
    public function &getPrivateForm();
}

$pluginPath = $_SERVER['DOCUMENT_ROOT'];

# See each of the following included files for more information about what
# functionality each provides.
hFrameworkInclude($pluginPath.'/hPlugin/hPlugin.library.php');
hFrameworkInclude($pluginPath.'/hFramework/hFrameworkApplication/hFrameworkApplication.php');
hFrameworkInclude($pluginPath.'/hPlugin/hPlugin.php');
hFrameworkInclude($pluginPath.'/hFramework/hFrameworkVariables/hFrameworkVariables.php');
hFrameworkInclude($pluginPath.'/hFile/hFilePath/hFilePath.php');
hFrameworkInclude($pluginPath.'/hFramework/hFrameworkResources/hFrameworkResources.php');
hFrameworkInclude($pluginPath.'/hHTTP/hHTTP.php');

class hFramework extends hHTTP {

    public $fusePlugins = array();
    public $loadedPaths = array();
    public $hDB = '';
    public $hDatabase;
    public $tables = array();
    public $hPrivateFramework = nil;

    private $hEditor;

    protected $tableObjects = array();

    public static function &singleton()
    {
        # @return hFramework

        # @description
        # <h2>Framework Singleton</h2>
        # <p>
        #   Returns the global framework object.
        # </p>
        # @end

        return $GLOBALS['hFramework'];
    }

    protected function setOutputBuffer()
    {
        # @return void

        # @description
        # <h2>Output Buffer</h2>
        # <p>
        #   Turns on the output buffer, if settings say to do so.  To turn on output buffering,
        #   set the <var>hServerOutputBuffer</var> framework variable to <var>true</var>. it is true, by default.
        # </p>

        if ($this->hServerOutputBuffer(true))
        {
            # <h2>GZip Compression</h2>
            # <p>
            #   If gzip compression is enabled via the framework variable <var>hServerGZip</var>, and the
            #   user-agent explicitly supports gzip compression via the specification of the <var>HTTP_ACCEPT_ENCODING</var>
            #   header, gzip compression will be used.
            # </p>
            # @end
            if ($this->hServerGZip(false) && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && stristr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
            {
                ob_start('ob_gzhandler');
            }
            else
            {
                ob_start();
            }
        }
    }

    public function defineTableObjects()
    {
        # @return void

        # @description
        # <h2>Database Reflection</h2>
        # <p>
        #   Retrieves a list of all database tables and stores that list of tables in
        #   the <var>tables</var> property.  This is then used to create a database reflection
        #   API that allows you to access database tables as objects in the framework.  For example,
        #   to select all rows in the <var>hFiles</var> table, you'd call <var>$this-&gt;hFiles-&gt;select()</var>.
        # </p>
        # @end
        $this->tables = $this->hDatabase->getTables();
        #return $this;
    }

    public function addTableObject($table)
    {
        # @return void

        # @description
        # <h2>Add a Database Table to the Reflection API</h2>
        # <p>
        #   To add a new database table to the reflection API at runtime, for example, at the moment
        #   a table is created, call this method.
        # </p>
        # <p class='hDocumentationNote'>
        #   <b>Note:</b> Adding a database table to the reflection API does not
        #   also add a database table to the actual database, for that you'd
        #   need to either execute the appropriate SQL query, or use the appropriate
        #   <a href='/Hot Toddy/Documentation?hDatabase' class='code'>hDatabase</a> API.
        # </p>
        # @end
        if (!in_array($table, $this->tables))
        {
            array_push($this->tables, $table);
        }

        $this->hDatabase->addTableToCache($table);
        $this->hDatabase->getColumns($table);

        #return $this;
    }

    public function refreshTableObjects()
    {
        # @return hFramework

        # @description
        # <h2>Refresh Database Tables in Reflection API</h2>
        # <p>
        #   To refresh all database tables in the reflection API at runtime, call this method.
        # </p>
        # @end
        foreach ($this->tableObjects as $table => $object)
        {
            unset($this->tableObjects[$table]);
        }

        $this->tables = $this->hDatabase->getTables();

        #return $this;
    }

    public function resetTableObject($table)
    {
        # @return hFramework

        # @description
        # <h2>Resetting a Database Table</h2>
        # <p>
        #   Resets a database table's cached columns and primary key information.  This
        #   can also be done as <var>$this-&gt;<i>Database Table</i>-&gt;reset();</var>.
        # </p>
        # @end

        if (isset($this->tableObjects[$table]))
        {
            $this->tableObjects[$table]->reset();
        }

        #return $this;
    }

    public function renameTableObject($oldName, $newName)
    {
        # @return hFramework

        # @description
        # <h2>Rename a Database Table in the Reflection API</h2>
        # <p>
        #   Call this method to rename a table at runtime.
        # </p>
        # <code>
        #   $this-&gt;renameTableObject('hDirectories', 'hFolders');
        # </code>
        # <p>
        #   The preceding example renames the <var>hDirectories</var> table
        #   to <var>hFolders</var> within Hot Toddy's reflection API at runtime.
        # </p>
        # <p class='hDocumentationNote'>
        #   <b>Note:</b> Renaming a database table in the reflection API does not
        #   also rename a database table in the actual database, for that you'd
        #   need to either execute the appropriate SQL query, or use the appropriate
        #   <a href='/Hot Toddy/Documentation?hDatabase' class='code'>hDatabase</a> API.
        # </p>
        # @end
        foreach ($this->tables as $i => $table)
        {
            if ($table == $oldName)
            {
                $this->tables[$i] = $newName;
            }
        }

        if (isset($this->tableObjects[$oldName]))
        {
            $this->tableObjects[$newName] = $this->tableObjects[$oldName];
            unset($this->tableObjects[$oldName]);

            $this->tableObjects[$newName]->renameThisTable($newName);
        }

        $this->hDatabase->renameTableCache($oldName, $newName);

        #return $this;
    }

    public function deleteTableObject($table)
    {
        # @return hFramework

        # @description
        # <h2>Remove a Database Table from the Reflection API</h2>
        # <p>
        #   To remove a table from the database reflection API at runtime, call this method.
        # </p>
        # <code>
        # $this-&gt;deleteTableObject('hFiles');
        # </code>
        # <p>
        #   The preceding example demonstrates how the <var>hFiles</var> table would be
        #   removed from Hot Toddy's reflection API at run time.
        # </p>
        # <p class='hDocumentationNote'>
        #   <b>Note:</b> Removing a database table from the reflection API does not
        #   also remove a database table from the actual database, for that you'd
        #   need to either execute the appropriate SQL query, or use the appropriate
        #   <a href='/Hot Toddy/Documentation?hDatabase' class='code'>hDatabase</a> API.
        # </p>
        # @end

        foreach ($this->tables as $i => $tableName)
        {
            if ($tableName == $table)
            {
                unset($this->tables[$i]);
            }
        }

        unset($this->tableObjects[$table]);

        $this->hDatabase->deleteTableCache($table);

        #return $this;
    }

    public function execute()
    {
        # @return void

        # @description
        # <h2>Executing Hot Toddy</h2>
        # <p>
        #   This method starts off a chain of method calls to assemble the document, or the
        #   retrieve a document from the file system.
        # </p>

        # <h4>Including the <var>hTemplate</var> Plugin</h4>
        # <p>
        #   The <a href='/Hot Toddy/Documentation?hTemplate' class='code'>hTemplate</a> plugin provides two things.
        # </p>
        # <ol>
        #   <li>Customized document template on a per-directory basis</li>
        #   <li>Template scripting capabilities.</li>
        # </ol>
        # <p>
        # See: <a href='/Hot Toddy/Documentation?hTemplate' class='code'>hTemplate</a>
        # </p>
        # <p>
        #   This plugin is included as though it is part of the hFramework object.  i.e., calls to methods
        #   existing in the <a href='/Hot Toddy/Documentation?hTemplate' class='code'>hTemplate</a> object
        #   can be made anywhere framework-wide.
        # </p>
        $this->plugin('hTemplate', array(), true);

        # <h4>Inspecting the File Path</h4>
        # <p>
        #   The call to <a href='/Hot Toddy/Documentation?hFile/hFilePath#inspectFilePath' class='code'>inspectFilePath()</a>
        #   looks at the file path and determines what file is being requested.
        #   If the request path is for a directory, e.g., <var>http://www.example.com/something</var>
        #   <a href='/Hot Toddy/Documentation?hFile/hFilePath#inspectFilePath' class='code'>insepectFilePath()</a>
        #   will look for a default file, such as <var>index.html</var>
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hFile/hFilePath' class='code'>hFile/hFilePath</a>
        # </p>
        $this->inspectFilePath();

        # <h4>Command Line Interface</h4>
        # <p>
        #   The framework variable <var>hShellCLI</var> alerts the script to whether or not the framework is
        #   being executed from the context of a command line interface.
        # </p>

        if (!$this->hShellCLI(false))
        {
            # <p>
            #   If the framework is not executed from the context of a command line interface, the output buffer is
            #   turned on, if settings say to do so.
            # </p>
            $this->setOutputBuffer();

            # <p>
            #   Also, the custom session handler is included.  This plugin sets up a custom
            #   database in/out API for session data, and makes it so that PHP sessions
            #   are stored in the database.  Session data can then be queried in the database
            #   like any other data.  A session is automatically created for every user, whether
            #   that user is logged in or not.
            # </p>
            # <p>
            #   See: <a href='/Hot Toddy/Documentation?hUser/hUserSession' class='code'>hUser/hUserSession</a>
            # </p>
            if (!isset($GLOBALS['hUserSessionLoaded']))
            {
                $this->plugin('hUser/hUserSession');
            }
        }

        # <h2>User Authentication API</h2>
        # <p>
        #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php' class='code'>hUserAuthentication</a>
        #   provides security and permissions API.  For example, if you want
        #   to know whether or not a user has permission to access a document, you'd call <var>$this-&gt;hasPermission()</var>
        # </p>
        # <p>
        # The method <var>hasPermission()</var> is located in the <var>hUserAuthenticationLibrary</var>. This plugin
        # also provides a variety of other things, like whether or not a user is a member of a group. Whether or not
        # a user is logged in.
        # </p>
        # <p>
        # See: <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php' class='code'>hUser/hUserAuthentication/hUserAuthentication.library.php</a>
        # </p>
        # <p>
        # This plug-in is included as though it is part of the hFramework object.  i.e., calls to methods
        # existing in the
        # <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php' class='code'>hUserAuthentication</a>
        # object can be made anywhere framework-wide.
        # </p>
        $this->library('hUser/hUserAuthentication', array(), true);

        # <h2>Logging User Activity</h2>
        # <p>
        # <var>hUserActivityLogEnabled</var> is a framework variable, set it to <var>true</var> to
        # enable logging of user activity (it is <var>false</var>, by default).
        # </p>
        if ($this->isLoggedIn() && $this->hUserActivityLogEnabled(false))
        {
            # <p>
            # This plug-in logs all modifications a user makes to the framework,
            # be it editing a document, a calendar, a user account.  All activity is recorded and
            # can be accessed in either the Console application or the Contacts application.
            # </p>
            # <p>
            # See: <a href='/Hot Toddy/Documentation?hUser/hUserActivityLog' class='code'>hUser/hUserActivityLog</a>
            # </p>
            $this->plugin('hUser/hUserActivityLog', array(), true);
        }

        # <h2>Framework Variables</h2>
        # <p>
        # Framework variables come from a variety of sources, beginning with configuration files for
        # the whole framework, then for a hostname, then for a template, filtering down in specificity.
        # </p>
        # <p>
        # Although rarely needed or used, framework variables may also be stored in the database in
        # the database table <var>hFrameworkVariables</var>.  Variables stored in the database overwrite
        # previously set variables for the framework or hostname.
        # </p>
        # <p>
        # Retrieves key value pairs from the hFrameworkVariables database table and creates framework
        # variables for each pair.
        # </p>
        $this->setVariables(
            $this->hFrameworkVariables->selectColumnsAsKeyValue(
                array(
                    'hFrameworkVariable',
                    'hFrameworkValue'
                )
            )
        );

        # <h2>Domain, Folder, and File Templates</h2>
        # <p>
        # The method <a href='/Hot Toddy/Documentation?hTemplate#setDocumentTemplate' class='code'>setDocumentTemplate()</a>
        # is defined in <a href='/Hot Toddy/Documentation?hTemplate' class='code'>hTemplate.php</a>, and it
        # looks at template configurations.
        # </p>
        # <p>
        # It looks to see if the document template should be updated based on the folder.  If not,
        # it simply retrieves meta-data for the template already set in place for the hostname.
        # </p>

        $this->setDocumentTemplate();

        # <h2>Framework Command Line Interface</h2>
        # <p>
        # If the framework is executed in the context of a command line interface, the code
        # branches off and executes shell-specific components.
        # </p>
        if ($this->hShellCLI(false))
        {
            # <h4><var>hFileDocument</var> From the Shell</h4>
            # <p>
            # <a href='/Hot Toddy/Documentation?hFile/hFileDocument' class='code'>hFileDocument</a>
            # provides in/out for documents and document meta data
            # such as retrieving file title, <var>$this-&gt;getFileTitle($hFileId)</var> and a method
            # that assembles HTML headers using a template and various framework variables.
            # </p>
            # <p>
            # This plugin is not always included when accessing the framework through a
            # browser.  For example, it is not loaded when accessing a binary document.
            # </p>
            # <p>
            # It is included when using the command line interface since some shell scripts
            # use APIs it provides.
            # </p>
            # <p>
            # See: <a href='/Hot Toddy/Documentation?hFile/hFileDocument' class='code'>hFile/hFileDocument</a>
            # </p>
            # <p>
            # This plugin is included as though it is part of the hFramework object.  i.e.,
            # calls to methods existing in the
            # <a href='/Hot Toddy/Documentation?hFile/hFileDocument' class='code'>hFileDocument</a>
            # object can be made anywhere framework-wide.
            # </p>
            $this->plugin('hFile/hFileDocument', array(), true);

            # <h4>The <var>hShell</var> Plug-in</h4>
            # <p>
            # <var>hShell</var> is a plug-in that provides the foundation API for using Hot Toddy through
            # a command line interface.
            # </p>
            # <p>
            # See: hShell
            # </p>
            $this->plugin('hShell');
        }
        else
        {
            # <h2>Framework Browser Interface</h2>
            # <p>
            # The <var>getFile()</var> method call queries the framework's database file system for the file
            # specified in the request.  See <var>getFiles()'s</var> documentation for more information.
            # </p>
            $this->getFile();

            # <h4>HTML Documents</h4>
            # <p>
            # The framework may be outputting any type of document, a PDF document, a Word
            # document, an H.264 video stream.  But, HTML, obviously is a big part of what the
            # framework provides.  If the document to be output is an HTML document, the <var>$html</var>
            # variable will be used as that document is assembled, even as various PHP template files are included,
            # those template files will append template content to the <var>$html</var> variable.
            # If the document is not an HTML document, this variable will not be used at all.
            # </p>

            $html = '';

            # <h3>The Template Path</h3>
            # <p>
            # If there is a template path it will be stored in <var>hTemplatePath</var>, a framework variable.
            # Its value will depend on a variety of factors.  A default template is applied at the
            # hostname level in the
            # <a href='/Hot Toddy/Documentation?hFile/hFileDomain' class='code'>hFileDocument</a>hFileDomain</a> plugin, where the
            # template is first determined by analyzing the hostname and looking up the default
            # template associated with that hostname in the <var>hFileDomains</var> database table.
            # </p>
            # <p>
            # Once a template is determined by the hostname, the framework variable <var>hTemplateId</var>
            # is set.  Then, later, in a call to <a href='/Hot Toddy/Documentation?hTemplate#setDocumentTemplate' class='code'>setDocumentTemplate()</a> (located in the
            # <var>hTemplate</var> plugin), the path is analyzed to see whether or not a different template has
            # been applied to the folder.  If a different template has been applied to the folder,
            # a new <var>hTemplateId</var> is assigned along with other meta data about the template
            # including <var>hTemplatePath</var>, which is retrieved from the database table
            # <var>hTemplates</var>.
            # </p>
            # <p>
            # <var>hTemplatePath</var> can be changed at any point after the automatic assignment based on
            # either hostname or directory.  To completely disable framework templates, you can
            # set <var>hTemplatePath</var> to nil.  You'd do this if you wanted to provide an HTML document
            # (or another type of document) yourself.
            # </p>
            # <p>
            # <var>hTemplatePath</var> is expected to be either <var>nil</var> or a
            # path to a PHP script that exists in either the <var>Hot Toddy</var> folder, or the <var>Plugins</var>
            # folder.  Template PHP scripts expect a variable, <var>$html</var> to already exist, and the
            # template PHP script should assign to the <var>$html</var> variable the content that will be the document.
            # </p>

            if ($this->hTemplatePath)
            {
                # <h4>Getting the Path to a PHP Template Script</h4>
                # <p>
                # A call to <a href='/Hot Toddy/Documentation?hFile/hFilePath#getIncludePath' class='code'>$this-&gt;getIncludePath()</a>
                # (a method defined in <a href='/Hot Toddy/Documentation?hFile/hFilePath' class='code'>hFilePath</a>) determines
                # whether or not the template path exists in the <var>Hot Toddy</var> folder or the <var>Plugins</var>
                # folder.  Template PHP scripts are expected to follow the naming convention <var><i>Plugin Name</i>.template.php</var>
                # and exist in the same folder as the plug-in that uses the template.
                # </p>

                $path = $this->getIncludePath($this->hServerDocumentRoot.$this->hTemplatePath);

                # <p>
                # If the PHP template script does not exist, you'll get a blank page or an error message
                # stating the template doesn't exist, depending on your framework error settings.
                # If you get a blank page, the error will be logged to the database and will appear
                # in both the Console application and the <var>hErrorLog</var> database table.
                # </p>

                $path = $this->insertSubExtension(
                    $path,
                    'mobile',
                    $this->userAgent->interfaceIdiomIsPhone
                );

                if (file_exists($path))
                {
                    # <h3>In Page WYSIWYG Editor</h3>
                    # <p>
                    # <a href='/Hot Toddy/Documentation?hEditor/hEditor.library.php' class='code'>hEditorLibrary</a>
                    # is a WYSIWYG editor that sets itself up within any
                    # given HTML page of the website.  It functioning properly depends on it being correctly
                    # configured (see <a href='/Hot Toddy/Documentation?hEditor/hEditor.library.php' class='code'>hEditorLibrary</a>
                    # for configuration information).  The
                    # WYSIWYG editor can be explicitly turned on via the framework variable <var>hEditorTemplateEnabled</var>
                    # or via the existence of a <var>$_GET</var> argument of the same name, <var>hEditorTemplateEnabled</var>.
                    # </p>
                    if ($this->isLoggedIn() && ($this->hEditorTemplateEnabled('auto') === true || $this->hEditorTemplateEnabled('auto') === 1 || isset($_GET['hEditorTemplateEnabled'])))
                    {
                        # <p>
                        # To access and use the WYSIWYG editor, the user must be logged in, and have
                        # read-write privileges on the document they wish to edit.
                        # </p>
                        if ($this->hFiles->hasPermission($this->hEditorTemplateFileId($this->hFileId), 'rw'))
                        {
                            # <p>
                            # The plugin <a href='/Hot Toddy/Documentation?hEditor/hEditorTemplate' class='code'>hEditorTemplate</a> sets up the WYSIWYG editor.
                            # </p>
                            # <p>
                            # See: <a href='/Hot Toddy/Documentation?hEditor/hEditorTemplate' class='code'>hEditor/hEditorTemplate</a>
                            # </p>

                            $this->hEditor = $this->library('hEditor');
                            $this->hEditor->wysiwyg();

                            //$this->hPlugin('hEditor/hEditorTemplate');
                        }
                    }

                    # <h3>Including the PHP Template Script</h3>
                    # <p>
                    # The PHP script referenced in the <var>hTemplatePath</var> framework variable is included
                    # with a call to <var>include_once</var>, meaning the template script is included and executed
                    # in the context of the <var>execute()</var> method.
                    # </p>
                    # <p>
                    # The template should do something with the value of the framework variable <var>hFileDocument</var>,
                    # such as to assimilate it into a template and append the resulting document to the <var>$html</var>
                    # variable. The value of <var>hFileDocument</var> will be reset to <var>nil</var> immediately following
                    # template script execution.
                    # </p>

                    include_once $path;

                    # <p>
                    # <var>hFileDocument</var> is set to <var>nil</var> so that it can be reused.
                    # </p>

                    $this->hFileDocument = '';

                    # <h3>Including Core Metrics Analytics</h3>
                    # <p>
                    # CoreMetrics is a commercial, comprehensive analytics API.  To set up
                    # CoreMetrics, you should first define your <var>clientId</var> in the framework variable
                    # <var>hCoreMetricsClientId</var>.  CoreMetrics is not included if the framework is being
                    # used in the context of a stand-alone desktop application.
                    # </p>
                    if ($this->hCoreMetricsClientId(nil) && !$this->hDesktopApplication(false))
                    {
                        # <p>
                        # See: <a href='/Hot Toddy/Documentation?hCoreMetrics/hCoreMetricsTag' class='code'>hCoreMetrics/hCoreMetricsTag</a>
                        # </p>
                        $this->plugin('hCoreMetrics/hCoreMetricsTag');
                    }

                    # <h3>Including Google Analytics</h3>
                    # <p>
                    #   To use Google Analytics, simply specify your Google Analytics
                    #   id in the framework variable <var>hGoogleAnalytics</var>.  This should be in the form:
                    # </p>
                    # <code>
                    # hGoogleAnalytics: "UA-XXXXXX-X"
                    # </code>
                    # <p>
                    #   Contrary to Google's recommendation that the Google Analytics code be included
                    #   in the <head> of the document, it is included in the <body> of the document just
                    #   before the closing <var>&lt;/body&gt;</var> tag, so that
                    #   slowdowns or outages in the Google Analytics service do not affect the load time
                    #   of your site.
                    # </p>

                    if ($this->hGoogleAnalytics(false) && !$this->hDesktopApplication(false))
                    {
                        # <p>
                        #   See: <a href='/Hot Toddy/Documentation?hGoogle/hGoogleAnalytics' class='code'>hGoogle/hGoogleAnalytics</a>
                        # </p>
                        $this->plugin('hGoogle/hGoogleAnalytics');
                    }

                    # <p>
                    #   By now, most of the body of the document has been generated and is assigned
                    #   to the <var>$html</var> variable.  The <var>hFileDocument</var> variable may now have additional
                    #   HTML to be appended to the body of content from the other plug-ins included (Core Metrics,
                    #   Google Analytics, et al).  Any additional content is not assimilated into the document.
                    # </p>

                    $body = $html.$this->hFileDocument;

                    # <h3>Automatic, Universal Support for H.264</h3>
                    # <p>
                    #   The <var>parseBody()</var> method looks for things like the inclusion of HTML5 <var>&lt;video&gt;</var> and
                    #   <var>&lt;audio&gt;</var> tags.  The framework has built-in support for H.264 video, for example.
                    #   If you include a properly encoded H.264 video in <var>&lt;video&gt;</var> tags, the framework will
                    #   automatically include a 3rd-party JavaScript library called <var>MediaElement.js</var>, which
                    #   then makes H.264 support universal by dynamically using a Flash player in browsers
                    #   that do not support H.264 natively, and using <var>&lt;video&gt;</var> in browsers that do support
                    #   H.264 natively.
                    # </p>
                    $this->parseBody($body);

                    # <h3>Assembling HTML Headers</h3>
                    # <p>
                    #   If the framework variable <var>hFileHTMLHeaders</var> is <var>true</var> (it is, by default), HTML headers
                    #   are automatically assembled from various framework variable configurations and prepended to the document.
                    # </p>

                    $this->hFileDocument = ($this->hFileHTMLHeaders(true)? $this->getFileHeaders() : '').$body;

                    # <h3>Parsing the Document and Caching</h3>
                    # <p>
                    #   If the <var>hFileDocumentParseEnabled</var> framework variable is <var>true</var> (it is, by default), the
                    #   document will be automatically parsed for all paths that point internally (within the
                    #   same site), and those paths will be automatically URL-encoded, as needed, and most paths will be appended
                    #   with a GET argument:
                    # </p>
                    # <code>
                    # ?hFileLastModified=<i>Unix Timestamp</i>
                    # </code>
                    # <p>
                    #   When a file is called with the <var>hFileLastModified</var> argument appended to the URL,
                    #   it is set to be cached indefinitely, vastly improving performance.  If the file is updated, the
                    #   value of the <var>hFileLastModified</var> argument is also updated, forcing the cached version
                    #   of the document to be automatically refreshed.
                    # </p>

                    if ($this->hFileDocumentParseEnabled(true))
                    {
                        // parseDocument() is defined later in this document.
                        $this->hFileDocument = $this->parseTemplateMarkup($this->hFileDocument);
                        $this->hFileDocument = $this->parseDocument($this->hFileDocument);
                    }

                    # <h3>Closing Markup</h3>
                    # <p>
                    #   If the framework variable <var>hFileCloseMarkup</var> is <var>true</var> (it is, by default), the
                    #   HTML document will be automatically closed with <var>&lt;/body&gt;</var> and <var>&lt;/html&gt;</var> tags, and
                    #   some benchmarking will also be included in HTML comments.
                    # </p>
                    if ($this->hFileCloseMarkup(true))
                    {
                        # <h4>Diagnostics: Outputting Loaded Paths</h4>
                        # <p>
                        #   If the framework variable <var>hFrameworkOutputLoadedPaths</var> is set
                        #   to <var>true</var> (it is not, by default), then a list of every path loaded
                        #   during framework execution will be included in the HTML comments.
                        # </p>
                        # <p class='hDocumentationWarning'>
                        #   <b>Warning:</b> This statistical component has not been updated for some time and
                        #   might not be accurate.
                        # </p>

                        if ($this->hFrameworkOutputLoadedPaths(false))
                        {
                            $html = "<!-- Loaded Paths ".count($this->loadedPaths)."\n";

                            foreach ($this->loadedPaths as $path)
                            {
                                $html .= "  {$path}\n";
                            }

                            $html .= "-->\n";

                            $this->hFileDocument .= $html;
                        }

                        # <h4>Appending Additional Content Just Before the Closing Tags</h4>
                        # <p>
                        #   Sometimes you have need of appending content just before the close of the <var>&lt;body&gt;</var>
                        #   tag for purposes of presentation (absolute positioning with CSS, for example, as
                        #   opposed to fixed).  If you have need of this, you can do so by assigning markup
                        #   to the <var>hFileDocumentAppend</var> framework variable.
                        # </p>

                        if ($this->hFileDocumentAppend(nil))
                        {
                            $this->hFileDocument .= $this->hFileDocumentAppend;
                        }

                        # <h4>Including the Closing Tags</h4>
                        # <p>
                        #   The template
                        #   <a href='/System/Framework/Hot Toddy/hFramework/HTML/Close.html' target='_blank' class='code'>/hFramework/HTML/Close.html</a>
                        #   includes framework benchmarks in HTML
                        #   comments as well as the closing <var>&lt;/body&gt;</var> and <var>&lt;/html&gt;</var> tags.
                        # </p>
                        $this->hFileDocument .= $this->getTemplate(
                            dirname(__FILE__).'/HTML/Close.html',
                            array(
                                'benchmark' => $this->getBenchmark()
                            )
                        );
                    }

                    # <h4>Diagnostics: Analyzing Database Queries</h4>
                    # <p>
                    #   If the framework variable <var>hDatabaseOptimize</var> is turned on (it's not, by default),
                    #   database optimization data will be gathered and created in the <var>hDatabaseBenchmark</var>
                    #   database table.  This is useful for pinpointing inefficient queries so that
                    #   database tables can be optimized with indexes, better data typing and so on.
                    # </p>
                    if ($this->hDatabaseOptimize(false))
                    {
                        $this->plugin('hDatabase/hDatabaseBenchmark');
                    }
                }
                else
                {
                    $this->warning("The template '{$path}' does not exist.", __FILE__, __LINE__);
                }
            }
            else if (!$this->hFileSystemDocument)
            {
                # <h2>Custom Documents</h2>
                # <p>
                #   Documents can be customized and output however you like. If you set <var>hTemplatePath</var>
                #   to <var>nil</var>, you have the option of being more responsible for how a document is created and
                #   output.
                # </p>
                # <p>

                $headers = '';

                $body = $html.$this->getDocument();

                # <h3>Adding Pre-Built HTML Headers to a Custom Document</h3>
                # <p>
                # 	Optionally, you can use the framework's automatic HTML header generation, to do so,
                #   you must set <var>hFileHTMLHeaders</var> to <var>true</var>, it is <var>false</var> by default.
                # </p>
                # <h4>Automatic, Universal H.264 Support in Custom Documents</h4>
                # <p>
                # 	Custom documents are also analyzed for the presence of <var>&lt;video&gt;</var> and <var>&lt;audio&gt;</var>
                # 	tags (if the document is an HTML document), and if either of these tags are discovered, the <var>MediaElement.js</var>
                # 	library is automatically included.
                # </p>

                $this->parseBody($body);

                if ($this->isHTMLDocument() && $this->hFileHTMLHeaders(false))
                {
                    $headers = $this->getFileHeaders();
                }

                $this->hFileDocument = $headers.$body;

                # <h3>Parsing the Document and Caching in a Custom Document</h3>
                # <p>
                #   If the document is an HTML document, as with templated documents, custom
                #   documents are also analyzed automatically for internal pointing paths, which
                #   are automatically URL-encoded (if necessary) and appended with a unix timestamp
                #   of the time the file was last modified for purposes of caching.
                # </p>
                if ($this->isHTMLDocument() && $this->hFileDocumentParseEnabled(true))
                {
                    $this->hFileDocument = $this->parseTemplateMarkup($this->hFileDocument);
                    $this->hFileDocument = $this->parseDocument($this->hFileDocument);
                }

                # <h3>Closing a Custom Document</h3>
                # <p>
                #   If the is an HTML document, and if headers are enabled, then closing the document automatically
                #   is also enabled (but can be explicitly disabled, if desired).
                # </p>
                # <p>
                #   See: <a href='/System/Framework/Hot Toddy/hFramework/HTML/Close.html' target='_blank' class='code'>/hFramework/HTML/Close.html</a>
                # </p>

                if ($this->isHTMLDocument() && ($this->hFileHTMLHeaders(false) || $this->hFileCloseMarkup(false)))
                {
                    if ($this->hFileDocumentAppend(nil))
                    {
                        $this->hFileDocument .= $this->hFileDocumentAppend;
                    }

                    $this->hFileDocument .= $this->getTemplate(
                        dirname(__FILE__).'/HTML/Close.html',
                        array(
                            'benchmark' => $this->getBenchmark(),
                            'hDesktopApplication' => $this->hDesktopApplication(false)
                        )
                    );
                }
            }

            if (!$this->hFileSystemDocument)
            {
                # <h2>Outputting a Document</h2>
                # <p>
                #   If the document is dynamically created (as HTML, or possibly even another type
                #   of document), the entire document will have been created and stored in the variable
                #   <var>hFileDocument</var>.
                # </p>
                # <p>
                #   To get the size of the document, you merely have to look at the length of
                #   <var>hFileDocument</var>.
                # </p>

                $this->hFileContentLength = strlen($this->hFileDocument);

                # <p>
                #   Hot Toddy creates all relevant HTTP headers itself with a call to the <var>setHTTPHeaders()</var>
                #   method, which is defined in the <var>hHTTP</var> plug-in.
                # </p>

                $this->setHTTPHeaders();

                # <p>
                #   After the HTTP headers are set, the final, assembled document is output and execution ends.
                # </p>
                echo $this->hFileDocument;
            }
            else
            {
                # <h2>Outputting a File System Document</h2>
                # <p>
                #   <var>hFileSystemDocument</var> is a boolean framework variable that Hot Toddy users to
                #   determine whether or not the document being requested is a dynamic HTML document that
                #   will come from the framework's database, or whether the document is a file system document
                #   that will come from the server's file system.  If <var>hFileSystemDocument</var> is <var>true</var>,
                #   the document comes from the server's file system.
                # </p>

                # <p>
                #   A call to <a href='/Hot Toddy/Documentation?hFile/hFilePath#getFileSystemPath' class='code'>getFileSystemPath()</a>
                #   returns the full path to the document on the
                #   server's file system.
                # </p>
                # <p>
                #   <a href='/Hot Toddy/Documentation?hFile/hFilePath#getFileSystemPath' class='code'>getFileSystemPath()</a>
                #   is defined in <a href='/Hot Toddy/Documentation?hFile/hFilePath' class='code'>hFilePath</a>
                # </p>

                $file = $this->getFileSystemPath();

                # <p>
                #   Before a file system document is attempted to be accessed, some sanity checks are
                #   done.  Does the file exist?  Can Hot Toddy ready the file?  If not, some errors
                #   are logged to the error console (<var>hErrorLog</var>).
                # </p>
                if (file_exists($file))
                {
                    if (is_readable($file))
                    {
                        # <h3>Zlib Compression on a File System Document</h3>
                        # <p>
                        #   Compressing output can have unwanted effects and, unfortunately, is not
                        #   supported in some browsers for some types of files.
                        #   In addition, it imposes a tremendous amount of additional
                        #   processing burden on the server.  For these reasons, zlib compression
                        #   is explicitly disabled when it comes to serving up files directly from
                        #   the file system.
                        # </p>
                        ini_set('zlib.output_compression', false);

                        # <h3>Time Limits on File System Documents</h3>
                        # <p>
                        #   If the user is downloading a large file, or streaming a video clip,
                        #   there should be no time limit, so the time limit is explicitly
                        #   disabled for file system documents.
                        # </p>

                        set_time_limit(0);

                        # <h3>File Size</h3>
                        # <p>
                        #   Browsers will need to know the correct file size to estimate download
                        #   times and to conform with HTTP standards.  Some versions of Internet
                        #   Explorer, in fact, won't download a file without a correct file
                        #   size.  For this reason, the file size is retrieved and assigned to
                        #   the <var>hFileSize</var> variable (even if <var>hFileSize</var>) is
                        #   already set. <var>hFileSize</var> will be used in the HTTP headers to
                        #   pass the file size along to the browser.
                        # </p>

                        $this->hFileSize = filesize($file);

                        # <h4>Updating the File Size Stored in the Database</h4>
                        # <p>
                        #   To ensure that the value for <var>hFileSize</var> stored in the database
                        #   table <var>hFileProperties</var> is correct, it is automatically refreshed.
                        #   In theory, doing this only when the file is modified and then
                        #   pulling the database-cached value should work just fine, but this fails to
                        #   capture when a file is modified outside of framework tools.
                        # </p>
                        # <p>
                        #   The API for calling framework variables such as <var>hFileSize</var> as
                        #   though they are methods is defined in
                        #   <a href='/Hot Toddy/Documentation?hFramework/hFrameworkVariables#fuseObjects' class='code'>hFramework/hFrameworkVariables</a>
                        # </p>

                        $this->hFileSize(
                            $this->hFileSize,
                            $this->hFileId,
                            true
                        );

                        # <h3>Streaming Video and Audio Content</h3>
                        # <p>
                        #   If the file is a movie or audio, the framework will provide
                        #   the ability to stream that content if the browser supports
                        #   HTML5 <var>&lt;video&gt;</var> and <var>&lt;audio&gt;</var> or Flash.
                        #   Hot Toddy supports simple streaming via HTTP byte range headers.
                        #   This will also work with other audio/video plugins like QuickTime, or
                        #   other products that support HTTP byte range headers.
                        # </p>

                        $movie = false;
                        $audio = false;

                        switch (true)
                        {
                            case $this->isVideo($this->hFileName, $this->hFileMIME):
                            {
                                # <h4>Throttle and Burst Size for Video</h4>
                                # <p>
                                #   The throttle and burst size control how much data
                                #   is sent at once for streaming content, and slows how
                                #   often data is sent to avoid flooding the pipe at once.
                                #   Throttle can be customized in the framework variable
                                #   <var>hFileThrottle</var> and burst size can be customized
                                #   in the framework variable <var>hFileBurstSize</var>.
                                # </p>
                                # <p>
                                #   See: <a href='/Hot Toddy/Documentation?hFile/hFilePath#isMovie' class='code'>isMovie()</a>
                                # </p>

                                $this->hFileThrottle = 320;
                                $this->hFileBurstSize = 500;
                                $movie = true;
                                break;
                            }
                            case $this->isAudio($this->hFileName, $this->hFileMIME):
                            {
                                # <h4>Throttle and Burst Size for Audio</h4>
                                # <p>
                                #   Similarly, throttle and burst size can be controlled for audio
                                #   files.  Hot Toddy uses smaller throttle and burst for streaming
                                #   audio content.
                                # </p>
                                # <p>
                                #   See: <a href='/Hot Toddy/Documentation?hFile/hFilePath#isAudio' class='code'>isAudio()</a>
                                # </p>

                                $this->hFileThrottle = 84;
                                $this->hFileBurstSize = 120;
                                $audio = true;
                                break;
                            }
                        }

                        # <h4>HTTP Byte Ranges: Streaming Media, Pause and Resume for File Downloads</h4>
                        # <p>
                        #   The <var>range()</var> method analyzes the request header, <var>HTTP_RANGE</var>,
                        #   for start and end byte markers.  i.e., the point in the
                        #   file to begin the download and the point in the file to
                        #   end download.
                        # </p>
                        # <p>
                        #   This is used both for streaming HTML5, Flash, et al, audio/video,
                        #   and for pausing and resuming arbitrary file downloads.
                        # </p>
                        # <p>
                        #   See: <a href='/Hot Toddy/Documentation?hHTTP#range' class='code'>range()</a>
                        # </p>

                        $this->range();

                        # <p>
                        #   If there was an <var>HTTP_RANGE</var>, the value will be stored in the
                        #   framework variable <var>hFileRange</var>.  The value will look something like
                        #   this: <var>2908234-2340983234</var>
                        # </p>
                        # <p>
                        #   The number on the left is the point at which to begin output of
                        # 	the file, and the number of the right is the point at which to end
                        # 	output of the file.
                        # </p>

                        $range = $this->hFileRange(false);

                        # <p>
                        #   Hot Toddy's range support is triggered by whether or not
                        #   <var>hFileRange</var> is empty.
                        # </p>

                        $useRange = !empty($range);

                        # <h3>Caching When a URL Specifies a <var>hFileLastModified</var> Argument</h3>
                        # <p>
                        #   If there is a <var>hFileLastModified</var> argument in the <var>$_GET</var> array and the
                        #   content is not video or audio, then the browser is instructed to indefinitely
                        # 	cache the content.  If the content is audio or video a cache is enabled,
                        # 	but much more passively.  Audio and video content simply get the HTTP header
                        #   <var>Pragma: public</var>, which enables the browser to decide when to cache and for
                        #   how long.
                        # </p>

                        if (isset($_GET['hFileLastModified']) && !$movie && !$audio)
                        {
                            $this->hFileDisableCache = false;
                            $this->hFileEnableCache  = true;
                            $this->hFileCacheExpires = strtotime('+10 Years');
                        }

                        # <p>
                        #   However, if a range is present, or the content is audio or video, the cache is explicitly
                        #   disabled.
                        # </p>

                        if ($useRange || $movie || $audio)
                        {
                            // Why are there two variables?
                            // There probably shouldn't be.

                            $this->hFileDisableCache = true;
                            $this->hFileEnableCache = false;
                        }

                        # <h3>Overriding the Document Source</h3>
                        # <p>
                        #   The framework variable <var>hFrameworkOverrideDocumentOutput</var> can be set if
                        #   you want to provide the content of a document yourself.
                        # </p>
                        # <p>
                        #   One example of where this is used is in
                        #   <a href='/Hot Toddy/Documentation?hFile/hFileLibrary' class='code'>hFile/hFileLibrary</a>, a plug-in
                        #   that provides output for the <var>Library</var> folder, in addition to arbitrary
                        #   content in the <var>Hot Toddy</var> and <var>Plugins</var> folders.  That plug-in,
                        #   among other things, is responsible for outputting nearly all JavaScript and CSS
                        #   content served by the framework.
                        # </p>
                        # <p>
                        #   Since this plug-in serves all JavaScript and CSS content, it also has the ability
                        #   to compress this content, and to dynamically parse the content using the
                        #   framework's template scripting.  In that scenario, a file system path is set and
                        #   provided for each document, but the actual content of each document can be
                        #   overridden and provided by the <a href='/Hot Toddy/Documentation?hFile/hFileLibrary' class='code'>hFileLibrary</a> plugin when the
                        #   content is compressed or processed for template scripting.
                        # </p>
                        # <p>
                        #   So, instead of originating from the file system, document content is provided
                        #   in the <var>hFileDocument</var> variable.
                        # </p>

                        if ($this->hFrameworkOverrideDocumentOutput(false))
                        {
                            $this->hFileSize = strlen($this->hFileDocument);
                        }

                        # <p>
                        #   The framework variable <var>hFileContentLength</var> is used to set the HTTP header
                        #   <var>Content-Length</var>, which tells the browser what the total size of the body of the
                        #   content will be in bytes.  This header <b>must</b> be accurate, as Internet Explorer
                        #   will provide all sorts of headaches if the value provided is incorrect.  The value
                        #   assigned to <var>hFileContentLength</var> will be either the file size stored in
                        #   <var>hFileSize</var> or it will be the range of bytes to be served.
                        # </p>

                        if ($useRange)
                        {
                            $this->hFileContentLength = $this->hFileRangeEnd - $this->hFileRangeStart + 1;
                        }
                        else
                        {
                            $this->hFileContentLength = $this->hFileSize;
                        }

                        # <h3>Output Buffering</h3>
                        # <p>
                        #   If the content buffer is turned on, it is turned off and the output buffer is completely expunged.
                        #   Output buffers should not be used when outputting a file system document.
                        # </p>

                        @ob_end_clean();

                        # <h3>HTTP Headers</h4>
                        # <p>
                        #   All relevant HTTP headers are created with a call to <a href='/Hot Toddy/Documentation?hHTTP#setHTTPHeaders' class='code'>setHTTPHeaders()</a>, which is
                        #   defined in the <a href='/Hot Toddy/Documentation?hHTTP' class='code'>hHTTP</a> plug-in.
                        # </p>
                        $this->setHTTPHeaders();

                        # <h3>Overriding Document Output</h3>
                        # <p>
                        #   If the <var>hFrameworkOverrideDocumentOutput</var> framework variable is
                        #   true, the contents of <var>hFileDocument</var> are output and execution ends.
                        # </p>

                        if ($this->hFrameworkOverrideDocumentOutput(false))
                        {
                            echo $this->hFileDocument;
                        }
                        else
                        {
                            # <h3>Outputting File System Documents, Partial or Whole</h3>
                            # <p>
                            #   As a file system document is output, Hot Toddy is careful not to
                            #   run into memory limits, or to overwhelm the network pipe.  All documents
                            #   are broken into smaller chunks and then output.  This prevents PHP from
                            #   reaching its memory limit.
                            # </p>

                            $sent = 0;

                            // Open the file and create a handle.

                            $handle = fopen($file, 'r');

                            # <h4>Prepping for Outputting a Partial Document Using a Range</h4>
                            # <p>
                            #   If a partial document is being output, as with a byte range, the beginning of the
                            #   range will be defined in the framework variable <var>hFileRangeStart</var> and the end will be
                            #   defined in <var>hFileRangeEnd</var>.
                            # </p>

                            if ($this->hFileRangeStart(0) > 0)
                            {
                                # <p>
                                #   The file pointer is moved to the start of the byte range with a call to <var>fseek()</var>
                                # </p>

                                fseek($handle, $this->hFileRangeStart);

                                # <p>
                                #   If the document being output is Flash Video content, additional Flash headers are output.
                                #   This functionality has not yet been tested and might not function correctly.
                                # </p>

                                if ($this->hFileMIME == 'video/x-flv')
                                {
                                    echo 'FLV'.pack('C', 1).pack('C', 1).pack('N', 9).pack('N', 9);
                                }
                            }

                            # <h4>Outputting a Full Document</h4>
                            # <p>
                            #   If no range is defined, the entire file will be output.
                            # </p>

                            if (!$useRange)
                            {
                                # <p>
                                #   Using the PHP function <var>file_get_contents()</var>, fails miserably when
                                #   there is a very large file for output, and can easily exceed memory
                                #   limits.  To avoid this, the file is output in smaller chunks (1MB chunks).
                                # </p>

                                $blocksize = (1 << 10); // 1M chunks

                                # <p>
                                #   Output of the file continues as long as the amount <var>$sent</var> is less than the total size
                                #   of the file.
                                # </p>
                                # <p>
                                #   If the connection is abruptly aborted (canceled by the user, or whatever else may cause the
                                #   connection to fail), output is halted and script execution ends.
                                # </p>

                                while ($sent < $this->hFileSize && !(connection_aborted() || connection_status() == 1))
                                {
                                    // Output the block.

                                    echo fread($handle, $blocksize);
                                    $sent += $blocksize;
                                }
                            }
                            else
                            {
                                # <h4>Outputting a Document Using a Range</h4>
                                # <p>
                                #   The implementation used by Hot Toddy was gathered from several 3rd-party sources (Thanks!),
                                #   I do not fully understand what it does, hence the lack of commentary.
                                # </p>

                                $speed = 0;
                                $chunk = 1;
                                $throttle = $this->hFileThrottle(false);
                                $burst = $this->hFileBurst(nil)? $this->hFileBurst * 1024 : 0;
                                $buffer = $this->hFileBufferSize(8) * 1024;

                                while (!(connection_aborted() || connection_status() == 1) && $sent < $this->hFileContentLength)
                                {
                                    if ($sent >= $burst)
                                    {
                                        $speed = $throttle;
                                    }

                                    if ($sent + $buffer > $this->hFileContentLength)
                                    {
                                        $buffer = $this->hFileContentLength - $sent;
                                    }

                                    echo fread($handle, $buffer);

                                    $sent += $buffer;

                                    if ($speed && ($sent - $burst > $speed * $chunk * 1024))
                                    {
                                        sleep(1);
                                        $chunk++;
                                    }
                                }
                            }

                            // Clean up after ourselves by closing the file handle and exit.
                            fclose($handle);
                        }
                    }
                    else
                    {
                        // Throw a framework error logging that the file couldn't be accessed.
                        // Framework errors can be viewed in the Console application or in the
                        // hErrorLog database table.  You may access the Console application from
                        // any page on the website via the keyboard shortcut Option + Shift + C

                        $this->warning(
                            "'{$file}' could not be accessed because it is not readable.",
                            __FILE__,
                            __LINE__
                        );
                    }
                }
                else
                {
                    // Let the user know that the file is missing.

                    header('Content-type: text/html; charset=utf-8');
                    header('Content-disposition: inline; filename=error.html');

                    echo $this->getTemplate(
                        dirname(__FILE__).'/HTML/Missing File.html'
                    );

                    $this->warning(
                        "'{$file}' could not be accessed because it does not exist.",
                        __FILE__,
                        __LINE__
                    );
                }
            }

            // If the output buffer is turned on, flush it.
            if ($this->hServerOutputBuffer(false))
            {
                @ob_end_flush();
            }
        }

        # <h2>Logging File Activity</h2>
        # <p>
        #   If logging activity is enabled, the very last thing Hot Toddy will do before
        #   exiting will be to log file activity in the <var>hFileActivity</var> database table.
        # </p>

        if ($this->hFileActivityEnabled(false) && $this->hFileActivityId)
        {
            $this->hFileActivity->update(
                array(
                    'hFileId'                 => $this->hFileId,
                    'hFilePath'               => $this->hFilePath,
                    'hFileWildcardPath'       => $this->hFileWildcardPath,
                    'hFileReferrer'           => $_SERVER['HTTP_REFERER'],
                    'hUserId'                 => $this->isLoggedIn()? $_SESSION['hUserId'] : 0,
                    'hFileExecutionBenchmark' => $this->getBenchmark(),
                    'hDatabaseQueryBenchmark' => $this->hDatabaseQueryBenchmark,
                    'hDatabaseQueryCount'     => $this->hDatabaseQueryCount
                ),
                $this->hFileActivityId
            );
        }

        # @end

        exit;
    }

    public function isEditable()
    {
        # @return boolean

        # @description
        # <p>
        #   The <var>isEditable()</var> method is used to determine whether or not a given file is editable.  That is to
        #   say, whether or not the user can edit the page using the in-page editor loaded in the
        #   <a href='/Hot Toddy/Documentation?hEditor/hEditor.library.php' class='code'>hEditor/hEditor.library.php</a> plugin.
        # </p>
        # @end

        return (
            $this->isLoggedIn() && (
                $this->hEditorTemplateEnabled('auto') !== false &&
                $this->hFiles->hasPermission(
                    $this->hEditorTemplateFileId($this->hFileId),
                    'rw'
                )
            )
        );
    }

    public function isHTMLDocument()
    {
        # @return boolean

        # @description
        # <h2>Determining Whether This Document is HTML</h2>
        # <p>
        #   A call to <var>isHTMLDocument()</var> determines whether or not the current
        #   document is an HTML document.  The default MIME type in Hot Toddy is HTML,
        #   if no MIME type is specified.
        # </p>
        # @end
        switch ($this->hFileMIME('text/html'))
        {
            case 'text/html':
            case 'application/xhtml+xml':
            {
                return true;
            }
        }

        return false;
    }

    public function parseBody($document)
    {
        # @return hFramework

        # @description
        # <h2>Detecting HTML5 Media Elements</h2>
        # <p>
        #   <var>parseBody()</var> determines whether a document contains HTML5 <var>&lt;video&gt;</var>
        #   or <var>&lt;audio&gt;</var> elements. If either is detected, the framework variable
        #   <var>hFileMediaElement</var> is set to <var>true</var>.
        # </p>
        # <p>
        #   Consequently, if the <var>hFileMediaElement</var> framework variable is true, JS and CSS for
        #   the <var>MediaElement</var> library is included automatically in the HTTP headers to ensure
        #   universal support for multimedia content.
        # </p>
        # @end

        if ($this->isHTMLDocument())
        {
            preg_match('/\<video|\<audio/siU', $document, $matches);

            if (isset($matches[0]) && $matches[0] == '<video')
            {
                $this->hFileMediaElement = true;
            }
        }

        #return $this;
    }

    public function parseDocument($document)
    {
        # @return string
        # <p>
        #   The parsed document with all paths expanded, URL encoded, and appended with file
        #   last modified times, where applicable.  Additionally, certain segments of the document
        #   will be XML CData escaped, if this is an XHTML document.
        # </p>
        # @end

        # @description
        # <h2>Document and Document Path Analysis</h2>
        # <p>
        #   <var>parseDocument()</var> analyzes the document and corrects certain things.
        # </p>
        # <h3>XML CData</h3>
        # <p>
        #   If the document is a true XHTML document, <var>&lt;style&gt;</var> and <var>&lt;script&gt;</var>
        # 	tags are analyzed and XML CData wrappers are dynamically added as is necessary.
        # </p>
        # <h3>Dynamic File Paths</h3>
        # <p>
        #   If the document is HTML or XHTML, each path in the document is analyzed and
        #   inspected more closely.  Embedded references to file ids are expanded.  Embedded
        # 	file id references can be thought of as dynamic linking.  When a file is saved,
        # 	Hot Toddy converts paths to files stored in the database to file ids like so:
        # </p>
        # <p>
        #   <var>/index.html</var> becomes <var>{/hFileId:1}</var>
        # </p>
        # <p>
        #   When the file is output, the references are converted back into file paths.
        # </p>
        # <p>
        #   <var>{/hFileId:1}</var> becomes <var>/index.html</var> again. This is done so that if a
        #   filename is modified, or the location of a file is modified, links to that file will
        #   still function.
        # </p>
        # <h3>Document Caching</h3>
        # <p>
        #   Furthermore, to fully take advantage of client-side caching, each path linking to a
        #   file stored in the file system is prefixed with the argument <var>?hFileLastModified=</var>
        # 	and then the value of the last modified time is added.  When that file is subsequently
        # 	requested, the presence of the <var>hFileLastModified</var> argument triggers aggressive,
        # 	indefinite caching of the document.  The idea being to force the browser to pull the
        # 	locally caches version until the path is updated by virtue of the <var>?hFileLastModified=</var>
        # 	argument being refreshed with a newer timestamp.
        # </p>
        # <h3>URL Encoding</h3>
        # <p>
        # 	Finally, another important operation performed by this method is checking the encoding
        # 	on files and paths, making it possible for a much wider range of special characters to
        # 	appear in folder and file names.  For example, space characters are converted to + (plus
        # 	signs).  Ampersands in file or folder names are converted to %26, and so on.
        # </p>
        # <h3>Disabling <var>parseDocument()</var></h3>
        # <p>
        # 	To speed things up, calling <var>parseDocument()</var> can be explicitly disabled, but
        # 	the trade-off comes with a steep price, as you also lose a great deal of caching,
        # 	conversion and encoding.
        # </p>
        # @end

        if ($this->hFileMIME == 'application/xhtml+xml')
        {
            # Automatically add XML CData wrappers where appropriate
            # if XHTML is enabled.
            $document = preg_replace_callback(
                array(
                    '/(<style[^>]*?>)(.*?)(<\/style>)/siU',
                    '/(<script[^>]*?>)(.*?)(<\/script>)/si'
                ),
                array($this, 'XMLCDataCallback'),
                $document
            );
        }

        switch ($this->hFileMIME('text/html'))
        {
            case 'text/html':
            case 'application/xhtml+xml':
            {
                # Parse paths:
                #  Correct unencoded ampersands in paths.
                #  Adjust the document path if Hot Toddy is installed in a sub-folder.
                #  Add last modified time to images so images can be indefinitely cached.
                $document = preg_replace_callback(
                    "/(href|action|src|background|poster)\=(\'|\")(.*)(\'|\")/iU",
                    array(
                        $this,
                        'attributePathCallback'
                    ),
                    $document
                );

                break;
            }
        }

        switch ($this->hFileMIME('text/html'))
        {
            case 'text/html':
            case 'application/xhtml+xml':
            case 'text/css':
            {
                $document = preg_replace_callback(
                    "/(url\()(\"|\')(.*)(\"|\')(\))/iU",
                    array(
                        $this,
                        'CSSPathCallback'
                    ),
                    $document
                );
                break;
            }
        }

        return $document;
    }

    public function attributePathCallback($matches)
    {
        $attribute = $matches[1];
        $quote     = $matches[2];
        $path      = $matches[3];

        # @return string

        # @description
        # <h2>Processing Paths in HTML Attributes</h2>
        # <p>
        #   <var>attributePathCallback()</var> is a helper method for <var>parseDocument()</var>
        # </p>
        # <p>
        #   The call to <a href='/Hot Toddy/Documentation?hFile/hFilePath#isFrameworkPath' class='code'>isFrameworkPath()</a>
        #   determines if the path is an internally pointing URL,
        #   i.e., a URL that points within the framework, rather than to an external
        #   site or resource.
        # </p>
        # <p>
        #   If the path is a URL to an external site or resource, nothing is done
        # 	to the path.
        # </p>
        # <p>
        #   The <a href='/Hot Toddy/Documentation?hFile/hFilePath#isFrameworkPath' class='code'>isFrameworkPath()</a>
        # 	method is defined in <a href='/Hot Toddy/Documentation?hFile/hFilePath' class='code'>hFilePath</a>
        # </p>

        if ($this->isFrameworkPath($path))
        {
            # <p>
            #   <a href='/Hot Toddy/Documentation?hFile/hFilePath#makeFrameworkPath' class='code'>makeFrameworkPath()</a>
            #   looks much more closely at the path, doing things
            #   like encoding special characters and appending the <var>?hFileLastModified=</var>
            #   argument.
            # </p>
            # <p>
            #   The <a href='/Hot Toddy/Documentation?hFile/hFilePath#makeFrameworkPath' class='code'>makeFrameworkPath()</a>
            #   method is defined in <a href='/Hot Toddy/Documentation?hFile/hFilePath' class='code'>hFilePath</a>
            # </p>
            # @end

            $path = $this->makeFrameworkPath($path, true);
        }

        # Return the original attribute and quoted path post-processing.

        return $attribute.'='.$quote.$path.$quote;
    }

    public function CSSPathCallback($matches)
    {
        # @return string

        # @description
        # <h2>Processing Paths in Stylesheets</h2>
        # <p>
        #   The <var>CSSPathCallback()</var> method is a helper method for <var>parseDocument()</var>,
        #   it assists with paths found in style sheets.
        # </p>
        # @end

        $opening = $matches[1];
        $quote   = $matches[2];
        $path    = $matches[3];
        $closing = $matches[5];

        # This method is defined in hFile/hFilePath

        if (!$this->isFrameworkPath($path))
        {
            return $matches[0];
        }

        # This method is defined in hFile/hFilePath

        $path = $this->makeFrameworkPath($path);

        return $opening.$quote.$path.$quote.$closing;
    }

    public function XMLCDataCallback($matches)
    {
        # @return string

        # @description
        # <h2>Wrapping XML CData Islands</h2>
        # <p>
        #   The <var>XMLCDataCallback()</var> method is a helper method for <var>parseDocument()</var>,
        #   it assists with content that is to be wrapped with XML CDATA wrappers.
        # </p>
        # @end

        if (!empty($matches[2]))
        {
            return $matches[1]."<![CDATA[\n".$matches[2]."]]>".$matches[3];
        }

        return $matches[0];
    }

    protected function getFile()
    {
        # @return void

        # @description
        # <h2>Getting the File from the Database</h2>
        # <p>
        #   <var>getFiles()</var> queries the file database, gets the file, and loads the appropriate plugins.
        # </p>

        if (!$this->isLoggedIn())
        {
            # <h3>Logging in a User</h3>
            # <p>
            #   The <a href='/Hot Toddy/Documentation?hUser/hUserLogin/hUserLogin.library.php' class='code'>hUserLogin</a>
            #   library is used to authenticate users, this
            # 	library is included if the user is not logged in, and will catch any attempt to
            #   login.
            # </p>
            # <p>
            # 	When a login attempt occurs, it attempts to authenticate the user.  If authentication
            #   is successful, a session is created.  Once a session is created and active,
            # 	this plugin is not loaded again unless a session ends.
            # </p>
            # <p>
            # 	See: <a href='/Hot Toddy/Documentation?hUser/hUserLogin/hUserLogin.library.php' class='code'>hUser/hUserLogin/hUserLogin.library.php</a>
            # </p>
            # <p>
            # 	This plugin is included as though it is part of the hFramework object.  i.e.,
            # 	calls to methods existing in the
            #   <a href='/Hot Toddy/Documentation?hUser/hUserLogin/hUserLogin.library.php' class='code'>hUserLoginLibrary</a>
            #   object can be made anywhere framework-wide.
            # </p>
            $this->library('hUser/hUserLogin', array(), true);
            $this->login();
        }

        # <h2>Desktop Applications</h2>
        # <p>
        #   If the user is logged in and is a root user (or 'Developer') and there is a GET argument
        #   by the name of <var>hDesktopApplication</var>, this run si treated as though Hot Toddy is
        #   operating in desktop application mode by creating the framework variable <var>hDesktopApplication</var>.
        # 	Desktop application mode can be used to compile and extract
        # 	HTML, CSS, and JavaScript for use in a stand-alone, compiled desktop application.
        # </p>
        # <p>
        #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#isLoggedIn' class='code'>isLoggedin()</a> and
        #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#inGroup' class='code'>inGroup()</a>
        #   are defined in
        #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php' class='code'>hUser/hUserAuthentication</a>
        # </p>

        if ($this->isLoggedIn() && $this->inGroup('root') && isset($_GET['hDesktopApplication']))
        {
            $this->hDesktopApplication = true;
        }

        # <h2>Password-Protected Documents</h2>
        # <p>
        #   If the framework variable <var>hFilePasswordsEnabled</var> is set to <var>true</var>
        #   (it's <var>false</var>, by default), then the <a href='/Hot Toddy/Documentation?hFile/hFilePassword' class='code'>hFile/hFilePassword</a> plugin is included.
        #   The <a href='/Hot Toddy/Documentation?hFile/hFilePassword' class='code'>hFilePassword</a> plugin provides functionality that allows users to access password
        # 	protected documents.  Password-protected documents check user privileges, if a user does not
        # 	have 'read' access to the document, but there is a password set on the document, the user
        # 	will be presented with a form to enter the document's password.
        # </p>
        # <p>
        # 	This is most useful when a user has no website account, and thus, no explicit read privileges
        # 	for the file.
        # </p>
        # <p>
        #   A password-protected file allows access to a file without requiring a user account. The access
        # 	to the file can be temporary.  i.e., you can create a password for a file and set a time limit
        # 	on the password. When the time limit is up you can choose to either:
        # </p>
        # <ol>
        #   <li>Delete the password</li>
        #   <li>Delete the file</li>
        # </ol>
        # <p>
        #   Or you can have a simple password with no time limit constraints.
        # </p>
        # <p>
        # 	Finally, you can also impose a password on a file and force entry of that password even if
        #   a user does have an account, is logged in, and does have explicit read access to the file in
        # 	question, to add an additional layer of security for ultra-sensitive documents.  This,
        # 	however, is optional.  The default behavior is to only require password entry if a password
        # 	exist on the file and the user is otherwise unauthorized to access the file.
        # </p>
        # <p>
        # 	See: <a href='/Hot Toddy/Documentation?hFile/hFilePassword' class='code'>hFile/hFilePassword</a>
        # </p>

        if ($this->hFilePasswordsEnabled(false))
        {
            $this->plugin('hFile/hFilePassword');
        }
        else
        {
            # <h2>File Permissions</h2>
            # <p>
            #   If <var>hFilePasswordsEnabled</var> is <var>false</var> (passwords access to files is
            # 	disabled), the framework variable <var>hFileAuthorized</var> is
            # 	given a value to determine whether or not the user has permission to read (merely access)
            # 	the file.
            # </p>
            # <p>
            # 	If <var>hFilePasswordsEnabled</var> were, on the other hand, set to <var>true</var>,
            # 	the <var>hFileAuthorized</var> variable is set in the <a href='/Hot Toddy/Documentation?hFile/hFilePassword' class='code'>hFile/hFilePassword</a> plugin.
            # </p>
            # <p>
            # 	The method <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#hasPermission' class='code'>hasPermission()</a>
            # 	is defined in the <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php' class='code'>hUserAuthentication</a>
            # 	library.
            # </p>

            $this->hFileAuthorized = $this->hFiles->hasPermission($this->hFileId, 'r');
        }

        if ($this->hFileAuthorized)
        {
            # <h2>Retrieving the File From Hot Toddy's File System</h2>
            # <p>
            # 	If the user is authorized to access the file. The file is queried.  The SQL query pulls a
            # 	variety of data for the document from the database and then that data is imported to
            #   framework variables.
            # </p>
            # <p>
            #   The internal search engine referenced below is the Search application, or hSearch plug-in.
            # </p>
            # <p>
            #   The following framework variables are created...
            # </p>
            # <p>
            #   From the <var>hFiles</var> table:
            # </p>
            # <table>
            #   <tbody>
            #       <tr>
            #           <td>hUserId</td>
            #           <td>The user that owns the file.</td>
            #       </tr>
            #       <tr>
            #           <td>hFileParentId</td>
            #           <td>
            #               The parent file, this is used to create simple parent/child
            #               relationships commonly, this functionality is used to create
            #               breadcrumbs.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hPlugin</td>
            #           <td>If set, the plugin path.</td>
            #       </tr>
            #       <tr>
            #           <td>hFileCreated</td>
            #           <td>A timestamp referring to the time the file was created.</td>
            #       </tr>
            #       <tr>
            #           <td>hFileLastModified</td>
            #           <td>A timestamp referring to the time the file was last modified.</td>
            #       </tr>
            #   </tbody>
            # </table>
            # <p>
            #   From the <var>hFileDocuments</var> table:
            # </p>
            # <table>
            #   <tbody>
            #       <tr>
            #           <td>hFileDescription</td>
            #           <td>
            #               A short summary of the document, this content is included in the meta
            #               description element in the HTML headers, it's used in search results both
            #               in the framework's internal search and in external search engines.  And
            #               may also be used in a variety of other contexts.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hFileKeywords</td>
            #           <td>
            #               A list of comma separated keywords.  This content is included in the
            #               meta keywords element in the HTML headers, and is used by external search
            #               engines and the framework's search.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hFileTitle</td>
            #           <td>
            #               The title of the document.  This content is included in the <var>&lt;title&gt;</var> element
            #               in the HTML headers.  It is also used as the default title for the body
            #               of the document in the <var>&lt;h1&gt;</var> tag.  And the <var>hFileTitle</var>
            #               can also be used in a plethora of other applications.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hFileDocument</td>
            #           <td>
            #               The content of the document.  If the document is an HTML document, this will
            #               be an HTML snippet intended to snap into the main content area of a document.
            #               If the document is a PDF or OFfice document, the text content of document
            #               will be stored in this field for the internal search engine.
            #           </td>
            #       </tr>
            #   </tbody>
            # </table>
            # <p>
            #   From the <var>hFileHeaders</var> table:
            # </p>
            # <table>
            #   <tbody>
            #       <tr>
            #           <td>hFileCSS</td>
            #           <td>
            #               An additional field where CSS markup can be stored for a document.  The content
            #               specified here is added with other CSS content in the HTML headers.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hFileJavaScript</td>
            #           <td>
            #               An additional field where JavaScript markup can be stored.  The content specified
            #               here is added with other JavaScript content in the HTML headers.
            #           </td>
            #       </tr>
            #   </tbody>
            # </table>
            # <p>
            #   From the <var>hFileProperties</var> table:
            # </p>
            # <table>
            #   <tbody>
            #       <tr>
            #           <td>hFileMIME</td>
            #           <td>The MIME type of the document.  e.g., text/html</td>
            #       </tr>
            #       <tr>
            #           <td>hFileSize</td>
            #           <td>The size of the document in bytes (if applicable).</td>
            #       </tr>
            #       <tr>
            #           <td>hFileDownload</td>
            #           <td>
            #               A boolean flag, when set to 1, the browser will force a download dialogue to
            #               appear for the document.  The default is 0.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hFileIsSystem</td>
            #           <td>
            #               A boolean flag that indicates whether the document is a system document.
            #               System documents would be documents that are part of the framework itself,
            #               rather than a website hosted by the framework.  The flag is used to prevent
            #               users from accidentally deleting or modifying important system documents.
            #           </td>
            #       </tr>
            #       <tr>
            #           <td>hFileSystemPath</td>
            #           <td>
            #               This variable can be used to indicate where on the server the file resides,
            #               making it possible to override the framework's default behavior with regards
            #               to file storage.
            #           </td>
            #       </tr>
            #   </tbody>
            # </table>

            $data = $this->hDatabase->getAssociativeResults(
                $this->getTemplate(
                    dirname(__FILE__).'/SQL/getFile.sql',
                    array(
                        'hFileId' => $this->hFileId
                    )
                )
            );

            # <p>
            #   If the <var>hFileSystemPath</var> variable is empty, it is deleted so that
            #   the existing framework variable does not get overwritten with a nil value.
            # </p>

            if (empty($data['hFileSystemPath']))
            {
                unset($data['hFileSystemPath']);
            }

            # <p>
            #   If no MIME type is set the default is set to text/html.
            # </p>

            if (empty($data['hFileMIME']))
            {
                $data['hFileMIME'] = 'text/html';
            }

            if (!$this->hFileSystemDocument)
            {
                foreach ($data as $key => &$value)
                {
                    switch ($key)
                    {
                        case 'hFileDocument':
                        case 'hFileCSS':
                        case 'hFileJavaScript':
                        {
                            # <p>
                            #   The framework stores documents in the database with all HTML and UTF-8 characters
                            #   encoded as HTML entities, this method decodes the entities, making the content
                            #   useable HTML again.
                            # </p>

                            $value = hString::decodeHTML($value);
                            break;
                        }
                    }
                }
            }

            # <p>
            #   All of the data for the file gathered so far are transfered to framework variables
            #   with a call to <a href='/Hot Toddy/Documentation?hFramework/hFrameworkVariables#setVariables' class='code'>setVariables()</a>
            # </p>

            $this->setVariables($data);

            # <p>
            #   If this is a file system document, the <var>hTemplatePath</var> variable is set to nil to
            #   disable framework templates.
            # </p>

            if ($this->hFileSystemDocument)
            {
                 $this->hTemplatePath = '';
            }
            else
            {
                # <p>
                #   If this is not a file system document, additional plug-ins and variables
                #   needed for dynamic HTML content are setup with a call to <a href='#getDocumentFrameworks'>getDocumentFrameworks()</a>
                # </p>

                $this->getDocumentFrameworks();
            }

            # <h2>Per-File Framework Variables</h2>
            # <p>
            #   Framework variables can be specified at many different levels, similar to cascading style sheets.
            #   Framework variables can be set for the entire installation, for just a hostname, for just a template
            # 	for a particular plug-in, and also for individual files.
            # </p>
            # <p>
            #   A call to <a href='/Hot Toddy/Documentation?hFramework/hFrameworkVariables#setFileVariables' class='code'>setFileVariables()</a>
            #   retrieves and sets all framework variables associated with
            #   a particular file.
            # </p>
            # <p>
            # 	This method is defined in <a href='/Hot Toddy/Documentation?hFramework/hFrameworkVariables' class='code'>hFrameworkVariables</a>
            # </p>

            $this->setFileVariables();

            # <h2>Plug-ins</h2>
            # <p>
            #   If a <var>hPlugin</var> is specified on the file, the plug-in is loaded and executed.
            # 	Plug-ins allow a fantastic amount of flexibility in what Hot Toddy is able to do.
            # </p>
            # <p>
            #   See: <a href='/Hot Toddy/Documentation?hPlugin/hPlugin.library.php#hPlugin' class='code'>hPlugin()</a>
            # </p>

            if ($this->hPlugin)
            {
                $this->plugin($this->hPlugin);
            }
        }
        else
        {
            # <h2>If the User is Not Authorized...</h2>


            $this->hFileMIME = 'text/html';

            $this->getDocumentFrameworks();
            $this->hFileSystemDocument = false;

            $file = array(
                'hFileLastModified' => time(),
                'hFileCSS' => '',
                'hFileJavaScript' => '',
                'hFileKeywords' => '',
                'hFileDescription' => ''
            );

            if (!$this->hFilePassword(false))
            {
                if ($this->isLoggedIn())
                {
                    $this->setVariables($file);

                    # <p>
                    #   A call to
                    #   <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php#notAuthorized' class='code'>notAuthorized()</a>
                    #   shows the user Hot Toddy's default
                    #   message shown when a user does not have authorization to access a resource.
                    #   This message can be customized.  See <a href='/Hot Toddy/Documentation?hUser/hUserAuthentication/hUserAuthentication.library.php' class='code'>hUserAuthentication</a>
                    # </p>

                    $this->notAuthorized();

                    # <p>
                    #   The user does not have access to the resource, and cannot gain access without
                    #   the help of an administrator.  Therefore, the <var>h403</var> variable is set
                    #   to <var>true</var>, which is meant to mirror HTTP 403 "Forbidden", though I
                    #   don't actually send that response, since the user needs to see a custom error
                    #   message, and not the browser's default "Forbidden" error message.
                    # </p>

                    $this->h403 = true;
                    $this->hFileStatusCode = 403;
                }
                else
                {
                    $this->setVariables($file);
                    $this->notLoggedIn();
                }
            }
            else
            {
                $this->getPluginCSS('hFile/hFilePassword');
            }
        }

        # <h2>Logging File Statistics</h3>
        # <p>
        #   <var>hFileStatisticsEnabled</var> is a boolean framework variable it is set to <var>false</var> by default.
        #   Setting it to <var>true</var> loads the <a href='/Hot Toddy/Documentation?hFile/hFileStatistics' class='code'>hFileStatistics</a> plug-in, which gathers remedial
        #   statistics about how many times a file has been accessed, the last time it was accessed,
        #   and does this gathering in two ways.
        # </p>
        # <ol>
        #   <li>Globally for all files in the <var>hFileStatistics</var> database table.</li>
        #   <li>If a user is logged in, the files that user accesses are tracked in
        #   the hFileUserStatistics table.</li>
        # </ol>
        # <p>
        #   The information gathered is presently used to gage the popularity of a file.  The information gathered
        #   can not be used to reconstruct a linear path of access, since it does not log each access time
        #   individually, but rather, maintains an all-time access count, and the last time a file was accessed.
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hFile/hFileStatistics' class='code'>hFile/hFileStatistics</a>
        # </p>

        if ($this->hFileStatisticsEnabled(false))
        {
            $this->plugin('hFile/hFileStatistics');
        }

        # @end
    }

    public function getDocumentFrameworks()
    {
        # @return void

        # @description
        # <h2>Setting Up For Dynamic Content</h2>
        # <p>
        #   Some documents stored in Hot Toddy don't need all the extra stuff that's available
        #   in the <var>hFileDocument</var> plug-in.  The <var>hFileDocument</var> plug-in is only
        #   loaded when a document is an HTML document and is stored in the file database.
        # </p>
        # <p>
        #   Additionally, a default private plug-in can be loaded. The default private plug-in to
        #   load is specified in the <var>hPrivatePlugin</var> framework variable.
        # </p>
        # <h3>XHTML Documents</h3>
        # <p>
        #   If the <var>hFileXHTML</var> boolean framework variable is set to <var>true</var>
        #   (it is <var>false</var> by default),
        #   and the user agent supports XHTML.  The document will be set to the XHTML MIME type.
        # </p>

        if ($this->hFileMIME == 'text/html' && $this->hFileXHTML(false) && !empty($_SERVER['HTTP_ACCEPT']))
        {
            $accept = explode(',', $_SERVER['HTTP_ACCEPT']);

            if (in_array('application/xhtml+xml', $accept))
            {
                $this->hFileMIME = 'application/xhtml+xml';
            }
        }

        # <h3>Multi-Language Support</h3>
        # <p>
        #   If multiple languages are enabled via the <var>hFileLanguagesEnabled</var> boolean framework variable
        #   (set to false, by default), the <a href='/Hot Toddy/Documentation?hLanguage' class='code'>hLanguage</a> plug-in is included.
        # </p>
        # <p>
        #   The <var>hLanguage</var> plug-in provides a simple API for translating words and phrases via database
        #   tables <var>hLanguages</var> and <var>hLanguageText</var>.  These tables are set up with translations for English
        #   words or phrases using the application located at <var>/System/Applications/Languages/Text.html</var>
        # </p>
        # <p>
        #   Once a translation is entered, the language API can be used by calling <a href='/Hot Toddy/Documentation?hLanguage#translate' class='code'>$this->translate('Thing');</a>
        # </p>
        # <p>
        #   If Spanish were set as the site language, and various translations for the English word "Thing"
        #   entered.  <a href='/Hot Toddy/Documentation?hLanguage#translate' class='code'>$this->translate('Thing')</a> would return the Spanish translation 'Cosa'.
        # </p>
        # <p>
        #   If multiple languages are disabled, <a href='/Hot Toddy/Documentation?hLanguage#translate' class='code'>$this->translate()</a> will behave as though it is a framework
        #   variable, and whatever is passed as the first argument will be returned automatically.
        # </p>
        # <p>
        #   Changing the site language can be done in one of three ways.
        # </p>
        # <ol>
        #   <li>
        #       By setting GET argument hLanguageId to the desired numerical id corresponding to the language
        #       in the hLanguages database table.
        #   </li>
        #   <li>
        #       By setting the framework variable hLanguageId to the desired numerical id corresponding to the
        #       language in the hLanguages database table.
        #   </li>
        #   <li>
        #       With a GET argument or framework variable, the framework will attempt to automatically set a
        #       language based on the suffix of the hostname.  For example, www.example.de will result in
        #       German being set as the default language.
        #   </li>
        # </ol>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hLanguage' class='code'>hLanguage</a>
        # </p>
        # <p>
        #   When enabled, this plug-in is included as though it is part of the hFramework object.  i.e., calls to methods
        #   existing in the hLanguage.php object can be made anywhere framework-wide.
        # </p>

        if ($this->hFileLanguagesEnabled(false))
        {
            $this->plugin('hLanguage', array(), true);
        }

        # <h3>Document API</h3>
        # <p>
        #   <a href='/Hot Toddy/Documentation?hFile/hFileDocument' class='code'>hFile/hFileDocument</a>
        #   provides in/out for documents and document meta data
        #   such as retrieving file title, <a href='/Hot Toddy/Documentation?hFile/hFileDocument#getFileTitle' class='code'>$this->getFileTitle($fileId)</a> and a method
        #   that assembles HTML headers using a template and various framework variables.
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hFile/hFileDocument' class='code'>hFile/hFileDocument</a>
        # </p>
        # <p>
        #   This plug-in is included as though it is part of the hFramework object.  i.e.,
        #   calls to methods existing in the
        #   <a href='/Hot Toddy/Documentation?hFile/hFileDocument' class='code'>hFileDocument</a> object can be made anywhere
        #   framework wide.
        # </p>

        $this->plugin('hFile/hFileDocument', array(), true);

        # <h3>Breadcrumbs API</h3>
        # <p>
        #   If the boolean framework variable <var>hFileBreadcrumbsEnabled</var> is set to <var>true</var>
        #   (it's <var>false</var>, by default) The breadcrumb API in the plug-in
        #   <a href='/Hot Toddy/Documentation?hFile/hFileBreadcrumbs' class='code'>hFileBreadcrumbs</a> is included.
        # </p>
        # <p>
        #   Breadcrumbs are a common UI widget that make it easier for a user to find their way
        #   back to the home page after clicking on one or more items on a site.  Breadcrumbs typically
        #   display a navigational hierarchy.  In Hot Toddy, breadcrumbs are set-up by defining
        #   parent/child relationships between documents, and this is done by setting the hFileParentId
        #   column in the hFiles database table.  Graphically, this can be done in the editor
        #   application located at /Applications/Editor.
        # </p>
        # <p>
        #   By default, breadcrumbs display the page title defined in <var>hFileTitle</var>, but this can be
        #   overridden by specifying a title in the "Breadcrumbs Title" field in the editor application.
        #   Programatically, this is done by setting the hFileBreadcrumbTitle framework variable.
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hFile/hFileBreadcrumbs' class='code'>hFile/hFileBreadcrumbs</a>
        # </p>
        # <p>
        #   When enabled, this plug-in is included as though it is part of the hFramework object.  i.e.,
        #   calls to methods existing in the <a href='/Hot Toddy/Documentation?hFile/hFileBreadcrumbs' class='code'>hFileBreadcrumbs</a> object can be made anywhere
        #   framework wide.
        # </p>

        if ($this->hFileBreadcrumbsEnabled(false))
        {
            $this->plugin('hFile/hFileBreadcrumbs', array(), true);
        }

        # <h3>Lists API</h3>
        # <p>
        #   If the boolean framework variable <var>hFileListsEnabled</var> is set to <var>true</var> (it
        #   is <var>false</var>, by default). The list API defined in the plug-in <var>hList</var> is included.
        # </p>
        # <p>
        #   Lists can be thought of as any situation where you need to have a relationship between one
        #   document (a parent) and one or more other documents.  A common use case for this API is
        #   setting up "Related Documents".  In this situation you navigate to a page, which has one
        #   or more additional links to other documents in a side box under the heading "Related Documents".
        # </p>
        # <p>
        #   Lists can be useful for many other things, however.  The List API provided in hList makes
        #   methods available for getting lists and information about lists in simple programatical form,
        #   in addition to providing a default UI template for retrieving a list as HTML.
        # </p>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hList' class='code'>hList</a>
        # </p>
        # <p>
        #   When enabled, the <a href='/Hot Toddy/Documentation?hList' class='code'>hList</a> plug-in is included as though it is part of the hFramework object.  i.e.,
        #   calls to methods existing in the <a href='/Hot Toddy/Documentation?hList' class='code'>hList</a> object can be made anywhere
        #   framework-wide.
        # </p>

        if ($this->hFileListsEnabled(false))
        {
            $this->plugin('hList', array(), true);
        }

        if ($this->hCoreMetricsClientId)
        {
            $this->plugin('hCoreMetrics/hCoreMetricsAPI', array(), true);
        }

        # <h3>Private Plug-ins</h3>
        # <p>
        # If a private plug-in is defined in <var>hPrivatePlugin</var>, the plug-in path referenced
        # in the <var>hPrivatePlugin</var> framework variable is retrieved and assigned to the
        # <var>hPrivateFramework</var> framework variable.
        # </p>
        # <p>
        # <var>hPrivatePlugin</var> is typically used to define an object needed to provide business logic.
        # The <var>hPrivatePlugin</var> can be used to override or supplement framework stylesheets or javascript.
        # </p>
        # <p>
        # Additionally, reusable methods can be defined in the hPrivatePlugin.  Careful though,
        # when it's enabled, this plug-in is included as though it is part of the hFramework object.  i.e.,
        # calls to methods existing in the <var>hFileDocument.php</var> object can be made anywhere
        # framework-wide.  And since this plug-in is included to provide business logic, extra care must
        # be taken to avoid conflicting with methods already defined in other framework plug-ins.  So
        # avoid using method names that are in use in other core framework plug-ins.
        # </p>

        if ($this->hPrivatePlugin && !$this->hDesktopApplication(false))
        {
            $this->hPrivateFramework = $this->plugin($this->hPrivatePlugin, array(), true);

            if (!is_object($this->hPrivateFramework))
            {
                $this->hPrivateFramework = $this->plugin('hTemplate/hTemplateDefault', array(), true);    
            }
        }
        else
        {
            $this->hPrivateFramework = $this->plugin('hTemplate/hTemplateDefault', array(), true);
        }

        # <h3>Template Plug-ins</h3>
        # <p>
        # If the boolean framework variable hTemplatePluginsEnabled is set to true (it's false, by default),
        # the following code will look at the hTemplatePlugins database table to see if a plug-in has been set
        # and should be loaded for a given template.
        # </p>
        # <p>
        # To make a plug-in that loads for a given template, simply go into the hTemplatePlugins database table
        # and add the hTemplateId of the template (found in hTemplates database table), the hPlugin of the
        # plug-in (see the hPlugins database table), and indicate whether or not the plug-in is private.
        # </p>
        # @end

        if ($this->hTemplatePluginsEnabled(false))
        {
            if ($this->hDatabase->tableExists('hTemplatePlugins'))
            {
                $query = $this->hTemplatePlugins->select(
                    'hPlugin',
                    array(
                        'hTemplateId' => (int) $this->hTemplateId
                    )
                );

                foreach ($query as $data)
                {
                    $this->plugin($data['hPlugin'], array(), true);
                }
            }
        }
    }

    public function addLoadedPath($path)
    {
        # @return hFramework

        # @description
        # <h2>Diagnostics: Keeping Track of Loaded Resources</h2>
        # <p>
        # The <var>addLoadedPath()</var> method keeps track of paths used to load additional
        # content so that the number of items opened during framework execution can be tracked
        # for statistics, benchmarking, and diagnostic purposes.
        # </p>
        # @end

        if (!is_array($this->loadedPaths))
        {
            $this->loadedPaths = array();
        }

        if (!in_array($path, $this->loadedPaths))
        {
            array_push($this->loadedPaths, $path);
        }

        #return $this;
    }

    public function getGMTTime()
    {
        # @return integer

        # @description
        # <h2>Getting a Unix Timestamp in GMT</h2>
        # <p>
        # <var>getGMTTime() returns a GMT unix timestamp.
        # </p>
        # @end

        return ((time() - (-date('Z'))) * 1000);
    }

    public function getBenchmark()
    {
        # @return float

        # @description
        # <h2>Benchmarking Hot Toddy Execution Time</h2>
        # <p>
        # <var>getBenchmark()</var> returns the time of framework execution in milliseconds as a float.
        # </p>
        # @end

        return round((hFrameworkBenchmarkMicrotime() - hFrameworkBenchmarkStart), 3) * 1000;
    }

    public function bytes($bytes, $space = ' ')
    {
        # @return string

        # @description
        # <h2>Converting Bytes to the Bytes, Kilobytes, Megabytes, or Gigabytes.</h2>
        # <p>
        # <var>bytes()</var> converts an integer containing the number of bytes in a file to one of Bytes, Kilobytes,
        # Megabytes, or Gigabytes, appending the unit of measurement.
        # </p>
        # @end

        switch (true)
        {
            case ($bytes < pow(2,10)):
            {
                return $bytes.$space.'Bytes';
            }
            case ($bytes >= pow(2,10) && $bytes < pow(2,20)):
            {
                return round($bytes / pow(2,10), 0).$space.'KB';
            }
            case ($bytes >= pow(2,20) && $bytes < pow(2,30)):
            {
                return round($bytes / pow(2,20), 1).$space.'MB';
            }
            case ($bytes > pow(2,30)):
            {
                return round($bytes / pow(2,30), 2).$space.'GB';
            }
        }
    }

    public function getRandomString($length = 7, $randomUppercase = false, $nonAlphaNumericCharacters = false)
    {
        # @return string

        # @description
        # <h2>Generating Passwords and Other Random Strings</h2>
        # <p>
        # <var>getRandomString()</var> is used to generate a password, session id, or whatever may have need of
        # a random string of letters and numbers.  Lowercase "l" and the number "1" are left out to
        # avoid confusion.
        # </p>
        # @end

        $string = '';

        $alpha  = 'abcdefghijkmnopqrstuvwxyz';
        $pool = $alpha.'023456789';

        if ($nonAlphaNumericCharacters)
        {
            $pool .= '!@#$%^&*()~`{}[]:;<>,.?/+=-_';
        }

        $count = strlen($pool);

        while (strlen($string) <= $length)
        {
            $bit = substr($pool, rand(1, $count), 1);

            if (!is_numeric($bit) && strstr($alpha, $bit) && $randomUppercase && rand(1, 2) == 1)
            {
                $bit = strtoupper($bit);
            }

            $string .= $bit;
        }

        return $string;
    }

    public function decodeJSON($json)
    {
        # @return mixed

        # @description
        # <h2>Decode JSON</h2>
        # <p>
        # A call to <var>decodeJSON()</var> will decode the supplied JSON string
        # and return a PHP object, array, string, or whatever is dictated by the
        # supplied JSON.
        # </p>
        # @end

        if (!function_exists('json_decode'))
        {
            # PHP4, yuk!
            $this->setToPHP4();
            include_once 'Services/JSON.php';
        }

        $obj = json_decode(str_replace(array("\n", "\r"), '', $json), false);

        if ($obj === nil)
        {
            $lastError = json_last_error();

            switch ($lastError)
            {
                case JSON_ERROR_NONE:
                {
                    break;
                }
                case JSON_ERROR_DEPTH:
                {
                    $this->warning("JSON: The maximum stack depth has been exceeded");
                    break;
                }
                case JSON_ERROR_STATE_MISMATCH:
                {
                    $this->warning("JSON: Invalid or malformed JSON");
                    break;
                }
                case JSON_ERROR_CTRL_CHAR:
                {
                    $this->warning("JSON: Control character error, possibly incorrectly encoded");
                    break;
                }
                case JSON_ERROR_SYNTAX:
                {
                    $this->warning("JSON: Syntax error");
                    break;
                }
                case JSON_ERROR_UTF8:
                {
                    $this->warning("JSON: Malformed UTF-8 characters, possibly incorrectly encoded");
                    break;
                }
            }
        }

        $this->setToDefault();
        return $obj;
    }

    public function addClass(&$existing, $classToAdd)
    {
        # @return hFramework

        # @description
        # <h2>Add a Class Name to a Class Name</h2>
        # <p>
        #   Adds an HTML class name to an existing HTML class name for use
        #   in an HTML <var>class</var> attribute.
        # </p>
        # @end

        if (!isset($existing))
        {
            $existing = '';
        }

        if (empty($existing))
        {
            $existing = $classToAdd;
        }
        else
        {
            $existing .= ' '.$classToAdd;
        }

        #return $this;
    }

    public function get($argument, $default = 0)
    {
        # @return string

        # @description
        # <h2>Retrieving a GET Variable</h2>
        # <p>
        #   Returns the specified GET variable, if the variable is set. If the variable is not set
        #   then the value specified in <var>$default</var> is returned.
        # </p>
        # @end

        $arguments = func_get_args();

        if (count($arguments) > 1)
        {
            $default = array_pop($arguments);
        }

        foreach ($arguments as $argument)
        {
            $hArgumentName = 'h'.ucfirst($argument);

            if (isset($_GET[$argument]))
            {
                return $_GET[$argument];
            }
            else if (isset($_GET[$hArgumentName]))
            {
                return $_GET[$hArgumentName];
            }
        }

        return $default;
    }

    public function post($argument, $default = nil)
    {
        # @return string

        # @description
        # <h2>Retrieving a POST Variable</h2>
        # <p>
        #   Returns the specified POST variable, if the variable is set. If the variable is not set
        #   then the value specified in <var>$default</var> is returned.
        # </p>
        # @end

        $arguments = func_get_args();

        if (count($arguments) > 1)
        {
            $default = array_pop($arguments);
        }

        foreach ($arguments as $argument)
        {
            $hArgumentName = 'h'.ucfirst($argument);

            if (isset($_POST[$argument]))
            {
                return $_POST[$argument];
            }
            else if (isset($_POST[$hArgumentName]))
            {
                return $_POST[$hArgumentName];
            }
        }

        return $default;
    }

    public function fileUpload($argument, $attribute)
    {
        $hArgumentName = 'h'.ucfirst($argument);

        if (isset($_FILES[$argument]))
        {
            $file = $_FILES[$argument];
        }
        else if (isset($_FILES[$hArgumentName]))
        {
            $file = $_FILES[$hArgumentName];
        }
        else
        {
            return false;
        }

        switch ($attribute)
        {
            case 'temporaryName':
            case 'tmp_name':
            {
                return $file['tmp_name'];
            }
            case 'mime':
            case 'type':
            {
                return $file['type'];
            }
            case 'size':
            {
                return $file['size'];
            }
        }
    }
}

?>