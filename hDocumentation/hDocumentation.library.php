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
# <h1>Documentation API</h1>
# <p>
#   <var>hDocumentationLibrary</var> provides several methods that facilitate retrieving
#   documentation from the database.
# </p>
# @end

class hDocumentationLibrary extends hPlugin {

    private $methods = array();
    private $objectName;
    private $createFolders = true;
    private $createFiles = true;
    private $files = array();

    private $documentationPath;

    private $hFileUtilities;
    private $hFileCache;

    public function hConstructor($options)
    {


    }

    public function getFile($documentationFileId)
    {
        # @return array

        # @description
        # <h2>Getting a Documentation File</h2>
        # <p>
        #   Returns information associated with a documentation file from the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hDocumentationFiles/hDocumentationFiles.sql' class='code' target='_blank'>hDocumentationFiles</a>
        #   database table.  The following information is returned:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>Column</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hDocumentationFileId</td>
        #           <td>Numeric, unique Id for the documentation file</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationFile</td>
        #           <td>The path to the file.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationFileTitle</td>
        #           <td>The name of the file.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationFileDescription</td>
        #           <td>The file description.  This is the description associated with the class, and appears before the class definition.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hDocumentationFileClosingDescription</td>
        #           <td>The file description.  This is the description associated with the class, and appears after the class definition.</td>
        #       </tr>
        #   </tbody>
        # </table>
        # @end

        return $this->hDocumentationFiles->selectAssociative(
            array(
                'hDocumentationFileId',
                'hDocumentationFile',
                'hDocumentationFileTitle',
                'hDocumentationFileDescription',
                'hDocumentationFileClosingDescription'
            ),
            (int) $documentationFileId
        );
    }

    public function searchFiles($search)
    {
        # @return HTML
        # <p>
        #   The results of the search in the form of a template.  If no files are
        #   found, then a search of methods is done and the results of that
        #   search in the form of an HTML template is returned.
        # </p>
        # @end

        # @description
        # <h2>Searching Documentation Files</h2>
        # <p>
        #   Searches documentation files stored in
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hDocumentationFiles/hDocumentationFiles.sql' class='code' target='_blank'>hDocumentationFiles</a>
        #   for the string specified in <var>$search</var>.
        #   Search is performed on the <var>hDocumentationFileTitle</var> column with a right
        #   side wildcard.  For example: <var>WHERE `hDocumentationFileTitle` LIKE 'hFile%'</var>.
        #   This will bring back all files that begin with <var>hFile</var>.
        # </p>
        # <p>
        #   If no files are found, then methods are automatically searched.
        # </p>
        # @end
        $sql = $this->getTemplateSQL(
            array(
                'search' => $search
            )
        );

        $query = $this->hDatabase->getResults($sql);

        if (is_array($query) && count($query))
        {
            $this->prepFileResults($query);

            return $this->getTemplate(
                'Files',
                array(
                    'search' => $search,
                    'files' => $this->hDatabase->getResultsForTemplate($query)
                )
            );
        }
        else
        {
            return $this->searchMethods($search);
        }
    }

    public function searchMethods($search)
    {
        # @return HTML

        # @description
        # <h2>Searching Documentation Methods</h2>
        # <p>
        #   Searches documentation methods indexed in
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hDocumentationMethods/hDocumentationMethods.sql' class='code' target='_blank'>hDocumentationMethods</a>
        #   for the string specified in <var>$search</var>.
        #   Search is performed on the <var>hDocumentationMethodName</var> column with a right
        #   side wildcard.  For example: <var>WHERE `hDocumentationMethodName` LIKE 'getFile%'</var>.
        #   This brings back all methods that start with <var>getFile</var>.
        # </p>
        # @end

        $sql = $this->getTemplateSQL(
            array(
                'search' => $search
            )
        );

        # echo $sql."\n";

        $methods = $this->hDatabase->getResults($sql);

        $this->prepMethodResults($methods);

        $html = '';

        foreach ($methods as $method)
        {
            $method['methodSearch'] = true;

            $html .= $this->getTemplate('Method', $method);
        }

        return $this->getTemplate(
            'Method Results',
            array(
                'methods' => $html,
                'search' => $search,
                'count' => count($methods)
            )
        );
    }

