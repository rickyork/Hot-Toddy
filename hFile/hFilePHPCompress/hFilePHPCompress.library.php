<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy File PHP Compress Library
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
# @description
# <h1>PHP Compression Library</h1>
# <p>
#   Tokenizes and removes white space and comments from PHP files.
# </p>
# @end

class hFilePHPCompressLibrary extends hPlugin {

    private $hFileUtilities;

    public function hConstructor()
    {

    }

    public function &all()
    {
         # @return hFilePHPCompressLibrary

         # @description
         # <h2>Scanning for Framework PHP Files</h2>
         # <p>
         #  Scans for all PHP files within Hot Toddy, then tokenizes and compresses each file.
         # </p>
         # @end

        $this->hFileUtilities = $this->library(
            'hFile/hFileUtilities',
            array(
                'autoScanEnabled' => true,
                'fileTypes' => array(
                    'php'
                )
            )
        );

        $files = $this->hFileUtilities->getFiles();

        foreach ($files as $file)
        {
            $this->console('Tokenizing: '.$file);
            $this->tokenize($file);
        }

        return $this;
    }

    public function tokenize($file)
    {
        $cachePath = dirname($file).'/Cache';

        $cachedFilePath = dirname($file).'/.'.basename($file);

        $cachedFileExists = file_exists($cachedFilePath);

        if ($cachedFileExists)
        {
            $cachedFileMTime  = filemtime($cachedFilePath);
        }

        $sourceFileMTime  = filemtime($file);

        if (!$cachedFileExists || $sourceFileMTime > $cachedFileMTime || $this->shellArgumentExists('force', '--force'))
        {
            $tokens = token_get_all(
                file_get_contents($file)
            );

            $buffer = '';

            while (list($i, $token) = each($tokens))
            {
                if (is_array($token))
                {
                    $name = $token[0];
                    $source = $token[1];

                    switch ($name)
                    {
                        case T_COMMENT:
                        {
                            break;
                        }
                        case T_WHITESPACE:
                        {
                            $buffer .= ' ';
                            break;
                        }
                        default:
                        {
                            $buffer .= $source;
                        }
                    }
                }
                else
                {
                    $buffer .= $token;
                }
            }

            $this->console("Cached to: {$cachedFilePath}");

            //$buffer = preg_replace_callback('/(\s+)/m', array($this, 'fixWhitespace'), $buffer);

            file_put_contents($cachedFilePath, $buffer);
        }
    }

    public function fixWhitespace($matches)
    {
        return ' ';
    }
}

?>