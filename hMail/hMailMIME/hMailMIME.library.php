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

require_once(dirname(__FILE__) . '/mimePart.php');

class hMailMIMELibrary extends hPlugin {

    # hMailMIME is based on htmlMimeMail5, written by Richard Heyes and is
    # subject to the terms below.
    #
    # This file is forked from the htmlMimeMail5 package (http://www.phpguru.org/)
    #
    # htmlMimeMail5 is free software; you can redistribute it and/or modify
    # it under the terms of the GNU General Public License as published by
    # the Free Software Foundation; either version 2 of the License, or
    # (at your option) any later version.
    #
    # htmlMimeMail5 is distributed in the hope that it will be useful,
    # but WITHOUT ANY WARRANTY; without even the implied warranty of
    # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    # GNU General Public License for more details.
    #
    # You should have received a copy of the GNU General Public License
    # along with htmlMimeMail5; if not, write to the Free Software
    # Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
    #
    # © Copyright 2015 Richard Heyes

    # The html part of the message
    # @var string
    private $html = '';

    # The text part of the message(only used in TEXT only messages)
    # @private string
    private $text = '';

    # The main body of the message after building
    # @private string
    private $output;

    # An array of embedded images   /objects
    # @private array
    private $htmlImages = array();

    # An array of recognised image types for the findHtmlImages() method
    # @private array
    private $imageTypes = array(
        'gif'  => 'image/gif',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpe'  => 'image/jpeg',
        'bmp'  => 'image/bmp',
        'png'  => 'image/png',
        'tif'  => 'image/tiff',
        'tiff' => 'image/tiff',
        'swf'  => 'application/x-shockwave-flash'
    );

    # Parameters that affect the build process
    # @private array
    private $buildParams;

    # Array of attachments
    # @private array
    private $attachments = array();

    # The main message headers
    # @private array
    private $headers = array();

    # Whether the message has been built or not
    # @private boolean
    private $isBuilt = false;

    # The return path address. If not set the From: address is used instead
    # @private string
    private $returnPath;

    # Sendmail path. Do not include -f
    # @private $sendmailPath
    private $sendmailPath;

    # hMailSMTPServer
    # hMailSMTPHelo
    # hMailSMTPAuthMethod
    private $Mail_RFC822;

    private $smtpServer;
    private $smtpHelo;
    private $smtpAuthMethod;

    public function hConstructor()
    {
        $this->setup();
    }

    public function freeze()
    {
        # Places all data in object properties in an array so that data can be saved
        # for later restoration.

        return serialize(
            array(
                'html' => $this->html,
                'text' => $this->text,
                'output' => $this->output,
                'htmlImages' => $this->htmlImages,
                'imageTypes' => $this->imageTypes,
                'buildParams' => $this->buildParams,
                'attachments' => $this->attachments,
                'headers' => $this->headers,
                'isBuilt' => $this->isBuilt,
                'returnPath' => $this->returnPath,
                'sendmailPath' => $this->sendmailPath,
                'smtpServer' => $this->smtpServer,
                'smtpHelo' => $this->smtpHelo,
                'smtpAuthMethod' => $this->smtpAuthMethod
            )
        );
    }

    public function setReturnPath($returnPath)
    {
        $this->returnPath = $returnPath;
    }

    public function restore($data)
    {
        $data = unserialize($data);

        $this->html = $data['html'];
        $this->text = $data['text'];
        $this->output = $data['output'];
        $this->htmlImages = $data['htmlImages'];
        $this->imageTypes = $data['imageTypes'];
        $this->buildParams = $data['buildParams'];
        $this->attachments = $data['attachments'];
        $this->headers = $data['headers'];
        $this->isBuilt = $data['isBuilt'];
        $this->returnPath = $data['returnPath'];
        $this->sendmailPath = $data['sendmailPath'];
        $this->smtpServer = $data['smtpServer'];
        $this->smtpHelo = $data['smtpHelo'];
        $this->smtpAuthMethod = $data['smtpAuthMethod'];

        $this->setup();
    }