    private function prepFileResults(&$query, $forTemplate = true)
    {
        # @return void

        # @description
        # <h2>Preparing File Results</h2>
        # <p>
        #   Prepares file results by doing
        #   <a href='/Hot Toddy/Documentation?hString#decodeHTML'>hString::decodeHTML()</a> on the
        #   <var>hDocumentationFileDescription</var> and <var>hDocumentationFileClosingDescription</var> fields,
        #   which are stored in the database with
        #   HTML special characters encoded as HTML entities, and before <var>hDocumentationFileDescription</var>
        #   or <var>hDocumentationFileClosingDescription</var> can be used in an HTML document, each has to be
        #   decoded back to HTML special characters.
        # </p>
        # @end

        if (is_array($query))
        {
            if ($forTemplate)
            {
                foreach ($query as $i => &$data)
                {
                    $data['hDocumentationFileDescription'] = hString::decodeHTML($data['hDocumentationFileDescription']);
                    $data['hDocumentationFileClosingDescription'] = hString::decodeHTML($data['hDocumentationFileClosingDescription']);
                }
            }
            else
            {
                $query['hDocumentationFileDescription'] = hString::decodeHTML($query['hDocumentationFileDescription']);
                $query['hDocumentationFileClosingDescription'] = hString::decodeHTML($query['hDocumentationFileClosingDescription']);
            }
        }
        else
        {
            $query = array();
        }
    }

    private function prepMethodResults(&$query, $forTemplate = true)
    {
        # @return void

        # @description
        # <h2>Preparing Method Results</h2>
        # <p>
        #   Prepares method results by doing
        #   <a href='/Hot Toddy/Documentation?hString#decodeHTML'>hString::decodeHTML()</a> on the
        #   <var>hDocumentationMethodDescription</var>, <var>hDocumentationMethodSignature</var>,
        #   <var>hDocumentationMethodBody</var>, <var>hDocumentationMethodReturnType</var>, and
        #   <var>hDocumentationMethodReturnDescription</var> fields.  These fields are stored in the database with
        #   HTML special characters encoded as HTML entities, and before any of them
        #   can be inserted in an HTML document, they have to be decoded back to HTML special characters.
        # </p>
        # @end
        if (is_array($query))
        {
            if ($forTemplate)
            {
                foreach ($query as $i => &$data)
                {
                    $data['hDocumentationMethodDescription'] = hString::decodeHTML($data['hDocumentationMethodDescription']);
                    $data['hDocumentationMethodSignature'] = hString::decodeHTML($data['hDocumentationMethodSignature']);
                    $data['hDocumentationMethodBody'] = hString::decodeHTML($data['hDocumentationMethodBody']);
                    $data['hDocumentationMethodReturnType'] = hString::decodeHTML($data['hDocumentationMethodReturnType']);
                    $data['hDocumentationMethodReturnDescription'] = hString::decodeHTML($data['hDocumentationMethodReturnDescription']);
                }
            }
        }
        else
        {
            $query = array();
        }
    }

    private function prepArgumentResults(&$query)
    {
        # @return void

        # @description
        # <h2>Preparing Argument Results</h2>
        # <p>
        #   Prepares argument results by doing
        #   <a href='/Hot Toddy/Documentation?hString#decodeHTML'>hString::decodeHTML()</a> on the
        #   <var>hDocumentationMethodArgumentDescription</var> and <var>hDocumentationMethodArgumentDefault</var>
        #   fields.  These fields are stored in the database with
        #   HTML special characters encoded as HTML entities, and before any of them
        #   can be inserted in an HTML document, they have to be decoded back to HTML special characters.
        # </p>
        # @end

        if (is_array($query))
        {
            foreach ($query as &$data)
            {
                $data['hDocumentationMethodArgumentDescription'] = hString::decodeHTML($data['hDocumentationMethodArgumentDescription']);
                $data['hDocumentationMethodArgumentDefault']     = hString::decodeHTML($data['hDocumentationMethodArgumentDefault']);
            }
        }
        else
        {
            $query = array();
        }
    }

