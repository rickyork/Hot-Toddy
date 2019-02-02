
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
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

class HString

    static $begin;
    static $end;

    def scrubRequestData()

        array_walk($_POST,   array(__CLASS__, 'escapeStringCallback'));
        array_walk($_GET,    array(__CLASS__, 'escapeStringCallback'));
        array_walk($_COOKIE, array(__CLASS__, 'escapeStringCallback'));
        array_walk($_SERVER, array(__CLASS__, 'escapeStringCallback'));

        if (isset($GLOBALS['argv']))
        {
            array_walk($GLOBALS['argv'], array(__CLASS__, 'escapeStringCallback'));
        }
    end

    def escapeStringCallback(&$value)
    {
        if (is_array($value))
        {
            foreach ($value as $i => $string)
            {
                if (is_array($value[$i]))
                {
                    array_walk($value[$i], array(__CLASS__, 'escapeStringCallback'));
                }
                else
                {
                    $value[$i] = self::escapeAndEncode($value[$i]);
                }
            }
        }
        else
        {
            $value = self::escapeAndEncode($value);
        }
    end

    def escapeAndEncode($value)
    {
        $value = htmlspecialchars($value, ENT_QUOTES);
        $value = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');

        return $value;
    end


    #
    # Apply to all values in an array:
    #
    #   Decode HTML entities x2
    #   Strip away any HTML tags
    #   Re-encode HTML special characters
    #   Trim for leading / trailing space
    #   Escape special characters.
    #
    def scrubArray(array, length = 0)

        foreach ($array as $key => $value)
        {
            if (is_array($array[$key]))
            {
                self::scrubArray($array[$key], $length);
            }
            else
            {
                $array[$key] = self::scrubValue($array[$key], (is_array($length)? $length[$key] : $length));
            }
        }
    end

    def scrubValue($string, $length = 0)

        $string = mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
        $string = strip_tags($string);
        $string = htmlspecialchars($string, ENT_QUOTES);
        $string = trim($string);

        if (!empty($length))
        {
            $string = substr($string, 0, $length);
        }

        // Special characters will be mangled otherwise
        $string = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');

        return $string;
    end

    def clip(&$string, $length)
        if (!empty($length))
        {
            $string = substr($string, 0, $length);
        }
    end

    #
    # Limit the characters allowed in a string.
    # Allow letters, numbers, underscores, hyphens, spaces, periods, exclamation points, 
    # question marks, and at signs.
    #
    def scrubString($string, $length = 0)  
        $matches = array();
        
        self::clip($string, $length);

        preg_match_all("/([A-Z]|[0-9]|[a-z]|\_|\-|\s|\.|\!|\?|\@)+/", $string, $matches);
        return implode($matches[0]);
    end

    #
    # Ensures that a string only contains letters, numbers, underscores, or hyphens.
    # Everything else is discarded.
    #
    def scrubWord($string, $length = 0)
        $matches = array();

        self::clip($string, $length);

        preg_match_all("/([A-Z]|[0-9]|[a-z]|\_|\-)+/", $string, $matches);
        return implode($matches[0]);
    end

    def scrubHTML($string)

        $string = mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');

        $count = 0;

        # Remove the most obvious of elements considered to be dangerous
        $string = preg_replace(
            array(
                '/<script[^>]*?>.*?<\/script>/si',                                        # Strip out javascript
                '/<link[^>]*?>/si',                                                       # Strip external CSS
                '/<embed[^>]*?>/si',                                                      # Strip embed element
                '/<object[^>]*?>.*?<\/object>/si',                                        # Strip out object
                '/<style[^>]*?>.*?<\/style>/siU',                                         # Strip style tags properly
                '/<!DOCTYPE[^>]*?>/',                                                     # Strip DOCTYPE
                '/<![\s\S]*?--[ \t\n\r]*>/',                                              # Strip multi-line comments including CDATA
                '/<iframe[^>]*?>.*?<\/iframe>/si',                                        # Strip inline frame
                '/<form[^>]*?>.*?<\/form>/si',                                            # Strip out forms
                '/<input[^>]*?>/si',                                                      # Strip out inputs
                '/<textarea[^>]*?>.*?<\/textarea>/si',                                    # Strip out textareas
                '/<select[^>]*?>.*?<\/select>/si',                                        # Strip out select
                '/<button[^>]*?>.*?<\/button>/si',                                        # Strip out buttons
            ),
            '',
            $string,
            -1,
            $count
        );

        # No font tags please.
        $string = preg_replace(
            array(
                '/<font[^>]*?>/si',
                '/<\/font>/si',
                '/<p[^>]*?>(\s|\&nbsp\;)*?<\/p>/siU',         # Strip out empty paragraph tags
                '/<span[^>]*?>(\s|\&nbsp\;)*?<\/span>/siU',   # Strip out empty span tags
                '/\&nbsp\;/'
            ),
            '',
            $string,
            -1
        );

        $string = preg_replace_callback(
            array(
                '/<[^>]*\son[^>]*?("|\')?(.)*("|\')?>/siU',                               # Strip elements that contain event handlers
                '/<[^>]*\sdom[^>]*?("|\')?(.)*("|\')?>/siU',                              # Strip elements that contain event handlers
                '/<[^>]*\sid[^>]*?("|\')?(.)*("|\')?>/siU',                               # Strip elements that contain ids
                '/<[^>]*\sclass[^>]*?("|\')?(.)*("|\')?>/siU',                            # Strip elements that contain classes
                '/<[^>]*\sstyle[^>]*?("|\')?(.)*(expression|behavior)?(.)*("|\')?>/siU',  # Strip elements that contain dynamic CSS
            ),
            'hString::scrubHTMLCallback',
            $string,
            -1   
        );

        # Default to being paranoid if items in the first group are discovered.
        if ($count > 0)
        {
            $string = strip_tags($string);
        }

        return trim(htmlspecialchars(mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8'), ENT_QUOTES));
    end

    def scrubHTMLCallback($matches)
    {
        # Keeps the document (mostly) valid HTML/XHTML..  can't help what monstrosities the 
        # client-side editor has otherwise inserted.
        $submatches = array();

        preg_match('/<.*\s/uiU', $matches[0], $submatches);

        if (isset($submatches[0]))
        {
            return trim($submatches[0]).'>';
        }
    end

    #
    # Decodes HTML special characters while preserving UTF-8 characters as
    # entities.
    #
    def decodeHTML($string)
        # mb_convert_encoding, converts all entities to their character equivalent
        # All characters are shifted to their character equivalent, then special characters
        # are re-encoded as entities.
        $string = mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
        $string = mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8');

        return $string;
    end

    #
    # Converts entities to UTF-8 characters, while preserving HTML special
    # character entity encoding.  Use this method to output special characters
    # in XML without defining a DTD.
    #
    def entitiesToUTF8($string, $encodeSpecialCharacters = true)
        if ($encodeSpecialCharacters)
        {
            return htmlspecialchars(mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES'));
        }
        else
        {
            return mb_convert_encoding($string, 'UTF-8', 'HTML-ENTITIES');
        }
    end

    def decodeEntitiesAndUTF8($string)
        return self::entitiesToUTF8($string, false);
    end

    def arrayToUTF8(&$array, $encodeSpecialCharacters = true, $stripQuotes = false)
    {
        foreach ($array as $key => $value)
        {
            if (is_array($array[$key]))
            {
                self::arrayToUTF8($array[$key], $encodeSpecialCharacters);
            }
            else
            {
                $array[$key] = self::entitiesToUTF8($array[$key], $encodeSpecialCharacters);
                
                if ($stripQuotes)
                {
                    $array[$key] = str_replace('"', '', $array[$key]);
                }
            }
        }
    end

    #
    # Use this to store XML in the database (preserves special characters)
    #
    def XMLEncode($xml)

        # To preserve encoding, first encode the whole file
        # Convert all html special characters to entities.
        $xml = htmlspecialchars($xml, ENT_QUOTES);

        # Now convert the special characters to entities.
        $xml = mb_convert_encoding($xml, 'HTML-ENTITIES', 'UTF-8');

        # Encode everything again!
        $xml = htmlspecialchars($xml, ENT_QUOTES);

        return $xml;
    end

    def replaceEntities($string)

        return str_replace(
            array(
                '&mdash;',
                '&ldquo;',
                '&rdquo;',
                '&lsquo;',
                '&rsquo;',
                '&nbsp;',
                '&hellip;'
            ),
            array(
                '-',
                '"',
                '"',
                "'",
                "'",
                ' ',
                '...'
            ),
            $string
        );
    end

    def safelyDecodeURLPath(&$string)

        if (isset($string) && !empty($string))
        {
            $string = str_replace(
                array("'", '"'),
                array('&#039;', '&quot;'),
                hString::decodeHTML($string)
            );
            
            return $string;
        }
    end

    #
    # Tricksey hobbitses must be careful not to leave quote characters lying around
    # so carelessly.
    #
    def safelyDecodeURL(&$string)
    
        if (isset($string) && !empty($string))
        {
            $string = str_replace(
                array("'", '"'),
                array('&#039;', '&quot;'),
                urldecode($string)
            );
            
            return $string;
        }
    end

    #
    # Use this when retrieving XML from the database.
    #
    def XMLDecode($xml)
    {
        # First pass, make special chacter entities back into entities
        $xml = htmlspecialchars_decode($xml, ENT_QUOTES);

        # Decode everything, convert special characters to UTF-8, and everything else to it's character equivalent.
        $xml = hString::decodeEntitiesAndUTF8($xml);

        return $xml;
    end

    def encodeEntities($string, $quote)
        return htmlspecialchars($string, $quote);
    end

    def encodeHTML($html, $mbReverse = false)
        return htmlspecialchars(
            mb_convert_encoding(
                $html,
                $mbReverse? 'UTF-8'         : 'HTML-ENTITIES',
                $mbReverse? 'HTML-ENTITIES' : 'UTF-8'
            ),
            ENT_QUOTES
        );
    end

    def probeForPaths(&$html, $callback)

        # See if there are any images in the HTML message
        #
        # Get all the image urls
        # Add the full image URI to an array
        # Replace the full image URI with only the filename.
        #
        # src=
        # background=
        # url()

        $html = mb_convert_encoding($html, 'UTF-8', 'HTML-ENTITIES');

        $patterns = array(
            '/src[\=][\"|\'](.*)[\"|\']/iUx',
            '/background[\=][\"|\']?(.*)[\"|\']/iUx',
            '/url[\(][\"|\']?(.*)[\"|\']?[\)]/iUx',
            '/href[\=][\"|\'](.*)[\"|\']/iUx'
        );

        foreach ($patterns as $pattern)
        {
            $html = preg_replace_callback($pattern, $callback, $html);
        }

        $html = self::encodeHTML($html);
    end

    def implodeToList($array, $separator = ',', $begin = "'", $end = null)

        if (empty($end))
        {
            $end = $begin;
        }

        self::$begin = $begin;
        self::$end   = $end;

        array_walk($array, array(__CLASS__, 'encloseString'));

        return(implode($separator, $array));
    end

    def encloseString(&$string)
        $string = self::$begin.$string.self::$end;
    end

    def softURLEncode($path)
        return str_replace(
            array('#',   ':',   '?',   '@'),
            array('%23', '%3A', '%3F', '%40'),
            $path
        );
    end

end