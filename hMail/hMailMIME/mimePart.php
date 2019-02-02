<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Mail MIME
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

class Mail_MIMEPart {

    /**
    * This file is forked from the htmlMimeMail5 package (http://www.phpguru.org/)
    *
    * htmlMimeMail5 is free software; you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation; either version 2 of the License, or
    * (at your option) any later version.
    *
    * htmlMimeMail5 is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with htmlMimeMail5; if not, write to the Free Software
    * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    *
    * © Copyright 2015 Richard Heyes
    */

    /**
    *
    *  Raw mime encoding class
    *
    * What is it?
    *   This class enables you to manipulate and build
    *   a mime email from the ground up.
    *
    * Why use this instead of mime.php?
    *   mime.php is a userfriendly api to this class for
    *   people who aren't interested in the internals of
    *   mime mail. This class however allows full control
    *   over the email.
    *
    * Eg.
    *
    * // Since multipart/mixed has no real body, (the body is
    * // the subpart), we set the body argument to blank.
    *
    * $parameters['content_type'] = 'multipart/mixed';
    * $email = new Mail_mimePart('', $parameters);
    *
    * // Here we add a text part to the multipart we have
    * // already. Assume $body contains plain text.
    *
    * $parameters['content_type'] = 'text/plain';
    * $parameters['encoding']     = '7bit';
    * $text = $email->addSubPart($body, $parameters);
    *
    * // Now add an attachment. Assume $attach is
    * the contents of the attachment
    *
    * $parameters['content_type'] = 'application/zip';
    * $parameters['encoding']     = 'base64';
    * $parameters['disposition']  = 'attachment';
    * $parameters['dfilename']    = 'example.zip';
    * $attach =& $email->addSubPart($body, $parameters);
    *
    * // Now build the email. Note that the encode
    * // function returns an associative array containing two
    * // elements, body and headers. You will need to add extra
    * // headers, (eg. Mime-Version) before sending.
    *
    * $email = $message->encode();
    * $email['headers'][] = 'Mime-Version: 1.0';
    *
    *
    * Further examples are available at http://www.phpguru.org
    *
    * TODO:
    *  - Set encode() to return the $obj->encoded if encode()
    *    has already been run. Unless a flag is passed to specifically
    *    re-build the message.
    *
    * @author  Richard Heyes <richard@phpguru.org>
    * @version $Revision: 1.3 $
    * @package Mail
    */

    /**
    * The encoding type of this part
    * @var string
    */
    private $encoding;

    /**
    * An array of subparts
    * @var array
    */
    private $subparts;

    /**
    * The output of this part after being built
    * @var string
    */
    private $encoded;

    /**
    * Headers for this part
    * @var array
    */
    private $headers;

    /**
    * The body of this part (not encoded)
    * @var string
    */
    private $body;