    public function getFileTemplate($documentationFileId)
    {
        # @return HTML

        # @description
        # <h2>Getting a Documentation File</h2>
        # <p>
        #   This method <var>getFileTemplate()</var> returns the complete documentation for
        #   the specified <var>$documentationFileId</var>.  This is used to create permanent
        #   links to documentation files that can, in turn, be used in the documentation to
        #   reference other files or to quickly pull up the documentation for a given file.
        # </p>
        # @end

        $file = $this->getFile($documentationFileId);

        $this->prepFileResults($file, false);

        return $this->getTemplate(
            'Files',
            array_merge(
                array(
                    'search' => false,
                    'file' => true,
                    'methods' => $this->getMethodsTemplate($documentationFileId, true)
                ),
                $file
            )
        );
    }

    public function getMethods($documentationFileId)
    {
        # @return array

        # @description
        # <h2>Getting Documentation Methods</h2>
        # <p>
        #   Returns all methods associated with the specified <var>$documentationFileId</var>
        #   as an array, with all relevant fields decoded and ready to be inserted into HTML
        #   documents.
        # </p>
        # @end

        $query = $this->hDatabase->getResults(
            $this->getTemplateSQL(
                array(
                    'documentationFileId' => (int) $documentationFileId
                )
            )
        );

        $this->prepMethodResults($query);

        return $query;
    }

    public function getMethodsTemplate($documentationFileId, $staticPage = false)
    {
        # @return HTML

        # @description
        # <h2>Getting a Methods Template</h2>
        # <p>
        #   Returns an HTML template containing all the methods for the specified
        #   <var>$documentationFileId</var>.  The results are used for static documentation,
        #   or in file searches.
        # </p>
        # @end

        $methods = $this->getMethods((int) $documentationFileId);

        $html = '';

        foreach ($methods as $method)
        {
            $html .= $this->getTemplate('Method', $method);
        }

        $variables = array_merge(
            $this->getFile((int) $documentationFileId),
            array(
                'methodsHTML' => $html,
                'methods' => $this->hDatabase->getResultsForTemplate($methods),
                'staticPage' => $staticPage
            )
        );

        return $this->getTemplate(
            'Methods', $variables
        );
    }

    public function getMethodArguments($methodId)
    {
        # @return array

        # @description
        # <h2>Getting Method Arguments</h2>
        # <p>
        #   Returns an array of arguments and data about the arguments for the
        #   specified <var>$methodId</var>.
        # </p>
        # @end

        $query = $this->hDatabase->getResults(
            $this->getTemplateSQL(
                array(
                    'methodId' => (int) $methodId
                )
            )
        );

        $this->prepArgumentResults($query);

        return $this->hDatabase->getResultsForTemplate($query);
    }

    public function getDocumentationFileIdByMethodId($methodId)
    {
        # @return integer

        # @description
        # <h2>Get a Document File Id By Method Id</h2>
        # <p>
        #   Returns the <var>hDocumentationFileId</var> for the specified <var>$methodId</var>.
        # </p>
        # @end

        return (int) $this->hDocumentationMethods->selectColumn(
            'hDocumentationFileId',
            $methodId
        );
    }

    public function getDocumentationFileByDocumentationId($documentationFileId)
    {
        # @return string

        # @description
        # <h2>Get a Documentation File Path By Documentation Id</h2>
        # <p>
        #   Returns the <var>hDocumentationFile</var> (the path to the documentation file)
        #   for the specified <var>$documentationFileId</var>.
        # </p>
        # @end

        return $this->hDocumentationFiles->selectColumn(
            'hDocumentationFile',
            $documentationFileId
        );
    }

