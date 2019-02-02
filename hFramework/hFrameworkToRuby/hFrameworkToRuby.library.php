<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework to Ruby Library
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hFrameworkToRubyLibrary extends hPlugin {

    private $hFileUtilities;
    private $hFile;

    private $path;
    private $files;
    private $folders;
    private $substr;
    private $buffer;
    private $parenthesisCounter = 0;

    private $positionMarkers = array();
    private $parenthesisMarkers = array();

    private $extractions = array();

    private $requiredPaths = array();

    private $functions = array(
        'substr',
        'implode',
        'explode',
        'in_array',
        'array_push',
        'strstr',
        'stristr',
        'str_replace',
        'method_exists',
        'console',
        'notice',
        'warning',
        'fatal',
        'plugin',
        'library',
        'shell'
    );

    private $functionMap = array(
        'null'                          => 'nil',
        'is_numeric'                    => 'isNumeric?',
        'is_executable'                 => 'File.executable?',
        'is_readable'                   => 'File.readable?',
        'basename'                      => 'File.basename',
        'class_exists'                  => 'classExists?',
        'dirname'                       => 'File.dirname',
        'unlink'                        => 'File.delete',
        'rename'                        => 'File.rename',
        'rmdir'                         => 'Dir.delete',
        'filesize'                      => 'File.size',
        
        'file_get_contents'             => 'getFileContents',
        'file_exists'                   => 'File.exists?',
        'filemtime'                     => 'File.mtime',
        'is_dir'                        => 'File.directory?',
        'mkdir'                         => 'Dir.mkdir',
        'pow'                           => 'Math.pow',

        'hLibrary'                      => 'library',
        'hPlugin'                       => 'plugin',
        'hFramework'                    => '$framework',
        'hDatabase'                     => '@@HDatabase',
        'hString'                       => '@@HString',
        'hUser'                         => '@@HUser',
        'hContact'                      => '@@HContact',

        'expandDocumentIds'             => 'expandDocumentIds',
        'getDirectoryId'                => 'getDirectoryId',
        'getFilePathById'               => 'getFilePathByFileId',
        'getGroupId'                    => 'getGroupId',
        'getUserId'                     => 'getUserId',
        'groupExists'                   => 'groupExists?',
        'whichUserId'                   => 'whichUserId!',

        'categoryExists'                => 'categoryExists?',

        'getCalendarFileId'             => 'getCalendarFileId',
        'getContactIdByEmailAddress'    => 'getContactIdByEmailAddress',
        'getContactIdByUserId'          => 'getContactIdByUserId',
        'getFileIdPath'                 => 'getFileIdPath',
        'getIdByFilePath'               => 'getFileIdByFilePath',
        'getFileIdByPluginId'           => 'getFileIdByPluginId',
        'getFilePathByPluginId'         => 'getFilePathByPluginId',
        'getCategoryIdFromPath'         => 'getCategoryIdFromPath',
        'getSessionId'                  => 'getSessionId',

        'isActivated'                   => 'isActivated?',
        'isAuthor'                      => 'isAuthor?',
        'isCategoryPath'                => 'isCategoryPath?',
        'isHomeCategoryPath'            => 'isHomeCategoryPath?',
        'isListenerPath'                => 'isListenerPath?',
        'beginsPath'                    => 'beginsPath?',
        'inPath'                        => 'inPath?',
        'isDomainGroup'                 => 'isDomainGroup?',
        'isElevated'                    => 'isElevated?',
        'isFrameworkPath'               => 'isFrameworkPath?',
        'isFieldId'                     => 'isFieldId?',
        'isInElevated'                  => 'isInElevated?',
        'isLoggedIn'                    => 'isLoggedIn?',
        'isServerPath'                  => 'isServerPath?',
        'isSSLEnabled'                  => 'isSSLEnabled?',
        'inGroup'                       => 'inGroup?',
        'hasPermission'                 => 'hasPermission?',
        'hasWorldRead'                  => 'hasWorldRead?',
        'isMovie'                       => 'isMovie?',
        'isAudio'                       => 'isAudio?',
        'isVideo'                       => 'isVideo?',
        'isImage'                       => 'isImage?',
        'modifyAddressBookByContactId'  => 'modifyAddressBookByContactId',
        'queryFieldId'                  => 'queryFieldId',
        'resultsExist'                  => 'resultsExist?',
        'setCategoryId'                 => 'setCategoryId=',
        'setResultCount'                => 'setResultCount=',
        'setContactId'                  => 'setContactId=',
        'setForm'                       => 'setForm=',
        'setId'                         => 'setId=',
        'setDuplicateFields'            => 'setDuplicateFields=',
        'selectExists?'                 => 'selectExists?',
        'shellArgumentExists'           => 'shellArgumentExists?',
        'hFileHeaders'                  => 'getPluginFiles',
        'hConstructor'                  => 'hConstructor',
        'hFile'                         => '@HFile'
    );

    public function hConstructor()
    {
        ini_set('MAX_EXECUTION_TIME', 0);
        ini_set('MEMORY_LIMIT', -1);

        $this->path = $this->hFrameworkPath;
        
        $this->hFile = $this->library('hFile');
    
        if (!file_exists($this->path.'/Ruby'))
        {
            $this->mkdir($this->path.'/Ruby');
        }

         $this->console("Path: ".$this->path.'/Hot Toddy');
    }

    public function rubify($path)
    {
        if (file_exists($path))
        {
            $this->requiredPaths = array();
            
            $filePath = $this->getEndOfPath($path, $this->path.'/Hot Toddy');
            $rubyPath = $this->path.'/Ruby'.str_replace('.php', '.rb', $filePath);

            if (!strstr($path, 'hDatabase.php') && !strstr($path, 'hFileInterfaceDatabase.library.php') && !strstr($path, 'hFrameworkToRuby.library.php'))
            {
                $this->console("Processing: {$path}");
            
                $code = file_get_contents($path);
                $code = $this->parseObject($code);
                
                $directory = dirname($rubyPath);
                
                $this->hFile->makeServerPath($directory);
                
                file_put_contents($rubyPath, $code);
                $this->console("Created: {$rubyPath}");
            }
        }
    }
    
    public function rubifyFolder($path)
    {
        if (file_exists($path) && is_dir($path))
        {    
            $this->hFileUtilities = $this->library(
                'hFile/hFileUtilities',
                array(
                    'autoScanEnabled' => false,
                    'fileTypes' => array('php')
                )
            );
        
            $this->hFileUtilities->scanFiles($path);
            
            $this->files = $this->hFileUtilities->getFiles();
            $this->folders = $this->hFileUtilities->getFolders();

            $this->createFolders();
            
            foreach ($this->files as $file)
            {
                $this->rubify($file);
            }
        }
    }

    public function rubifyAll()
    {
        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'autoScanEnabled' => false,
                'fileTypes' => array('php')
            )
        );

        $this->hFileUtilities->scanFiles($this->path.'/Hot Toddy');
        
        $this->files = $this->hFileUtilities->getFiles();
        $this->folders = $this->hFileUtilities->getFolders();

        $this->createFolders();

        foreach ($this->files as $file)
        {
            $this->rubify($file);
        }
    }

    public function createFolders()
    {
        foreach ($this->folders as $folder)
        {
            $folder = $this->getEndOfPath($folder, $this->path.'/Hot Toddy');
            
            $rubyPath = $this->path.'/Ruby'.$folder;
            
            if (!file_exists($rubyPath))
            {
                $this->mkdir($rubyPath);
                $this->console("Created Folder: {$rubyPath}");
            }
        }
    }
    
    public function addRequiredPath($path)
    {
        if (!in_array($path, $this->requiredPaths))
        {
            array_push($this->requiredPaths, $path);
        }
    }

    public function parseObject($code)
    {
        $code = str_replace("\r\n", "\n", $code);

        $tokens = token_get_all($code);
        
        $class = false;
        $extends = false;
        $function = false;
        $classProperties = false;
        $parenthesisToBrackets = false;
        $skipParenthesis = false;
        $empty = false;
        $properties = array();
        $objectThis = false;
        
        $this->parenthesisCounter = 0;

        $skipStartedAtParenthesis = 0;
        $skipMethod = '';
        
        $appendMethodAtParenthesis = 0;
        $appendMethod = null;
        
        $quotedString = false;

        $requirePaths = array();

        foreach ($this->functions as $function)
        {
            $this->positionMarkers[$function] = array();
            $this->parenthesisMarkers[$function] = array();
            $this->extractions[$function] = array();
        }

        $this->buffer = '';

        $if = false;
        $array = array();

        while (list($i, $token) = each($tokens))
        {
            if (is_array($token))
            {
                $name = $token[0];
                $source = $token[1];

                switch ($name)
                {
                    case T_OPEN_TAG:
                    case T_CLOSE_TAG:
                    case T_STATIC:
                    {
                        break;
                    }
                    case T_CLASS:
                    {
                        $class = true;
                        $classProperties = true;
                        $this->buffer .= "{#require.placeholder}\n\nclass";
                        break;
                    }
                    case T_EXTENDS:
                    {
                        $extends = true;
                        $classProperties = true;
                        $this->buffer .= '<';
                        break;
                    }
                    case T_FUNCTION:
                    {
                        if ($classProperties)
                        {
                            $classProperties = false;
                        }
                    
                        $this->buffer .= 'def';
                        $function = true;
                        break;
                    }
                    case T_PUBLIC:
                    {
                        if ($classProperties)
                        {
                            $this->buffer .= 'property';
                        }

                        break;
                    }
                    case T_PRIVATE:
                    {
                        if ($classProperties)
                        {
                            $this->buffer .= 'property';
                        }

                        break;
                    }
                    case T_PROTECTED:
                    {
                        if ($classProperties)
                        {
                            $this->buffer .= 'property';
                        }
                    
                        break;
                    }
                    case T_INCLUDE_ONCE:
                    {
                        $this->buffer .= 'require';
                        break;
                    }
                    case T_IF:
                    {
                        $this->buffer .= $source;
                        $if = true;
                        break;
                    }
                    case T_SWITCH:
                    {
                        $this->buffer .= 'case';
                        break;
                    }
                    case T_CASE:
                    {
                        $this->buffer .= 'when';
                        break;
                    }
                    case T_DEFAULT:
                    {
                        $this->buffer .= 'else';
                        break;
                    }
                    case T_COMMENT:
                    {
                        if (substr($source, 0, 2) == '//')
                        {
                            $this->buffer .= substr_replace($source, '#', 0, 2);
                        }
                        else if (substr($source, 0, 1) == '#')
                        {
                            $this->buffer .= $source;
                        }
                        else
                        {
                            # Multiline comments.  Still need to think of how to 
                            # transform these into single line comments.
                        }

                        break;
                    }
                    case T_STRING: # Can be functions or strings.
                    {
                        switch (true)
                        {                  
                            case $class:
                            {
                                $this->buffer .= ucfirst($source);
                                $class = false;
                                break;
                            }
                            case $extends:
                            {
                                $this->buffer .= ucfirst($source);
                                $extends = false;
                                break;       
                            }
                            case in_array($source, $properties):
                            {
                                $this->buffer .= '@'.$source;
                                break;
                            }
                            case $function:
                            default:
                            {
                                switch ($source)
                                {
                                    case 'count':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.length';
                                        break;
                                    }
                                    case 'is_array':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.kind_of?(Array)';
                                        break;
                                    }   
                                    case 'array_reverse':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.reverse';
                                        break;
                                    }
                                    case 'array_shift':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.shift';
                                        break;
                                    }   
                                    case 'is_null':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.nil?';
                                        break;
                                    }
                                    case 'trim':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.strip';
                                        break;
                                    }
                                    case 'array_pop':
                                    {
                                        $skipParenthesis = true;
                                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                                        $skipMethod = '.pop';
                                        break;
                                    }
                                    case 'base64_encode':
                                    {
                                        $this->buffer .= 'Base64.encode64';       
                                        $this->addRequiredPath('base64');
                                        break;
                                    }
                                    case 'copy':
                                    {
                                        $this->buffer .= 'FileUtils.copy';
                                        $this->addRequiredPath('FileUtils');
                                        break;
                                    }   
                                    case 'move':
                                    {
                                        $this->buffer .= 'FileUtils.move';
                                        $this->addRequiredPath('FileUtils');
                                        break;
                                    }   
                                    case 'touch':
                                    {
                                        $this->buffer .= 'FileUtils.touch';
                                        $this->addRequiredPath('FileUtils');
                                        break;
                                    }  
                                    case 'mktime':
                                    {
                                        $this->buffer .= 'Time.mktime';
                                        $appendMethodAtParenthesis = $this->parenthesisCounter;
                                        $appendMethod = '.to_i';
                                        break;
                                    }
                                    case 'strlen':
                                    {
                                        $appendMethodAtParenthesis = $this->parenthesisCounter;
                                        $appendMethod = '.length';
                                        break;
                                    }
                                    default:
                                    {
                                        if (in_array($source, $this->functions))
                                        {
                                            if (!isset($this->parenthesisMarkers[$source]) || !is_array($this->parenthesisMarkers[$source]))
                                            {
                                                $this->parenthesisMarkers[$source] = array();
                                            }

                                            $this->parenthesisMarkers[$source][] = $this->parenthesisCounter + 1;

                                            $this->buffer .= $source;
                                        }
                                        else if (isset($this->functionMap[$source]))
                                        {
                                            $this->buffer .= $this->functionMap[$source];
                                        }
                                        else
                                        {
                                            if (strstr($source, 'Id'))
                                            {
                                                $source = str_replace('Id', 'Id', $source);
                                            }

                                            $functions = array(
                                                'header',
                                                'href',
                                                'hasAccess'
                                            );

                                            if (substr($source, 0, 1) == 'h' && substr($source, 0, 3) != 'has' && !in_array($source, $functions))
                                            {
                                                $tables = $this->hDatabase->getTables();
                                                
                                                if (in_array($source, $tables))
                                                {
                                                    $this->buffer .= '@@H'.substr($source, 1);
                                                }
                                                else
                                                {
                                                    $this->buffer .= '@@Hot.'.substr($source, 1);
                                                }
                                            }
                                            else if (!$classProperties || $function)
                                            {
                                                $this->buffer .= $source;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        break;
                    }
                    case T_CONCAT_EQUAL:
                    {
                        $this->buffer .= '<<';
                        break;
                    }
                    case T_INC:
                    {
                        $this->buffer .= ' += 1';
                        break;
                    }
                    case T_DEC:
                    {
                        $this->buffer .= ' -= 1';
                        break;
                    }
                    case T_LOGICAL_AND:
                    {
                        $this->buffer .= '&&';
                        break;
                    }
                    case T_LOGICAL_OR:
                    {
                        $this->buffer .= '||';
                        break;
                    }
                    case T_INCLUDE:
                    case T_INCLUDE_ONCE:
                    case T_REQUIRE:
                    case T_REQUIRE_ONCE:
                    {
                        $this->buffer .= 'require';
                        break;
                    }
                    case T_RETURN:
                    case T_FOREACH:
                    case T_AS:
                    case T_FOR:
                    case T_WHILE:
                    case T_LNUMBER:
                    case T_BOOLEAN_AND:
                    case T_BOOLEAN_OR:
                    case T_DOUBLE_ARROW:
                    case T_FILE:
                    case T_LINE:
                    case T_IS_EQUAL:
                    case T_IS_NOT_EQUAL:
                    case T_IS_IDENTICAL:
                    case T_IS_NOT_IDENTICAL:
                    case T_IS_SMALLER_OR_EQUAL:
                    case T_IS_GREATER_OR_EQUAL:
                    case T_SL:
                    case T_PLUS_EQUAL:
                    case T_MINUS_EQUAL:
                    case T_MOD_EQUAL:
                    case T_MUL_EQUAL:
                    case T_ENCAPSED_AND_WHITESPACE:
                    case T_WHITESPACE:
                    case T_ELSE:
                    {
                        $this->buffer .= $source;
                        break;
                    }
                    case T_DOUBLE_COLON:
                    {
                        $this->buffer .= '.';
                        break;
                    }
                    case T_UNSET:
                    {
                        $skipParenthesis = true;
                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                        $skipMethod = ' = nil';
                        break;
                    }
                    case T_ARRAY:
                    {
                        array_push($array, $this->parenthesisCounter + 1);
                        break;
                    }
                    case T_CONSTANT_ENCAPSED_STRING:
                    {
                        if (!$classProperties)
                        {
                            if (strstr($source, 'Id'))
                            {
                                $source = str_replace('Id', 'Id', $source);
                            }
                            
                            if ($source == "''")
                            {
                                $this->buffer .= "''";
                            }
                            else if (preg_match('/\'([A-Z]|[a-z]){1}\w*\'/', $source))
                            {
                                $this->buffer .= ':'.str_replace("'", '', $source);
                            }
                            else
                            {
                                $this->buffer .= $source;  
                            }
                        }

                        break;
                    }
                    case T_OBJECT_OPERATOR:
                    {
                        if (!$objectThis)
                        {
                            $this->buffer .= '.';   
                        }
                        else
                        {
                            $objectThis = false;
                        }
                        
                        break;
                    }
                    case T_EMPTY:
                    {
                        $empty = true;
                        $skipParenthesis = true;
                        $skipStartedAtParenthesis = $this->parenthesisCounter;
                        $skipMethod = '.empty?';
                        break;
                    }
                    case T_ISSET:
                    {
                        $this->buffer .= 'defined?';
                        break;
                    }
                    case T_VARIABLE:
                    {
                        $variable = substr($source, 1);
                        
                        if (strstr($variable, 'Id'))
                        {
                            $variable = str_replace('Id', 'Id', $variable);
                        }
                    
                        if (!$classProperties)
                        {
                            switch ($source)
                            {
                                case '$this->':
                                case '$this':
                                {
                                    $objectThis = true;
                                    break;
                                }
                                case '_GET':
                                {
                                    break;
                                }
                                case '$_SERVER':                       $this->buffer .= 'ENV';                        break;
                                case '$_POST':                         $this->buffer .= '$post';                      break;
                                case '$_GET':                          $this->buffer .= '$get';                       break;
                                case '$_COOKIE':                       $this->buffer .= '$cookie';                    break;
                                case '$_SESSION':                      $this->buffer .= '$session';                   break;
                                case '$phpFunctions':                  $this->buffer .= 'rubyFunctions';              break;
                                default:
                                {
                                    $variables = array(
                                        'handle',
                                        'html',
                                        'header',
                                        'headers',
                                        'http',
                                        'https',
                                        'href',
                                        'hasAccess'
                                    );

                                    if (substr($variable, 0, 1) == 'h' && !in_array($variable, $variables))
                                    {
                                        $this->buffer .= strtolower(substr($variable, 1, 1)).substr($variable, 2);
                                    }
                                    else
                                    {
                                        $this->buffer .= $variable;
                                    }
                                }
                            }
                        }
                        else
                        {
                            if (substr($source, 0, 1) == '$')
                            {                                     
                                $this->buffer .= ':'.$variable;
                                array_push($properties, $variable);
                            }
                        }
                        
                        break;
                    }
                    case T_CURLY_OPEN:
                    {
                        if ($quotedString)
                        {
                            $this->buffer .= '#{';
                        }

                        break;
                    }
                }
            }
            else
            {
                switch ($token)
                {
                    case '(':
                    {
                        $this->parenthesisCounter++;
                    
                        if (!$skipParenthesis)
                        {
                            $transformArray = false;
                        
                            foreach ($array as $i)
                            {
                                if ($i == $this->parenthesisCounter)
                                {
                                    $this->buffer .= '{';
                                    $transformArray = true;
                                }
                            }
                            
                            if (!$transformArray)
                            {
                                $this->buffer .= '(';
                            }
                            
                            foreach ($this->functions as $function)
                            {
                                $this->setStartMarker($function);
                            }
                        }

                        if ($if)
                        {
                            $this->buffer .= '[if]';
                            $if = false;
                        }
                    
                        break;
                    }
                    case ')':
                    {
                        if (!$skipParenthesis)
                        {
                            $transformArray = false;
                            
                            foreach ($array as $c => $i)
                            {
                                if ($i == $this->parenthesisCounter)
                                {
                                    $this->buffer .= '}';
                                    $transformArray = true;
                                    
                                    unset($array[$c]);
                                }
                            }

                            if (!$transformArray)
                            {
                                $this->buffer .= ')';
                            }
                            
                            foreach ($this->functions as $function)
                            {
                                $this->setEndMarker($function);
                            }
                        }

                        $this->parenthesisCounter--;
                        
                        if ($skipStartedAtParenthesis == $this->parenthesisCounter)
                        {
                            $this->buffer .= $skipMethod;
                        
                            $skipParenthesis = false;
                            $skipStartedAtParenthesis = 0;
                            $skipMethod = '';
                        }

                        if ($appendMethodAtParenthesis == $this->parenthesisCounter && !empty($appendMethod))
                        {
                            $this->buffer .= $appendMethod;
                        
                            $appendMethodAtParenthesis = 0;
                            $appendMethod = '';
                        }

                        if ($this->parenthesisCounter < 0)
                        {
                            $this->fatal("Parse Error: unmatched parenthesis pair.");
                        }

                        break;
                    }
                    case '"':
                    {
                        if (!$quotedString)
                        {
                            $quotedString = true;
                        }
                        else
                        {
                            $quotedString = false;
                        }

                        $this->buffer .= $token;

                        break;
                    }
                    case '!':
                    case '=':
                    case ',':
                    case '&&':
                    case '||':
                    case '>':
                    case '<':
                    case '&':
                    case '|':
                    case '[':
                    case ']':
                    case ':':
                    case '/':
                    case '*':
                    case '-':
                    case '+':
                    case '`':
                    {
                        if (!$classProperties)
                        {
                            $this->buffer .= $token;
                        }
                        break;
                    }
                    case '?':
                    {
                        $this->buffer .= ' ?';
                        break;
                    }
                    case '.':
                    {
                        $this->buffer .= ' + ';
                        break;
                    }
                    case '}':
                    {
                        if ($quotedString)
                        {
                            $this->buffer .= '}';                        
                        }
                        else
                        {
                            $this->buffer .= 'end';
                        }

                        break;
                    }
                    case '{':
                    {
                        if ($quotedString)
                        {
                            $this->buffer .= '#{';
                            break;
                        }
                    }
                    case ';':
                    {
                        break;
                    }
                }
            }
        }
        
        foreach ($this->functions as $function)
        {
            $this->extraction($function);
        }

        $this->rewriteExtractions();

/*
        if (count($this->positionMarkers['substr']))
        {
            var_dump($this->positionMarkers['substr']);
        
            exit;
        }        
*/

        $paths = '';
        
        foreach ($this->requiredPaths as $path)
        {
            $paths .= "require '{$path}'\n";
        }
        
        $this->buffer = str_replace(
            array(
                'property def',
                'property  def',
                '     def',
                'else if',
                'def __call(method, arguments)',
                '{#require.placeholder}',
                '$framework.Dir.mkdir',
                'else:',
                "GLOBALS['argv']",
                "GLOBALS['hUser']",
                "GLOBALS['hContact']",
                "isLoggedIn?()",
                '= &',
                'def Dir.mkdir',
                "ini_get(:safe_mode)",
                'FilePHPFunctions'
            ),
            array(
                'def',
                'def',
                '    def',
                'elsif',
                'def method_missing(method, *arguments, &block)',
                $paths,
                '$framework.mkdir',
                'else',
                'ARGV',
                '$user',
                '$contact',
                "isLoggedIn?",
                '= ',
                'def mkdir',
                'false',
                'FileRubyFunctions'
            ),
            $this->buffer
        );

        //$this->buffer = preg_replace_callback('/(end(\s*)(when|else|elsif))/mU', array($this, 'fixSpacing'), $this->buffer);
        
        //$this->buffer = preg_replace_callback('/foreach\s*\((\w*)\s*as\s*(\w*)\)/mU', array($this, 'fixForeachAsValue'), $this->buffer);
        
        //$this->buffer = preg_replace_callback('/foreach\s*\((\w*)\s*as\s*(\w*)\s*\=\>\s*(\&|\w*)\)/mU', array($this, 'fixForeachAsKeyValue'), $this->buffer);

        //$this->buffer = preg_replace_callback('/when\s*(.*)\:$/Um', array($this, 'fixWhen'), $this->buffer);

        //$this->buffer = preg_replace_callback('/\(([^()]+|(?R))*\)/Umx', array($this, 'matchParenthesis'), $this->buffer);

        $this->buffer = str_replace(
            array(
                'if [unless]',
                '@h',
                'GLOBALS[:argv]',
                'GLOBALS[:hFramework]',
                'GLOBALS[:hUser]',
                'GLOBALS[:hContact]',
                'elsunless ',
                'def FileUtils.touch',
                'def File.rename',
                'def FileUtils.move',
                'def FileUtils.copy'
            ),
            array(
                'unless ',
                '@H',
                'ARGV',
                '$framework',
                '@@HUser',
                '@@HContact',
                'elsif !',
                'def touch',
                'def rename',
                'def move',
                'def copy'
            ),
            $this->buffer
        );

        $this->buffer = str_replace("\r", '', $this->buffer);

        $lines = explode("\n", $this->buffer);

        foreach ($lines as &$line)
        {
            $line = rtrim($line);
        }

        return implode("\n", $lines);
    }
    
    public function rewriteExtractions()
    {
        foreach ($this->functions as $function)
        {
            if (isset($this->extractions[$function]) && is_array($this->extractions[$function]))
            {        
                foreach ($this->extractions[$function] as $extraction)
                {
                    $arguments = $this->getArguments(substr($extraction, strlen($function.'('), -1));
                    
                    $replacement = '';

                    switch ($function)
                    {
                        # substr
                        # console
                        # notice
                        # warning
                        # fatal

                        case 'implode':
                        {
                            $replacement = $arguments[1].'.join('.$arguments[0].')';
                            break;
                        }
                        case 'explode':
                        {
                            $replacement = $arguments[1].'.split('.$arguments[0].')';
                            break;
                        }
                        case 'in_array':
                        {
                            $replacement = $arguments[1].'.include? '.$arguments[0];
                            break;
                        }
                        case 'array_push':
                        {
                            $replacement = $arguments[0].'.push('.$arguments[1].')';
                            break;
                        }
                        case 'substr':
                        {
                            # path[-1..-1]                               substr($path, -1)
                            # directoryPath[-1..-1]                      substr($hDirectoryPath, -1, 1)
                            # directoryPath[0..-2]                       substr($hDirectoryPath, 0, -1)
                            # name[0..0]                                 substr($name, 0, 1)
                            # path[-1..-1]                               substr($path, -1, 1)
                            # pathHaystack[0..pathNeedles.length + 1]    substr($pathHaystack, 0, strlen($pathNeedles.'/')
                            # pathHaystack[0..pathNeedle.length + 1]     substr($pathHaystack, 0, strlen($pathNeedle.'/')
                            # path[0..beginning.length]                  substr($path, strlen($beginning))

                            break;
                        }
                        case 'str_replace':
                        {
                            $replacement = $arguments[2].'.gsub('.$arguments[0].', '.$arguments[1].')';
                            break;
                        }
                        case 'strstr':
                        {
                            $replacement = $arguments[0].'.include? '.$arguments[1];
                            break;
                        }
                        case 'stristr':
                        {
                            $replacement = $arguments[0].'.casecmp('.$arguments[1].') == 0';
                            break;
                        }
                        case 'method_exists':
                        {
                            $replacement = $arguments[0].'.respond_to?('.$arguments[1].')';
                            break;
                        }
                        case 'console':
                        {
                            
                            break;
                        }
                        case 'notice':
                        {
                            break;
                        }
                        case 'warning':
                        {
                            break;
                        }
                        case 'fatal':
                        {
                            break;
                        }
                    }

                    if (!empty($replacement))
                    {
                        $this->buffer = str_replace($extraction, $replacement, $this->buffer);
                    }
                }
            }
        }
    }

    public function extraction($function)
    {
        if (isset($this->positionMarkers[$function]) && is_array($this->positionMarkers[$function]))
        {
            array_reverse($this->positionMarkers[$function]);
            
            $this->extractions[$function] = array();

            foreach ($this->positionMarkers[$function] as $position)
            {
                if (isset($position['start']) && isset($position['end']))
                {
                    # This will create an array of code snippets, 
                    # each code snippet can then be transformed individually into ruby code
                    # and once transformed, the transformed buffer can be updated with the 
                    # new code by doing a simple string replace.

                    $this->extractions[$function][] = substr(
                        $this->buffer,
                        
                        # Move the cursor to the left to include the function name and the opening parenthesis.
                        $position['start'] - strlen($function) - 1, 

                        # Move the cursor to the right to compensate for including the function name and parenthesis.
                        $position['end'] - $position['start'] + strlen($function) + 1
                    );
                }
            }
        }
    }

    public function setStartMarker($function)
    {
        if (isset($this->parenthesisMarkers[$function]) && is_array($this->parenthesisMarkers[$function]))
        {
            foreach ($this->parenthesisMarkers[$function] as $i)
            {
                if ($i == $this->parenthesisCounter)
                {
                    $p = count($this->positionMarkers[$function]);
                    $this->positionMarkers[$function][$p]['start'] = strlen($this->buffer);
                }
            }
        }
    }

    public function setEndMarker($function)
    {
        if (isset($this->parenthesisMarkers[$function]) && is_array($this->parenthesisMarkers[$function]))
        {
            foreach ($this->parenthesisMarkers[$function] as $c => $i)
            {
                if ($i == $this->parenthesisCounter)
                {
                    $p = count($this->positionMarkers[$function]) - 1;
                    $this->positionMarkers[$function][$p]['end'] = strlen($this->buffer);

                    unset($this->parenthesisMarkers[$function][$c]);
                }
            }
        }
    }

    public function matchParenthesis($matches)
    {
        $statement = $matches[0];
    
        if (strstr($statement, '[if]'))
        {
            if (strstr($statement, '[if]!') && !strstr($statement, '&&') && !strstr($statement, '||'))
            {
                return str_replace('[if]!', '[unless]', substr($statement, 1, -1));
            }
            else
            {
                return str_replace('[if]', '', substr($statement, 1, -1));
            }
        }

        return $statement;
    }
    
    public function fixPlugin($matches)
    {
        return $matches[1].' '.substr($matches[2], 1, -1);
    }
    
    public function fixArray($matches)
    {
        if ($matches[2] == 'array' && $matches[3] == '()')
        {
            return '[]';
        }
        
        if (substr($matches[3], 0, 1) == '(' && substr($matches[3], -1) == ')')
        {
            $innerMatch = trim(substr($matches[3], 1, -1));
            
            $items = explode(',', $innerMatch);
            
            $isHash = false;
            
            foreach ($items as $item)
            {
                if (substr(trim($item), 0, 1) == ':' && strstr($item, '=>'))
                {
                    $isHash = true;
                }
                else
                {
                    $isHash = false;
                    break;
                }
            }
            
            if ($isHash)
            {            
                return ' {'.substr($matches[3], 1, -1).'}';
            }            
        }

        return ' ['.substr($matches[3], 1, -1).']';
    }

    public function fixWhen($matches)
    {
        return substr($matches[0], 0, -1);
    }

    public function fixForeachAsValue($matches)
    {
        return $matches[1].'.each do |'.$matches[2].'|';
    }
    
    public function fixForeachAsKeyValue($matches)
    {
        return $matches[1].'.each do |'.$matches[2].', '.$matches[3].'|';
    }

    public function fixErrorMessages($matches)
    {   
        $message = "{$matches[1]} \"{$matches[4]}Originated from #{__FILE__}:#{__LINE__}\"";
        
        $message = str_replace(
            array(
                "\" + '. In",
                ' inOriginated',
                '. InOriginated',
                "' + ",
                " + '",
                "Originated fromOriginated from",
                '$framework.@@Hot'
            ),
            array(
                ". ",
                '. Originated',
                '. Originated',
                '#{',
                '}',
                "Originated from",
                '@@Hot'
            ),
            $message
        );
        
        return $message;
    }
    
    public function fixSpacing($matches)
    {    
        return isset($matches[3])? $matches[3] : '';
    }
    
    public function getArguments($string)
    {    
        $characters = str_split($string);
        
        $comments = array();
        
        $singleLine = false;
        $multiLine = false;

        $escapeCharacter = false;
        
        $doubleQuoteString = false;
        $singleQuoteString = false;
        
        $expression = false;
        $parenthesisCounter = 0;
        
        $array = false;
        $arrayCounter = 0;
        
        $hash = false;
        $hashCounter = 0;
        
        $commas = array();
        
        $commentCounter = 0;

        while (list($i, $character) = each($characters))
        {
            $current = current($characters);

            switch ($character)
            {
                case "\\":
                {
                    $backSlash = true;
        
                    if ($current == '"' || $current = "'")
                    {
                        $escapeCharacter = true;
                    }
                    
                    break;
                }
                case '(':
                {
                    if (!$multiLine && !$singleLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $expression = true;
                        $parenthesisCounter++;
                    }

                    break;
                }
                case ')':
                {
                    if (!$multiLine && !$singleLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $parenthesisCounter--;
                        
                        if (!$parenthesisCounter)
                        {
                            $expression = false;
                        }
                    }

                    break;
                }
                case '[':
                {
                    if (!$multiLine && !$singleLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $array = true;
                        $arrayCounter++;
                    }

                    break;
                }
                case ']':
                {
                    if (!$multiLine && !$singleLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $arrayCounter--;
                    
                        if (!$arrayCounter)
                        {
                            $array = false;
                        }
                    }

                    break;
                }
                case '{':
                {
                    if (!$multiLine && !$singleLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $hash = true;
                        $hashCounter++;
                    }

                    break;
                }
                case '}':
                {
                    if (!$multiLine && !$singleLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $hashCounter--;
                    
                        if (!$hashCounter)
                        {
                            $hash = false;
                        }
                    }
                    
                    break;
                }
                case '"':
                {
                    $doubleQuote = true;
        
                    if (!$escapeCharacter)
                    {
                        if ($doubleQuoteString)
                        {
                            $doubleQuoteString = false;
                        }
                        else if (!$multiLine && !$singleLine && !$singleQuoteString)
                        {
                            $doubleQuoteString = true;
                        }
                    }
                    else
                    {
                        $escapeCharacter = false;
                    }
        
                    break;
                }
                case "'":
                {
                    $singleQuote = true;
        
                    if (!$escapeCharacter)
                    {
                        if ($singleQuoteString)
                        {
                            $singleQuoteString = false;
                        }
                        else if (!$multiLine && !$singleLine && !$doubleQuoteString)
                        {
                            $singleQuoteString = true;
                        }
                    }
                    else
                    {
                        $escapeCharacter = false;
                    }
        
                    break;
                }
                case '/':
                {
                    $forwardSlash = true;
        
                    switch ($current)
                    {
                        case '/':
                        {
                            if (!$multiLine && !$doubleQuoteString && !$singleQuoteString)
                            {
                                $singleLine = true;
                            }

                            break;
                        }
                        case '*':
                        {
                            if (!$singleLine && !$doubleQuoteString && !$singleQuoteString)
                            {
                                $multiLine = true;
                            }

                            break;
                        }
                    }

                    break;
                }
                case '#':
                {
                    if (!$multiLine && !$doubleQuoteString && !$singleQuoteString)
                    {
                        $singleLine = true;
                    }

                    break;
                }
                case "\r":
                case "\n":
                {
                    if ($singleLine)
                    {
                        $singleLine = false;
                    }

                    break;
                }
                case '*':
                {
                    $asterisk = true;
        
                    if ($multiLine && $current == '/')
                    {
                        $multiLine = false;
                    }

                    break;
                }
                case ',':
                {
                    if (!$doubleQuoteString && !$singleQuoteString && !$singleLine && !$multiLine && !$array && !$expression && !$hash)
                    {
                        array_push($commas, $i);
                    }
                    
                    break;
                }
            }
        }

        if (count($commas))
        {
            $arguments = array();

            while (list($i, $comma) = each($commas))
            {
                $current = current($commas);
                
                if (!$i)
                {
                    array_push($arguments, trim(substr($string, 0, $comma)));
                }
                else
                {                    
                    array_push($arguments, trim(substr($string, $last + 1, ($comma - $last - 1))));
                }

                if (empty($current))
                {
                    array_push($arguments, trim(substr($string, $comma + 1)));
                }
                    
                $last = $comma;
            }
            
            return $arguments;
        }
        else
        {
            return $string;
        }
    }
}

?>