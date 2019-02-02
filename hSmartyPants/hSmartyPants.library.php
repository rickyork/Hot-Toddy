<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Smarty Pants Library
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
#
# Author: John Gruber
# http://daringfireball.net/projects/smartypants/
#
# See the readme or POD for details, installation instructions, and license information.
# http://daringfireball.net/projects/downloads/SmartyPants_1.5.1.zip
#
# Copyright © 2003-2004 John Gruber
# Ported to PHP by Richard York

class hSmartyPantsLibrary extends hPlugin {

    private $attribute = 1;
    private $tagsToSkip = '<(/?)(?:pre|code|kbd|script|math)[\s>]';

    public function modifier($text, $attribute = null)
    {
        return $this->get($text, $attribute);
    }

    public function get($text, $attribute = null)
    {
        if (empty($attribute))
        {
            $attribute = $this->attribute;
        }

        if (empty($attribute))
        {
            return $text;
        }

        $stupefy = false;
        $convertQuotes = false;

        switch ($attribute)
        {
            case 1:
            {
                $quotes = 1;
                $backticks = 1;
                $dashes = 1;
                $ellipses = 1;
                break;
            }
            case 2:
            {
                $quotes = 1;
                $backticks = 1;
                $dashes = 2;
                $ellipses = 1;
                break;
            }
            case 3:
            {
                $quotes = 1;
                $backticks = 1;
                $dashes = 3;
                $ellipses = 1;
                break;
            }
            case -1:
            {
                $stupefy = 1;
                break;
            }
            default:
            {
                $characters = preg_split('//', $attribute);

                foreach ($characters as $character)
                {
                    switch ($character)
                    {
                        case 'q':
                        {
                            $quotes = 1;
                            break;
                        }
                        case 'b':
                        {
                            $backticks = 1;
                            break;
                        }
                        case 'B':
                        {
                            $backticks = 2;
                            break;
                        }
                        case 'd':
                        {
                            $dashes = 1;
                            break;
                        }
                        case 'D':
                        {
                            $dashes = 2;
                            break;
                        }
                        case 'i':
                        {
                            $dashes = 3;
                            break;
                        }
                        case 'e':
                        {
                            $ellipses = 1;
                            break;
                        }
                        case 'w':
                        {
                            $convertQuotes = true;
                            break;
                        }
                    }
                }
            }
        }

        $tokens = $this->tokenizeHTML($text);

        $result = '';

        $inPreFormatted = false;

        $previousTokenCharacter = '';

        foreach ($tokens as $token)
        {
            if ($token[0] == 'tag')
            {
                $result .= $token[1];

                if (preg_match("@{$this->tagsToSkip}@", $token[1], $matches))
                {
                    $inPreFormatted = !(isset($matches[1]) && $matches[1] == '/');
                }
            }
            else
            {
                $token = $token[1];

                $lastCharacter = substr($token, -1);

                if (!$inPreFormatted)
                {
                    $token = $this->processEscapes($token);

                    if ($convertQuotes)
                    {
                        $token = preg_replace('/&quot;', '"', $token);
                    }

                    if ($dashes)
                    {
                        switch ($dashes)
                        {
                            case 1:
                            {
                                $token = $this->educateDashes($token);
                                break;
                            }
                            case 2:
                            {
                                $token = $this->educateDashesOldSchool($token);
                                break;
                            }
                            case 3:
                            {
                                $token = $this->educateDashesOldSchoolInverted($token);
                                break;
                            }
                        }
                    }

                    if ($ellipses)
                    {
                        $token = $this->educateEllipses($token);
                    }

                    if ($backticks)
                    {
                        $token = $this->educateBackticks($token);

                        if ($backticks == 2)
                        {
                            $token = $this->educateSingleBackticks($token);
                        }
                    }

                    if ($quotes)
                    {
                        if ($token == "'")
                        {
                            # Special case: single-character ' token
                            if (preg_match('/\S/', $previousTokenCharacter))
                            {
                                $token = '&rsquo;';
                            }
                            else
                            {
                                $token = '&lsquo;';
                            }
                        }
                        else if ($token == '"')
                        {
                            # Special case: single-character " token
                            if (preg_match('/\S/', $previousTokenCharacter))
                            {
                                $token = '&rdquo;';
                            }
                            else
                            {
                                $token = '&ldquo;';
                            }
                        }
                        else
                        {
                            # Normal case:
                            $token = $this->educateQuotes($token);
                        }
                    }

                    if ($stupefy)
                    {
                        $token = $this->stupefyEntities($token);
                    }
                }

                $previousTokenCharacter = $lastCharacter;
                $result .= $token;
            }
        }

        # Other special characters
        return str_ireplace(
            array(
                '(c)',
                '(r)',
                '(tm)',
            ),
            array(
                '&copy;',
                '&reg;',
                '&trade;'
            ),
            $result
        );
    }