    public function setup()
    {
        # Set these up
        $this->buildParams['html_encoding'] = new QPrintEncoding();
        $this->buildParams['text_encoding'] = new SevenBitEncoding();
        $this->buildParams['html_charset'] = 'ISO-8859-1';
        $this->buildParams['text_charset'] = 'ISO-8859-1';
        $this->buildParams['head_charset'] = 'ISO-8859-1';
        $this->buildParams['text_wrap'] = 998;

        # Make sure the MIME version header is first.
        $this->headers['MIME-Version'] = '1.0';
        $this->headers['X-Mailer'] = 'htmlMimeMail5 <http://www.phpguru.org/>';
    }

    # Accessor to set the CRLF style
    # @param string $crlf CRLF style to use.  Use \r\n for SMTP, and \n for normal.
    public function setCRLF($crlf = "\n")
    {
        if (!defined('CRLF'))
        {
            define('CRLF', $crlf, true);
        }

        if (!defined('MAIL_MIMEPART_CRLF'))
        {
            define('MAIL_MIMEPART_CRLF', $crlf, true);
        }
    }

    # Sets sendmail path and options (optionally) (when directly piping to sendmail)
    # @param string $path Path and options for sendmail command
    public function &setSendmailPath($path)
    {
        $this->sendmailPath = $path;
        return $this;
    }

    # Accessor function to set the text encoding
    # @param object $encoding Text encoding to use
    public function &setTextEncoding(iEncoding $encoding)
    {
        $this->buildParams['text_encoding'] = $encoding;
        return $this;
    }

    # Accessor function to set the HTML encoding
    # @param object $encoding HTML encoding to use
    public function &setHTMLEncoding(iEncoding $encoding)
    {
        $this->buildParams['html_encoding'] = $encoding;
        return $this;
    }

    # Accessor function to set the text charset
    # @param string $charset Character set to use
    public function &setTextCharset($charset = 'ISO-8859-1')
    {
        $this->buildParams['text_charset'] = $charset;
        return $this;
    }

    # Accessor function to set the HTML charset
    # @param string $charset Character set to use
    public function &setHTMLCharset($charset = 'ISO-8859-1')
    {
        $this->buildParams['html_charset'] = $charset;
        return $this;
    }

    # Accessor function to set the header encoding charset
    # @param string $charset Character set to use
    public function &setHeadCharset($charset = 'ISO-8859-1')
    {
        $this->buildParams['head_charset'] = $charset;
        return $this;
    }

    # Accessor function to set the text wrap count
    # @param integer $count Point at which to wrap text
    public function &setTextWrap($count = 998)
    {
        $this->buildParams['text_wrap'] = $count;
        return $this;
    }

    # Accessor to set a header
    #
    # @param string $name  Name of header
    # @param string $value Value of header
    public function &setHeader($name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    # Accessor to set priority. Priority given should be either
    # high, normal or low. Can also be specified numerically,
    # being 1, 3 or 5 (respectively).
    #
    # @param mixed $priority The priority to use.
    public function &setPriority($priority = 'normal')
    {
        switch (strtolower($priority))
        {
            case 'high':
            case '1':
            {
                $this->headers['X-Priority'] = '1';
                $this->headers['X-MSMail-Priority'] = 'High';
                break;
            }
            case 'normal':
            case '3':
            {
                $this->headers['X-Priority'] = '3';
                $this->headers['X-MSMail-Priority'] = 'Normal';
                break;
            }
            case 'low':
            case '5':
            {
                $this->headers['X-Priority'] = '5';
                $this->headers['X-MSMail-Priority'] = 'Low';
                break;
            }
        }

        return $this;
    }

    public function &setMailer($mailer)
    {
        $this->headers['X-Mailer'] .= $mailer;
        return $this;
    }

    # Adds plain text. Use this function when NOT sending html email
    # @param string $text Plain text of email
    public function &setText($text)
    {
        $this->text = $text;
        return $this;
    }

    # Adds HTML to the emails, with an associated text part. If third part is
    # given, images in the email will be loaded from this directory.
    #
    # @param string $html       HTML part of email
    # @param string $images_dir Images directory
    function &setHTML($html, $imageFolder = null)
    {
        $this->html = $html;

        if (!empty($imageFolder))
        {
            $this->findHtmlImages($imageFolder);
        }

        return $this;
    }

    # Function for extracting images from html source. This function will look
    # through the html code supplied by setHTML() and find any file that ends
    # in one of the extensions defined in $obj->imageTypes.  If the file exists
    # it will read it in and embed it, (not an attachment).
    #
    # @param string $images_dir Images directory to look in
    private function findHtmlImages($dir)
    {
        $this->html = str_replace('{$sid}', '', $this->html);

        $htmlImages = array();

        # Build the list of image extensions
        $extensions = array_keys($this->imageTypes);

        preg_match_all('/src[\=][?:"|\'](.*)[?:"|\']/iUx', $this->html, $matches);

        foreach ($matches[1] as $path)
        {
            if (file_exists($dir.$path))
            {
                $htmlImages[] = $path;
                $this->html = str_replace($path, basename($path), $this->html);
            }
        }

        # Go thru found images
        if (!empty($htmlImages))
        {
            # If duplicate images are embedded, they may show up as attachments, so remove them.
            $htmlImages = array_unique($htmlImages);
            sort($htmlImages);

            foreach ($htmlImages as $img)
            {
                if ($image = file_get_contents($dir.$img))
                {
                    $this->addEmbeddedImage(
                        new stringEmbeddedImage(
                            $image,
                            basename($img),
                            $this->imageTypes[preg_replace('#^.*\.(\w{3,4})$#e', 'strtolower("$1")', $img)]
                        )
                    );
                }
            }
        }
    }

    # Adds an image to the list of embedded images.
    # @param string $object Embedded image object
    public function addEmbeddedImage($embeddedImage)
    {
        $embeddedImage->cid = md5(uniqid(time()));

        $this->htmlImages[] = $embeddedImage;
    }

    # Adds a file to the list of attachments.
    # @param string $attachment Attachment object
    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }

