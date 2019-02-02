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
# <h1>Mail API</h1>
# <p>
#   <var>hMailLibrary</var> is responsible for sending mail in Hot Toddy.  Mail is sent
#   using templates that are created using JSON configuration files, and HTML or TXT
#   templates for the HTML or plain text parts of the mailer.
# </p>
# @end

class hMailLibrary extends hPlugin {

    private $preview = false;
    private $message = array();
    private $saveToDisk = false;
    private $email = array();
    private $text;
    private $html;
    private $debug = false;
    private $queue = false;
    private $sendMethod;

    private $debugSendmail;
    private $sendmailPath;
    private $returnPath;

    private $templateName = null;
    private $options = array();
    private $json;

    private $hMailMIME;
    private $hMailDatabase;

    public function hConstructor()
    {
        # @return void

        # @description
        # <h2>Mail Constructor</h2>
        # <p>
        #   Gets the <var>hMailMIMELibrary</var> object.  Adds Hot Toddy as the authoring
        #   software in the <var>X-Mailer</var> header.
        # </p>
        # <p>
        #   Sets the internal <var>$queue</var> property based on the value of the
        #   <var>hMailQueue</var> framework variable.  Setting <var>hMailQueue</var> to
        #   true will queue messages in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hMailQueue/hMailQueue.sql' class='code' target='_blank'>hMailQueue</a> database table.
        #   If the mail scheduler is created using
        #   <a href='/Hot Toddy/Documentation?hMail/hMail.shell.php'>hMailShell</a>, the queue will be
        #   automatically emptied every 10 minutes (the default value), or whatever interval in minutes
        #   specified upon setup of the scheduled process.
        # </p>
        # @end

        $this->hMailMIME = $this->library('hMail/hMailMIME');
        $this->hMailMIME->setMailer(', Hot Toddy <http://www.hframework.com/>');

        if ($this->hMailQueue(false))
        {
            $this->queue = $this->hMailQueue(false);
        }

        $this->hMailDatabase = $this->database('hMail');
    }

    # Set address headers like so:
    #   $this->hMail->setTo($address, $name, $id);
    #
    # Otherwise,
    #   $this->hMail->setSubject('Some Subject');

    public function &setTo($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds an address to the <var>To</var> mail header.
        # </p>
        # @end

        $this->addAddressHeader(
            'To',
            $address,
            $name,
            $contactId
        );

        return $this;
    }

    public function &addTo($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #   Alias for <a href='setTo' class='code'>setTo()</a>
        # </p>
        # @end

        return $this->setTo(
            $address,
            $name,
            $contactId
        );
    }

    public function &setCc($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds an address to the <var>Cc</var> mail header.
        # </p>
        # @end

        $this->addAddressHeader(
            'Cc',
            $address,
            $name,
            $contactId
        );

        return $this;
    }

    public function &addCc($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #   Alias for <a href='setTo' class='code'>setCc()</a>
        # </p>
        # @end

        return $this->setCc(
            $address,
            $name,
            $contactId
        );
    }

    public function &setBcc($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds an address to the <var>Bcc</var> mail header.
        # </p>
        # @end

        $this->addAddressHeader(
            'Bcc',
            $address,
            $name,
            $contactId
        );

        return $this;
    }

    public function &addBcc($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #   Alias for <a href='setTo' class='code'>setBcc()</a>
        # </p>
        # @end

        return $this->addBcc(
            $address,
            $name,
            $contactId
        );
    }

    public function &setFrom($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds an address to the <var>From</var> mail header.
        # </p>
        # @end

        $this->addAddressHeader(
            'From',
            $address,
            $name,
            $contactId
        );

        return $this;
    }

    public function &setReplyTo($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds an address to the <var>Reply-To</var> mail header.
        # </p>
        # @end

        $this->addAddressHeader(
            'Reply-To',
            $address,
            $name,
            $contactId
        );

        return $this;
    }

    public function &setReturnPath($address, $name = null, $contactId = 0)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds an address to the <var>Return-Path</var> mail header.
        # </p>
        # @end

        $this->addAddressHeader(
            'Return-Path',
            $address,
            $name,
            $contactId
        );

