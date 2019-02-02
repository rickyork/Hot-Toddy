<?php
  class hUserValidationLibrary extends hPlugin { private $password; private $email; public function isValidUserName($username) {  return strlen($username) < 40; } public function isUniqueUserName($username) { return !$this->hUsers->selectExists( 'hUserId', array( 'hUserName' => $username ) ); }  /**
     * Check email address validity
     * @param   $email   Email address to be checked
     * @return  True if email is valid, false if not
     */ public function isValidEmailAddress($email) {  if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $email)) { return false; }  $position = strrpos($email, '@'); if ($position === false) {  return false; } $mailbox = substr($email, 0, $position); $domain = substr($email, $position + 1);     if (strrpos(preg_replace('/"[^"]+"/','', $mailbox).$domain, '@') !== false) {  return false; }  if (!$this->isValidMailbox($mailbox)) { return false; }  if (!$this->isValidDomain($domain)) { return false; }  return true; } /**
     * Checks email section before "@" symbol for validity
     * @param   $local     Text to be checked
     * @return  True if local portion is valid, false if not
     */ public function isValidMailbox($mailbox) {    if (!$this->isLength($mailbox, 1, 64)) { return false; }     $bits = explode('.', $mailbox); for ($i = 0, $max = count($bits); $i < $max; $i++) { $match = preg_match( '.^('. '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]'. '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'. '|'. '("[^\\\"]{0,62}")'. ')$.', $bits[$i] ); if (!$match) { return false; } } return true; } /**
    * Checks email section after "@" symbol for validity
    * @param   domain     Text to be checked
    * @return  True if domain portion is valid, false if not
    */ public function isValidDomain($domain) {  if (!$this->isLength($domain, 1, 255)) { return false; } $match = preg_match( '/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'. '(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/', $domain ) || preg_match( '/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'. '(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/', $domain );  if ($match) { return true; } else { $bits = explode('.', $domain); if (sizeof($bits) < 1) { return false;  } for ($i = 0, $max = count($bits); $i < $max; $i++) {  if (!$this->isLength($bits[$i], 1, 63)) { return false; } if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/', $bits[$i])) { return false; } } } return true; } /**
     * Check given text length is between defined bounds
     * @param   text     Text to be checked
     * @param   minimum  Minimum acceptable length
     * @param   maximum  Maximum acceptable length
     * @return  True if string is within bounds (inclusive), false if not
     */ private function isLength($text, $minimum, $maximum) {  $length = strlen($text); return !(($length < $minimum) || ($length > $maximum)); } public function isUniqueEmailAddress($email) { return !$this->hUsers->selectExists( 'hUserId', array( 'hUserEmail' => $email ) ); } public function setPassword(&$password) { if (isset($password)) { $this->password = &$password; } } public function setEmailAddress(&$email) { if (isset($email)) { $this->email = &$email; } } public function confirmPasswordMatches($value) { return ($this->password === $value); } public function confirmEmailMatches($value) { return ($this->email === $value); } } ?>