    # Adds a text subpart to a mime_part object
    # @param  object $obj
    # @return object      Mime part object
    private function addTextPart(&$message)
    {
        $params = array(
            'content_type' => 'text/plain',
            'encoding' => $this->buildParams['text_encoding']->getType(),
            'charset' => $this->buildParams['text_charset']
        );

        if (!empty($message))
        {
            $message->addSubpart($this->text, $params);
        }
        else
        {
            $message = new Mail_mimePart($this->text, $params);
        }
    }

    # Adds a html subpart to a mime_part object
    # @param object $obj
    # @return object     Mime part object
    private function addHtmlPart(&$message)
    {
        $params = array(
            'content_type' => 'text/html',
            'encoding' => $this->buildParams['html_encoding']->getType(),
            'charset' => $this->buildParams['html_charset']
        );

        if (!empty($message))
        {
            $message->addSubpart($this->html, $params);
        }
        else
        {
            $message = new Mail_mimePart($this->html, $params);
        }
    }

    # Starts a message with a mixed part
    # @return object Mime part object
    private function addMixedPart(&$message)
    {
        $params['content_type'] = 'multipart/mixed';
        $message = new Mail_mimePart('', $params);
    }

    # Adds an alternative part to a mime_part object
    # @param  object $obj
    # @return object      Mime part object
    private function addAlternativePart(&$message)
    {
        $params['content_type'] = 'multipart/alternative';

        if (!empty($message))
        {
            return $message->addSubpart('', $params);
        }
        else
        {
            $message = new Mail_mimePart('', $params);
        }
    }

    # Adds a html subpart to a mime_part object
    # @param  object $obj
    # @return object      Mime part object
    private function addRelatedPart(&$message)
    {
        $params['content_type'] = 'multipart/related';

        if (!empty($message)) {
            return $message->addSubpart('', $params);
        } else {
            $message = new Mail_mimePart('', $params);
        }
    }

    # Adds all html images to a mime_part object
    # @param  object $obj Message object
    private function addHtmlImageParts(&$message)
    {
        foreach ($this->htmlImages as $value)
        {
            $message->addSubpart(
                $value->data,
                array(
                    'content_type' => $value->contentType,
                    'encoding' => $value->encoding->getType(),
                    'disposition'  => 'inline',
                    'dfilename' => $value->name,
                    'cid' => $value->cid
                )
            );
        }
    }

    # Adds all attachments to a mime_part object
    # @param object $obj Message object
    private function addAttachmentParts(&$message)
    {
        foreach ($this->attachments as $value)
        {
            $message->addSubpart(
                $value->data,
                array(
                    'content_type' => $value->contentType,
                    'encoding' => $value->encoding->getType(),
                    'disposition' => 'attachment',
                    'dfilename' => $value->name
                )
            );
        }
    }