    public function getDocumentationFileByMethodId($methodId)
    {
        # @return string

        # @description
        # <h2>Get a Documentation File Path By Method Id</h2>
        # <p>
        #   Returns the <var>hDocumentationFile</var> (the path to the documentation file)
        #   for the specified <var>$methodId</var>
        # </p>
        # @end

        return $this->getDocumentationFileByDocumentationId(
            $this->getDocumentationFileIdByMethodId($methodId)
        );
    }

    public function getDocumentationFileType($file)
    {
        # @return string
        # <p>
        #   The type of documentation file.  One of: <var>plugin</var>, <var>library</var>,
        #   <var>listener</var>, or <var>shell</var>.
        # </p>
        # @end

        # @description
        # <h2>Get Documentation File Type</h2>
        # <p>
        #   Returns the type of documentation file for the specified <var>$file</var>,
        #   <var>$file</var> should be an <var>hDocumentationFile</var> (the path to the
        #   documentation file).
        # </p>
        # @end

        $fileName = basename($file);

        $plugin = explode('.', $fileName);

        $pluginName = $plugin[0];

        return ($plugin[1] != 'php')? $plugin[1] : 'plugin';
    }

    public function getMethodNameByMethodId($methodId)
    {
        # @return string

        # @description
        # <h2>Getting a Documentation Method Name By Method Id</h2>
        # <p>
        #   Returns the method name (<var>hDocumentationMethodName</var>) for the specified
        #   <var>$methodId</var>.
        # </p>
        # @end

        return $this->hDocumentationMethods->selectColumn(
            'hDocumentationMethodName',
            $methodId
        );
    }

    public function getNavigation()
    {
        # @return string

        # @description
        # <h2>Getting Documentation Navigation</h2>
        # <p>
        #    Returns plugin navigation for all plugins in the framework.  Plugin navigation
        #    is assembled in a series of lists divided by base plugin name.
        # </p>
        # @end

        $this->hFileCache = $this->library('hFile/hFileCache');

        $html = $this->hFileCache->getCachedDocument(
            'hDocumentationPluginNavigation',
            0,
            array(
                $this->hPlugins->getLastModified(),
                $this->hPluginsPrivate->getLastModified()
            )
        );

        if ($html === false)
        {
            $this->hFileUtilities = $this->library(
                'hFile/hFileUtilities',
                array(
                    'autoScanEnabled' => true,
                    'fileTypes' => array(
                        'php'
                    ),
                    'excludeFolders' => array(
                        'HTML',
                        'XML',
                        'JS',
                        'CSS',
                        'SQL',
                        'XHTML',
                        'TXT',
                        'PHP',
                        'Templates',
                        'hDatabaseStructure',
                        'Database'
                    )
                )
            );

            $files = $this->hFileUtilities->getFiles();

            $baseName = '';
            $basePath = '';
            $lastBaseName = '';

            $variables = array(
                'baseName' => array(),
                'paths' => array()
            );

            $baseNameCounter = 0;
            $pathCounter = 0;

            $basePaths = array();

            foreach ($files as $file)
            {
                $baseName = $this->getBaseName($file);
                $basePath = $this->getBasePath($file);

                if (in_array($basePath, $basePaths, true))
                {
                    continue;
                }

                array_push($basePaths, $basePath);

                if (empty($basePath))
                {
                    continue;
                }

                if (!empty($lastBaseName) && $lastBaseName != $baseName)
                {
                    $baseNameCounter++;
                    $pathCounter = 0;
                }

                $variables['baseName'][$baseNameCounter] = $baseName;
                $variables['paths'][$baseNameCounter]['basePath'][$pathCounter] = $basePath;
                $variables['paths'][$baseNameCounter]['name'][$pathCounter] = $this->getBaseObjectName($basePath);

                $lastBaseName = $baseName;

                $pathCounter++;
            }

            $html = $this->getTemplate(
                'Plugin Navigation',
                array(
                    'paths' => $variables
                )
            );

            $this->hFileCache->saveDocumentToCache(
                'hDocumentationPluginNavigation',
                0,
                $html
            );
        }

        return $html;
    }
}

?>