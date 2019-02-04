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
# <h1>Documentation Parser</h1>
# <p>
#   <var>hDocumentationParserLibrary</var> tokenizes each PHP document in Hot Toddy and
#   extracts documentation from each file.  That documentation is stored in the database
#   to make it easy to search.  The tokenization process also makes it easy to include
#   PHP syntax highlighting.
# </p>
# @end

class hDocumentationParserLibrary extends hPlugin {

    private $hDocumentationDatabase;
    private $hFileUtilities;

    private $files = array();
    private $php = array();

    public function hConstructor()
    {
        $this->hDocumentationDatabase = $this->database('hDocumentation');

        ini_set('MAX_EXECUTION_TIME', 0);
        ini_set('MEMORY_LIMIT', -1);

        $functions = get_defined_functions();

        $this->php = $functions['internal'];
    }

    public function parseFiles()
    {
        # @return void

        # @description
        # <h2>Parsing a Plethora of Framework Files</h2>
        # <p>
        #    Creates a list of framework files by gathering all PHP files from both
        #    the <var>Plugins</var> and <var>Hot Toddy</var> folders.
        # </p>
        # @end

        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'autoScanEnabled' => true,
                'fileTypes' => array('php')
            )
        );

        $files = $this->hFileUtilities->getFiles();

        foreach ($files as $file)
        {
            $this->console('Tokenizing: '.$file);
            $this->tokenize($file);
        }
    }

    public function tokenize($file)
    {
        # @return void

        # @description
        # <h2>Tokenizing Files</h2>
        # <p>
        #    The tokenizer takes just one file and parses the bits it contains.  It records each method in
        #    a given PHP file, isolates that method's source code, argument list, and inline documentation,
        #    and stores all of those bits in a database.
        # </p>
        # @end

        $tokens = token_get_all(
            file_get_contents($file)
        );

        $this->files[$file] = array(
            'methods' => array(

            )
        );
        
        $functionName = '';
        $functionSignature = '';
        $function = '';
        $functionBody = '';
        $isFunctionDescription = false;
        $functionDescription = '';
        $classDescription = '';
        $isClassDescription = false;
        $classClosingDescription = '';
        $isClassClosingDescription = false;
        $className = '';

        $variableName = '';
        $bufferLength = 0;
        $byReference = false;
        $arguments = array();
        $typeHint = '';

        $extends = false;

        $isClass = false;
        $classEnded = false;
        $isFunction = false;
        $isFunctionSignature = false;
        $isQuotedString = false;
        $isArgumentList = false;
        $isArgumentDescription = false;
        $argumentName = '';
        $argumentType = '';
        $argumentDescription = '';

        $isPublic = false;
        $isPrivate = false;
        $isProtected = false;

        $isStatic = false;

        $returnsReference = false;
        $returnType = '';
        $isReturnDescription = false;
        $returnDescription = '';

        $isPotentialFunction = false;
        $catchOpenCurly = false;

        $curlyCount = 0;
        $parenthesisCount = 0;

        $isInterface = false;

        $buffer = '';

        while (list($i, $token) = each($tokens))
        {
            if ($token[0] == T_CLASS)
            {
                $isClass = true;
                $classEnded = false;
            }

            #if (!$isClass)
            #{
            #    continue;
            #}

            if (is_array($token))
            {
                $name = $token[0];
                $source = $token[1];

                switch ($name)
                {
                    case T_PUBLIC:
                    {
                        if (!$isInterface)
                        {
                            $buffer = '';

                            $isPotentialFunction = true;
                            $isFunctionSignature = true;
                            $isPublic = true;
                            $buffer .= "    <a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenVisibility hDocumentationTokenPublic'>{$source}</a>";
                        }

                        break;
                    }
                    case T_PRIVATE:
                    {
                        if (!$isInterface)
                        {
                            $buffer = '';

                            $isPotentialFunction = true;
                            $isFunctionSignature = true;
                            $isPrivate = true;
                            $buffer .= "    <a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenVisibility hDocumentationTokenPrivate'>{$source}</a>";
                        }

                        break;
                    }
                    case T_PROTECTED:
                    {
                        if (!$isInterface)
                        {
                            $buffer = '';

                            $isPotentialFunction = true;
                            $isFunctionSignature = true;
                            $isProtected = true;
                            $buffer .= "    <a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenVisibility hDocumentationTokenProtected'>{$source}</a>";
                        }

                        break;
                    }
                    case T_STATIC:
                    {
                        if ($isPotentialFunction && !$isInterface)
                        {
                            $isStatic = true;
                            $buffer .= "<a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenStatic'>{$source}</a>";
                        }

                        break;
                    }
                    case T_FUNCTION:
                    {
                        if ($isPotentialFunction && !$isInterface)
                        {
                            $isFunction = true;
                            $buffer .= "<a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenFunction'>{$source}</a>";;
                            $catchOpenCurly = true;
                        }

                        break;
                    }
                    case T_AND_EQUAL:
                    {
                        $buffer .= '&amp;=';
                        break;
                    }
                    case T_BOOLEAN_AND:
                    {
                        $buffer .= '&amp;&amp;';
                        break;
                    }
                    case T_IS_SMALLER_OR_EQUAL:
                    {
                        $buffer .= '&lt;=';
                        break;
                    }
                    case T_IS_GREATER_OR_EQUAL:
                    {
                        $buffer .= '&gt;=';
                        break;
                    }
                    case T_IS_NOT_EQUAL:
                    {
                        if ($source == '<>')
                        {
                            $buffer .= '&lt;&gt;';
                        }
                        else
                        {
                            $buffer .= $source;
                        }

                        break;
                    }
                    case T_SL: # <<    bitwise operators
                    {
                        $buffer .= '&lt;&lt;';
                        break;
                    }
                    case T_SL_EQUAL: # <<=    assignment operators
                    {
                        $buffer .= '&lt;&lt;=';
                        break;
                    }
                    case T_SR: # >>    bitwise operators
                    {
                        $buffer .= '&gt;&gt;';
                        break;
                    }
                    case T_SR_EQUAL: # >>= assignment operators
                    {
                        $buffer .= '&gt;&gt;=';
                        break;
                    }
                    case T_START_HEREDOC: # <<<
                    {
                        $buffer .= '&lt;&lt;&lt;';
                        break;
                    }
                    case T_OBJECT_OPERATOR:
                    {
                        $buffer .= "<span class='hDocumentationTokenObjectOperator'>-&gt;</span>";
                        break;
                    }
                    case T_CONSTANT_ENCAPSED_STRING:
                    {
                        $left = substr($source, 0, 1);
                        $right = substr($source, -1);

                        $middle = substr($source, 1, -1);

                        $quote = '';

                        if ($left == "'")
                        {
                            $quote = '&apos;';
                        }
                        else if ($left == '"')
                        {
                            $quote = '&quot;';
                        }

                        $buffer .= "<span class='hDocumentationTokenString'>".$quote.str_replace('\\', '&#92;', htmlspecialchars($middle, ENT_QUOTES)).$quote."</span>";
                        break;
                    }
                    case T_STRING:
                    {
                        if ($isClass && empty($this->files[$file]['name']))
                        {
                            $this->files[$file]['name'] = $source;
                            $className = $source;
                        }

                        if ($isPotentialFunction && !$isFunction)
                        {
                            $isPotentialFunction = false;
                            $buffer = '';
                        }

                        if ($isArgumentList && !$variableName)
                        {
                            $typeHint = $source;
                        }

                        if ($isFunction)
                        {
                            if ($isFunctionSignature && !$isArgumentList)
                            {
                                # This is a function name;
                                $functionName = $source;

                                if (!isset($this->files[$file]['methods'][$functionName]))
                                {
                                    $this->files[$file]['methods'][$functionName] = array(
                                        'body'             => '',
                                        'signature'        => '',
                                        'isPublic'         => false,
                                        'isPrivate'        => false,
                                        'isProtected'      => false,
                                        'isStatic'         => false,
                                        'returnsReference' => false,
                                        'description'      => '',
                                        'returnType'       => ''
                                    );
                                }

                                $buffer .= "<span id='{$source}' class='hDocumentationTokenObjectMethod'>{$source}</span>";
                            }
                            else
                            {
                                if (in_array($source, $this->php))
                                {
                                    $buffer .= "<a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenPHPFunction'>{$source}</a>";
                                }
                                else
                                {
                                    switch (strtolower($source))
                                    {
                                        case 'null':
                                        case 'true':
                                        case 'false':
                                        {
                                            $buffer .= "<span class='hDocumentationTokenKeyword'>{$source}</span>";
                                            break;
                                        }
                                        default:
                                        {
                                            $buffer .= "<span class='hDocumentationTokenUserDefined'>".htmlspecialchars($source, ENT_QUOTES)."</span>";
                                        }
                                    }
                                }
                            }
                        }

                        break;
                    }
                    case T_VARIABLE:
                    {
                        if ($isPotentialFunction)
                        {
                            $isPotentialFunction = false;
                        }

                        if ($isFunction)
                        {
                            if ($isArgumentList)
                            {
                                $variableName = str_replace('$', '', $source);
                                $arguments[$variableName] = array(
                                    'byReference' => $byReference,
                                    'typeHint' => $typeHint,
                                    'default' => '',
                                    'description' => '',
                                    'type' => ''
                                );

                                $typeHint = '';
                                $byReference = false;
                            }

                            $buffer .= "<span class='hDocumentationTokenVariable'>{$source}</span>";
                        }

                        break;
                    }
                    case T_COMMENT:
                    {
                        if (substr($source, 0, 2) == '//')
                        {

                        }
                        else if (substr($source, 0, 1) == '#')
                        {
                            $string = substr($source, 1);

                            $addToDescription = true;
                            $addToReturnDescription = true;
                            $addToArgumentDescription = true;

                            switch (strtolower(trim($string)))
                            {
                                case '@description':
                                {
                                    if (!$isClass)
                                    {
                                        $isClassDescription = true;
                                        $isReturnDescription = false;
                                        $isFunctionDescription = false;
                                        $isArgumentDescription = false;

                                        if ($classEnded)
                                        {
                                            $isClassClosingDescription = true;
                                            $isClassDescription = false;
                                        }
                                    }
                                    else
                                    {
                                        $isClassDescription = false;
                                        $isClassClosingDescription = false;
                                        $isFunctionDescription = true;
                                        $isReturnDescription = false;
                                        $isArgumentDescription = false;
                                    }

                                    $addToDescription = false;
                                    break;
                                }
                                case '@end':
                                {
                                    if ($isClassDescription)
                                    {
                                        $this->files[$file]['description'] = $this->parseTemplate(
                                            str_replace(
                                                '\\',
                                                '&#92;',
                                                $classDescription
                                            )
                                        );
                                    }

                                    if ($isClassClosingDescription)
                                    {
                                        $this->files[$file]['closingDescription'] = $this->parseTemplate(
                                            str_replace(
                                                '\\',
                                                '&#92;',
                                                $classClosingDescription
                                            )
                                        );
                                    }

                                    if ($isArgumentDescription)
                                    {
                                        $arguments[$argumentName]['description'] = $this->parseTemplate(
                                            str_replace(
                                                '\\',
                                                '&#92;',
                                                $argumentDescription
                                            )
                                        );
                                    }

                                    $isFunctionDescription = false;
                                    $isClassDescription = false;
                                    $isClassClosingDescription = false;
                                    $isReturnDescription = false;
                                    $isArgumentDescription = false;

                                    $argumentName = '';
                                    $argumentType = '';
                                    $argumentDescription = '';
                                    $classDescription = '';
                                    $classClosingDescription = '';
                                    break;
                                }
                                default:
                                {
                                    if (substr(trim(strtolower($string)), 0, strlen('@argument')) == '@argument')
                                    {
                                        $isReturnDescription = false;
                                        $isFunctionDescription = false;
                                        $isArgumentDescription = true;
                                        $addToArgumentDescription = false;

                                        $argumentData = trim(
                                            substr(
                                                trim($string),
                                                strlen('@argument')
                                            )
                                        );

                                        $bits = explode(' ', $argumentData);

                                        $argumentName = str_replace('$', '', array_shift($bits));

                                        $argumentType = implode(' ', $bits);

                                        $arguments[$argumentName]['type'] = $argumentType;
                                    }
                                    else if (substr(trim(strtolower($string)), 0, strlen('@return')) == '@return')
                                    {
                                        $isReturnDescription = true;
                                        $isFunctionDescription = false;
                                        $isArgumentDescription = false;
                                        $addToReturnDescription = false;

                                        $returnType = trim(
                                            substr(
                                                trim($string),
                                                strlen('@return') + 1
                                            )
                                        );
                                    }
                                }
                            }

                            if ($addToDescription && $isClassDescription)
                            {
                                $classDescription .= $string;
                            }

                            if ($addToDescription && $isClassClosingDescription)
                            {
                                $classClosingDescription .= $string;
                            }

                            if ($addToDescription && $isFunctionDescription)
                            {
                                $functionDescription .= $string;
                            }

                            if ($addToReturnDescription && $isReturnDescription)
                            {
                                $returnDescription .= $string;
                            }

                            if ($addToArgumentDescription && $isArgumentDescription)
                            {
                                $argumentDescription .= $string;
                            }
                        }
                        else
                        {

                        }

                        if ($isFunction)
                        {
                            $buffer .= "<span class='hDocumentationTokenComment'>".htmlspecialchars($source, ENT_QUOTES)."</span>";
                        }

                        break;
                    }
                    case T_ARRAY:
                    case T_EXTENDS:
                    case T_IF:
                    case T_ELSE:
                    case T_ELSEIF:
                    case T_SWITCH:
                    case T_CASE:
                    case T_BREAK:
                    case T_CONTINUE:
                    case T_DEFAULT:
                    case T_FOREACH:
                    case T_AS:
                    case T_FOR:
                    case T_DO:
                    case T_WHILE:
                    case T_EMPTY:
                    case T_ISSET:
                    case T_UNSET:
                    case T_EVAL:
                    case T_ECHO:
                    case T_PRINT:
                    case T_EXIT:
                    case T_INCLUDE:
                    case T_INCLUDE_ONCE:
                    case T_CATCH:
                    case T_TRY:
                    case T_THROW:
                    case T_RETURN:
                    {
                        $buffer .= "<a href='http://www.php.net/{$source}' target='_blank' class='hDocumentationPHPManualLink hDocumentationTokenKeyword'>{$source}</a>";
                        break;
                    }
                    case T_ENCAPSED_AND_WHITESPACE:
                    {
                        $buffer .= "<span class='hDocumentationTokenString'>".str_replace('\\', '&#92;', htmlspecialchars($source, ENT_QUOTES))."</span>";
                        break;
                    }
                    case T_INTERFACE:
                    {
                        $isInterface = true;
                        $buffer .= $source;
                        break;
                    }
                    default:
                    {
                        if ($isFunction || $isPotentialFunction)
                        {
                            $buffer .= $source;
                        }
                    }

                }
            }
            else
            {
                $source = $token;

                switch ($token)
                {
                    case '(':
                    {
                        $parenthesisCount++;

                        if ($isFunction && !$functionSignature && $isFunctionSignature)
                        {
                            $isArgumentList = true;
                        }

                        $buffer .= "<span class='hDocumentationTokenParenthesis'>{$source}</span>";
                        break;
                    }
                    case ')':
                    {
                        $parenthesisCount--;

                        if ($parenthesisCount == 0 && $isArgumentList && $bufferLength)
                        {
                            $arguments[$variableName]['default'] = trim(substr($buffer, $bufferLength));
                            $bufferLength = 0;
                        }

                        $buffer .= "<span class='hDocumentationTokenParenthesis'>{$source}</span>";

                        if ($parenthesisCount == 0 && $isArgumentList)
                        {
                            $variableName = '';
                            $isArgumentList = false;
                            $isFunctionSignature = false;
                            $functionSignature = $buffer;

                            if ($isInterface)
                            {
                                $isFunction = false;
                                $isPotentialFunction = false;
                                $isPublic = false;
                                $isPrivate = false;
                                $isProtected = false;
                                $isStatic = false;
                                $returnsReference = false;

                                $catchOpenCurly = false;

                                $functionDescription = '';
                                $returnDescription = '';
                                $returnType = '';
                                $functionSignature = '';
                                $buffer = '';

                                $isFunctionDescription = false;
                                $isArgumentDescription = false;
                                $isReturnDescription = false;

                                $arguments = array();
                            }
                        }

                        break;
                    }
                    case '{':
                    {
                        if (!$isQuotedString)
                        {
                            $curlyCount++;

                            if ($isFunction && $catchOpenCurly)
                            {
                                $catchOpenCurly = false;
                            }
                        }

                        $buffer .= "<span class='hDocumentationTokenCurlyBrace'>{$source}</span>";

                        break;
                    }
                    case '}':
                    {
                        $buffer .= "<span class='hDocumentationTokenCurlyBrace'>{$source}</span>";

                        if (!$isQuotedString)
                        {
                            $curlyCount--;

                            if ($isClass && !$curlyCount) # End of the class
                            {
                                $classEnded = true;
                                $isClass = false;
                                $className = '';
                            }

                            if ($isInterface && !$curlyCount) # End of the interface
                            {
                                $isInterface = false;
                            }

                            if ($curlyCount == 1)  # End of the method
                            {
                                $this->files[$file]['methods'][$functionName] = array(
                                    'body' => $buffer,
                                    'signature' => trim($functionSignature),
                                    'isPublic' => $isPublic,
                                    'isPrivate' => $isPrivate,
                                    'isProtected' => $isProtected,
                                    'isStatic' => $isStatic,
                                    'returnsReference' => $returnsReference,
                                    'description' => $this->parseTemplate(
                                        str_replace(
                                            '\\',
                                            '&#92;',
                                            $functionDescription
                                        )
                                    ),
                                    'returnType' => $returnType,
                                    'returnDescription' => $this->parseTemplate(
                                        str_replace(
                                            '\\',
                                            '&#92;',
                                            $returnDescription
                                        )
                                    ),
                                    'arguments' => $arguments
                                );

                                $isFunction = false;
                                $isPotentialFunction = false;
                                $isPublic = false;
                                $isPrivate = false;
                                $isProtected = false;
                                $isStatic = false;
                                $returnsReference = false;

                                $catchOpenCurly = false;

                                $functionDescription = '';
                                $returnDescription = '';
                                $returnType = '';
                                $functionSignature = '';
                                $buffer = '';

                                $isFunctionDescription = false;
                                $isArgumentDescription = false;
                                $isReturnDescription = false;


                                $arguments = array();
                            }
                        }

                        break;
                    }
                    case '"':
                    {
                        if (!$isQuotedString)
                        {
                            $isQuotedString = true;
                        }
                        else
                        {
                            $isQuotedString = false;
                        }

                        $buffer .= '&quot;';

                        break;
                    }
                    case '&&':
                    {
                        $buffer .= '&amp;&amp;';
                        break;
                    }
                    case '&':
                    {
                        if ($isFunction && !$isArgumentList)
                        {
                            $returnsReference = true;
                        }

                        if ($isArgumentList)
                        {
                            $byReference = true;
                        }

                        $buffer .= '&amp;';
                        break;
                    }
                    case '>':
                    {
                        $buffer .= '&gt;';
                        break;
                    }
                    case '<':
                    {
                        $buffer .= '&lt;';
                        break;
                    }
                    case '=':
                    {
                        $buffer .= '=';

                        if ($isArgumentList)
                        {
                            $bufferLength = strlen($buffer);
                        }

                        break;
                    }
                    case ',':
                    {
                        $buffer .= ',';

                        if ($isArgumentList)
                        {
                            if ($bufferLength)
                            {
                                $arguments[$variableName]['default'] = trim(
                                    substr(
                                        $buffer,
                                        $bufferLength
                                    )
                                );

                                $bufferLength = 0;
                            }

                            $variableName = '';
                        }

                        break;
                    }
                    default:
                    {
                        $buffer .= $source;
                    }
                }
            }
        }

        foreach ($this->files as $file => $fileBits)
        {
            $this->hDocumentationDatabase->saveMethods($file, $this->files[$file]);
            unset($this->files[$file]);
        }   
    }
}

?>