    # Builds the multipart message.
    private function build()
    {
        if (!empty($this->htmlImages))
        {
            foreach ($this->htmlImages as $value)
            {
                $quoted = preg_quote($value->name);
                $cid = preg_quote($value->cid);

                $this->html = preg_replace("#src=\"$quoted\"|src='$quoted'#", "src=\"cid:$cid\"", $this->html);
                $this->html = preg_replace("#background=\"$quoted\"|background='$quoted'#", "background=\"cid:$cid\"", $this->html);
            }
        }

        $message = null;
        $attachments = !empty($this->attachments);
        $htmlImages = !empty($this->htmlImages);
        $html = !empty($this->html);
        $text = !$html;

        switch (true)
        {
            case $text:
            {
                $message = null;

                if ($attachments)
                {
                    $this->addMixedPart($message);
                }

                $this->addTextPart($message);

                # Attachments
                $this->addAttachmentParts($message);
                break;
            }
            case $html && !$attachments && !$htmlImages:
            {
                $this->addAlternativePart($message);
                $this->addTextPart($message);
                $this->addHtmlPart($message);
                break;
            }
            case $html && !$attachments && $htmlImages:
            {
                $this->addRelatedPart($message);
                $alt = $this->addAlternativePart($message);

                $this->addTextPart($alt);
                $this->addHtmlPart($alt);

                # HTML images
                $this->addHtmlImageParts($message);
                break;
            }
            case $html && $attachments && !$htmlImages:
            {
                $this->addMixedPart($message);
                $alt = $this->addAlternativePart($message);

                $this->addTextPart($alt);
                $this->addHtmlPart($alt);

                # Attachments
                $this->addAttachmentParts($message);
                break;
            }
            case $html && $attachments && $htmlImages:
            {
                $this->addMixedPart($message);
                $rel = $this->addRelatedPart($message);
                $alt = $this->addAlternativePart($rel);

                $this->addTextPart($alt);
                $this->addHtmlPart($alt);

                # HTML images
                $this->addHtmlImageParts($rel);

                # Attachments
                $this->addAttachmentParts($message);
                break;
            }
        }

        if (isset($message))
        {
            $output = $message->encode();
            $this->output = $output['body'];
            $this->headers = array_merge($this->headers, $output['headers']);
            $this->headers['Message-Id'] = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), $this->hServerHost);
            $this->isBuilt = true;
            return true;
        }
        else
        {
            return false;
        }
    }

    # Function to encode a header if necessary
    # according to RFC2047
    #
    # @param  string $input   Value to encode
    # @param  string $charset Character set to use
    # @return string          Encoded value
    private function encodeHeader($input, $charset = 'ISO-8859-1')
    {
        preg_match_all('/(\w*[\x80-\xFF]+\w*)/', $input, $matches);

        foreach ($matches[1] as $value)
        {
            $replacement = preg_replace('/([\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
            $input = str_replace($value, '=?' . $charset . '?Q?' . $replacement . '?=', $input);
        }

        return $input;
    }

    public function setSMTPServer($smtpServer)
    {
        $this->smtpServer = $smtpServer;
    }

    public function setSMTPHelo($smtpHelo)
    {
        $this->smtpHelo = $smtpHelo;
    }

    public function setSMTPAuthMethod($smtpAuthMethod)
    {
        $this->smtpAuthMethod = $smtpAuthMethod;
    }

    # Sends the mail.
    #
    # @param  array  $recipients Array of receipients to send the mail to
    # @param  string $type       How to send the mail ('mail' or 'sendmail' or 'smtp')
    # @return mixed
    public function send($recipients = array(), $type = 'mail')
    {
        if (!defined('CRLF'))
        {
            $this->setCRLF(($type == 'mail' OR $type == 'sendmail') ? "\n" : "\r\n");
        }

        if (!$this->isBuilt)
        {
            $this->build();
        }

        switch ($type)
        {
            case 'mail':
            {
                $subject = '';

                if (!empty($this->headers['Subject']))
                {
                    $subject = $this->encodeHeader($this->headers['Subject'], $this->buildParams['head_charset']);
                    unset($this->headers['Subject']);
                }

                # Get flat representation of headers
                foreach ($this->headers as $name => $value)
                {
                    $headers[] = $name . ': ' . $this->encodeHeader($value, $this->buildParams['head_charset']);
                }

                $to = $this->encodeHeader(implode(', ', $recipients), $this->buildParams['head_charset']);

                if (!empty($this->returnPath))
                {
                    $result = mail($to, $subject, $this->output, implode(CRLF, $headers), '-f' . $this->returnPath);
                }
                else
                {
                    $result = mail($to, $subject, $this->output, implode(CRLF, $headers));
                }

                # Reset the subject in case mail is resent
                if ($subject !== '')
                {
                    $this->headers['Subject'] = $subject;
                }

                # Return
                return $result;
            }
            case 'sendmail':
            case 'get':
            {
                # Get flat representation of headers
                foreach ($this->headers as $name => $value)
                {
                    $headers[] = $name.': '.$this->encodeHeader($value, $this->buildParams['head_charset']);
                }

                # Encode To:
                $headers[] = 'To: '.$this->encodeHeader(implode(', ', $recipients), $this->buildParams['head_charset']);

                # Get return path arg for sendmail command if necessary
                $returnPath = '';

                if (!empty($this->returnPath))
                {
                    $returnPath = '-f' . $this->returnPath;
                }

                if ($type == 'sendmail')
                {
                    $pipe = popen($this->sendmailPath.' '.$returnPath, 'w');
                    $bytes = fputs($pipe, implode(CRLF, $headers).CRLF.CRLF.$this->output);
                    return pclose($pipe);
                }
                else
                {
                    return implode(CRLF, $headers).CRLF.CRLF.$this->output;
                }
            }
            case 'smtp':
            {
                # SMTP Notes
                #
                # hMailSMTPServer URIs
                #
                # ssl://user:password@smtp.example.com:465
                # smtp://user:password@smtp.example.com:25
                #
                # ssl://user:password@smtp.gmail.com:465     // GMail
                # ssl://user:password@smtp.gmail.com         // Also valid, port 465 is the default for SSL
                # smtp://user:password@smtp.example.com      // Also valid, port 25 is the default for unprotected SMTP
                # smtp://smtp.example.com                    // No authentication required
                #
                # Password encryption options
                #
                # smtp://user:password@smtp.example.com?plain     // Authenticate with plain text
                # smtp://user:password@smtp.example.com?login     // Authenticate with login
                # smtp://user:password@smtp.example.com?cram-md5  // ...
                #
                # If specified in the URI, that configuration takes precedence over what's specified in
                # hMailSMTPAuthMethod
                #
                # hMailSMTPAuthMethod
                # One of 'DIGEST-MD5', 'CRAM-MD5', 'LOGIN' or 'PLAIN' (see Net_SMTP)
                #
                # hMailSMTPHelo
                # Defaults to hServerHost

                # PHP 4, yuk!
                # Set to PHP4 error reporting for PEAR libraries
                $this->setToPHP4();

                if (empty($this->smtpServer))
                {
                    $this->smtpServer = $this->hMailSMTPServer;
                }

                if (empty($this->smtpHelo))
                {
                    $this->smtpHelo = $this->hMailSMTPHelo($this->hServerHost);
                }

                if (empty($this->smtpAuthMethod))
                {
                    $this->smtpAuthMethod = $this->hMailSMTPAuthMethod('PLAIN');
                }

                $parameters = parse_url($this->smtpServer);

                if ($parameters['scheme'] == 'smtps')
                {
                    $parameters['scheme'] == 'ssl';
                }

                # scheme - e.g. http
                # host
                # port
                # user
                # pass
                # path
                # query - after the question mark ?
                # fragment
                if (!class_exists('Net_SMTP'))
                {
                    require_once 'Net/SMTP.php';
                }

                if (!class_exists('Mail_RFC822'))
                {
                    require_once('RFC822.php');
                }

                $this->Mail_RFC822 = new Mail_RFC822();

                # Assume port 25
                $port = 25;

                if (!empty($parameters['port']))
                {
                    # Unless explicitly provided
                    $port = (int) $parameters['port'];
                }
                else if ($parameters['scheme'] == 'ssl')
                {
                    # And assume port 465 if the ssl scheme is specified, but the port is not
                    $port = 465;
                }

                # Don't pass the scheme
                $host = $parameters['host'];

                if ($parameters['scheme'] == 'ssl')
                {
                    # Unless the ssl scheme is specified
                    $host = 'ssl://'.$host;
                }

                $smtp = new Net_SMTP($host, $port, $this->smtpHelo);

                # Parse recipients argument for internet addresses
                foreach ($recipients as $recipient)
                {
                    $addresses = $this->Mail_RFC822->parseAddressList($recipient, $this->smtpHelo, null, false);

                    foreach ($addresses as $address)
                    {
                        $smtp_recipients[] = sprintf('%s@%s', $address->mailbox, $address->host);
                    }
                }

                unset($addresses); # These are reused
                unset($address);   # These are reused

                # Get flat representation of headers, parsing
                # Cc and Bcc as we go
                foreach ($this->headers as $name => $value)
                {
                    if ($name == 'Cc' || $name == 'Bcc')
                    {
                        $addresses = $this->Mail_RFC822->parseAddressList($value, $this->smtpHelo, null, false);

                        foreach ($addresses as $address)
                        {
                            $smtp_recipients[] = sprintf('%s@%s', $address->mailbox, $address->host);
                        }
                    }

                    if ($name == 'Bcc')
                    {
                        continue;
                    }

                    $headers[] = $name . ': ' . $this->encodeHeader($value, $this->buildParams['head_charset']);
                }

                # Add To header based on $recipients argument
                $headers[] = 'To: ' . $this->encodeHeader(implode(', ', $recipients), $this->buildParams['head_charset']);

                # Add headers to send_params
                $recipients = array_unique($smtp_recipients);

                $body = $this->output;

                # Setup return path
                if (!empty($this->returnPath))
                {
                    $from = $this->returnPath;
                }
                else if (!empty($this->headers['From']))
                {
                    $from = $this->Mail_RFC822->parseAddressList($this->headers['From']);
                    $from = sprintf('%s@%s', $from[0]->mailbox, $from[0]->host);
                }
                else
                {
                    $from = 'postmaster@' . $this->smtpHelo;
                }

                if (pearIsError($error = $smtp->connect()))
                {
                    $this->warning($error.'.', __FILE__, __LINE__);
                }

                if (!empty($parameters['user']))
                {
                    if (!empty($parameters['query']))
                    {
                        $method = strtoupper($parameters['query']);
                    }
                    else
                    {
                        $method = $this->smtpAuthMethod;
                    }

                    $smtp->auth(urldecode($parameters['user']), urldecode($parameters['pass']), $method);
                }

                if (pearIsError($smtp->mailFrom($from)))
                {
                    $this->warning("SMTP Error: Unable to set sender to: {$from}.", __FILE__, __LINE__);
                }

                foreach ($recipients as $recipient)
                {
                    if (pearIsError($res = $smtp->rcptTo($recipient)))
                    {
                        $this->warning("SMTP Error: Unable to add recipient: {$recipient} because ".$res->getMessage().".", __FILE__, __LINE__);
                    }
                }

                # Transparency
                if (pearIsError($smtp->data(trim(implode(CRLF, $headers)).CRLF.CRLF.$body)))
                {
                    $this->warning('SMTP Error: Unable to send data.', __FILE__, __LINE__);
                }

                $smtp->disconnect();

                # Reset back to default error reporting
                $this->setToDefault();
                return true;
            }
        }
    }

    # Use this method to return the email
    # in message/rfc822 format. Useful for
    # adding an email to another email as
    # an attachment. there's a commented
    # out example in example.php.
    #
    # @param string $type       Method to be used to send the mail.
    #                           Used to determine the line ending type.
    public function getRFC822($type = 'mail')
    {
        # Make up the date header as according to RFC2822
        $this->setHeader('Date', date('r'));

        if (!defined('CRLF'))
        {
            $this->setCRLF($type == 'mail' ? "\n" : "\r\n");
        }

        if (!$this->isBuilt)
        {
            $this->build();
        }

        # Get flat representation of headers
        foreach ($this->headers as $name => $value)
        {
            $headers[] = ucwords($name).': '.$value;
        }

        return implode(CRLF, $headers) . CRLF . CRLF . $this->output;
    }
}

