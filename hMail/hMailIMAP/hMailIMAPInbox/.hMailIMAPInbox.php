<?php
  class hMailIMAPInbox extends hPlugin { private $hMailIMAP; public function hConstructor() { $this->plugin('hApplication/hApplicationForm'); $this->getPluginCSS(); $this->hMailIMAP = $this->library('hMail/hMailIMAP'); $connection = 'imaps://richard:'.urlencode('{bugger!}').'@moria.deadmarshes.com/INBOX#novalidate-cert'; if (!$this->hMailIMAP->connect($connection)) { echo $this->hMailIMAP->alerts(); echo $this->hMailIMAP->errors(); }  $messageCount = $this->hMailIMAP->getMessageCount(); echo $messageCount."\n"; for ($messageId = 1; $messageId <= $messageCount; $messageId++) { $headers = $this->hMailIMAP->getHeaders($messageId, '0'); $parts = $this->hMailIMAP->getParts($messageId, '0', true); var_dump($headers); var_dump($parts); } exit; } } ?>