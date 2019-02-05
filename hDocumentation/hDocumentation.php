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
# <h1>Documentation</h1>
# <p>
#   Hot Toddy builds documentation directly into every source file.  A parser then
#   tokenizes each source file and extracts the documentation, isolates objects,
#   object methods, source code, and documentation written into the source code and
#   stores all of this in a few database tables.  This approach has the following
#   benefits:
# </p>
# <ol>
#   <li>
#       It makes documentation easy to search, in just a few seconds you can
#       search hundreds of source files and thousands of methods.
#   </li>
#   <li>
#       Keeping documentation in the source files themselves encourages documentation
#       to be written with the source code, and maintained with the source code, so
#       there is less of an excuse for separately maintained source code files to
#       be out-of-sync with the actual code.
#   </li>
# </ol>
# @end

class hDocumentation extends hPlugin {

    private $hDocumentation;
    private $hFileCache;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Displaying Documentation, Documentation Search</h2>
        # <p>
        #   The <var>hDocumentation</var> plugin is loaded for every page of Hot Toddy
        #   documentation you see.  It does things like include the style sheets, javascript
        #   and bring the various presentational bits associated with Hot Toddy's
        #   documentation API together.
        # </p>

        $this->hDocumentation = $this->library('hDocumentation');

        $html = '';

        $documentationFileId = 0;

        # <h3>Query Strings</h3>
        # <p>
        #   Query strings are used to link directly to documentation in Hot Toddy.
        #   For example: <var>http://www.example.com/Hot Toddy/Documentation?hDocumentation</var>.  The
        #   preceding path uses a query string to specify the path to the documentation
        #   file that you want to view.  The path that you specify is written as a
        #   Hot Toddy plugin path, which is to say, a path like
        #   <var>/Hot Toddy/hDocumentation/hDocumentation.php</var> becomes just
        #   <var>hDocumentation</var>.  And a path like
        #   <var>/Hot Toddy/hDocumentation/hDocumentation.library.php</var> becomes just
        #   <var>hDocumentation/hDocumentation.library.php</var>.
        # </p>
        # <p>
        #   Beyond Hot Toddy plugin paths, you can also use an <var>hDocumentationFileId</var>
        #   in the query string to link to a documentation file.  So if, for example,
        #   a file like <var>/Hot Toddy/hApplication/hApplication.library.php</var> has an
        #   <var>hDocumentationFileId</var> of 1, you can link to that file like this:
        #   <var>http://www.example.com/Hot Toddy/Documentation?1</var>
        # </p>
        # <h3>Methods as Anchors</h3>
        # <p>
        #   Each method in a documentation file is also set up to act as an anchor.  For example,
        #   you could link to this documentation as:
        #   <var>http://www.example.com/Hot Toddy/Documentation?hDocumentation#hConstructor</var>
        # </p>
        # <h3>"Private" Documentation</h3>
        # <p>
        #   Hot Toddy's documentation API also works with "private" plugins, so documentation
        #   written for business logic methods of a framework installation will also be tokenized
        #   and indexed in the documentation database.
        # </p>
        # @end

        if (!empty($_SERVER['QUERY_STRING']))
        {
            if (is_numeric($_SERVER['QUERY_STRING']))
            {
                $documentationFileId = (int) $_SERVER['QUERY_STRING'];
            }
            else
            {
                $path = hString::safelyDecodeURL($_SERVER['QUERY_STRING']);

                $plugin = $this->queryPlugin($path);

                if ($plugin['isPrivate'])
                {
                    $path = '/Plugins'.$plugin['path'];
                }
                else
                {
                    $path = '/Hot Toddy'.$plugin['path'];
                }

                $documentationFileId = $this->hDocumentationFiles->selectColumn(
                    'hDocumentationFileId',
                    array(
                        'hDocumentationFile' => $path
                    )
                );
            }
        }

        // This is very important, don't want to parse template markup in comments.
        $this->hFileDocumentParseEnabled = false;
        
        if (!empty($documentationFileId))
        {
            $html = $this->hDocumentation->getFileTemplate($documentationFileId);
        }
        
        $this->getPluginFiles();
        $this->getPluginCSS('/hDocumentation/CSS/Syntax Coloring', true);

        $this->hFileTitle = 'Hot Toddy Documentation';

        $pluginNavigation = '';

        if (empty($documentationFileId))
        {
            $this->hFileCache = $this->library('hFile/hFileCache');
            $pluginNavigation = $this->hDocumentation->getNavigation();
        }

        
        $this->hDocumentation->setUpTemplate();
        
        $this->hDocumentation->setDocument($html, $pluginNavigation);
    }
}

?>