    /**
    * Constructor.
    *
    * Sets up the object.
    *
    * @param $body   - The body of the mime part if any.
    * @param $parameters - An associative array of parameters:
    *                  content_type - The content type for this part eg multipart/mixed
    *                  encoding     - The encoding to use, 7bit, 8bit, base64, or quoted-printable
    *                  cid          - Content Id to apply
    *                  disposition  - Content disposition, inline or attachment
    *                  dfilename    - Optional filename parameter for content disposition
    *                  description  - Content description
    *                  charset      - Character set to use
    * @access public
    */
    public function __construct($body = '', $parameters = array())
    {
        if (!defined('MAIL_MIMEPART_CRLF'))
        {
            define(
                'MAIL_MIMEPART_CRLF',
                defined('MAIL_MIME_CRLF') ? MAIL_MIME_CRLF : "\r\n", true
            );
        }

        foreach ($parameters as $key => $value)
        {
            switch ($key)
            {
                case 'content_type':
                {
                    $headers['Content-Type'] =
                        $value . (isset($charset) ? '; charset="' . $charset . '"' : '');

                    break;
                }
                case 'encoding':
                {
                    $this->encoding = $value;
                    $headers['Content-Transfer-Encoding'] = $value;
                    break;
                }
                case 'cid':
                {
                    $headers['Content-Id'] = '<' . $value . '>';
                    break;
                }
                case 'disposition':
                {
                    $headers['Content-Disposition'] =
                        $value . (isset($dfilename) ? '; filename="' . $dfilename . '"' : '');

                    break;
                }
                case 'dfilename':
                {
                    if (isset($headers['Content-Disposition']))
                    {
                        $headers['Content-Disposition'] .= '; filename="' . $value . '"';
                    }
                    else
                    {
                        $dfilename = $value;
                    }

                    break;
                }
                case 'description':
                {
                    $headers['Content-Description'] = $value;
                    break;
                }
                case 'charset':
                {
                    if (isset($headers['Content-Type']))
                    {
                        $headers['Content-Type'] .= '; charset="' . $value . '"';
                    }
                    else
                    {
                        $charset = $value;
                    }

                    break;
                }
            }
        }

        // Default content-type
        if (!isset($headers['Content-Type']))
        {
            $headers['Content-Type'] = 'text/plain';
        }

        // Default encoding
        if (!isset($this->encoding))
        {
            $this->encoding = '7bit';
        }

        // Assign stuff to member variables
        $this->encoded = array();
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
    * Encodes and returns the email. Also stores
    * it in the encoded member variable
    *
    * @return An associative array containing two elements,
    *         body and headers. The headers element is itself
    *         an indexed array.
    */
    public function encode()
    {
        $encoded =& $this->encoded;

        if (!empty($this->subparts))
        {
            srand((double) microtime() * 1000000);

            $boundary = '=_' . md5(uniqid(rand()) . microtime());

            $this->headers['Content-Type'] .=
                ';'.MAIL_MIMEPART_CRLF."\tboundary=".'"'.$boundary.'"';

            // Add body parts to $subparts
            for ($i = 0; $i < count($this->subparts); $i++)
            {
                $headers = array();
                $tmp = $this->subparts[$i]->encode();

                foreach ($tmp['headers'] as $key => $value)
                {
                    $headers[] = $key . ': ' . $value;
                }

                $subparts[] =
                    implode(MAIL_MIMEPART_CRLF, $headers) .
                    MAIL_MIMEPART_CRLF .
                    MAIL_MIMEPART_CRLF .
                    $tmp['body'];
            }

            $encoded['body'] =
                '--'.$boundary.MAIL_MIMEPART_CRLF.
                implode('--'.$boundary.MAIL_MIMEPART_CRLF, $subparts).
                '--'.$boundary.'--'.MAIL_MIMEPART_CRLF;
        }
        else
        {
            $encoded['body'] =
                $this->getEncodedData($this->body, $this->encoding) .
                MAIL_MIMEPART_CRLF;
        }

        // Add headers to $encoded
        $encoded['headers'] =& $this->headers;

        return $encoded;
    }

    /**
    * Adds a subpart to current mime part and returns
    * a reference to it
    *
    * @param $body   The body of the subpart, if any.
    * @param $parameters The parameters for the subpart, same
    *                as the $parameters argument for constructor.
    * @return A reference to the part you just added.
    */
    public function addSubPart($body, $parameters)
    {
        $this->subparts[] = new Mail_MIMEPart($body, $parameters);

        return $this->subparts[count($this->subparts) - 1];
    }

    /**
    * Returns encoded data based upon encoding passed to it
    *
    * @param $data     The data to encode.
    * @param $encoding The encoding type to use, 7bit, base64,
    *                  or quoted-printable.
    */
    private function getEncodedData($data, $encoding)
    {
        switch ($encoding)
        {
            case '8bit':
            case '7bit':
            {
                return $data;
            }
            case 'quoted-printable':
            {
                return $this->quotedPrintableEncode($data);
            }
            case 'base64':
            {
                return rtrim(
                    chunk_split(
                        base64_encode($data),
                        76,
                        MAIL_MIMEPART_CRLF
                    )
                );
            }
            default:
            {
                return $data;
            }
        }
    }

    /**
    * Encodes data to quoted-printable standard.
    *
    * @param $input    The data to encode
    * @param $maximumLineLength Optional max line length. Should
    *                  not be more than 76 chars
    */
    private function quotedPrintableEncode($input, $maximumLineLength = 76)
    {
        $lines = preg_split("/\r?\n/", $input);
        $eol = MAIL_MIMEPART_CRLF;
        $escape = '=';
        $output = '';

        while (list(, $line) = each($lines))
        {
            $lineLengh = strlen($line);
            $newline = '';

            for ($lineCounter = 0; $lineCounter < $lineLengh; $lineCounter++)
            {
                $character = substr($line, $lineCounter, 1);
                $dec = ord($character);

                if (($dec == 32) && ($lineCounter == ($lineLengh - 1)))    // convert space at eol only
                {
                    $character = '=20';
                }
                else if ($dec == 9)
                {
                    // Do nothing if a tab.
                }
                else if (($dec == 61) OR ($dec < 32 ) || ($dec > 126))
                {
                    $character = $escape.strtoupper(sprintf('%02s', dechex($dec)));
                }

                if ((strlen($newline) + strlen($character)) >= $maximumLineLength)          // MAIL_MIMEPART_CRLF is not counted
                {
                    $output .= $newline.$escape.$eol;                    // soft line break; " =\r\n" is okay
                    $newline = '';
                }

                $newline .= $character;
            }

            $output .= $newline.$eol;
        }

        $output = substr($output, 0, -1 * strlen($eol)); // Don't want last crlf

        return $output;
    }
}

?>