    public function smartDashes($text, $attribute = null)
    {
        if (empty($attribute))
        {
            $attribute = $this->attribute;
        }

        if (empty($attribute))
        {
            return $text;
        }

        # reference to the subroutine to use for dash education, default to EducateDashes:
        $dashMethod = 'educateDashes';

        switch ($attribute)
        {
            case 2:
            {
                $dashMethod = 'educateDashesOldSchool';
                break;
            }
            case 3:
            {
                $dashMethod = 'educateDashesOldSchoolInverted';
                break;
            }
        }

        $tokens = $this->tokenizeHTML($text);

        $result = '';

        $inPreFormatted = false;  # Keep track of when we're inside <pre> or <code> tags

        foreach ($tokens as $token)
        {
            if ($token[0] == 'tag')
            {
                # Don't mess with quotes inside tags
                $result .= $token[1];

                if (preg_match("@{$this->tagsToSkip}@", $token[1], $matches))
                {
                    $inPreFormatted = !(isset($matches[1]) && $matches[1] == '/');
                }
            }
            else
            {
                $token = $token[1];

                if (!$inPreFormatted)
                {
                    $token = $this->processEscapes($token);
                    $token = $this->{"{$dashMethod}"}($token);
                }

                $result .= $token;
            }
        }

        return $result;
    }

    public function smartEllipses($text, $attribute = null)
    {
        # Paramaters:
        if (empty($attribute))
        {
            $attribute = $this->attribute;
        }

        if (empty($attribute))
        {
            # do nothing;
            return $text;
        }

        $tokens = $this->tokenizeHTML($text);

        $result = '';

        $inPreFormatted = false;  # Keep track of when we're inside <pre> or <code> tags

        foreach ($tokens as $token)
        {
            if ($token[0] == 'tag')
            {
                # Don't mess with quotes inside tags
                $result .= $token[1];

                if (preg_match("@{$this->tagsToSkip}@", $token[1], $matches))
                {
                    $inPreFormatted = !(isset($matches[1]) && $matches[1] == '/');
                }
            }
            else
            {
                $token = $token[1];

                if (!$inPreFormatted)
                {
                    $token = $this->processEscapes($token);
                    $token = $this->educateEllipses($token);
                }

                $result .= $token;
            }
        }

        return $result;
    }

    public function smartQuotes($text, $attribute = null)
    {
        if (empty($attribute))
        {
            $attribute = $this->attribute;
        }

        if (empty($attribute))
        {
            # do nothing;
            return $text;
        }

        $doBackticks = false;

        if ($attribute == 2)
        {
            # smarten ``backticks'' -style quotes
            $doBackticks = true;
        }

        # Special case to handle quotes at the very end of $text when preceded by
        # an HTML tag. Add a space to give the quote education algorithm a bit of
        # context, so that it can guess correctly that it's a closing quote:
        $addExtraSpace = false;

        if (preg_match("/>['\"]\\z/", $text))
        {
            $addExtraSpace = true; # Remember, so we can trim the extra space later.
            $text .= ' ';
        }

        $tokens = $this->tokenizeHTML($text);
        $result = '';

        $inPreFormatted = false;  # Keep track of when we're inside <pre> or <code> tags

        $previousTokenCharacter = '';     # This is a cheat, used to get some context

        # for one-character tokens that consist of just a quote char. What we do is remember
        # the last character of the previous text token, to use as context to curl single-
        # character quote tokens correctly.

        foreach ($tokens as $token)
        {
            if ($token[0] == 'tag')
            {
                # Don't mess with quotes inside tags
                $result .= $token[1];

                if (preg_match("@{$this->tagsToSkip}@", $token[1], $matches))
                {
                    $inPreFormatted = !(isset($matches[1]) && $matches[1] == '/');
                }
            }
            else
            {
                $token = $token[1];

                $lastCharacter = substr($token, -1); # Remember last char of this token before processing.

                if (!$inPreFormatted)
                {
                    $token = $this->processEscapes($token);

                    if ($doBackticks)
                    {
                        $token = $this->educateBackticks($t);
                    }

                    if ($token == "'")
                    {
                        # Special case: single-character ' token
                        if (preg_match('/\S/', $previousTokenCharacter))
                        {
                            $token = '&rsquo;';
                        }
                        else
                        {
                            $token = '&lsquo;';
                        }
                    }
                    else if ($token == '"')
                    {
                        # Special case: single-character " token
                        if (preg_match('/\S/', $previousTokenCharacter))
                        {
                            $token = '&rdquo;';
                        }
                        else
                        {
                            $token = '&ldquo;';
                        }
                    }
                    else
                    {
                        # Normal case:
                        $token = $this->educateQuotes($token);
                    }
                }

                $previousTokenCharacter = $lastCharacter;
                $result .= $token;
            }
        }

        if ($addExtraSpace)
        {
            preg_replace('/ \z/', '', $result);  # Trim trailing space if we added one earlier.
        }

        return trim($result);
    }

