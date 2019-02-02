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

class hJSONLibrary extends hPlugin {

    private $JSONCache = array();

    public function hConstructor()
    {

    }

    public function getJSON($path, $cache = true)
    {
        if (isset($this->JSONCache[$path]) && $cache)
        {
            return $this->JSONCache[$path];
        }
        else
        {
            $isWeb = substr($path, 0, 7) == 'http://' || substr($path, 0, 8) == 'https://';

            if ($isWeb || file_exists($path))
            {
                $hFileCache = array();

                if ($cache)
                {
                    $lastModified = $isWeb? 0 : filemtime($path);

                    // See if a database cache exists...
                    $hFileCache = $this->hFileCache->selectAssociative(
                        array(
                            'hFileCacheId',
                            'hFileCacheDocument',
                            'hFileCacheLastModified'
                        ),
                        array(
                            'hFileCacheResource' => 'JSON',
                            'hFileCacheResourcePath' => md5($path)
                        )
                    );
                }

                if (count($hFileCache) && ($isWeb || (!empty($hFileCache['hFileCacheLastModified']) && $lastModified <= $hFileCache['hFileCacheLastModified'])))
                {
                    $json = unserialize(
                        htmlspecialchars_decode(
                            $hFileCache['hFileCacheDocument'],
                            ENT_QUOTES
                        )
                    );
                }
                else
                {
                    $json = file_get_contents($path);

                    $json = $this->decodeJSON($cache? $this->prepareJSON($json) : $json);

                    if ($cache)
                    {
                        $this->hFileCache->save(
                            array(
                                'hFileCacheId' => !empty($hFileCache['hFileCacheId'])? (int) $hFileCache['hFileCacheId'] : 0,
                                'hLanguageId' => 1,
                                'hFileCacheResourceId' => 0,
                                'hFileCacheResource' => 'JSON',
                                'hFileCacheResourcePath' => md5($path),
                                'hFileCacheDocument' => htmlspecialchars(serialize($json), ENT_QUOTES),
                                'hFileCacheLastModified' => $lastModified
                            )
                        );
                    }
                }

                if ($cache)
                {
                    $this->JSONCache[$path] = $json;
                }

                if (empty($json))
                {
                    $this->warning(
                        "Unable to parse JSON file '{$path}'. Check JSON syntax.",
                        __FILE__,
                        __LINE__
                    );

                    return false;
                }
                else
                {
                    return $json;
                }
            }
            else
            {
                $this->warning(
                    "JSON path {$path} does not exist.",
                    __FILE__,
                    __LINE__
                );
            }
        }
    }

    /**
    * This function strips out comments from the JSON file, and puts double quotes
    * around properties.
    *
    */
    public function prepareJSON($json)
    {
        $characters = str_split($json);

        $comments = array();

        $singleLine = false;
        $endSingleLine = false;

        $multiLine = false;
        $endMultiLine = false;

        $escapeCharacter = false;

        $doubleQuoteString = false;
        $singleQuoteString = false;

        $isProperty = false;
        $isBoolean = false;
        $property = '';
        $properties = array();
        $isValue = false;

        $commentCounter = 0;

        while (list($characterOffset, $character) = each($characters))
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
                case "\n":
                {
                    if ($singleLine)
                    {
                        $endSingleLine = true;
                    }

                    if ($isValue)
                    {
                        $isValue = false;
                    }

                    break;
                }
                case '*':
                {
                    $asterisk = true;

                    if ($multiLine && $current == '/')
                    {
                        $endMultiLine = true;
                    }

                    break;
                }
                case ':':
                {
                    if (!$singleLine && !$multiLine && !$doubleQuoteString && !$singleQuoteString && $isProperty && !$isValue)
                    {
                        $isProperty = false;
                        $properties[] = $property;
                        $property = '';
                        $isValue = true;
                    }

                    break;
                }
                case ',':
                {
                    if (!$singleLine && !$multiLine && !$doubleQuoteString && !$singleQuoteString && !$isProperty)
                    {
                        $isValue = false;
                    }

                    break;
                }
                default:
                {
                    if (preg_match('/[A-Z|a-z|0-9|\_|\$]/', $character))
                    {
                        if (!$singleLine && !$multiLine && !$doubleQuoteString && !$singleQuoteString && !$isValue)
                        {
                            if (is_numeric($character) && empty($property))
                            {
                                break;
                            }

                            $isProperty = true;
                            $property .= $character;
                        }
                    }
                }
            }

            if (($singleLine || $multiLine) && !$endSingleLine)
            {
                $comments[$commentCounter][] = $characterOffset;
                unset($characters[$characterOffset]);

                if ($endMultiLine)
                {
                    $comments[$commentCounter][] = $characterOffset + 1;
                    unset($characters[$characterOffset + 1]);
                }
            }

            if ($endSingleLine)
            {
                $singleLine = false;
                $endSingleLine = false;
                $commentCounter++;
            }

            if ($endMultiLine)
            {
                $multiLine    = false;
                $endMultiLine = false;
                $commentCounter++;
            }
        }

        $json = implode('', $characters);

        foreach ($properties as $property)
        {
            $json = preg_replace_callback(
                "/({$property})(\s{0,})\:/",
                array(
                    $this,
                    'quoteProperties'
                ),
                $json
            );
        }

        return $json;
    }

    public function quoteProperties($matches)
    {
        return '"'.$matches[1].'":';
    }
}

?>