        return $this;
    }

    public function &setSubject($subject)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds the message's subject line.
        # </p>
        # @end

        $this->message['Subject'] = $subject;

        return $this;
    }

    public function &setText($text)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds the message's plain text body.
        # </p>
        # @end

        $this->text = $text;

        return $this;
    }

    public function &setHTML($html)
    {
        # @return hMailLibrary

        # @description
        # <p>
        #    Adds the message's HTML body.
        # </p>
        # @end

        $this->html = $html;

        return $this;
    }

    private function cleanInput(&$value, $isAddress = true)
    {
        # @return void

        # @description
        # <h2>Sanatizing Input</h2>
        # <p>
        #   Charcters that have special meaning in mail headers are explicitly
        #   removed so that spammers can't game the API to send mail to unintended
        #   recipients.
        # </p>
        # @end

        if (!$isAddress)
        {
            $value = str_replace('@', '', $value);
        }
        else if (substr_count($value, '@') > 1)
        {
            $value = '';
        }

        // Remove characters that have special meaning in mail headers to prevent
        // spammers from exploiting the mail library for purposes of sending spam.
        //
        // This could do with a regular expression that verifies valid headers per RFC 2822.
        $value = str_replace(
            array(':', ',', '<', '>', "\n", "\r"),
            '',
            hString::decodeEntitiesAndUTF8($value)
        );
    }

    private function addAddressHeader($header, $address, $name = '', $id = 0)
    {
        # @return void

        # @description
        # <h2>Adding a Mail Header</h2>
        # <p>
        #   This method is responsible for assembling mail headers into an array,
        #   in preparation for sending a message.
        # </p>
        # <p>
        #   The arrays created for each header look like this:
        # </p>
        # <code>
        #   $this->message['To'] = array(
        #       'address' => array(
        #           'john@example.com'
        #       ),
        #       'account' => array(
        #           'john'
        #       ),
        #       'domain' => array(
        #           'example.com'
        #       ),
        #       'name' => array(
        #           'John Appleseed'
        #       )
        #       'id' => array(
        #           123
        #       )
        #   );
        # </code>
        # <p>
        #   Similar arrays are created for <var>To</var>, <var>Cc</var>, <var>Bcc</var>,
        #   <var>From</var>, <var>Reply-To</var>, and <var>Return-Path</var>.
        # </p>
        # <p>
        #   <var>$id</var> refers to an <var>hContactId</var>, this will only be specified
        #   if one is being used.
        # </p>
        # @end

        $this->cleanInput($address);
        $this->cleanInput($name, false);

        $address = strToLower($address);

        if (!empty($address))
        {
            if (!isset($this->message[$header]))
            {
                $this->message[$header] = array(
                    'address' => array(),
                    'account' => array(),
                    'domain' => array(),
                    'name' => array(),
                    'id' => array()
                );
            }

            $addressCount = count($this->message[$header]['address']);

            if (!$this->isRecipient($header, $address))
            {
                $this->message[$header]['address'][$addressCount] = $address;

                list($account, $domain) = explode('@', $address);

                $this->message[$header]['account'][$addressCount] = $account;
                $this->message[$header]['domain'][$addressCount] = $domain;
                $this->message[$header]['name'][$addressCount] = $name;
                $this->message[$header]['id'][$addressCount] = $id;
            }
        }
    }

    public function isRecipient($header, $address)
    {
        # @return boolean

        # @description
        # <h2>Determining if an Address is a Recipient</h2>
        # <p>
        #   The <var>isRecipient()</var> method looks at a header and an email
        #   address an determines if the address is a recipient in the <var>To</var>,
        #   <var>Cc</var>, or <var>Bcc</var> headers.
        # </p>
        # @end

        if ($header == 'To' || $header == 'Cc' || $header == 'Bcc')
        {
            return(
                isset($this->message['To']['address']) &&
                is_array($this->message['To']['address']) &&
                in_array($address, $this->message['To']['address']) ||

                isset($this->message['Cc']['address']) &&
                is_array($this->message['Cc']['address']) &&
                in_array($address, $this->message['Cc']['address']) ||

                isset($this->message['Bcc']['address']) &&
                is_array($this->message['Bcc']['address']) &&
                in_array($address, $this->message['Bcc']['address'])
            );
        }

        return false;
    }

    public function explodeAddressList($header, $addressList)
    {
        # @return void

        # @description
        # <h2>Transforming Address Lists</h2>
        # <p>
        #   <var>explodeAddressList()</var> takes an address list string that you'd specify
        #   in the <var>To</var>, <var>Cc</var>, or <var>Bcc</var> fields, isolates each
        #   address, and creates an array of addresses from the string.
        # </p>
        # @end

        $addresses = explode(',', $addressList);

        foreach ($addresses as $address)
        {
            # Take
            # First Last <name@localhost>
            if (strstr($address, '<'))
            {
                # [1] = name [2] = email
                $matches = array();

                preg_match('/^(.*)<(.*)>$/U', trim($address), $matches);

                if (!empty($matches[2]) && strstr($matches[2], '@'))
                {
                    $this->{"set{$header}"}(isset($matches[2])? trim($matches[2]) : '', isset($matches[1])? trim($matches[1]): '');
                }
            }
            else if (strstr($address, '@'))
            {
                $this->{"set{$header}"}(trim($address));
            }
        }
    }

    public function implodeAddressList($header)
    {
        # @return string

        # @description
        # <h2>Imploding a Header's Address List Into a String</h2>
        # <p>
        #   Takes the array of addresses data from the mail header specified in
        #   <var>$header</var> and returns a string of addresses in the format of:
        # </p>
        # <p>
        #   John Appleseed &lt;john@example.com&gt;, Jane Appleseed &lt;jane@example.com&gt;
        # </p>
        # <p>
        #   Possible headers are <var>To</var>, <var>Cc</var>, <var>Bcc</var>, <var>From</var>,
        #   <var>Reply-To</var>, or <var>Return-Path</var>
        # </p>
        # @end

        $addresses = array();

        foreach ($this->message[$header]['address'] as $i => $address)
        {
            $addresses[] = !empty($this->message[$header]['name'][$i])? $this->message[$header]['name'][$i].' <'.$address.'>' : $address;
        }

        return implode(',', $addresses);
    }

    public function sendMail($templateName, $templateVariables, $callingPluginPath, $json, $jsonmtime)
    {
        $this->json = $json;
        # @return void

        # @description
        # <h2>Sending Mail Using a JSON Configuration File</h2>
        # <p>
        #   The prefered method of sending mail using a mail template involves creating a JSON
        #   configuration file for mail.  Within plugins, you can easily make a call to
        #   <var>sendMail()</var> to send a message using a message template that's defined
        #   using a JSON configuration file.
        # </p>
        # <p>
        #   The <var>sendMail()</var> method used by plugins is different than the <var>sendMail()</var>
        #   method defined here, to learn more about the <var>sendMail()</var> method as it would
        #   be used in plugins, see <a href='/Hot Toddy/Documentation?hPlugin' class='code'>hPlugin</a>.
        # </p>
        # <h3>Variables Used in JSON Mail Configurations</h3>
        # <table>
        #   <colgroup>
        #       <col style='width: 150px;' />
        #       <col />
        #   </colgroup>
        #   <thead>
        #       <tr>
        #           <th>Variable</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>to, hMailTo</td>
        #           <td>
        #               The <var>To</var> line of the message.  For example:
        #               <code>John Appleseed &lt;john@example.com&gt;, Jane Appleseed &lt;jane@example.com&gt;</code>
        #               Template variables can also be used in any header.  For example:
        #               <code>{/hContactDisplayName} &lt;{/hContactEmailAddress}&gt;</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>bcc, hMailBcc</td>
        #           <td>
        #               The <var>Bcc</var> line of the message.  For example:
        #               <code>John Appleseed &lt;john@example.com&gt;, Jane Appleseed &lt;jane@example.com&gt;</code>
        #               Template variables can also be used in any header.  For example:
        #               <code>{/hContactDisplayName} &lt;{/hContactEmailAddress}&gt;</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>cc, hMailCc</td>
        #           <td>
        #               The <var>Cc</var> line of the message.  For example:
        #               <code>John Appleseed &lt;john@example.com&gt;, Jane Appleseed &lt;jane@example.com&gt;</code>
        #               Template variables can also be used in any header.  For example:
        #               <code>{/hContactDisplayName} &lt;{/hContactEmailAddress}&gt;</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>debug, hMailDebug</td>
        #           <td>
        #               Set when you want to debug a message.
        #               See: <a href='#debugMessage' class='code'>debugMessage()</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>debugSendmail, hMailDebugSendmail</td>
        #           <td>
        #               Boolean variable, when turned on enables sendmail enviornemnt variables <var>MAIL_VERBOSE</var>
        #               and <var>MAIL_DEBUG</var>.  The results are written to Hot Toddy's error console.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>from, hMailFrom</td>
        #           <td>
        #               The <var>From</var> line of the message.  For example:
        #               <code>John Appleseed &lt;john@example.com&gt;</code>
        #               Template variables can also be used in any header.  For example:
        #               <code>{/hContactDisplayName} &lt;{/hContactEmailAddress}&gt;</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>HTML, html, hMailHTML</td>
        #           <td>
        #               The name of the file to use in the plugin's HTML folder for the HTML part of the message.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>preview, hMailPreview</td>
        #           <td>
        #               Set when you want to preview a message.
        #               See: <a href='#previewMessage' class='code'>previewMessage()</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>queue, hMailQueue</td>
        #           <td>
        #               Set when you want to turn on message queueing for a template.
        #               See: <a href='#queueMessages' class='code'>queueMessages()</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>replyTo, hMailReplyTo</td>
        #           <td>
        #               The <var>Reply-To</var> line of the message.  For example:
        #               <code>John Appleseed &lt;john@example.com&gt;</code>
        #               Template variables can also be used in any header.  For example:
        #               <code>{/hContactDisplayName} &lt;{/hContactEmailAddress}&gt;</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>returnPath, hMailReturnPath</td>
        #           <td>
        #               Sets the <var>Return-Path</var> of the message.
        #               If the return-path is not provided in this variable, the framework variable <var>hMailReturnPath</var>
        #               is used.  <var>Return-Path</var> can also be set by specifying it as a message
        #               header.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>saveToDisk, hMailSaveToDisk</td>
        #           <td>
        #               Used in conjunction with <var>hMailDebug</var> or <var>hMailPreview</var>.
        #               See: <a href='#debugMessage' class='code'>debugMessage()</a> and
        #               <a href='#previewMessage' class='code'>previewMessage()</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>sendmailPath, hMailSendmailPath</td>
        #           <td>
        #               The path to the sendmail program.  If not path is provided, the value of the framework
        #               variable <var>hMailSendmailPath</var> is used.  If no value is specified for that
        #               variable, the default sendmail path is <var>/usr/sbin/sendmail -ti -r</var>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>sendMethod, hMailSendMethod</td>
        #           <td>
        #               One of <var>smtp</var>, <var>mail</var>, or <var>sendmail</var>.   <var>smtp</var> uses
        #               a PHP PEAR SMTP script to send messages.  <var>mail</var> uses the PHP
        #               <a href='http://www.php.net/mail' class='code'>mail()</a> to send messages.  And, finally,
        #               <var>sendmail</var> uses the sendmail program to send messages.  If not specified,
        #               the value of the framework variable <var>hMailSendMethod</var> is used.  If the framework
        #               variable <var>hMailSendMethod</var> is not specified, the default method is <var>sendmail</var>.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>smtpAuthMethod, hMailSMTPAuthMethod</td>
        #           <td>
        #               The SMTP auth method. See: <a href='#smtpAuthMethods'>SMTP Authentication Methods</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>smtpHelo, hMailSMTPHelo</td>
        #           <td>
        #               Defaults to <var>{hServerHost}</var>, the value of the framework variable <var>hServerHost</var>.
        #               Can be specified as a framework variable <var>hMailSMTPHelo</var>.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>smtpServer, hMailSMTPServer</td>
        #           <td>
        #               The connection arguments for the SMTP server. See: <a href='#smtpConnections'>SMTP Connections</a>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>subject, hMailSubject</td>
        #           <td>
        #               The <var>Subject</var> line of the message.  For example:
        #               <code>Re: Something I've Written You About</code>
        #               Template variables can also be used in any header.  For example:
        #               <code>[{/mailTopic}] A new quote!</code>
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>text, hMailText</td>
        #           <td>
        #               The name of the file to use in the plugin's TXT folder for the text part of the message.
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>

        $pluginPath = dirname($callingPluginPath);

        $this->option('debug', 'hMailDebug')
             ->option('preview', 'hMailPreview')
             ->option('queue', 'hMailQueue')
             ->option('saveToDisk', 'hMailSaveToDisk')
             ->option('sendMethod', 'hMailSendMethod')
             ->option('debugSendmail', 'hMailDebugSendmail')
             ->option('sendmailPath', 'hMailSendmailPath')
             ->option('returnPath', 'hMailReturnPath');

        # <h3 id='smtpConnections'>SMTP Connections</h3>
        # <p>
        #   SMTP connections are provided in the <var>hMailSMTPServer</var> variable in a messages's
        #   JSON configuration file, or in a framework variable of the same name.  SMTP connections
        #   are written as URIs, following is a valid list of URIs that can be used as SMTP
        #   connection strings.
        # </p>
        # <ul>
        #   <li>
        #       <var>smtp://user:password@smtp.example.com</var> A simple authenticated
        #       SMTP connection over port 25.  Port 25 is assumed on non-SSL connections
        #       when the port is not explicitly specified.
        #   </li>
        #   <li><var>ssl://user:password@smtp.example.com</var>
        #       This specifies an SSL connection to <var>smtp.example.com</var>
        #       on port 465.  Port 465 is assumed on SSL connections that have no
        #       port specified explicitly.
        #   </li>
        #   <li>
        #       <var>smtps://user:password@smtp.example.com:465</var>
        #       This is identical to the previous example, it's an SSL connection on
        #       port 465.
        #   </li>
        #   <li>
        #       <var>ssl://user:password@smtp.gmail.com</var> Uses GMail.
        #   </li>
        #   <li>
        #       <var>smtp://smtp.example.com</var> Wide-open SMTP that does not require
        #       authentication.
        #   </li>
        #   <li>
        #       <var>smtp://user:password@smtp.example.com?plain</var> Specifies that the
        #       password is plain-text.
        #   </li>
        #   <li>
        #       <var>smtp://user:password@smtp.example.com?login</var> Specifies that the
        #       password is login encrypted.
        #   </li>
        #   <li>
        #       <var>smtp://user:password@smtp.example.com?cram-md5</var> Specifies that the
        #       password is cram-md5 encrypted.
        #   </li>
        # </ul>
        # <p>
        #   Usernames or passwords with special characters should be url-encoded, for example,
        #   <var>p@ss</var> would have the at-sign encoded using <var>%40</var>, so the password
        #   would become <var>p%40ss</var>.
        # </p>
        # <h3 id='smtpAuthMethods'>SMTP Authentication Methods</h3>
        # <p>
        #   The password authentication method can be added to the connection URI or specified
        #   separately, possible values are: <var>digest-md5</var>, <var>cram-md5</var>,
        #   <var>login</var> or <var>plain</var>.  If specified separately, it should be added
        #   to the framework/mail variable <var>hMailSMTPAuthMethod</var>
        # </p>
        # @end

        $this-
            >mimeOption(
                'smtpServer',
                'hMailSMTPServer',
                'setSMTPServer'
            )
            ->mimeOption(
                'smtpHelo',
                'hMailSMTPHelo',
                'setSMTPHelo'
            )
            ->mimeOption(
                'smtpAuthMethod',
                'hMailSMTPAuthMethod',
                'setSMTPAuthMethod'
            );

        $this->hMailDatabase->saveTemplateFromJSON(
            $templateName,
            $this->json,
            $jsonmtime
        );

        $baseTemplatePath = $this->getIncludePath(
            $this->hServerDocumentRoot.dirname($callingPluginPath)
        );

        $htmlPath = '';
        $textPath = '';

        if (!isset($templateVariables['hMailHTMLTemplate']) || empty($templateVariables['hMailHTMLTemplate']))
        {
            switch (true)
            {
                case isset($this->json->hMailHTML):
                {
                    $htmlPath = $this->json->hMailHTML;
                    break;
                }
                case isset($this->json->html):
                {
                    $htmlPath = $this->json->html;
                    break;
                }
                case isest($this->json->HTML):
                {
                    $htmlPath = $this->json->HTML;
                    break;
                }
                default:
                {
                    $htmlPath = $templateName;
                }
            }

            $htmlPath = "{$baseTemplatePath}/HTML/{$htmlPath}.html";

            switch (true)
            {
                case isset($this->json->hMailText):
                {
                    $textPath = $this->json->hMailText;
                    break;
                }
                case isset($this->json->text):
                {
                    $textPath = $this->json->text;
                    break;
                }
                default:
                {
                    $textPath = $templateName;
                }
            }

            $textPath = "{$baseTemplatePath}/TXT/{$textPath}.txt";
        }

        if (!empty($htmlPath) && !file_exists($htmlPath))
        {
            $htmlPath = '';

            $this->notice(
                "The HTML path '{$htmlPath}' does not exist. Regarding mail template: '{$templateName}'.",
                __FILE__,
                __LINE__
            );
        }

        if (!empty($textPath) && !file_exists($textPath))
        {
            $textPath = '';
            $this->notice(
                "The path to plain text file '{$textPath}' does not exist. Regarding mail template: '{$templateName}'.",
                __FILE__,
                __LINE__
            );
        }

        $this->getMessageFromTemplate(
            $templateName,
            $templateVariables,
            $htmlPath,
            $textPath
        );
    }

    private function &mimeOption($option, $name, $method)
    {
        if (isset($this->json->$name))
        {
            $this->hMailMIME->$method($this->json->$name);
            unset($this->json->$name);
        }

        if (isset($this->json->$option))
        {
            $this->hMailMIME->$method($this->json->$option);
            unset($this->json->$option);
        }

        return $this;
    }

    private function &option($option, $name)
    {
        if (isset($this->json->$name))
        {
            $this->$option = $this->json->$name;
            unset($this->json->$name);
        }

        if (isset($this->json->$option))
        {
            $this->$option = $this->json->$option;
            unset($this->json->$option);
        }

        return $this;
    }

    public function getMessageFromTemplate($mailTemplateId, $variables = array(), $htmlPath = null, $textPath = null)
    {
        # @return void

        # @description
        # <h2>Sending a Message via a Database Mail Template</h2>
        # <p>
        #   Sends a message using the specified <var>hMailTemplateId</var>, which should
        #   match an <var>hMailTemplateId</var> in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hMailTemplates/hMailTemplates.sql' class='code' target='_blank'>hMailTemplates</a>
        #   database table.
        # </p>
        # <p>
        #   The <var>$variables</var> specified will be used to process all headers and message
        #   parts for template scripting.  For more information about template scripting
        #   see: <a href='/Hot Toddy/Documentation?hTemplate' class='code'>hTemplate</a>.
        # </p>
        # <p>
        #   Finally, the HTML and Text portions can be explicity supplied by providing the
        #   path to a physical <var>.html</var> or <var>.txt</var> file on the server in the
        #   <var>$hMailHTMLPath</var> and <var>$hMailTextPath</var> arguments.  These
        #   files will also be processed for template scripting using the supplied <var>$variables</var>.
        # </p>
        # @end

        $this->templateName = $this->hMailDatabase->getTemplateName($mailTemplateId);

        $message = $this->hMailDatabase->getTemplate($mailTemplateId);

        if (count($message))
        {
            $expandHTMLAndText = true;

            if (!empty($htmlPath))
            {
                $message['hMailHTML'] = $this->getTemplate(
                    $htmlPath,
                    $variables
                );

                $expandHTMLAndText = false;
            }
            else if (!empty($variables['hMailHTMLTemplate']))
            {
                $message['hMailHTML'] = $this->parseTemplateMarkup(
                    $variables['hMailHTMLTemplate'],
                    $variables
                );

                $expandHTMLAndText = false;
            }
            else if (!empty($this->html))
            {
                $message['hMailHTML'] = $this->parseTemplateMarkup(
                    $this->html,
                    $variables
                );
            }
            else
            {
                $message['hMailHTML'] = '';
            }

            if (!empty($textPath))
            {
                $message['hMailText'] = $this->getTemplate(
                    $textPath,
                    $variables
                );

                $expandHTMLAndText = false;
            }
            else if (!empty($variables['hMailTextTemplate']))
            {
                $message['hMailText'] = $this->parseTemplateMarkup(
                    $variables['hMailTextTemplate'],
                    $variables
                );

                $expandHTMLAndText = false;
            }
            else if (!empty($this->text))
            {
                $message['hMailText'] = $this->parseTemplateMarkup(
                    $this->text,
                    $variables
                );
            }
            else
            {
                $message['hMailText'] = '';
            }

            foreach ($message as $key => &$value)
            {
                $header = preg_replace('/hMail/', '', $key);

                if ((!$expandHTMLAndText && $key != 'hMailHTML' && $key != 'hMailText') || $expandHTMLAndText)
                {
                    $value = hString::decodeHTML($value);

                    $value = $this->parseTemplateMarkup(
                        $value,
                        $variables
                    );
                }

                switch ($key)
                {
                    case 'hMailTo':
                    case 'hMailCc':
                    case 'hMailBcc':
                    case 'hMailFrom':
                    case 'hMailReplyTo':
                    {
                        $this->explodeAddressList($header, $value);
                        break;
                    }
                    default:
                    {
                        $this->{"set{$header}"}($value);
                        break;
                    }
                }
            }
        }
        else
        {
            $this->warning(
                "Mailer template: '{$mailTemplateId}' does not exist.",
                __FILE__,
                __LINE__
            );
        }

        if ($this->preview)
        {
            if (!$this->saveToDisk)
            {
                $this->hTemplatePath = '';
                $this->hFileCSS = '';
                $this->hFileJavaScript = '';
                $this->hFileDocument = $this->html;
                return;
            }
            else
            {
                $mailerPath = $this->hFrameworkTemporaryPath.'/'.$this->templateName.'.html';

                if (file_exists($mailerPath))
                {
                    $this->rm($mailerPath, true);
                }

                file_put_contents(
                    $mailerPath,
                    $this->html
                );

                return;
            }
        }
        else
        {
            $this->send();
        }

        $this->reset();
    }

    public function previewMessage()
    {
        # @return void

        # @description
        # <h2>Turning on Message Preview</h2>
        # <p>
        #   Message previewing can be used for debugging or simply to show the user what
        #   their message will look like before it is sent.  To turn on previewing,
        #   call either this method prior to sending a message, or set the mail configuration
        #   variable <var>hMailPreview</var> true in a JSON mail configuration file.
        # </p>
        # <p>
        #   A message preview can also be saved to disk using the <var>hMailSaveToDisk</var>
        #   mail configuration variable. If <var>hMailSaveToDisk</var> is turned on along
        #   with <var>hMailPreview</var>, the HTML part of the message will be saved to
        #   save in Hot Toddy's <var>Temporary</var> folder, located at
        #   <var>{hFrameworkTemporaryPath}</var>.  The HTML file will be named <var><i>hMailTemplateName</i>.html</var>,
        #   where <var>hMailTemplateName</var> will be the name you've given your
        #   mail template.
        # </p>
        # @end

        $this->preview = true;
    }

    public function debugMessage()
    {
        # @return void

        # @description
        # <h2>Debugging a Message</h2>
        # <p>
        #   When debugging is turned on by either calling this method or setting the
        #   mail configuration variable <var>hMailDebug</var>, <var>hMailLibrary</var>
        #   will output the entire email message, including headers to either the browser
        #   window, or if <var>hMailSaveToDisk</var> is enabled, the message will be
        #   saved to Hot Toddy's <var>Temporary</var> folder instead, located at
        #   <var>{hFrameworkTemporaryPath}</var>.  The message will be named <var><i>hMailTemplateName</i>.eml</var>,
        #   where <var>hMailTemplateName</var> will be the name you've given your
        #   mail template.
        # </p>
        # @end

        $this->debug = true;
    }

    public function queueMessages()
    {
        # @return void

        # @description
        # <h2>Queueing Messages</h2>
        # <p>
        #   When the mail queue can be turned on by either setting the framework variable <var>hMailQueue</var>,
        #   the mail configuration variable <var>hMailQueue</var>, or by calling this method prior to
        #   sending a message.
        # </p>
        # <p>
        #   Turning on the mail queue will queue messages in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hMailQueue/hMailQueue.sql' class='code' target='_blank'>hMailQueue</a>
        #   database table.  If the mail scheduler is created using
        #   <a href='/Hot Toddy/Documentation?hMail/hMail.shell.php'>hMailShell</a>, the queue will be
        #   automatically emptied every 10 minutes (the default value), or whatever interval in minutes
        #   specified upon setup of the scheduled process.
        # </p>
        # @end

        $this->queue = true;
    }

    public function send($rtn = false)
    {
        # @return string | integer
        # <p>
        #   Returns the message if <var>$rtn</var> is set to <var>true</var>.
        #   Otherwise, it returns the number of bytes sent if the message is
        #   successfully sent, and zero if the message failed to be sent.
        # </p>
        # @end

        # @description
        # <h2>Sending a Message</h2>
        #
        # @end

        if (empty($this->sendMethod))
        {
            $this->sendMethod = $this->hMailSendMethod('sendmail');
        }

        $recipients = array();

        if ($this->addressesExist('To') || $this->addressesExist('Cc') || $this->addressesExist('Bcc'))
        {
            $this->hMailMIME
                ->setTextCharset('UTF-8')
                ->setHTMLCharset('UTF-8')
                ->setHeadCharset('UTF-8');

            foreach ($this->message as $header => $value)
            {
                if ($header == 'To' && $this->sendMethod == 'smtp' && !$this->debug)
                {
                    $recipients = $value['address'];
                }
                else
                {
                    $value = is_array($this->message[$header])? $this->implodeAddressList($header) : $this->message[$header];
                    $this->hMailMIME->setHeader($header, $value);
                }
            }

            if (!empty($this->text))
            {
                $this->hMailMIME->setText($this->text);
            }

            if (!empty($this->html))
            {
                $this->hMailMIME->setHTML($this->html, $this->hFileSystemPath);
            }

            if ($this->debugSendmail === null)
            {
                $this->debugSendmail = $this->hMailDebugSendmail(false);
            }

            if (empty($this->sendmailPath))
            {
                $postfixConfiguration = $this->hMailPostfixConfigurationPath(null);

                if (!empty($postfixConfiguration))
                {
                    $postfixConfiguration = '-C '.$postfixConfiguration;
                }

                $this->sendmailPath = $this->hMailSendmailPath('/usr/sbin/sendmail '.$postfixConfiguration.' -ti -r');
            }

            if (empty($this->returnPath))
            {
                $this->returnPath = $this->hMailReturnPath;
            }

            $this->hMailMIME->setReturnPath($this->returnPath);

            if ($this->debug && !$this->queue)
            {
                if (!$this->saveToDisk)
                {
                    $this->setDocumentForDebug(hString::encodeHTML($this->hMailMIME->getRFC822()));
                    return;
                }
                else
                {
                    $mailerPath = $this->hFrameworkTemporaryPath.'/'.$this->templateName.'.eml';

                    if (file_exists($mailerPath))
                    {
                        $this->rm($mailerPath, true);
                    }

                    file_put_contents(
                        $mailerPath,
                        $this->hMailMIME->getRFC822()
                    );
                }
            }
            else if ($rtn)
            {
                return $this->hMailMIME->getRFC822();
            }
            else if ($this->queue)
            {
                $mailMime = hString::encodeHTML($this->hMailMIME->freeze());
                $mailLibrary = hString::encodeHTML($this->freeze());

                if ($this->debug)
                {
                    $this->setDocumentForDebug(
                        "hMailMIME:\n".
                        $mailMime."\n\n".
                        "hMailLibrary:\n".
                        $mailLibrary."\n\n"
                    );
                }
                else
                {
                    # Queue up the mail..
                    $this->hDatabase->insert(
                        array(
                            'hMailQueueId' => null,
                            'hMailMIME'    => $mailMime,
                            'hMailLibrary' => $mailLibrary
                        ),
                        'hMailQueue'
                    );
                }
            }
            else
            {
                switch ($this->sendMethod)
                {
                    case 'smtp':
                    {
                        $this->hMailMIME->send($recipients, 'smtp');
                        break;
                    }
                    case 'mail':
                    {
                        $this->hMailMIME->send($recipients, 'mail');
                        break;
                    }
                    case 'sendmail':
                    {
                        //$this->hMailMIME->send($recipients, 'sendmail');

                        $folder = $this->hFrameworkTemporaryPath.'/Sendmail.txt';

                        $resources = array(
                            0 => array('pipe', 'r'), # stdin is a pipe that the child will read from
                            1 => array('pipe', 'w'), # stdout is a pipe that the child will write to
                            2 => array('file', $folder, 'a') # stderr is a file to write to
                        );

                        $pipes = array();

                        $sendmailEnv = array();

                        if ($this->debugSendmail)
                        {
                            $sendmailEnv = array(
                                'MAIL_VERBOSE' => 1,
                                'MAIL_DEBUG' => 1
                            );
                        }

                        $process = proc_open(
                            $this->sendmailPath.' '.$this->returnPath,
                            $resources,
                            $pipes,
                            '/tmp',
                            $sendmailEnv
                        );

                        if (is_resource($process))
                        {
                            # $pipes now looks like this:
                            # 0 => writeable handle connected to child stdin
                            # 1 => readable handle connected to child stdout
                            # Any error output will be appended to /tmp/error-output.txt
                            fwrite($pipes[0], trim($this->hMailMIME->getRFC822()));
                            fclose($pipes[0]);

                            $this->console("Mail: ".stream_get_contents($pipes[1]));

                            fclose($pipes[1]);

                            # It is important that you close any pipes before calling
                            # proc_close in order to avoid a deadlock
                            $return = proc_close($process);

                            $this->console("Sendmail command returned {$return}");
                        }

                        break;
                    }
                    default:
                    {
                        $this->warning(
                            'Invalid value for hMailSendMethod, '.$this->hMailSendMethod('sendmail'),
                            __FILE__,
                            __LINE__
                        );
                    }
                }
            }

            $this->reset();

            if (isset($bytes))
            {
                return $bytes;
            }
        }
        else
        {
            $this->warning(
                'There are no addresses specified to send the message to.',
                __FILE__,
                __LINE__
            );
        }

        return 0;
    }

    private function setDocumentForDebug($document)
    {
        # @return void

        # @description
        # <h2>Outputting a Message to the Browser Window</h2>
        # <p>
        #   <var>setDocumentForDebug()</var> sets the necessary framework variables for
        #   outputting a message directly to the browser window for purposes of
        #   debugging.  See: <a href='#debugMessage' class='code'>debugMessage()</a>
        # </p>
        # @end

        $this->hFileCSS = '';
        $this->hFileJavaScript = '';

        $this->plugin('hApplication/hApplicationForm');
        $this->hFileTitle = "Debug Mailer";

        $this->hFileDocument = "<pre>{$document}</pre>\n";
    }

    public function reset()
    {
        # @return void

        # @description
        # <h2>Resetting the hMailLibrary Object</h2>
        # <p>
        #   Resets the state of the <var>hMailLibrary</var> object, which is neccesary to
        #   send multiple messages from different mail templates.  This method is
        #   automatically called whenever <a href='#send' class='code'>send()</a> is called.
        # </p>
        # @end

        $this->preview = false;
        $this->message = array();
        $this->email   = array();
        $this->text    = null;
        $this->html    = null;
        $this->debug   = false;
        $this->queue   = false;
        $this->hMailMIME->hConstructor();
    }

    private function addressesExist($header)
    {
        # @return boolean

        # @description
        # <h2>Determining if Addresses Exist in a Header</h2>
        # <p>
        #   Determines whether or not addresses are specified in the supplied
        #   <var>$header</var>.  <var>$header</var> would be one of <var>To</var>,
        #   <var>Cc</var>, <var>Bcc</var>, <var>From</var>, <var>Reply-To</var>,
        #   or <var>Return-Path</var>.
        # </p>
        # @end

        return isset($this->message[$header]) && count($this->message[$header]['address']);
    }

    private function correctEncoding(&$array)
    {
        # @return void

        # @description
        # <h2>Correcting Encoding</h2>
        # <p>
        #   Makes sure that text and HTML parts are properly decoded to UTF-8.
        # </p>
        # @end

        foreach ($array as $key => $value)
        {
            switch ($key)
            {
                case 'html':
                case 'text':
                {
                    break;
                }
                default:
                {
                    $array[$key] = hString::decodeEntitiesAndUTF8($value);
                }
            }
        }
    }

    private function freeze()
    {
        # @return string

        # @description
        # <h2>Preserving hMailLibrary's State</h2>
        # <p>
        #   When a message is queued, the state of various internal properties are
        #   copied to an array and serialized so that those properties can be
        #   restored when sending a queue'd message via the mail scheduler.
        # </p>
        # @end

        $data = array(
            'preview'       => $this->preview,
            'message'       => $this->message,
            'saveToDisk'    => $this->saveToDisk,
            'email'         => $this->email,
            'text'          => $this->text,
            'html'          => $this->html,
            'debug'         => $this->debug,
            'queue'         => $this->queue,
            'sendMethod'    => $this->sendMethod,
            'debugSendmail' => $this->debugSendmail,
            'sendmailPath'  => $this->sendmailPath,
            'returnPath'    => $this->returnPath,
            'templateName'  => $this->templateName
        );

        return serialize($data);
    }

    private function restore($data)
    {
        # @return void

        # @description
        # <h2>Restoring hMailLibrary's State</h2>
        # <p>
        #   Restores the state of various internal properties that are
        #   essential for sending a queue'd message using the mail scheduler.
        # </p>
        # @end

        $data = unserialize($data);

        $this->preview       = false;
        $this->message       = $data['message'];
        $this->saveToDisk    = false;
        $this->email         = $data['email'];
        $this->text          = $data['text'];
        $this->html          = $data['html'];
        $this->debug         = false;
        $this->queue         = false;
        $this->sendMethod    = $data['sendMethod'];
        $this->debugSendmail = $data['debugSendmail'];
        $this->sendmailPath  = $data['sendmailPath'];
        $this->returnPath    = $data['returnPath'];
        $this->templateName  = $data['templateName'];
    }

    public function emptyQueue()
    {
        # @return void

        # @description
        # <h2>Emptying the Queue</h2>
        # <p>
        #   Sends all messages presently waiting in the
        #   <a href='/System/Framework/Hot Toddy/hDatabase/hDatabaseStructure/hMailQueue/hMailQueue.sql' class='code' target='_blank'>hMailQueue</a>
        #   database table.
        # </p>
        # @end

        $query = $this->hMailQueue->select(
            array(
                'hMailQueueId',
                'hMailMIME',
                'hMailLibrary'
            )
        );

        $i = 0;

        foreach ($query as $data)
        {
            $mailMime = hString::decodeEntitiesAndUTF8($data['hMailMIME']);
            $mailLibrary = hString::decodeEntitiesAndUTF8($data['hMailLibrary']);

            $this->hMailMIME->restore($mailMime);
            $this->restore($mailLibrary);

            $this->send();

            $this->console("Message Sent hMailQueueId:{$data['hMailQueueId']}");

            $this->hMailQueue->delete('hMailQueueId', $data['hMailQueueId']);

            $i++;
        }

        $this->console("\nNumber of messages sent: {$i}\n");
    }
}

?>