# Attachment classes
class attachment
{

    # Data of attachment
    # @var string
    public $data;

    # Name of attachment (filename)
    # @var string
    public $name;

    # Content type of attachment
    # @var string
    public $contentType;

    # Encoding type of attachment
    # @var object
    public $encoding;

    # Constructor
    #
    # @param string $data        File data
    # @param string $name        Name of attachment (filename)
    # @param string $contentType Content type of attachment
    # @param object $encoding    Encoding type to use
    public function __construct($data, $name, $contentType, iEncoding $encoding)
    {
        $this->data = $data;
        $this->name = $name;
        $this->contentType = $contentType;
        $this->encoding = $encoding;
    }
}

# File based attachment class
class fileAttachment extends attachment
{
    # Constructor
    #
    # @param string $filename    Name of file
    # @param string $contentType Content type of file
    # @param string $encoding    What encoding to use
    public function __construct($filename, $contentType = 'application/octet-stream', $encoding = null)
    {
        $encoding = is_null($encoding) ? new Base64Encoding() : $encoding;

        parent::__construct(file_get_contents($filename), basename($filename), $contentType, $encoding);
    }
}

# Attachment class to handle attachments which are contained
# in a variable.
class stringAttachment extends attachment
{

    # Constructor
    #
    # @param string $data        File data
    # @param string $name        Name of attachment (filename)
    # @param string $contentType Content type of file
    # @param string $encoding    What encoding to use
    public function __construct($data, $name = '', $contentType = 'application/octet-stream', $encoding = null)
    {
        $encoding = is_null($encoding) ? new Base64Encoding() : $encoding;
        parent::__construct($data, $name, $contentType, $encoding);
    }
}

