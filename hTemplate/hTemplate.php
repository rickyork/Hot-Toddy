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
# <h1>Template API</h1>
# <p>
#   The methods of the <var>hTemplate</var> object are available globally from all plugins.
#   These methods will be available assuming you have not defined a new method of the same
#   name in your plugin.  For example, you can call the method defined in <var>hTemplate</var>
#   called <var>getTemplate()</var> from any plugin the method call would look like this:
#   <var>$this-&gt;getTemplate()</var>.
# </p>
# <p>
#   The <var>hTemplate</var> object provides two purposes:
# </p>
# <ol>
#   <li>It allows you to set a template for a folder and that folder's descendents.</li>
#   <li>
#       It provides a simple template scripting syntax that allows documents to be
#       managed separately in an MVC fashion.
#   </li>
# </ol>
# <p>
#   Templates can be further defined per-document and per-hostname.  The code needed to
#   set a template per-document would be done in a Hot Toddy plugin.  The code needed
#   set a template per-hostname is done in the <a href='/Hot Toddy/Documentation?hFile/hFileDomain' class='code'>hFile/hFileDomain</a> plugin.
# </p>
# @end

class hTemplate extends hPlugin {

    private $hTemplateLanguage;
    private $templateVariables;
    private $template;

    private $matchCurlyBraces = "/\{([^{}]+|(?R))*\}/"; // Matches properly nested curly brace sets
    private $templateCache = array();

    public function setDocumentTemplate()
    {
        # @return void

        # @description
        # <h2>Domain, Folder, and Document Templates</h2>
        # <p>
        #   Templates are defined in the <var>hTemplates</var> database table, each has a
        #   unique id which corresponds to a PHP file that bridges data with presentational
        #   content, typically HTML.
        # </p>
        # <p>
        #   In <var>hFile/hFileDomain</var>, the hostname for the site is looked up in the
        #   <var>hFileDomains</var> table and a default template is determined based on the
        #   hostname. If the framework hosts multiple domains, each domain can have its own
        #   unique template, or the domain can fallback on a default template.
        # </p>
        # <p>
        #   Therefore, in <var>hFile/hFileDomain</var>, the framework variable
        #   <var>hTemplateId</var> is set based on the hostname.
        # </p>
        # <p>
        #   Beyond setting a unique template for a hostname, the framework also allows you
        #   to set unique templates on directories. If a template is defined for a directory,
        #   it applies to all documents in that directory and its sub-directories.
        # </p>
        # <p>
        #   The following method determines whether or not a template is configured for a
        #   directory. If a template is configured for a directory, that hTemplateId
        #   overwrites the hTemplateId defined for the hostname. If no template is configured
        #   for the directory, the hTemplateId defined for the hostname stands.
        # </p>
        # <p>
        #   Once the <var>hTemplateId</var> is determined, additional framework variables are created:
        # </p>
        # <ul>
        #   <li><var>hTemplateId</var> The numeric unique id used to identify the template</li>
        #   <li>
        #       <var>hTemplatePath</var> The path to the PHP template file.  This variable can be changed at any
        #       point later in execution.  For example, within another framework plugin.
        #   </li>
        #   <li><var>hTemplateName</var> The name of the template</li>
        #   <li><var>hTemplateDescription</var> A short description</li>
        # </ul>
        # <p>
        #   If the framework is accessed through the command line interface (CLI) no
        #   document template is set, since this is not needed.
        # </p>
        # @end

        if ($this->hShellCLI(false))
        {
            return;
        }

        $path = $this->hFilePath;

        do {
            # See if there is a template for this directory.
            $templateId = $this->hDatabase->selectColumn(
                array(
                    'hTemplateDirectories' => 'hTemplateId'
                ),
                array(
                    'hTemplateDirectories',
                    'hDirectories'
                ),
                array(
                    'hDirectories.hDirectoryPath' => $path,
                    'hTemplateDirectories.hDirectoryId' => 'hDirectories.hDirectoryId'
                )
            );

            if (!empty($templateId) || $path == '/')
            {
                break;
            }

        } while ($path = dirname($path));

        $this->hTemplateId = !empty($templateId)? (int) $templateId : $this->hTemplateId(1);

        $this->setVariables(
            $this->hTemplates->selectAssociative(
                array(
                    'hTemplateId',
                    'hTemplatePath',
                    'hTemplateName',
                    'hTemplateDescription'
                ),
                (int) $this->hTemplateId
            )
        );
    }

    public function getTemplate($path, $variables = array())
    {
        # @return string, void

        # @description
        # <h2>Template Scripting</h2>
        # <p>
        #   The remainder of this plugin provides a simple template scripting API similar to
        #   Smarty and ColdFusion, the template scripting API allows you to perform simple
        #   programming tasks directly in templates.
        # </p>
        # <p>
        #   Template scripting can be applied to a variety of text files. Most commonly it
        #   is used in HTML, CSS, SQL, XML and plain text files.
        # </p>
        # <h2>Getting a Template</h2>
        # <p>
        #   <var>getTemplate()</var> retreives a template file and parses it for template
        #   scripting.  Variables can be supplied in an array, which are then used with
        #   Hot Toddy's template scripting language.
        # </p>
        # @end

        $path = $this->insertSubExtension(
            $path,
            'mobile',
            $this->userAgent->interfaceIdiomIsPhone
        );

        if (file_exists($path))
        {
            $this->addLoadedPath('Template: '.$path);

            if (!isset($this->templateCache[$path]))
            {
                $this->template = file_get_contents($path);
                $this->templateCache[$path] = $this->template;
            }
            else
            {
                $this->template = $this->templateCache[$path];
            }

            return $this->parseTemplate(
                $this->template,
                $variables,
                $path
            );
        }
        else
        {
            $this->warning(
                "The template path '{$path}' does not exist in template '{$this->templatePath}'.",
                __FILE__,
                __LINE__
            );

            return '';
        }
    }

    public function parseTemplate($document, $variables = array(), $path = null, $templateName = '')
    {
        # @return string

        # @description
        # <h2>Parsing a Template</h2>
        # <p>
        #   <var>parseTemplate()</var> takes the supplied string <var>$document</var> and parses it
        #   for Hot Toddy's template scripting language.  Optionally, template variables can be
        #   supplied in the <var>$variables</var> argument.
        # </p>
        # <p>
        #   If <var>parseTemplate()</var> is called from <var>getTemplate()</var> the path to the
        #   template file is passed along in the <var>$path</var> argument.  If there is no path,
        #   you can optionally supply a template name in the <var>$templateName</var> argument.
        # </p>
        # <p>
        #   The <var>$path</var> and <var>$templateName</var> arguments are used to make error
        #   reporting more informative, by helping you to identify what template errors are
        #   occurring in.
        # </p>
        # @end

        # Replace escaped curly braces

        #$document = str_replace(
        #    array('\\{', '\\}', '/{', '/}'),
        #    array('&#123;', '&#125;', '&#123;', '&#125;'),
        #    $document
        #);

        # Get a count of the remaining braces to ensure that the opening
        # and closing count match
        $openingCount = substr_count($document, '{');
        $closingCount = substr_count($document, '}');

        if ($openingCount < $closingCount)
        {
            $this->notice(
                "The template document could not be parsed because it is missing an opening '{' symbol.",
                __FILE__,
                __LINE__
            );
        }

        if ($openingCount > $closingCount)
        {
            $this->notice(
                "The template document could not be parsed because it is missing a closing '}' symbol.",
                __FILE__,
                __LINE__
            );
        }

        if (empty($path))
        {
            $this->templatePath = $templateName? $templateName : "Generic Template Document";
        }
        else
        {
            $this->templatePath = $path;
        }

        if (!$this->userAgent->browser)
        {
            $this->userAgent->browser = 'webkit';
        }

        if (!isset($GLOBALS['hTemplateVariables']) || !is_array($GLOBALS['hTemplateVariables']))
        {
            $GLOBALS['hTemplateVariables'] = array();
        }

        foreach ($variables as $key => $value)
        {
            $GLOBALS['hTemplateVariables'][$key] = $value;
        }

        //var_dump($frameworkVariables);

        # Template variables should take precedence over framework variables
        $GLOBALS['hTemplateVariables'] = array_merge(
            $this->getVariables(),
            $GLOBALS['hTemplateVariables']
        );

        $document = preg_replace_callback(
            $this->matchCurlyBraces,
            array($this, 'parse'),
            $document
        );

        $GLOBALS['hTemplateVariables'] = array();

        return $document;
    }

