<?php
  class Mail_RFC822 { /**
    * RFC 822 Email address list validation Utility
    *
    * What is it?
    *
    * This class will take an address string, and parse it into it's consituent
    * parts, be that either addresses, groups, or combinations. Nested groups
    * are not supported. The structure it returns is pretty straight forward,
    * and is similar to that provided by the imap_rfc822_parse_adrlist(). Use
    * print_r() to view the structure.
    *
    * How do I use it?
    *
    * $address_string = 'My Group: "Richard Heyes" <richard@localhost> (A comment), ted@example.com (Ted Bloggs), Barney;';
    * $structure = Mail_RFC822::parseAddressList($address_string, 'example.com', TRUE)
    * print_r($structure);
    *
    * @author  Richard Heyes <richard@phpguru.org>
    * @author  Chuck Hagenbuch <chuck@horde.org>
    * @version $Revision: 1.1 $
    * @package Mail
    */ /**
     * The address being parsed by the RFC822 object.
     * @private string $address
     */ private $address = ''; /**
     * The default domain to use for unqualified addresses.
     * @private string $defaultDomain
     */ private $defaultDomain = 'localhost'; /**
     * Should we return a nested array showing groups, or flatten everything?
     * @private boolean $nestedGroups
     */ private $nestedGroups = true; /**
     * Whether or not to validate atoms for non-ascii characters.
     * @private boolean $validate
     */ private $validate = true; /**
     * The array of raw addresses built up as we parse.
     * @private array $addresses
     */ private $addresses = array(); /**
     * The final array of parsed address information that we build up.
     * @private array $structure
     */ private $structure = array(); /**
     * The current error message, if any.
     * @private string $error
     */ private $error = null; /**
     * An internal counter/pointer.
     * @private integer $index
     */ private $index = null; /**
     * The number of groups that have been found in the address list.
     * @private integer $groupCount
     * @access public
     */ private $groupCount = 0; /**
     * A variable so that we can tell whether or not we're inside a
     * Mail_RFC822 object.
     * @private boolean $mailRFC822
     */ private $mailRFC822 = true; /**
    * A limit after which processing stops
    * @private int $limit
    */ private $limit = null; /**
     * Sets up the object. The address must either be set here or when
     * calling parseAddressList(). One or the other.
     *
     * @access public
     * @param string  $address         The address(es) to validate.
     * @param string  $defaultDomain  Default domain/host etc. If not supplied, will be set to localhost.
     * @param boolean $nestedGroups     Whether to return the structure with groups nested for easier viewing.
     * @param boolean $validate        Whether to validate atoms. Turn this off if you need to run addresses through before encoding the personal names, for instance.
     *
     * @return object Mail_RFC822 A new Mail_RFC822 object.
     */ public function __construct($address = null, $defaultDomain = null, $nestedGroups = null, $validate = null, $limit = null) { if (isset($address)) { $this->address = $address; } if (isset($defaultDomain)) { $this->defaultDomain = $defaultDomain; } if (isset($nestedGroups)) { $this->nestedGroups = $nestedGroups; } if (isset($validate)) { $this->validate = $validate; } if (isset($limit)) { $this->limit = $limit; } } /**
     * Starts the whole process. The address must either be set here
     * or when creating the object. One or the other.
     *
     * @access public
     * @param string  $address         The address(es) to validate.
     * @param string  $defaultDomain  Default domain/host etc.
     * @param boolean $nestedGroups     Whether to return the structure with groups nested for easier viewing.
     * @param boolean $validate        Whether to validate atoms. Turn this off if you need to run addresses through before encoding the personal names, for instance.
     *
     * @return array A structured array of addresses.
     */ public function parseAddressList($address = null, $defaultDomain = null, $nestedGroups = null, $validate = null, $limit = null) { if (!isset($this->mailRFC822)) { return ( new Mail_RFC822( $address, $defaultDomain, $nestedGroups, $validate, $limit ) )->parseAddressList(); } if (isset($address)) { $this->address = $address; } if (isset($defaultDomain)) { $this->defaultDomain = $defaultDomain; } if (isset($nestedGroups)) { $this->nestedGroups = $nestedGroups; } if (isset($validate)) { $this->validate = $validate; } if (isset($limit)) { $this->limit = $limit; } $this->structure = array(); $this->addresses = array(); $this->error = null; $this->index = null; while ($this->address = $this->splitAddresses($this->address)) { continue; } if ($this->address === false || isset($this->error)) { return false; }   set_time_limit(30);  for ($addressCounter = 0; $addressCounter < count($this->addresses); $addressCounter++) { if (($return = $this->validateAddress($this->addresses[$addressCounter])) === false || isset($this->error)) { return false; } if (!$this->nestedGroups) { $this->structure = array_merge($this->structure, $return); } else { $this->structure[] = $return; } } return $this->structure; } /**
     * Splits an address into seperate addresses.
     *
     * @access private
     * @param string $address The addresses to split.
     * @return boolean Success or failure.
     */ private function splitAddresses($address) { if (!empty($this->limit) && count($this->addresses) == $this->limit) { return ''; } if ($this->isGroup($address) && !isset($this->error)) { $split_char = ';'; $isGroup = true; } else if (!isset($this->error)) { $split_char = ','; $isGroup = false; } else if (isset($this->error)) { return false; }  $parts = explode($split_char, $address); $string = $this->splitCheck($parts, $split_char);  if ($isGroup) {    if (strpos($string, ':') === false) { $this->error = 'Invalid address: ' . $string; return false; }  if (!$this->splitCheck(explode(':', $string), ':')) { return false; }  $this->groupCount++; }   $this->addresses[] = array( 'address' => trim($string), 'group' => $isGroup );   $address = trim(substr($address, strlen($string) + 1));    if ($isGroup && substr($address, 0, 1) == ',') { $address = trim(substr($address, 1)); return $address; } else if (strlen($address) > 0) { return $address; } return ''; } /**
     * Checks for a group at the start of the string.
     *
     * @access private
     * @param string $address The address to check.
     * @return boolean Whether or not there is a group at the start of the string.
     */ private function isGroup($address) {  $parts = explode(',', $address); $string = $this->splitCheck($parts, ',');    if (count($parts = explode(':', $string)) > 1) { $string2 = $this->splitCheck($parts, ':'); return ($string2 !== $string); } return false; } /**
     * A common function that will check an exploded string.
     *
     * @access private
     * @param array $parts The exloded string.
     * @param string $char  The char that was exploded on.
     * @return mixed False if the string contains unclosed quotes/brackets, or the string on success.
     */ private function splitCheck($parts, $character) { $string = $parts[0]; for ($partCounter = 0; $partCounter < count($parts); $partCounter++) { $condition = ( $this->hasUnclosedQuotes($string) || $this->hasUnclosedBrackets($string, '<>') || $this->hasUnclosedBrackets($string, '[]') || $this->hasUnclosedBrackets($string, '()') || substr($string, -1) == '\\' ); if ($condition) { if (isset($parts[$partCounter + 1])) { $string = $string.$character.$parts[$partCounter + 1]; } else { $this->error = 'Invalid address spec. Unclosed bracket or quotes'; return false; } } else { $this->index = $partCounter; break; } } return $string; } /**
     * Checks if a string has an unclosed quotes or not.
     *
     * @access private
     * @param string $string The string to check.
     * @return boolean True if there are unclosed quotes inside the string, false otherwise.
     */ private function hasUnclosedQuotes($string) { $string = explode('"', $string); $stringCount = count($string); for ($stringCounter = 0; $stringCounter < (count($string) - 1); $stringCounter++) { if (substr($string[$stringCounter], -1) == '\\') { $stringCount--; } } return ($stringCount % 2 === 0); } /**
     * Checks if a string has an unclosed brackets or not. IMPORTANT:
     * This function handles both angle brackets and square brackets;
     *
     * @access private
     * @param string $string The string to check.
     * @param string $chars  The characters to check for.
     * @return boolean True if there are unclosed brackets inside the string, false otherwise.
     */ private function hasUnclosedBrackets($string, $chars) { $leftAngleOffset = substr_count($string, $chars[0]); $rightAngleOffset = substr_count($string, $chars[1]); $this->hasUnclosedBracketsSub($string, $leftAngleOffset, $chars[0]); $this->hasUnclosedBracketsSub($string, $rightAngleOffset, $chars[1]); if ($leftAngleOffset < $rightAngleOffset) { $this->error = 'Invalid address spec. Unmatched quote or bracket (' . $chars . ')'; return false; } return ($leftAngleOffset > $rightAngleOffset); } /**
     * Sub function that is used only by hasUnclosedBrackets().
     *
     * @access private
     * @param string $string The string to check.
     * @param integer &$num    The number of occurences.
     * @param string $character   The character to count.
     * @return integer The number of occurences of $char in $string, adjusted for backslashes.
     */ private function hasUnclosedBracketsSub($string, &$numberOfOccurences, $character) { $parts = explode($character, $string); for ($partCounter = 0; $partCounter < count($parts); $partCounter++) { if (substr($parts[$partCounter], -1) == '\\' || $this->hasUnclosedQuotes($parts[$partCounter])) { $numberOfOccurences--; } if (isset($parts[$partCounter + 1])) { $parts[$partCounter + 1] = $parts[$partCounter].$character.$parts[$partCounter + 1]; } } return $numberOfOccurences; } /**
     * Function to begin checking the address.
     *
     * @access private
     * @param string $address The address to validate.
     * @return mixed False on failure, or a structured array of address information on success.
     */ private function validateAddress($address) { $isGroup = false; if ($address['group']) { $isGroup = true;  $parts = explode(':', $address['address']); $groupname = $this->splitCheck($parts, ':'); $structure = array();  if (!$this->validatePhrase($groupname)) { $this->error = 'Group name did not validate.'; return false; } else {   if ($this->nestedGroups) { $structure = new stdClass; $structure->groupname = $groupname; } } $address['address'] = ltrim(substr($address['address'], strlen($groupname . ':'))); }   if ($isGroup) { while (strlen($address['address']) > 0) { $parts = explode(',', $address['address']); $addresses[] = $this->splitCheck($parts, ','); $address['address'] = trim(substr($address['address'], strlen(end($addresses) . ','))); } } else { $addresses[] = $address['address']; }    if (!isset($addresses)) { $this->error = 'Empty group.'; return false; } for ($i = 0; $i < count($addresses); $i++) { $addresses[$i] = trim($addresses[$i]); }      array_walk( $addresses, array( $this, 'validateMailbox' ) );  if ($this->nestedGroups) { if ($isGroup) { $structure->addresses = $addresses; } else { $structure = $addresses[0]; }  } else { if ($isGroup) { $structure = array_merge($structure, $addresses); } else { $structure = $addresses; } } return $structure; } /**
     * Function to validate a phrase.
     *
     * @access private
     * @param string $phrase The phrase to check.
     * @return boolean Success or failure.
     */ private function validatePhrase($phrase) {  $parts = preg_split('/[ \\x09]+/', $phrase, -1, PREG_SPLIT_NO_EMPTY); $phraseParts = array(); while (count($parts) > 0) { $phraseParts[] = $this->splitCheck($parts, ' '); for ($indexCounter = 0; $indexCounter < $this->index + 1; $indexCounter++) { array_shift($parts); } } for ($phasePartCounter = 0; $phasePartCounter < count($phraseParts); $phasePartCounter++) {  if (substr($phraseParts[$phasePartCounter], 0, 1) == '"') { if (!$this->validateQuotedString($phraseParts[$phasePartCounter])) { return false; } continue; }  if (!$this->validateAtom($phraseParts[$phasePartCounter])) { return false; } } return true; } /**
     * Function to validate an atom which from rfc822 is:
     * atom = 1*<any CHAR except specials, SPACE and CTLs>
     *
     * If validation ($this->validate) has been turned off, then
     * validateAtom() doesn't actually check anything. This is so that you
     * can split a list of addresses up before encoding personal names
     * (umlauts, etc.), for example.
     *
     * @access private
     * @param string $atom The string to check.
     * @return boolean Success or failure.
     */ private function validateAtom($atom) { if (!$this->validate) {  return true; }  if (!preg_match('/^[\\x00-\\x7E]+$/i', $atom, $matches)) { return false; }  if (preg_match('/[][()<>@,;\\:". ]/', $atom)) { return false; }  if (preg_match('/[\\x00-\\x1F]+/', $atom)) { return false; } return true; } /**
     * Function to validate quoted string, which is:
     * quoted-string = <"> *(qtext/quoted-pair) <">
     *
     * @access private
     * @param string $qstring The string to check
     * @return boolean Success or failure.
     */ private function validateQuotedString($qstring) {  $qstring = substr($qstring, 1, -1);  return !(preg_match('/(.)[\x0D\\\\"]/', $qstring, $matches) && $matches[1] != '\\'); } /**
     * Function to validate a mailbox, which is:
     * mailbox =   addr-spec         ; simple address
     *           / phrase route-addr ; name and route-addr
     *
     * @access public
     * @param string &$mailbox The string to check.
     * @return boolean Success or failure.
     */ public function validateMailbox(&$mailbox) {  $phrase = ''; $comment = '';  $mailboxCopy = $mailbox; while (strlen(trim($mailboxCopy)) > 0) { $parts = explode('(', $mailboxCopy); $beforeComment = $this->splitCheck($parts, '('); if ($beforeComment != $mailboxCopy) {  $comment = substr(str_replace($beforeComment, '', $mailboxCopy), 1); $parts = explode(')', $comment); $comment = $this->splitCheck($parts, ')'); $comments[] = $comment;  $mailboxCopy = substr( $mailboxCopy, strpos($mailboxCopy, $comment) + strlen($comment) + 1 ); } else { break; } } for ($commentCounter = 0; $commentCounter < count(@$comments); $commentCounter++) { $mailbox = str_replace('('.$comments[$commentCounter].')', '', $mailbox); } $mailbox = trim($mailbox);  if (substr($mailbox, -1) == '>' && substr($mailbox, 0, 1) != '<') { $parts = explode('<', $mailbox); $name = $this->splitCheck($parts, '<'); $phrase = trim($name); $routeAddress = trim(substr($mailbox, strlen($name.'<'), -1)); if ($this->validatePhrase($phrase) === false || ($routeAddress = $this->validateRouteAddr($routeAddress)) === false) { return false; } } else {   if (substr($mailbox, 0, 1) == '<' && substr($mailbox, -1) == '>') { $addressSpecification = substr($mailbox, 1, -1); } else { $addressSpecification = $mailbox; } if (($addressSpecification = $this->validateAddressSpecification($addressSpecification)) === false) { return false; } }  $mbox = new stdClass();  $mbox->personal = $phrase; $mbox->comment = isset($comments) ? $comments : array(); if (isset($routeAddress)) { $mbox->mailbox = $routeAddress['local_part']; $mbox->host = $routeAddress['domain']; $routeAddress['adl'] !== '' ? $mbox->adl = $routeAddress['adl'] : ''; } else { $mbox->mailbox = $addressSpecification['local_part']; $mbox->host = $addressSpecification['domain']; } $mailbox = $mbox; return true; } /**
     * This function validates a route-addr which is:
     * route-addr = "<" [route] addr-spec ">"
     *
     * Angle brackets have already been removed at the point of
     * getting to this function.
     *
     * @access private
     * @param string $routeAddress The string to check.
     * @return mixed False on failure, or an array containing validated address/route information on success.
     */ private function validateRouteAddr($routeAddress) {  if (strpos($routeAddress, ':') !== false) { $parts = explode(':', $routeAddress); $route = $this->splitCheck($parts, ':'); } else { $route = $routeAddress; }   if ($route === $routeAddress) { unset($route); $addressSpecification = $routeAddress; if (($addressSpecification = $this->validateAddressSpecification($addressSpecification)) === false) { return false; } } else {  if (($route = $this->validateRoute($route)) === false) { return false; } $addressSpecification = substr($routeAddress, strlen($route . ':'));  if (($addressSpecification = $this->validateAddressSpecification($addressSpecification)) === false) { return false; } } if (isset($route)) { $return['adl'] = $route; } else { $return['adl'] = ''; } $return = array_merge($return, $addressSpecification); return $return; } /**
     * Function to validate a route, which is:
     * route = 1#("@" domain) ":"
     *
     * @access private
     * @param string $route The string to check.
     * @return mixed False on failure, or the validated $route on success.
     */ private function validateRoute($route) {  $domains = explode(',', trim($route)); for ($i = 0; $i < count($domains); $i++) { $domains[$i] = str_replace('@', '', trim($domains[$i])); if (!$this->validateDomain($domains[$i])) { return false; } } return $route; } /**
     * Function to validate a domain, though this is not quite what
     * you expect of a strict internet domain.
     *
     * domain = sub-domain *("." sub-domain)
     *
     * @access private
     * @param string $domain The string to check.
     * @return mixed False on failure, or the validated domain on success.
     */ private function validateDomain($domain) {  $subDomains = explode('.', $domain); while (count($subDomains) > 0) { $subDomainCopy[] = $this->splitCheck($subDomains, '.'); for ($indexCounter = 0; $indexCounter < $this->index + 1; $indexCounter++) { array_shift($subDomains); } } for ($subDomainCounter = 0; $subDomainCounter < count($subDomainCopy); $subDomainCounter++) { if (!$this->validateSubdomain(trim($subDomainCopy[$subDomainCounter]))) { return false; } }  return $domain; } /**
     * Function to validate a subdomain:
     *   subdomain = domain-ref / domain-literal
     *
     * @access private
     * @param string $subDomain The string to check.
     * @return boolean Success or failure.
     */ private function validateSubdomain($subDomain) { if (preg_match('|^\[(.*)]$|', $subDomain, $arr)) { if (!$this->validateDliteral($arr[1])) { return false; } } else { if (!$this->validateAtom($subDomain)) { return false; } }  return true; } /**
     * Function to validate a domain literal:
     *   domain-literal =  "[" *(dtext / quoted-pair) "]"
     *
     * @access private
     * @param string $dliteral The string to check.
     * @return boolean Success or failure.
     */ private function validateDliteral($dliteral) { return !preg_match('/(.)[][\x0D\\\\]/', $dliteral, $matches) && $matches[1] != '\\'; } /**
     * Function to validate an addr-spec.
     *
     * addr-spec = local-part "@" domain
     *
     * @access private
     * @param string $addressSpecification The string to check.
     * @return mixed False on failure, or the validated addr-spec on success.
     */ private function validateAddressSpecification($addressSpecification) { $addressSpecification = trim($addressSpecification);  if (strpos($addressSpecification, '@') !== false) { $parts = explode('@', $addressSpecification); $localPart = $this->splitCheck($parts, '@'); $domain = substr($addressSpecification, strlen($localPart . '@'));  } else { $localPart = $addressSpecification; $domain = $this->defaultDomain; } if (($localPart = $this->validateLocalPart($localPart)) === false) { return false; } if (($domain = $this->validateDomain($domain)) === false) { return false; }  return array( 'local_part' => $localPart, 'domain' => $domain ); } /**
     * Function to validate the local part of an address:
     *   local-part = word *("." word)
     *
     * @access private
     * @param string $localPart
     * @return mixed False on failure, or the validated local part on success.
     */ private function validateLocalPart($localPart) { $parts = explode('.', $localPart);  while (count($parts) > 0) { $words[] = $this->splitCheck($parts, '.'); for ($indexCounter = 0; $indexCounter < $this->index + 1; $indexCounter++) { array_shift($parts); } }  for ($wordCounter = 0; $wordCounter < count($words); $wordCounter++) { if ($this->validatePhrase(trim($words[$wordCounter])) === false) { return false; } }  return $localPart; } /**
    * Returns an approximate count of how many addresses are
    * in the given string. This is APPROXIMATE as it only splits
    * based on a comma which has no preceding backslash. Could be
    * useful as large amounts of addresses will end up producing
    * *large* structures when used with parseAddressList().
    *
    * @param  string $data Addresses to count
    * @return int          Approximate count
    */ public function approximateCount($data) { return count(preg_split('/(?<!\\\\),/', $data)); } /**
    * This is a email validating function seperate to the rest
    * of the class. It simply validates whether an email is of
    * the common internet form: <user>@<domain>. This can be
    * sufficient for most people. Optional stricter mode can
    * be utilised which restricts mailbox characters allowed
    * to alphanumeric, full stop, hyphen and underscore.
    *
    * @param  string  $data   Address to check
    * @param  boolean $strict Optional stricter mode
    * @return mixed           False if it fails, an indexed array
    *                         username/domain if it matches
    */ public function isValidInetAddress($data, $strict = false) { if ($strict) { $regularExpression = '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i'; } else { $regularExpression = '/^([*+!.&#$|\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i'; } if (preg_match($regularExpression, trim($data), $matches)) { return array($matches[1], $matches[2]); } return false; } } ?>