# File based embedded image class
class fileEmbeddedImage extends fileAttachment
{

}

# String based embedded image class
class stringEmbeddedImage extends stringAttachment
{

}

# Encoding interface
interface iEncoding
{
    public function encode($input);
    public function getType();
}

# Base64 Encoding class
class Base64Encoding implements iEncoding
{
    # Function to encode data using base64 encoding.
    # @param string $input Data to encode
    public function encode($input)
    {
        return rtrim(chunk_split(base64_encode($input), 76, defined('MAIL_MIME_PART_CRLF') ? MAIL_MIME_PART_CRLF : "\r\n"));
    }

    # Returns type
    public function getType()
    {
        return 'base64';
    }
}

# Quoted Printable Encoding class
class QPrintEncoding implements iEncoding
{
    # Function to encode data using quoted-printable encoding.
    # @param string $input Data to encode
    public function encode($input)
    {
        # Replace non printables
        $input = preg_replace(
            '/([^\x20\x21-\x3C\x3E-\x7E\x0A\x0D])/e',
            'sprintf("=%02X", ord("\1"))',
            $input
        );

        $inputLen = strlen($input);
        $outLines = array();
        $output = '';

        $lines = preg_split('/\r?\n/', $input);

        # Walk through each line
        for ($lineCounter = 0; $lineCounter < count($lines); $lineCounter++)
        {
            # Is line too long ?
            if (strlen($lines[$lineCounter]) > $lineMax)
            {
                # \r\n Gets added when lines are imploded
                $outLines[] = substr($lines[$lineCounter], 0, $lineMax - 1).'=';
                $lines[$lineCounter]  = substr($lines[$lineCounter], $lineMax - 1);

                # Ensure this line gets redone as we just changed it
                $lineCounter--;
            }
            else
            {
                $outLines[] = $lines[$lineCounter];
            }
        }

        return implode(
            "\r\n",
            preg_replace(
                '/(\x20+)$/me',
                'str_replace(" ", "=20", "\1")',
                $outLines
            )
        );
    }

    # Returns type
    public function getType()
    {
        return 'quoted-printable';
    }
}

# 7Bit Encoding class
class SevenBitEncoding implements iEncoding
{
    # Function to "encode" data using 7bit encoding.
    # @param string $input Data to encode
    public function encode($input)
    {
        return $input;
    }

    # Returns type
    public function getType()
    {
        return '7bit';
    }
}

# 8Bit Encoding class
class EightBitEncoding implements iEncoding
{
    # Function to "encode" data using 8bit encoding.
    # @param string $input Data to encode
    public function encode($input)
    {
        return $input;
    }

    # Returns type
    public function getType()
    {
        return '8bit';
    }
}

function pearIsError($data, $code = null)
{
    if (!is_a($data, 'PEAR_Error')) {
        return false;
    }

    if (is_null($code)) {
        return true;
    } elseif (is_string($code)) {
        return $data->getMessage() == $code;
    }

    return $data->getCode() == $code;
}

?>