    private function tokenizeHTML($string)
    {
        #   Parameter:  String containing HTML markup.
        #   Returns:    An array of the tokens comprising the input
        #               string. Each token is either a tag (possibly with nested,
        #               tags contained therein, such as <a href="<MTFoo>">, or a
        #               run of text between tags. Each element of the array is a
        #               two-element array; the first is either 'tag' or 'text';
        #               the second is the actual value.
        #
        #
        #   Regular expression derived from the _tokenize() subroutine in
        #   Brad Choate's MTRegex plugin.
        #   <http://www.bradchoate.com/past/mtregex.php>

        $index = 0;
        $tokens = array();

        $parts = preg_split(
            '{('.
                '(?s:<!(?:--.*?--\s*)+>)|'.    # comment
                '(?s:<\?.*?\?>)|'.            # processing instruction
                                           # regular tags
                '(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)'.
            ')}',
            $string,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        foreach ($parts as $part)
        {
            if (++$index % 2 && !empty($part))
            {
                $tokens[] = array('text', $part);
            }
            else
            {
                $tokens[] = array('tag', $part);
            }
        }

        return $tokens;
    }

    #   Parameter: $string.
    #   Returns:   The string, with after processing the following backslash
    #              escape sequences. This is useful if you want to force a "dumb"
    #              quote or other character to appear.

    public function processEscapes($string)
    {
        return str_replace(
            array(
                '\\\\',
                '\"',
                "\'",
                '\.',
                '\-',
                '\`'
            ),
            array(
                '&#92;',   # backslash
                '&quot;',  # &#34; quote
                '&apos;',  # &#39; apostrophe
                '&#46;',   # period
                '&#45;',   # hyphen
                '&#96;'    # backtick
            ),
            $string
        );
    }

    #   Parameter:  String.
    #   Returns:    The string, with each SmartyPants HTML entity translated to
    #               its ASCII counterpart.
    #
    #   Example input:  &#8220;Hello &#8212; world.&#8221;
    #   Example output: "Hello -- world."
    public function stupefyEntities($string)
    {
        return str_ireplace(
            array(
                '&#8211;', '&ndash;', '&#x2013;',
                '&#8212;', '&mdash;', '&#x2014;',

                '&#8216;', '&lsquo;', '&#x2018;',
                '&#8217;', '&rsquo;', '&#x2019;',

                '&#8220;', '&ldquo;', '&#x201C;',
                '&#8221;', '&rdquo;', '&#x201D;',

                '&#8230;', '&hellip;', '&#x2026;'
            ),
            array(
                '-', '-', '-',
                '--', '--', '--',

                "'", "'", "'",
                "'", "'", "'",

                '"', '"', '"',
                '"', '"', '"',

                '...', '...', '...'
            ),
            $string
        );
    }

    #   Parameter:  $string
    #   Returns:    The string, with ``backticks'' -style double quotes
    #               translated into HTML curly quote entities.
    public function educateBackticks($string)
    {
        return str_replace(
            array(
                "``",
                "''",
            ),
            array(
                '&ldquo;',
                '&rdquo;'
            ),
            $string
        );
    }

    #   Parameter:  $string
    #   Returns:    The string, with `backticks' -style single quotes
    #               translated into HTML curly quote entities.
    function educateSingleBackticks($string)
    {
        return str_replace(
            array(
                "`",
                "'",
            ),
            array(
                '&lsquo;',
                '&rsquo;'
            ),
            $string
        );
    }

    #   Parameter:  $string
    #
    #   Returns:    The string, with each instance of "--" translated to
    #               an em-dash HTML entity.
    public function educateDashes($string)
    {
        return str_replace('--', '&mdash;', $string);
    }

    #   Parameter:  $string
    #
    #   Returns:    The string, with each instance of "--" translated to
    #               an en-dash HTML entity, and each "---" translated to
    #               an em-dash HTML entity.
    public function educateDashesOldSchool($string)
    {
        return str_replace(
            array(
                "---",
                "--",
            ),
            array(
                '&mdash;',
                '&ndash;'
            ),
            $string
        );
    }

    #   Parameter:  $string
    #
    #   Returns:    The string, with each instance of "--" translated to
    #               an em-dash HTML entity, and each "---" translated to
    #               an en-dash HTML entity. Two reasons why: First, unlike the
    #               en- and em-dash syntax supported by
    #               EducateDashesOldSchool(), it's compatible with existing
    #               entries written before SmartyPants 1.1, back when "--" was
    #               only used for em-dashes.  Second, em-dashes are more
    #               common than en-dashes, and so it sort of makes sense that
    #               the shortcut should be shorter to type. (Thanks to Aaron
    #               Swartz for the idea.)
    public function educateDashesOldSchoolInverted($string)
    {
        return str_replace(
            array(
                '---',
                '--',
            ),
            array(
                '&ndash;',
                '&mdash;'
            ),
            $string
        );
    }

    #   Parameter:  $string
    #   Returns:    The string, with each instance of "..." translated to
    #               an ellipsis HTML entity. Also converts the case where
    #               there are spaces between the dots.
    public function educateEllipses($string)
    {
        return str_replace(
            array(
                "...",
                ". . ."
            ),
            '&hellip;',
            $string
        );
    }

    #   Parameter:  $string
    #   Returns:    The string, with "educated" curly quote HTML entities.
    public function educateQuotes($string)
    {
        # Make our own "punctuation" character class, because the POSIX-style
        # [:PUNCT:] is only available in Perl 5.6 or later:
        $punctuationClass = "[!\"#\\$\\%'()*+,-.\\/:;<=>?\\@\\[\\\\\]\\^_`{|}~]";

        # Special case if the very first character is a quote
        # followed by punctuation at a non-word-break. Close the quotes by brute force:
        $string = preg_replace(
            array(
                "/^'(?={$punctuationClass}\\B)/",
                "/^\"(?={$punctuationClass}\\B)/"
            ),
            array(
                '&rsquo;',
                '&rdquo;'
            ),
            $string
        );

        # Special case for double sets of quotes, e.g.:
        #   <p>He said, "'Quoted' words in a larger quote."</p>
        $string = preg_replace(
            array(
                "/\"'(?=\w)/",
                "/'\"(?=\w)/"
            ),
            array(
                '&ldquo;&lsquo;',
                '&lsquo;&ldquo;'
            ),
            $string
        );

        # Special case for decade abbreviations (the '80s):
        $string = preg_replace(
            "/'(?=\\d{2}s)/",
            '&rsquo;',
            $string
        );

        $closeClass = '[^\ \t\r\n\[\{\(\-]';
        $decimalDashes = '&ndash;|&mdash;';

        # Get most opening single quotes:
        $string = preg_replace(
            "{(
                    \\s |              # a whitespace char, or
                    &nbsp; |           # a non-breaking space entity, or
                    -- |               # dashes, or
                    &[mn]dash; |       # named dash entities
                    {$decimalDashes} | # or decimal entities
                    &\\#x201[34];      # or hex
                )'                     # the quote
                (?=\\w)                # followed by a word character
            }xi",
            '\1&lsquo;',
            $string
        );

