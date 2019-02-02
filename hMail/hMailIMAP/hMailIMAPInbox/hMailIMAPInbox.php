<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#/\\\       \\\\\\\\|
#/\\\ @@    @@\\\\\\| Hot Toddy Mail IMAP Inbox
#/\\ @@@@  @@@@\\\\\| 
#/\\\@@@@| @@@@\\\\\|
#/\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#/\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#/\\\\  \\_   \\\\\\|
#/\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#/\\\\\  ----  \@@@@| http://www.hframework.com/license
#/@@@@@\       \@@@@|
#/@@@@@@\     \@@@@@|
#/\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hMailIMAPInbox extends hPlugin {

    private $hMailIMAP;

    public function hConstructor()
    {
        $this->plugin('hApplication/hApplicationForm');
        $this->getPluginCSS();

        $this->hMailIMAP = $this->library('hMail/hMailIMAP');

        $connection = 'imaps://richard:'.urlencode('{bugger!}').'@moria.deadmarshes.com/INBOX#novalidate-cert';

        if (!$this->hMailIMAP->connect($connection))
        {
            echo $this->hMailIMAP->alerts();
            echo $this->hMailIMAP->errors();
        }

        //var_dump($this->hMailIMAP->getMailboxInfo());
        $messageCount = $this->hMailIMAP->getMessageCount();

        echo $messageCount."\n";

        for ($messageId = 1; $messageId <= $messageCount; $messageId++)
        {
            $headers = $this->hMailIMAP->getHeaders($messageId, '0');
            $parts = $this->hMailIMAP->getParts($messageId, '0', true);
            
            var_dump($headers);
            var_dump($parts);
        }
        
        exit;
    }
}

?>