    public function setTemplateVariable($key, $value)
    {
        # @return void

        # @description
        # <h2>Setting a Template Variable</h2>
        # <p>
        #   Typically template variables are passed into the template you wish to parse via array
        #   specified in the <var>$variables</var> argument.
        # </p>
        # <p>
        #   <var>setTemplateVariable()</var> allows you to set a single template variable.
        #   <var>setTemplateVariable()</var> is also used interally by <var>hTemplate</var>
        #   to set template variables.
        # </p>
        # @end

        $GLOBALS['hTemplateVariables'][$key] = $value;
    }

    public function walkTemplateObject(&$object, array $variables = array())
    {
        foreach ($object as $key => &$value)
        {
            if (is_object($value) || is_array($value))
            {
                $this->walkTemplateObject($value, $variables);
            }
            else
            {
                $value = $this->parseTemplateMarkup($value, $variables);
            }
        }
    }

    public function parseTemplateMarkup($document, array $variables = array())
    {
        # @return string

        # @description
        # <p>
        #   An alias for <var>parseTemplate()</var>.  This method exists for legacy
        #   reasons, is deprecated, and could be removed from a future release
        #   without warning.  Use at your own risk.
        # </p>
        # @end

        return $this->parseTemplate($document, $variables);
    }

    public function parse($matches)
    {
        $block = substr($matches[0], 1, -1);

        # @return string

        # @description
        # <h2>Template Parsing</h2>
        # <p>
        #   Template syntax parsing in Hot Toddy relies on PHP's support of matching
        #   curly brace pairs using regular expressions.  Matching occurs recursively,
        #   matching the outermost pairs first, then progressing inward.  Since template
        #   syntax relies on matching curly brace pairs, curly braces appearing in a
        #   template must have both the opening and closing curly brace.  Having one an
        #   opening curly brace or a closing curly brace but not a set will result in an
        #   error, and likely will result in no template.  The following documents
        #   the syntax supported by the parser.
        # </p>
        # <p>
        #   <var>parse()</var> is a callback function responsible for matching curly brace
        #   pairs then identifying and expanding template language syntax.
        # </p>
        # <h2>Template Comments</h2>
        # <p>
        #   Template comments can be added by prefacing them with a pound sign.
        # </p>
        # <code>
        #   {/# This template comment won't be included in the rendered output}
        # </code>
        if (substr($block, 0, 1) == '#')
        {
            return '';
        }

        # <h2>Escape Character</h2>
        # <p>
        #   Template code can be passed through, untouched by including a forward
        #   slash after the opening curly brace.
        # </p>
        # <code>
        #  {//# The forward slash will make this template comment visible.}
        # </code>
        # <p>
        #   The backlash can also be used, but must be properly escaped.
        # </p>

        if (substr($block, 0, 1) == '/' || substr($block, 0, 1) == '\\')
        {
            return '&#123;'.substr($block, 1).'&#125;';
        }

        # <h2>Compile CSS or JavaScript</h2>
        # <p>
        #   Compilation allows you to develop javascript and CSS with several,
        #   modularized files.  Developing in this way allows you to keep the
        #   code files small, better organized, and easier to manage.
        # </p>
        # <p>
        #   With this feature, you can add some template syntax into a single
        #   master JS or CSS file that indicates that the master file should be
        #   a compilation of several smaller files.  For Javascript the master
        #   file should be named <var>hSomthing.template.js</var>, and that file
        #   should contain only the template script declaring the master file and
        #   the included files, but no JavaScipt (which might confuse the template
        #   parser).  For CSS, the template syntax to compile multiple CSS files
        #   can be included in any CSS file, and can include CSS along with the
        #   template script.
        # </p>
        # <p>
        # To set up a compiled file, you include the following in the
        # master file.
        # </p>
        # <p>JavaScript:</p>
        # <code>
        # {/compile:js:{
        #   "master" : "/hFramework/hFramework.template.js",
        #   "files" : [
        #     "/hFramework/JS/Hot.js",
        #     "/hFramework/JS/Events.js",
        #     "/hFramework/JS/Focus.js",
        #     "/hFramework/JS/Select.js",
        #     "/hFramework/JS/Key Binding.js",
        #     "/hFramework/JS/Dialogue.js",
        #     "/hHTTP/hHTTP.js",
        #     "/hFramework/hFramework.legacy.js"
        #   ]
        # }}
        # </code>
        # <p>CSS:</p>
        # <code>
        # {/compile:css:{
        #    "master" : "/hFinder/hFinder.css",
        #    "files" : [
        #       "/hFinder/CSS/Views/Views.css",
        #       "/hFinder/CSS/Views/Tiles.css",
        #       "/hFinder/CSS/Views/Icons.css",
        #       "/hFinder/CSS/Views/Columns.css",
        #       "/hFinder/CSS/Views/List.css",
        #       "/hFinder/CSS/Dialogue.css",
        #       "/hFinder/CSS/Search.css",
        #       "/hFinder/CSS/Drag and Drop.css"
        #   ]
        # }}
        # </code>
        # <p>
        #   Note that the syntax to declare the master file and the included files is JSON.
        # </p>

        preg_match(
            '/'.                // Delimiter
            '^'.                // Start of string
            'compile[\.|\:]'.   // compile.
            '(js|css){1}\:'.    // compile:js: or compile:css:
            '(.*)'.             // JSON Block
            '$'.                // End of String
            '/'.                // Delimiter
            'sm',               // Modifiers
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[1]))
        {
            $type = $blockMatches[1];

            $json = $this->decodeJSON(
                $blockMatches[2]
            );

            if (isset($json->master) && isset($json->files) && is_array($json->files))
            {
                $name = basename($json->master);

                $compiledDirectory = $this->hFrameworkCompiledPath;

                if (!file_exists($compiledDirectory))
                {
                    $this->console("Making directory {$compiledDirectory}");

                    $this->mkdir(
                        $compiledDirectory
                    );
                }

                $destinationPath = $compiledDirectory.'/'.$name;

                $destinationExists = file_exists(
                    $destinationPath
                );

                $recompile = false;

                if ($destinationExists)
                {
                    $mtime = filemtime($destinationPath);

                    if ($mtime < filemtime($this->hServerDocumentRoot).$json->master)
                    {
                        $recompile = true;
                    }
                    else
                    {
                        foreach ($json->files as $file)
                        {
                            if (!file_exists($this->hServerDocumentRoot.$file))
                            {
                                $this->warning(
                                    "Unable to include a file in a complication for '{$name}' because the file '{$file}' does not exist.",
                                    __FILE__,
                                    __LINE__
                                );
                            }
                            else if (filemtime($this->hServerDocumentRoot.$file) > $mtime)
                            {
                                $recompile = true;
                                break;
                            }
                        }
                    }
                }

                $compiled = '';

                if (!$destinationExists || $recompile)
                {
                    foreach ($json->files as $file)
                    {
                        if (!file_exists($this->hServerDocumentRoot.$file))
                        {
                            $this->warning(
                                "Unable to include a file in a complication for '{$name}' because the file '{$file}' does not exist.",
                                __FILE__,
                                __LINE__
                            );
                        }
                        else
                        {
                            $compiled .= file_get_contents(
                                $this->hServerDocumentRoot.$file
                            );
                        }
                    }

                    file_put_contents(
                        $destinationPath,
                        $compiled
                    );
                }

                $content = !empty($compiled)? $compiled : file_get_contents($destinationPath);

                switch ($type)
                {
                    case 'js':
                    {
                        return $content;
                    }
                    case 'css':
                    {
                        return $this->parseTemplate($content);
                    }
                }
            }
            else
            {
                $this->warning(
                    "Unable to compile {$type} because the JSON configuration is not correctly formatted.",
                    __FILE__,
                    __LINE__
                );
            }
        }