        # Single closing quotes:
        $string = preg_replace(
            "{
                ({$closeClass})?
                '
                (?(1)|          # If \$1 captured, then do nothing;
                (?=\\s | s\\b)  # otherwise, positive lookahead for a whitespace
                )               # char or an 's' at a word ending position. This
                                # is a special case to handle something like:
                                # \"<i>Custer</i>'s Last Stand.\"
            }xi",
            '\1&rsquo;',
            $string
        );

        # Any remaining single quotes should be opening ones:
        $string = str_replace("'", '&lsquo;', $string);

        # Get most opening double quotes:
        $string = preg_replace(
            "{
                (
                    \\s |              # a whitespace char, or
                    &nbsp; |           # a non-breaking space entity, or
                    -- |               # dashes, or
                    &[mn]dash; |       # named dash entities
                    {$decimalDashes} | # or decimal entities
                    &\\#x201[34];      # or hex
                )
                \"                     # the quote
                (?=\\w)                # followed by a word character
            }ix",
            '\1&ldquo;',
            $string
        );

        # Double closing quotes:
        $string = preg_replace(
            "{
                ({$closeClass})?
                \"
                (?(1)|(?=\\s))   # If \$1 captured, then do nothing;
                                 # if not, then make sure the next char is whitespace.
            }ix",
            '\1&rdquo;',
            $string
        );

        # Any remaining quotes should be opening ones.
        return str_replace('"', '&ldquo;', $string);
    }
}

?>