        # <h2>Template Variables</h2>
        # <p>
        #   Variables can be included in templates, either
        #   passed in by you, or accessed from global PHP or
        #   framework variables.
        # </p>
        # <ul>
        #   <li><var>{/$withDollarSign}</var> - The dollar sign is optional.</li>
        #   <li><var>{/withoutDollarSign}</var></li>
        #   <li><var>{/associative.index}</var> - This is an example of accessing an associative indice from an array.</li>
        #   <li><var>{/numeric.0}</var> - This is an example of accessing a numeric indice from an array.</li>
        #   <li><var>{/post.test}</var> - You can access the PHP <var>$_POST</var> super-global like this.</li>
        #   <li><var>{/get.test}</var> - The <var>$_GET</var> super-global.</li>
        #   <li><var>{/server.test}</var> - The <var>$_SERVER</var> super-global.</li>
        #   <li><var>{/env.test}</var> - The <var>$_ENV</var> super-global.</li>
        #   <li><var>{/global.test}</var> - The <var>$GLOBALS</var> super-global.</li>
        #   <li><var>{/globals.test}</var> - The <var>$GLOBALS</var> super-global again.</li>
        #   <li><var>{/cookie.test}</var> - ...and <var>$_COOKIE</var>.</li>
        #   <li><var>{/session.test}</var> - finally, <var>$_SESSION</var>.</li>
        # </ul>
        preg_match(
            '/'.                    // Delimiter
            '^'.                    // Beginning of String
            '[\$|\w|\d|\.]{1,}'.    // Variable (see above)
            '$'.                    // End of String
            '/'.                    // Delimiter
            'U',                    // Modifier
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[0]))
        {
            return $this->getTemplateVariable($blockMatches[0]);
        }

        preg_match(
            '/'.                // Delimiter
            '^'.                // Beginning of String
            '(EncodeHTML)'.     // EncodeHTML modifier
            '(\?|\:)'.          // ? or :
            '(.*)'.             // Contents of block
            '$'.                // End of String
            '/'.                // Delimiter
            'ism',              // Modifiers
            $block,
            $blockMatches
        );

        if (count($blockMatches))
        {
            return htmlspecialchars(
                $blockMatches[3],
                ENT_QUOTES
            );
        }

        # <h2>Simple Variable Expressions</h2>
        # <p>
        #   You can test for certain conditions and modify how content is included in a document.
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>!</td>
        #       <td class='code nowrap'>{/!variable?something}</td>
        #       <td>
        #           If <var>variable</var> is empty, null, or not set, the content
        #           <var>something</var> is included.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td class='code'>?</td>
        #       <td class='code nowrap'>{/?variable?something}</td>
        #       <td>
        #           If <var>variable</var> is empty, <var>something</var> is included. If
        #           <var>variable</var> is not empty, the content of <var>variable</var> is
        #           included.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td class='code'>@</td>
        #       <td class='code nowrap'>{/@variable?something}</td>
        #       <td>
        #           If the <var>variable</var> is isset, <var>something</var> is included.
        #           If <var>variable</var> is not set, <var>something</var> is not included.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td class='code'>&amp;</td>
        #       <td class='code nowrap'>{/&amp;variable?something}</td>
        #       <td>
        #           If the <var>variable</var> is not empty it and <var>something</var> is
        #           included. If the <var>variable</var> is empty, nothing is included.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td></td>
        #       <td class='code nowrap'>{/variable?something}</td>
        #       <td>
        #           If <var>variable</var> evaluates to true, include <var>something</var>
        #       </td>
        #     </tr>
        #   </tbody>
        # </table>
        preg_match(
            '/'.                    // Delimiter
            '^'.                    // Beginning of String
            '(\!|\?|\@|\&)?'.       // Operators
            '([\$|\w|\d|\.]{1,})'.  // Variable
            '(\?'.                  // ?
            '(.*)'.                 // Block
            '){0,}'.                // Is the block repeated one or more times
            '$'.                    // End of String
            '/'.                    // Delimiter
            'sm',                   // Modifiers
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[2]) && isset($blockMatches[4]))
        {
            $operator = $blockMatches[1];
            $variable = $blockMatches[2];

            $this->getTemplateVariableName($variable);

            $content = $blockMatches[4];

            $result = eval("return({$variable});");

            return $this->getContentByOperator(
                $operator,
                $result,
                $content,
                $variable
            );
        }

        # <h2>Dynamic Paths: fileId to filePath</h2>
        # <p>
        #   Framework fileIds can be automatically expanded to filePath shortcuts.
        # </p>
        # <p>
        #   <var>{/hFileId:1}</var> is automatically expanded to <var>/index.html</var>
        # </p>
        # <p>
        #   Alternatively, this can be written as <var>{/fileId:1}</var>, as well.
        # </p>
        preg_match(
            '/^[\$]?(hFileId\:|fileId\:)(\d*)$/i',
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[2]))
        {
            return $this->getFilePathByFileId((int) $blockMatches[2]);
        }

        # <h2>Variable Assignment</h2>
        # <p>
        #  Simple variable assignments are also supported.  Assigning a number:
        # </p>
        # <code>
        #  {/variable = 1}
        # </code>
        # <p>
        #   Assigning a string:
        # </p>
        # <code>
        #  {/variable = asdfasd;asdj}
        # </code>
        # <p class='hDocumentationNote'>
        #   <b>Note:</b> String assignment does not require quotation delimiters,
        #   since the delimiters are the = and &#125. Assigned values are automatically
        #   trimmed of leading/trailing spaces.
        # </p>
        preg_match(
            '/^([\$|\w|\d|\.]+)\s*(\=)\s*(.*)$/Ums',
            $block,
            $blockMatches
        );

        if (isset($blockMatches[2]) && $blockMatches[2] == '=')
        {
            $this->assignTemplateVariable(
                $blockMatches[1],
                preg_replace_callback(
                    $this->matchCurlyBraces,
                    array(
                        $this,
                        'parse'
                    ),
                    trim($blockMatches[3])
                )
            );

            return '';
        }

        # <h2>Incrementing and Decrementing Variables</h2>
        # <p>
        #   Simple incrementing and decrementing numeric variables is done like so.
        # </p>
        # <p>
        #   Incrementing:
        # </p>
        # <code>
        # {/variable++}
        # </code>
        # <p>
        #   Decrementing:
        # </p>
        # <code>
        # {/variable--}
        # </code>

        preg_match(
            '/^([\$|\w|\d|\.]+)\s*(\+\+|\-\-)?$/',
            $block,
            $blockMatches
        );

        if (isset($blockMatches[2]))
        {
            switch ($blockMatches[2])
            {
                case '++':
                {
                    $this->incrementTemplateVariable($blockMatches[1]);
                    break;
                }
                case '--':
                {
                    $this->decrementTemplateVariable($blockMatches[1]);
                    break;
                }
            }

            return '';
        }

        $strings = array(
            'user.',
            'contact.',
            'string.',
            'hot.',
            'php.',
            '.'
        );

        if (false !== ($beginning = $this->beginsString($block, $strings)))
        {
            # <h2>Using Functions in a Template</h2>
            # <p>
            #   Hot Toddy's template scripting also supports executing and including the
            #   result of a function in a template.
            # </p>
            # <h3>Hot Toddy Framework Functions</h3>
            # <p>
            #   Hot Toddy functions are accessed one of two ways, with a leading dot:
            # </p>
            # <code>
            # {/.getFilePathByFileId(1)}
            # </code>
            # <p>
            #   Alternatively, Hot Toddy functions may also be accessed with <var>hot.</var>.
            # </p>
            # <code>
            # {/hot.getFilePathByFileId(1)}
            # </code>
            # <p>
            #   The result is the same, whichever you use is a matter of personal preference.
            # </p>
            # <h3>PHP Functions</h3>
            # <p>
            #   PHP functions can be accessed simply by prepending the function with <var>php.</var>
            # </p>
            # <code>
            # {/php.date('Y')}
            # </code>
            # <p>
            #   The preceding prints the year.
            # </p>
            # <h3>Mixing Template Variables</h3>
            # <p>
            #   You can also mix template syntax with functions:
            # </p>
            # <code>
            # {/.getFilePathByFileId({/fileId})}
            # </code>
            # <p>
            #   The preceding works, <var>{/hFileId}</var> is evaluated.
            # </p>
            # <p>
            #   You can also mix with PHP functions:
            # </p>
            # <code>
            #   {/php.substr({/filePath}, 1)}
            # </code>
            # <h3>Accessing the <var>hUser</var> API</h3>
            # <p>
            #   The <var>hUser</var> library can be accessed using the prefix <var>user.</var>
            # </p>
            # <code>
            # {/user.getUserName(1)}
            # </code>
            # <p>
            #   This would print the user name for the user with userId 1.
            # </p>
            # <h3>Accessing the <var>hContact</var> API</h3>
            # <p>
            #   The <var>hContact</var> library can be accessed using the prefix <var>contact.</var>
            # </p>
            # <code>
            # {/contact.getPhoneNumber(1, 'Work')}
            # </code>
            # <p>
            #   The preceding will print the work phone number for the user with contactId 1.
            # </p>
            # <h3>Accessing the <var>hString</var> API</h3>
            # <p>
            #   <var>hString</var> provides a variety of encoding and decoding functions. It
            #   can be accessed using the <var>string.</var> prefix.
            # </p>
            # <code >
            # {/string.encodeHTML('&lt;p&gt;')}
            # </code>
            # <p>
            #  The preceding call will print &amp;lt;p&amp;gt;.
            # </p>
            # <h3>Simple Function Expressions</h3>
            # <table>
            #   <tbody>
            #     <tr>
            #       <td class='code'>!</td>
            #       <td class='code nowrap'>{/!hot.call('this')?something}</td>
            #       <td>
            #           If the value returned from the function call is empty, null, or not set,
            #           the string <var>something</var> is included.
            #       </td>
            #     </tr>
            #     <tr>
            #       <td class='code'>?</td>
            #       <td class='code nowrap'>{/?hot.call('this')?something}</td>
            #       <td>
            #           If the value returned from the function call is empty, <var>something</var>
            #           is included. If the value returned from the function call is not empty,
            #           the value returned from the function call is included in the template and
            #           the string <var>something</var> is discarded.
            #       </td>
            #     </tr>
            #     <tr>
            #       <td class='code'>@</td>
            #       <td class='code nowrap'>{/@hot.call('this')?something}</td>
            #       <td>
            #           This operator only applies to variables, it does not apply to function calls.
            #       </td>
            #     </tr>
            #     <tr>
            #       <td class='code'>&amp;</td>
            #       <td class='code nowrap'>{/&amp;hot.call('this')?something}</td>
            #       <td>
            #           If the result of the function call is not empty, the result of the function
            #           is included along with the string <var>something</var>.  If the result of the
            #           function call is empty, nothing at all is included.
            #       </td>
            #     </tr>
            #     <tr>
            #       <td></td>
            #       <td class='code nowrap'>{/hot.call('this')?something}</td>
            #       <td>
            #           If the result of the function call evaluates to <var>true</var>, the string
            #           <var>something</var> is included.  If the value evaluates to <var>false</var>
            #           nothing is included.
            #       </td>
            #     </tr>
            #   </tbody>
            # </table>

            preg_match(
                '/^(\!|\?|\@|\&)?(.*?)(\(([^()]+|(?R))*\))(\?(.*)){0,}$/sm',
                $block,
                $blockMatches
            );

            $operator = $blockMatches[1];
            $content = $blockMatches[6];

            $bits = explode('.', $blockMatches[2]);

            $method = array_pop($bits);

            if ($beginning == 'php.' && $method == 'version')
            {
                $method = 'phpversion';
            }

            $originalMethod = $method;

            $expression = $this->getWithTemplateVariableNames($blockMatches[3]);

            $expression = preg_replace_callback(
                $this->matchCurlyBraces,
                array(
                    $this,
                    'parse'
                ),
                $expression
            );

            $method .= $expression;

            $result = null;

            switch ($beginning)
            {
                case 'user.':
                {
                    $string = '$GLOBALS[\'hFramework\']->user->'.$method;
                    break;
                }
                case 'contact.':
                {
                    $string = '$GLOBALS[\'hFramework\']->contact->'.$method;
                    break;
                }
                case 'string.':
                {
                    $string = 'hString::'.$method;
                    break;
                }
                case 'php.':
                {
                    if (!function_exists($originalMethod) && $originalMethod != 'empty' && $originalMethod != 'isset')
                    {
                        $this->notice(
                            "Execution of php function '{$originalMethod}' failed in template '{$this->templatePath}'.",
                            __FILE__,
                            __LINE__
                        );

                        return '';
                    }

                    $string = $method;
                    break;
                }
                case 'hot.':
                case '.':
                default:
                {
                    $string = '$GLOBALS[\'hFramework\']->'.$method;
                }
            }

            $result = eval("return ({$string});");

            if (!empty($blockMatches[5]))
            {
                return $this->getContentByOperator(
                    $operator,
                    $result,
                    $content
                );
            }
            else
            {
                return $result;
            }
        }

        # <h2>Including External Paths</h2>
        # <p>
        #   External documents can be easily included in templates.
        # </p>
        # <p>
        # Examples:
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>{/include:/Users/example/Documents/Document.txt}</td>
        #       <td>Absolute path to a file.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>{/include:/Hot Toddy/hFramework/hFramework.php}</td>
        #       <td>File located in website root.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>{/include:encodehtml:/hFramework/hFramework.php}</td>
        #       <td>File located in <var>hServerDocumentRoot</var> (Hot Toddy folder), encodes HTML.</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>{/include:fileId:1}</td>
        #       <td>Hot Toddy fileId, pulls from HtFS</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>{/include:encode:fileId:1}</td>
        #       <td>Hot Toddy fileId, pulls from HtFS, encodes HTML</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>{/include:encode:filePath:/index.html}</td>
        #       <td>Hot Toddy filePath, pulls from HtFS, encodes HTML</td>
        #     </tr>
        #     <tr>
        #       <td class='code'>{/include:encode:/Custom/File.txt}</td>
        #       <td>
        #           Pulls from the folder you specify in <var>hTemplateIncludePath</var>,
        #           then <var>File.txt</var> is HTML encoded
        #       </td>
        #     </td>
        #   </tbody>
        # </table>
        # <h3>Cascade of Document Existence for File Inclusion</h3>
        # <p>
        #   When including a document from the server file system, multiple locations are queried.
        #   The first location where the document's path exists wins. The order of precedence is
        #   as follows:
        # </p>
        # <ol>
        #   <li>Absolute Path</li>
        #   <li><var>Hot Toddy</var> installation path, <var>hServerDocumentRoot</var></li>
        #   <li>Framework installation path, <var>hFrameworkPath</var></li>
        #   <li>Template include folder, <var>hTemplateIncludePath</var></li>
        # </ol>
        # <p class='hDocumentationNote'>
        #   The syntax for encoding HTML can be either <var>encode</var> or <var>encodeHTML</var>, these
        #   words are case-insensitive.
        # </p>
        # <p class='hDocumentationNote'>
        #   The syntax for including HtFS files can be <var>fileId</var>, <var>hFileId</var>,
        #   <var>filePath</var>, or <var>hFilePath</var>. These words are case-insensitive.
        # </p>

        preg_match(
            '/'.
            '^'.
            'include\:('.
                'encodeHTML\:|'.
                'encode\:|'.
                'fileId\:|'.
                'hFileId\:|'.
                'hFilePath\:|'.
                'filePath\:'.
            ')?'.
            '('.
                'fileId\:|'.
                'hFileId\:|'.
                'hFilePath\:|'.
                'filePath\:'.
            ')?'.
            '(.*)'.
            '$'.
            '/'.
            'i',
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[0]))
        {
            $path = trim($blockMatches[3]);

            if (substr($path, 0, 1) == '{')
            {
                $path = preg_replace_callback(
                    $this->matchCurlyBraces,
                    array(
                        $this,
                        'parse'
                    ),
                    $path
                );
            }

            $type = strtolower($blockMatches[1]);
            $framework = strtolower($blockMatches[2]);

            if ($type == 'fileid:' || $type == 'hfileid:' || $type == 'hfilepath:')
            {
                return $this->getFrameworkFile($path);
            }

            if ($framework == 'fileid:' || $framework == 'hfileid:' || $framework == 'hfilepath:')
            {
                $file = $this->getFrameworkFile($path);
            }
            else
            {
                if (file_exists($path))
                {
                    $file = file_get_contents($path);
                }
                else if (file_exists($this->hServerDocumentRoot.$path))
                {
                    $file = file_get_contents($this->hServerDocumentRoot.$path);
                }
                else if (file_exists($this->hFrameworkPath.$path))
                {
                    $file = file_get_contents($this->hFrameworkPath.$path);
                }
                else if (file_exists($this->hTemplateIncludePath.$path))
                {
                    $file = file_get_contents($this->hTemplateIncludePath.$path);
                }
                else
                {
                    $this->notice(
                        "Failed to include path '{$path}' in template '{$this->templatePath}' because it does not exist.",
                        __FILE__,
                        __LINE__
                    );
                }
            }

            if (isset($file))
            {
                $file = $this->recursiveParse($file);

                switch ($type)
                {
                    case 'encode:':
                    case 'encodehtml:':
                    {
                        return htmlspecialchars($file);
                    }
                    default:
                    {
                        return $file;
                    }
                }
            }
        }

        # <h2 id='templatesEach'>Iterating Arrays in Templates with .each()</h2>
        # <code>
        #   {/navigation:each|key, value|?
        #
        #   }
        # </code>
        preg_match(
            '/'.                        // Delimiter
            '^'.                        // Start of string
            '([\$|\w|\d|\.]+)'.         // 1. Variable name
            '[\:|.](each)[\||\(]'.      // 2. .each()
            '\s*'.                      // Optional space
            '([\$|\w|\d|\.]+)'.         // 3. Left Variable
            '(\s*\,{1}\s*){0,1}'.       // 4. Optional space, comma, optional space
            '([\$|\w|\d|\.]+){0,1}'.    // 5. Right variable
            '\s*'.                      // Optional space
            '[\||\)]'.                  // Close :each||
            '\?'.                       // ? End of expression
            '(.*)'.                     // 6. Contents of block
            '$'.                        // End of string
            '/'.                        // Delimiter
            'sm',                       // Modifiers
            $block,
            $blockMatches
        );

        if (count($blockMatches))
        {
            $templateVariable = $this->getTemplateVariable($blockMatches[1]);

            if (is_array($templateVariable) || is_object($templateVariable))
            {
                $operation = $blockMatches[2]; // Just each for now.

                $leftVariable = $blockMatches[3];
                $rightVariable = $blockMatches[5];

                //if (empty($rightVariable))
                //{
                //    $rightVariable = $leftVariable;
                //}

                $block = $blockMatches[6];

                $builtBlock = '';

                foreach ($templateVariable as $key => $value)
                {
                    if (empty($rightVariable))
                    {
                        $this->setTemplateVariable($leftVariable, $value);
                    }
                    else
                    {
                        $this->setTemplateVariable($leftVariable, $key);
                        $this->setTemplateVariable($rightVariable, $value);
                    }

                    $builtBlock .= $this->recursiveParse($block);
                }

                return $this->recursiveParse($builtBlock);
            }
            else
            {
                return '';
            }
        }

        # <h2>Iterating Arrays in Templates</h2>
        # <p>
        #   <a href='#templatesEach'><b>DEPRECATED</b></a>
        # <p>
        #   Arrays of variables can be passed to a template and a block of template markup can be
        #   iterated again and again to file in information that repeats in some way, but the underlying
        #   template remains the same.
        # </p>
        # <p>
        #   While I have plans to create better and more diverse iterators, Hot Toddy
        #   presently supports only one type of iterator, and the arrays it uses
        #   must conform to a specific structure in order to be used.
        # </p>
        # <h3>Prparing an Array</h3>
        # <p>
        #   Template arrays must be structured like so:
        # </p>
        # <code>
        #   $beatles['first'][0] = 'John';
        #   $beatles['first'][1] = 'Paul';
        #   $beatles['first'][2] = 'George';
        #   $beatles['first'][3] = 'Ringo';
        # </code>
        # <p>
        #   Note that the label <var>first</var> is numerically offset.  If you wished to add
        #   other information to the collection, each data point must contain the same number
        #   of indices.  In this case, four.  For example, expanding upon the Beatles example,
        #   if you were to also add their last names, you must have exactly four last names.
        # </p>
        # <code>
        #   $beatles['last'][0] = 'Lennon';
        #   $beatles['last'][1] = 'McCartney';
        #   $beatles['last'][2] = 'Harrison';
        #   $beatles['last'][3] = 'Starr';
        # </code>
        # <p>
        #   If you only had three of the four, you'd have to fill in what you don't have with
        #   null entries so that you match four entries.
        # </p>
        # <h3>Template Arrays From the Database</h3>
        # <p>
        #   When retrieving data from the database, there are a plethora of APIs available to you
        #   for automatically getting arrays in the correct structure for templates.
        # </p>
        # <p>
        #   For example:
        # </p>
        # <code>
        # $this-&gt;hFiles-&gt;selectForTemplate(
        #   array(
        #       'hFileId',
        #       'hFileName'
        #   )
        # );
        # </code>
        # <p>
        #   The preceding retrieves an array from the <var>hFiles</var> database table suitable for
        #   direct inclusion in a template.
        # </p>
        # <p>
        #   If you did not want to get template structured arrays from the database right-away,
        #   you can also get associative arrays using <var>select()</var> and transform those
        #   arrays into template arrays later with a call to
        #   <var>$this-&gt;hDatabase-&gt;getResultsForTemplate();</var>
        # </p>
        # <h3>Iterating an Array</h3>
        # <p>
        #   Once you have a properly structured array, iterating that array is very easy.
        # </p>
        # <p>
        #   First, pass the array into a template:
        # </p>
        # <code>
        # $this-&gt;getTemplate(
        #     'Template Name',
        #     array(
        #         'beatles' =&gt; $beatles
        #     )
        # );
        # </code>
        # <p>
        # Then in the template, you'd do something like this:
        # </p>
        # <code>
        # {/beatles?
        # &ltul&gt;
        #    {beatles[]?&lt;li&gt;{first} {last}&lt;/li&gt;}
        # &lt/ul&gt;
        # }
        # </code>
        # <p>
        #   Very simple example here.  <var>{/beatles?...}</var> checks that the array
        #   exists and contains at least one value. Then <var>{/beatles[]?...}</var>
        #   iterates the collection.  Once inside the iterator, you use the labels
        #   to refer to items in your array.
        # </p>
        preg_match(
            '/^(\!)?([\$|\w|\d|\.]+)\[\]\?(.*)$/sm',
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[2]))
        {
            $not = $blockMatches[1];

            $array = $this->getTemplateVariable($blockMatches[2]);

            $block = $blockMatches[3];

            if (is_array($array) && count($array))
            {
                $builtBlock = '';

                $count = 1;

                while ($count)
                {
                    $templateVariables = array();

                    foreach ($array as $key => $value)
                    {
                        $templateVariables[$key] = array_shift($array[$key]);
                        $count = count($array[$key]);
                    }

                    foreach ($templateVariables as $key => $value)
                    {
                        $this->setTemplateVariable($key, $value);
                    }

                    $builtBlock .= $this->recursiveParse($block);
                }

                return $this->recursiveParse($builtBlock);
            }
            else
            {
                return '';
            }
        }

        # <h2>Complex Expressions</h2>
        # <p>
        #   Hot Toddy supports both simple variable expressions in a template, and complex expressions.
        #   Complex expressions are delimited by parenthesis to identify them.
        # </p>
        # <p>
        #   Since the expression will be eval'd, and is essentially just PHP code, it must contain
        #   either valid PHP or template syntax.  Template syntax allowed in an expression would be
        #   something like template variables.  These are translated to PHP variables, and the
        #   PHP variables are substituted, making template variables valid.
        # </p>
        # <p>
        #   Example of an expression:
        # </p>
        # <code>
        #   {/({/templateVariable} == {/post.value})?yes, include this content.}
        # </code>
        #
        preg_match(
            '/^(\!|\?|\@|\&)?(\(([^()]+|(?R))*\))(\?(.*)){0,}$/sm',
            $block,
            $blockMatches
        );

        if (!empty($blockMatches[2]))
        {
            $operator = $blockMatches[1];
            $syntax = trim($blockMatches[2]);
            $content = $blockMatches[5];

            if (substr($syntax, 0, 1) == '(' && substr($syntax, -1, 1) == ')')
            {
                $syntax = $this->getWithTemplateVariableNames($syntax);

                $result = eval("return{$syntax};");

                return $this->getContentByOperator(
                    $operator,
                    $result,
                    $content,
                    $syntax
                );
            }
        }

       // Expressions with no parenthesis
       // Can't figure out how to make this work, so, for now, this is disabled.
       // preg_match('/^(\!|\?|\@|\&)?(.*?)(\?(.*)){0,}$/sm', $block, $blockMatches);
       //
       // if (!empty($blockMatches[2]))
       // {
       //     $operator = $blockMatches[1];
       //     $syntax = $this->getWithTemplateVariableNames(trim($blockMatches[2]));
       //     $content = $blockMatches[4];
       //
       //     $result = eval("return({$syntax});");
       //
       //     return $this->getContentByOperator($operator, $result, $content);
       // }

        # <h2>Matching the User Agent</h2>
        # <p>
        #   Through the <var>hUserAgent</var> plugin and library, there are already a number of
        #   user-agent related variables made available globally for identifying and targeting
        #   particular browsers, operating systems, and even classes of mobile devices. See
        #   <var>hUser/hUserAgent</var> for more information about the framework variables it
        #   creates.
        # </p>
        # <p>
        #   Any framework variable created by <var>hUser/hUserAgent</var> can already be used in
        #   a template. For example, to target iOS in a style sheet:
        # </p>
        # <code>
        # {/iOS?
        #  p {
        #    border: 1px solid red;
        #  }
        # }
        # </code>
        # <p>
        #   If you put that in a Hot Toddy style sheet and loaded the page it is attached to in an
        #   iOS browser, all &lt;p&gt; elements would have a 1-pixel, solid, red border.
        # </p>
        # <p>
        #   Template scripting allows a little more convienience where it concerns targeting
        #   particular versions of a browser, for example.
        # </p>
        # <code>
        # {/userAgent:(trident < 10)?Something for Internet Explorer less than 10}
        # </code>
        # <p>
        #   This can target IE or anything running IE's trident rendering engine, and specifically,
        #   versions less than version 10.
        # </p>
        # <p>
        #   You can also toss in an operating system.
        # </p>
        # <code>
        # {/userAgent:(Windows):(ie < 10)? Windows and IE less than 10}
        # </code>
        # <p>
        #   You can use a not operator on the OS, target everything but Windows.
        # </p>
        # <code>
        # {/userAgent:!(Windows):(webkit)? Webkit not on Windows.}
        # </code>
        # <p>
        #   You can also use a not operator on the browser.
        # </p>
        # <code>
        # {/userAgent:!(webkit)? Any browser but webkit.}
        # </code>
        # <p>
        #   You can also use a not operator on both the OS and the browser.
        # </p>
        # <code>
        # {/userAgent:!(Windows):!(trident)? Any OS but Windows, any browser but trident.}
        # </code>
        #

        preg_match(
            '/^userAgent(\:(\!*)\((.*)\))*\:(\!*)\((\w*)\s*((\!|\>|\<|\=)*)\s*((\d|\.)*)\)\?(.*)$/ism',
            $block,
            $blockMatches
        );

        if (count($blockMatches) && !empty($blockMatches[0]))
        {
            // 2 == Not Operator for OS
            // 3 == OS
            // 4 == Not Operator for Browser
            // 5 == User Agent
            // 6 == Operator
            // 8 == Version
            // 10 == Data
            $notOS = !empty($blockMatches[2]);
            $OS = $blockMatches[3];

            $not = !empty($blockMatches[4]);
            $userAgent = trim($blockMatches[5]);

            if (strstr($userAgent, ','))
            {
                list($os, $userAgent) = explode(',', $userAgent);

                $os = trim($os);
                $userAgent = trim($os);
            }

            $operator = $blockMatches[6];
            $version = (float) $blockMatches[8];
            $block = $blockMatches[10];

            $isVersion = false;

            if (!empty($OS))
            {
                if ($this->userAgent->os == $OS)
                {
                    $isVersion = !$notOS;
                }
                else if ($notOS)
                {
                    $isVersion = true;
                }
            }

            if (!empty($userAgent) && ($isVersion && !empty($OS) || !$isVersion && empty($OS)))
            {
                if ($userAgent == $this->userAgent->browser)
                {
                    if (!empty($version))
                    {
                        if (!empty($operator))
                        {
                            switch ($operator)
                            {
                                case '>':
                                case '<':
                                case '>=':
                                case '>=':
                                case '!=':
                                case '<>':
                                case '==':
                                {
                                    break;
                                }
                                default:
                                {
                                    $operator = '==';
                                }
                            }
                        }
                        else
                        {
                            $operator = '==';
                        }

                        $rtn = eval("return ((float) {$this->userAgent->browserVersion} {$operator} (float) {$version});");

                        $isVersion = ($rtn && !$not || !$rtn && $not);
                    }
                    else
                    {
                        $isVersion = !$not;
                    }
                }
                else if ($not)
                {
                    $isVersion = true;
                }
                else
                {
                    $isVersion = false;
                }
            }

            if ($isVersion)
            {
                return $this->recursiveParse($block);
            }

            return '';
        }

        # @end

        return '{'.$this->recursiveParse($block).'}';
    }

    public function recursiveParse($block)
    {
        # @return string

        # @description
        # <h2>Recursive Parse</h2>
        # <p>
        #   Calls <var>parse()</var> recursively on smaller blocks, making it possible
        #   to embed template syntax within template syntax within template syntax.
        # </p>
        # @end

        return preg_replace_callback(
            $this->matchCurlyBraces,
            array(
                $this,
                'parse'
            ),
            $block
        );
    }

    private function getContentByOperator($operator, $result, $content, $syntax = '')
    {
        # @return string, void

        # @description
        # <h2>Template Content Operators</h2>
        # <p>
        #   How template content is returned and managed depends on what operator is used,
        #   and what the return value of eval'd content is.
        # </p>
        # <ul>
        #   <li>
        #       <b><var>$operator</var></b>: The operator used to decide how the eval'd content
        #       is handled.
        #   </li>
        #   <li><b><var>$result</var></b>: The eval'd result of the variable or function call.
        #   <li>
        #       <b><var>$content</var></b>: The content after the question mark that will be
        #       included or discarded depending on the <var>$result</var> and the
        #       <var>$operator</var>.
        #   </li>
        #   <li>
        #       <b><var>$syntax</var></b>: In the case of a variable, <var>$syntax</var> would be
        #       the actual PHP template variable. <var>$syntax</var> does not apply when using a
        #       function.
        #   </li>
        # </ul>
        # <h3>Operator Expressions</h3>
        # <p>
        #   The operator used affects what happens with the <var>$result</var> of the function
        #   call or template variable.
        # </p>
        # </p>
        # <table>
        #   <tbody>
        #     <tr>
        #       <td class='code'>!</td>
        #       <td class='code nowrap'>{/!variable?something}<br />{/!hot.call('this')?something}</td>
        #       <td>
        #           If the <var>variable</var> or value returned from the function call is empty,
        #           null, or not set, the content <var>something</var> is included.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td class='code'>?</td>
        #       <td class='code nowrap'>{/?variable?something}<br />{/?hot.call('this')?something}</td>
        #       <td>
        #           If <var>variable</var> or the value returned from the function call is empty,
        #           <var>something</var> is included. If the <var>variable</var> or value returned
        #           from the function call is not empty, the content of <var>variable</var> or the
        #           value returned from the function call is included in the template and the string
        #           <var>something</var> is discarded.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td class='code'>@</td>
        #       <td class='code nowrap'>{/@variable?something}</td>
        #       <td>
        #           If the <var>variable</var> is isset, <var>something</var> is included.  If
        #           <var>variable</var> is not set, <var>something</var> is not included.  This operator
        #           only applies to variables, it does not apply to function calls.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td class='code'>&amp;</td>
        #       <td class='code nowrap'>{/&amp;variable?something}<br />{/&amp;hot.call('this')?something}</td>
        #       <td>
        #           If the <var>variable</var> or the result of the function call is not empty, the
        #           variable or the result of the function is included along with the string
        #           <var>something</var>. If the <var>variable</var> or the result of the function call
        #           is empty, nothing at all is included.
        #       </td>
        #     </tr>
        #     <tr>
        #       <td></td>
        #       <td class='code nowrap'>{/variable?something}<br />{/hot.call('this')?something}</td>
        #       <td>
        #           If <var>variable</var> or the result of the function call evaluates to true,
        #           the string <var>something</var> is included.
        #       </td>
        #     </tr>
        #   </tbody>
        # </table>
        # @end

        switch ($operator)
        {
            case '!':
            {
                return empty($result)? $this->recursiveParse($content) : '';
            }
            case '?':
            {
                return empty($result)? $this->recursiveParse($content) : $result;
            }
            case '@':
            {
                if (!empty($syntax))
                {
                    $isset = eval("return(isset({$syntax}))");

                    if ($isset)
                    {
                        return $result;
                    }
                }

                return '';
            }
            case '&':
            {
                return !empty($result)? $result.$this->recursiveParse($content) : '';
            }
            default:
            {
                return !empty($result)? $this->recursiveParse($content) : '';
            }
        }
    }

    private function getFrameworkFile($path)
    {
        # @return string

        # @description
        # <h2>Return Framework Document</h2>
        # <p>
        #   Helper function for template inclusion syntax.  Returns a document from HtFS
        #   for inclusion in a template.  Argument <var>$path</var> can be either a number,
        #   <var>fileId</var>, or a string, <var>filePath</var>.
        # </p>
        # @end

        if (is_numeric($path) && !$this->getFilePathByFileId($path))
        {
            $this->notice(
                "Failed to include hFileId '{$path}' in template '{$this->templatePath}' ".
                "because it does not exist.",
                __FILE__, __LINE__
            );
        }
        else if (!$this->getFileIdByFilePath($path))
        {
            $this->notice(
                "Failed to include hFilePath '{$path}' in template '{$this->templatePath}' ".
                "because it does not exist.",
                __FILE__, __LINE__
            );
        }

        return $this->getFileDocument($path);
    }

    private function templateVariableExists($variable)
    {
        # @return boolean

        # @description
        # <h2>Determine Whether a Template Variable Exists</h2>
        # <p>
        #   Determines whether or not a template variable exists.
        # </p>
        # @end

        $exists = eval("return(isset({$variable}));");

        #if (!$exists)
        #{
        #    $this->notice("Template variable '{$variable}' does not exist in template '{$this->templatePath}'", __FILE__, __LINE__);
        #}
    }

    private function getWithTemplateVariableNames($string)
    {
        # @return string

        # @description
        # <h2>Expanding Only Template Variables</h2>
        # <p>
        #   A call to <var>getWithTemplateVariableNames()</var> returns a string with any template variable names
        #   replaced with the equivalent PHP variable names.
        # </p>
        # @end

        return preg_replace_callback(
            $this->matchCurlyBraces,
            array(
                $this,
                'expandTemplateVariables'
            ),
            $string
        );
    }

    public function expandTemplateVariables($matches)
    {
        # @return string

        # @description
        # <h2>Replace Template Variables with PHP Variables</h2>
        # <p>
        #   This callback function takes a template variable name and replaces it with the
        #   equivalent PHP variable syntax.
        # </p>
        # @end

        preg_match(
            '/^[\$|\w|\d|\.]{1,}$/U',
            $matches[1],
            $varMatches
        );

        $this->getTemplateVariableName($varMatches[0]);

        return $varMatches[0];
    }

    private function getTemplateVariableName(&$variable)
    {
        # @return void
        # <p>
        #   When you pass in $variable, which is a reference to a template
        #   variable, the value is reassigned and becomes its PHP syntax equivalent.
        # </p>
        # @end

        # @description
        # <h2>Template Variables</h2>
        # <p>
        #   As noted in <var>parse()</var>, template variables can come from a variety of sources.
        #   They can be supplied directly, they can come from PHP super-globals, and they can come
        #   from framework variables.
        # </p>
        # <p>
        #   <var>getTemplateVariableName()</var> takes the variable supplied in a template and transforms
        #   the syntax used in the template to syntax that can be used with PHP. So a template variable
        #   like <var>{/post.test}</var> is transformed to <var>$_POST['test']</var>, making it possible
        #   to reference PHP super-globals directly from a template.
        # </p>
        # <p>
        #   When you pass variables along with a template, those variables are stored in
        #   <var>$GLOBALS['hTemplateVariables']</var>.  When a variable is referenced in a template,
        #   the syntax used in a template such as <var>{/variableName}</var> is transformed back into PHP so
        #   that it can be eval'd, whether simply to get its value, or as part of an expression, or
        #   whatever.  So <var>{/variableName}</var> becomes <var>$GLOBALS['hTemplateVariables']['variableName']</var>
        #   and the fully expanded PHP variable can go on to later be included in eval'd code.
        # </p>
        # <p>
        #   When this method is called, the template variable provided in the <var>$variable</var> argument
        #   is overwritten with a string representation of the equivalent PHP variable.
        # </p>
        # @end

        $originalVariable = $variable;

        $variable = str_replace('$', '', $variable);

        preg_match(
            '/^(post|get|server|env|cookie|session|globals|global)\./',
            $variable,
            $superGlobalMatches
        );

        if (!empty($superGlobalMatches[1]))
        {
            # Expands:
            #  {post.test}
            #  {get.test}
            #  {server.test}
            #  {env.test}
            #  {cookie.test}
            #  {session.test}
            #  {globals.test}
            #  {global.test}

            $superGlobal = $superGlobalMatches[1];

            $underscore = ($superGlobal != 'global' && $superGlobal != 'globals');

            $this->convertDotsToBrackets($variable);

            $variable = substr_replace(
                $variable,
                '$'.($underscore? '_' : '').strtoupper($superGlobal == 'global'? 'globals' : $superGlobal),
                0,
                strlen($superGlobal)
            );
        }
        else if (strpos($variable, '.'))
        {
            if (substr($variable, 0, strlen('userAgent.')) == 'userAgent.')
            {
                # Expands:
                #  {userAgent.something}
                $variable = '$GLOBALS[\'hFramework\']->'.str_replace('.', '->', $variable);
            }
            else
            {
                # Expands:
                #  {associative.index}
                #  {numeric.0}
                #  {object.property}

                //$this->convertDotsToBrackets($variable, true);
                $variables = explode('.', $variable);

                $variableString = '$GLOBALS[\'hTemplateVariables\']';

                foreach ($variables as $variable)
                {
                    if (eval("return(is_object($variableString));"))
                    {
                        $variableString .= "->{$variable}";
                    }
                    else
                    {
                        if (is_numeric($variable))
                        {
                            $variableString .= "[{$variable}]";
                        }
                        else
                        {
                            $variableString .= "['{$variable}']";
                        }
                    }
                }

                $variable = $variableString;
            }
        }
        else
        {
            # Expands:
            #   {variable}
            $variable = '$GLOBALS[\'hTemplateVariables\'][\''.$variable.'\']';
        }

        $this->templateVariableExists($variable);
    }

    private function convertDotsToBrackets(&$variable, $all = false)
    {
        # @return void

        # @description
        # <h2>Template Arrays</h2>
        # <p>
        #   Within templates, arrays indices are specified using dots, rather than
        #   square brackets.  As in <var>{/template.variable}</var>. <var>convertDotsToBrackets()</var>
        #   tranforms the dots back into PHP-friendly square brackets, so that a variable
        #   name can be eval'd.  So <var>{/template.variable}</var> becomes
        #   <var>['template']['variable']</var> and then is finally referenced from PHP
        #   as <var>$GLOBALS['hTemplateVariables']['template']['variable']</var>.
        # </p>
        # @end

        if (!$all)
        {
            $variable = substr_replace($variable, "['", strpos($variable, '.'), 1);
        }

        $variable = str_replace('.', "']['", $variable);

        $variable = ($all? "['" : '').$variable."']";
    }

    private function getTemplateVariable($variable)
    {
        # @return mixed
        # <p>
        #   Returns the value of the template variable.
        # </p>
        # @end

        # @description
        # <h2>Getting Template Variables</h2>
        # <p>
        #   Takes a template variable, written with template variable syntax,
        #   and returns the PHP variable.
        # </p>
        # <p>
        #   If you have the variable <var>{/something}</var>.  The parser determines
        #   the name of the variable is <var>something</var> and the string <var>something</var>
        #   is passed to <var>getTemplateVariable()</var> in the argument <var>$variable</var>.
        #   <var>something</var> is determined to be the PHP variable
        #   <var>$GLOBALS['hTemplateVariables']['something']</var>.  This method determines
        #   if the PHP variable exists, if it does, its value is returned.  If it does not exist,
        #   null is returned.
        # </p>
        # @end

        $this->getTemplateVariableName($variable);

        return eval("return(isset({$variable})? {$variable} : '');");
    }

    private function assignTemplateVariable($variable, $value)
    {
        # @return void

        # @description
        # <h2>Assigning a Template Variable</h2>
        # <p>Hot Toddy's template scripting allows you to assign a value to a variable
        # directly in a template.  This can be useful for creating things like counters,
        # and perhaps in the future much more.
        # </p>
        # <p>
        # <var>assignTemplateVariable()</var> handles the assignment. It takes the supplied
        # variable and expands the variable into PHP syntax.
        # </p>
        # <p>For Example:</p>
        # <code>{/something = 1}</code>
        # <p>
        # The variable <var>something</var> is translated to a PHP variable,
        # <var>$GLOBALS['hTemplateVariables']['something']</var>.  Then through <var>eval()</var>,
        # the value 1 is assigned to it.
        # </p>
        # @end

        $this->getTemplateVariableName($variable);

        $value = trim($value);

        $firstCharacter = substr($value, 0, 1);
        $lastCharacter = substr($value, -1, 1);

        $quotedString = $this->isQuote($firstCharacter) && $this->isQuote($lastCharacter);

        if (!is_numeric($value) && $value != 'true' && $value != 'false' && !$quotedString)
        {
            $value = '"'.str_replace('"', '\\"', $value).'"';
        }

        if ($firstCharacter == '"' && $lastCharacter == '"')
        {
            $value = preg_replace_callback(
                $this->matchCurlyBraces,
                array(
                    $this,
                    'parse'
                ),
                $value
            );
        }

        if ($firstCharacter == '{' && $lastCharacter == '}')
        {
            $value = preg_replace_callback(
                $this->matchCurlyBraces,
                array(
                    $this,
                    'parse'
                ),
                $value
            );

            if (!is_numeric($value))
            {
                $value = '"'.str_replace('"', '\\"', $value).'"';
            }
        }

        eval("{$variable} = {$value};");
    }

    private function isQuote($string)
    {
        # @return boolean

        # @description
        # <h2>Is a String a Quote Character</h2>
        # <p>
        #   Simply determines whether or not the string provided is a single or
        #   double quote character, returning <var>true</var> if so, and <var>false</var> if not.
        # </p>
        # @end
        return $string == '"' || $string == "'";
    }

    private function incrementTemplateVariable($variable)
    {
        # @return integer

        # @description
        # <h2>Incrementing a Template Variable</h2>
        # <p>
        #   A template variable can be incremented using <var>{/variable++}</var>
        #   This method accepts the variable name to be incremented, <var>variable</var>
        #   gets the PHP version of the variable, <var>$GLOBALS['hTemplateVariables']['variable']</var>.
        #   If the PHP version of the variable exists, it is incremented.  If it does not exist,
        #   it is created with the value zero.
        # </p>
        # @end
        $this->getTemplateVariableName($variable);

        return eval("isset({$variable})? ++{$variable} : ({$variable} = 0);");
    }

    private function decrementTemplateVariable($variable)
    {
        # @return integer

        # @description
        # <h2>Decrementing a Template Variable</h2>
        # <p>
        #   A template variable can be decremented using <var>{/variable--}</var>
        #   This method accepts the variable name to be decremented, <var>variable</var>
        #   gets the PHP version of the variable, <var>$GLOBALS['hTemplateVariables']['variable']</var>.
        #   If the PHP version of the variable exists, it is decremented.  If it does not exist,
        #   it is created with the value zero.
        # </p>
        # @end
        $this->getTemplateVariableName($variable);

        return eval("isset({$variable})? --{$variable} : ({$variable} = 0);");
    }

    public function matchArrayBrackets($matches)
    {
        # @return string

        # @description
        # <p>
        #   A helper function for converting template arrays to PHP arrays.  This
        #   function corrects numeric indexing, removing quotes from numerically indexed
        #   arrays so that <var>$GLOBALS['hTemplateVariables']['array']['0']</var> becomes
        #   <var>$GLOBALS['hTemplateVariables']['array'][0]</var>.
        # </p>
        # @end

        if (is_numeric($matches[3]))
        {
            return '['.$matches[3].']';
        }

        return $matches[0];
    }

    public function beginsString($string, $beginnings)
    {
        # @return string, boolean

        # @description
        # <h2>Return the Beginning of a String</h2>
        # <p>
        #   Helper function for template function syntax.
        #   A string or an array can be specified in the argument
        #   <var>$beginnings</var>, and a regular string can be
        #   specified in the argument <var>$string</var>.  If the any of the
        #   strings specified in <var>$beginnings</var> begins the string
        #   <var>$string</var> the prefix is returned.
        # </p>
        # <p>
        #   For example:
        # </p>
        # <code>
        # $this-&gt;beginsString("user.getUserId('example')", 'user.');
        # </code>
        # <p>
        #   In the example above, the call to <var>beginsString</var> would return
        #   the string <i>user.</i> because the string does, in fact, begin with <i>user.</i>.
        # </p>
        # <p>
        #   Another example:
        # </p>
        # <code>
        # $this-&gt;beginsString(
        #     "php.substr('example', 2)",
        #     array(
        #         'user.',
        #         'contact.',
        #         'string.',
        #         'php.'
        #     )
        # );
        # </code>
        # <p>
        #   In the preceding example, the call to <var>beginsString()</var> returns the string
        #   <i>php.</i>, since <i>php.</i> is included in the array of search strings to look
        #   for at the beginning of the string.
        # </p>
        # <p>
        #   If the specified string or strings are not found at the beginning  of the supplied
        #   string, <var>beginsString()</var> returns <var>false</var>.
        # </p>
        # @end

        $beginsString = (
            substr($string, 0, 1) == '!' ||
            substr($string, 0, 1) == '?' ||
            substr($string, 0, 1) == '@' ||
            substr($string, 0, 1) == '&'
        );

        if ($beginsString)
        {
            $string = substr($string, 1);
        }

        if (is_array($beginnings))
        {
            foreach ($beginnings as $beginning)
            {
                if (substr($string, 0, strlen($beginning)) == $beginning)
                {
                    return $beginning;
                }
            }
        }
        else if (substr($string, 0, strlen($beginnings)) == $beginnings)
        {
            return $beginning;
        }

        return false;
    }
}

?>