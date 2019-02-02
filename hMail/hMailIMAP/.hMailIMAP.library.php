 <?php
  class hMailIMAPLibrary extends hPlugin { private $error; public $mailbox; private $mailboxInfo = array(); private $option = array(); private $structure = array(); private $msg = array(); private $header = array(); private $dataTypes = array( 'text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other' ); private $encodingTypes = array( '7bit', '8bit', 'binary', 'base64', 'quoted-printable', 'other' ); private $fields = array( 'fname', 'pid', 'ftype', 'fsize', 'has_at', 'charset', 'cid' ); public function hConstructor() { } /**
    * Wrapper method for {@link imap_open}. Accepts a URI abstraction in
    * the following format: imap://user:pass@mail.example.com:143/INBOX#notls
    * instead of the standard connection arguments used in imap_open.
    * Replace the protocol with one of pop3|pop3s imap|imaps nntp|nntps.
    * Place intial folder in the file path portion, and optionally append
    * tls|notls|novalidate-cert in the anchor portion of the URL. A port
    * number is optional, however, leaving it off could lead to a serious
    * degradation in preformance.
    *
    * Since Mail_IMAPv2 0.1.0 the $options argument became the $get_info argument.
    * constants for action were removed and the argument is now a BOOL toggle.
    *
    * @param string $uri server URI
    * @param bool $get_info
    * (optional) true by default. If true, make a call to {@link getMailboxInfo}
    * if false do not call {@link getMailboxInfo}
    * @return BOOL
    * @tutorial http://www.deadmarshes.com/index.php?content=Mail_IMAP/connect
    * @access public
    * @see imap_open
    * @todo Don't require the port number, assign port number automatically
    * based on the protocol (duh!) if one isn't present.
    */ public function connect($uri, $getInfo = true) { $uri = parse_url($uri); if (!$uri['fragment']) { $uri['fragment'] = ''; } $connection = '{'.$uri['host']; if (!empty($uri['port'])) { $connection .= ':'.$uri['port']; } else {  switch ($uri['scheme']) { case 'imap': case 'imap2': case 'imap2bis': case 'imap4': case 'imap4rev1': { $connection .= ':143'; break; } case 'imaps': case 'imap2s': case 'imap2biss': case 'imap4s': case 'imap4rev1s': { $connection .= ':993'; break; } case 'pop': case 'pop3': { $connection .= ':110'; break; } case 'pops': case 'pop3s': { break; } case 'nntp': { $connection .= ':119'; break; } case 'nntps': { break; } default: { $this->warning('The protocol supplied is not supported.', __FILE__, __LINE__); } } } if ($uri['scheme'] == 'imap2biss' || $uri['scheme'] != 'imap2bis') { $secure = ('tls' == substr($uri['fragment'], 0, 3))? '' : '/ssl'; $connection .= ('s' == (substr($uri['scheme'], -1)))? '/'.substr($uri['scheme'], 0, 4).$secure : '/'.$uri['scheme']; } else { $secure = ''; $connection .= '/'.$uri['scheme']; } if (!empty($uri['fragment'])) { $connection .= '/'.$uri['fragment']; } $connection .= '}'; $this->mailboxInfo['host'] = $connection;  if (!empty($uri['path'])) { $this->mailboxInfo['folder'] = substr($uri['path'], 1, (strlen($uri['path']) - 1)); $connection .= $this->mailboxInfo['folder']; } $this->mailboxInfo['user'] = urldecode($uri['user']); $this->mailbox = imap_open( $connection, urldecode($uri['user']), urldecode($uri['pass']), isset($this->option['open'])? $this->option['open'] : null ); if (false === $this->mailbox) { $this->warning('Unable to build a connection to the specified mail server.', __FILE__, __LINE__); $return = false; } else { $return = true; }  if ($getInfo) { $this->getMailboxInfo(false); } return $return; }  public function getMailboxInfo($return = true) {    if (!isset($this->mailboxInfo['Mailbox'])) { $this->mailboxInfo = array_merge( $this->mailboxInfo, get_object_vars( imap_mailboxmsginfo($this->mailbox) ) ); } return $return? $this->mailboxInfo : true; } /**
    * Set the $option member variable, which is used to specify optional imap_* function
    * arguments (labeled in the manual as flags or options e.g. FT_UId, OP_READONLY, etc).
    *
    * <b>Example:</b>
    * <code>
    * $msg->setOptions(array('body', 'fetchbody', 'fetchheader'), 'FT_UId');
    * </code>
    *
    * This results in imap_body, imap_fetchbody and imap_fetchheader being passed the FT_UId
    * option in the flags/options argument where ever these are called on by Mail_IMAPv2.
    *
    * Note: this method only sets optional imap_* arguments labeled as flags/options.
    *
    * @param array $options - function names to pass the arugument to
    * @param string $constant - constant name to pass.
    * @return PEAR_Error|true
    * @access public
    * @see $option
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/setOptions
    */ public function setOptions(array $options, $constant) { foreach ($options as $value) { if (!$this->option[$value] = constant($constant)) { $this->warning('The constant: '.$constant.' is not defined!.', __FILE__, __LINE__); } } return true; } /**
    * Wrapper method for {@link imap_close}. Close the IMAP resource stream.
    *
    * @return BOOL
    * @access public
    * @tutorial http://www.deadmarshes.com/index.php?content=Mail_IMAP/close
    * @see imap_close
    */ public function close() { return imap_close( $this->mailbox, isset($this->option['close'])? $this->option['close'] : null ); } /**
    * Wrapper method for {@link imap_num_msg}.
    *
    * @return int mailbox message count
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/messageCount
    * @access public
    * @see imap_num_msg
    */ public function getMessageCount() { return imap_num_msg($this->mailbox); } /**
    * Gather message information returned by {@link imap_fetchstructure} and recursively iterate
    * through each parts array. Concatenate part numbers in the following format `1.1`
    * each part id is separated by a period, each referring to a part or subpart of a
    * multipart message. Create part numbers as such that they are compatible with
    * {@link imap_fetchbody}.
    *
    * @param int &$messageId message id
    * @param array $sub_part recursive
    * @param string $sub_pid recursive parent part id
    * @param int $n recursive counter
    * @param bool $is_sub_part recursive
    * @param bool $skip_part recursive
    * @return mixed
    * @access protected
    * @see imap_fetchstructure
    * @see imap_fetchbody
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/declareParts
    */ private function declareParts(&$messageId, $subPart = null, $subPartId = null, $n = 0, $isSubPart = false, $skipPart = false, $lastWasSigned = false) { if (!is_array($subPart)) { $this->structure[$messageId]['obj'] = imap_fetchstructure( $this->mailbox, $messageId, isset($this->option['fetchstructure'])? $this->option['fetchstructure'] : null ); } if (isset($this->structure[$messageId]['obj']->parts) || is_array($subPart)) { if (!$isSubPart) { $parts = $this->structure[$messageId]['obj']->parts; } else { $parts = $subPart; $n++; } for ($p = 0, $i = 1; $p < count($parts); $n++, $p++, $i++) {        if (empty($parts[$p]->type)) { $ftype = $this->dataTypes[0].'/'.strtolower($parts[$p]->subtype); } else { $ftype = $this->dataTypes[$parts[$p]->type].'/'.strtolower($parts[$p]->subtype); } $this_was_signed = ($ftype == 'multipart/signed'); $skip_next = ($ftype == 'message/rfc822'); $skip = ( $ftype == 'multipart/mixed' && ($lastWasSigned || $skipPart) || $ftype == 'multipart/signed' || $skipPart && $ftype == 'multipart/alternative' || $ftype == 'multipart/related' && count($parts) == 1 ); if ($skip) { $n--; $skipped = true; } else { $skipped = false; $this->structure[$messageId]['pid'][$n] = !$isSubPart? (string) "$i" : (string) "$subPartId.$i"; $this->structure[$messageId]['ftype'][$n] = $ftype; $this->structure[$messageId]['encoding'][$n] = (empty($parts[$p]->encoding))? $this->_encodingTypes[0] : $this->_encodingTypes[$parts[$p]->encoding]; $this->structure[$messageId]['fsize'][$n] = (!isset($parts[$p]->bytes) || empty($parts[$p]->bytes))? 0 : $parts[$p]->bytes;  if ($parts[$p]->ifparameters) { foreach ($parts[$p]->parameters as $param) { $this->structure[$messageId][strtolower($param->attribute)][$n] = strtolower($param->value); } }  if ($parts[$p]->ifdisposition) { $this->structure[$messageId]['disposition'][$n] = strtolower($parts[$p]->disposition); if ($parts[$p]->ifdparameters) { foreach ($parts[$p]->dparameters as $param) { if (strtolower($param->attribute) == 'filename') { $this->structure[$messageId]['fname'][$n] = $param->value; break; } } } } else { $this->structure[$messageId]['disposition'][$n] = 'inline'; } if ($parts[$p]->ifid) { $this->structure[$messageId]['cid'][$n] = $parts[$p]->id; } } if (isset($parts[$p]->parts) && is_array($parts[$p]->parts)) { if (!$skipped) { $this->structure[$messageId]['has_at'][$n] = true; } $n = $this->declareParts($messageId, $parts[$p]->parts, $this->structure[$messageId]['pid'][$n], $n, true, $skip_next, $this_was_signed); } else if (!$skipped) { $this->structure[$messageId]['has_at'][$n] = false; } } if ($isSubPart) { return $n; } } else {  $this->structure[$messageId]['pid'][0] = 1; if (empty($this->structure[$messageId]['obj']->type)) { $this->structure[$messageId]['obj']->type = (int) 0; } if (isset($this->structure[$messageId]['obj']->subtype)) { $this->structure[$messageId]['ftype'][0] = $this->dataTypes[$this->structure[$messageId]['obj']->type].'/'.strtolower($this->structure[$messageId]['obj']->subtype); } if (empty($this->structure[$messageId]['obj']->encoding)) { $this->structure[$messageId]['obj']->encoding = (int) 0; } $this->structure[$messageId]['encoding'][0] = $this->_encodingTypes[$this->structure[$messageId]['obj']->encoding]; if (isset($this->structure[$messageId]['obj']->bytes)) { $this->structure[$messageId]['fsize'][0] = strtolower($this->structure[$messageId]['obj']->bytes); } $this->structure[$messageId]['disposition'][0] = 'inline'; $this->structure[$messageId]['has_at'][0] = false;  if (isset($this->structure[$messageId]['obj']->ifparameters) && $this->structure[$messageId]['obj']->ifparameters) { foreach ($this->structure[$messageId]['obj']->parameters as $param) { $this->structure[$messageId][strtolower($param->attribute)][0] = $param->value; } } } return; } /**
    * Checks if the part has been parsed, if not calls on declareParts to
    * parse the message.
    *
    * @param int &$messageId message id
    * @param bool $checkPid
    * @return void
    * @access protected
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/_checkIfParsed
    */ private function checkIfParsed(&$messageId, $checkPid = true, $get_mime = 'text/html') { if (!isset($this->structure[$messageId]['pid'])) { $this->declareParts($messageId); } if ($checkPid && !isset($this->msg[$messageId]['pid'])) { $this->getDefaultPartId($messageId, $get_mime); } return; } /**
    * sets up member variables containing inline parts and attachments for a specific
    * part in member variable arrays beginning with 'in' and 'attach'. If inline parts
    * are present, sets {@link $inPid}, {@link $inFtype}, {@link $inFsize},
    * {@link $inHasAttach}, {@link $inInlineId} (if an inline CId is specified). If
    * attachments are present, sets, {@link $attachPid}, {@link $attachFsize},
    * {@link $attachHasAttach}, {@link $attachFname} (if a filename is present, empty
    * string otherwise).
    *
    * @param int &$messageId message id
    * @param int &$partId part id
    * @param bool $ret
    * false by default, if true returns the contents of the $in* and $attach* arrays.
    * If false method returns BOOL.
    *
    * @param string $args (optional)
    * Associative array containing optional extra arguments. The following are the
    * possible indices.
    *
    * $args['get_mime'] STRING
    * Values: text/plain|text/html, text/html by default. The MIME type for
    * the part to be displayed by default for each level of nesting.
    *
    * $agrs['get_alternative'] BOOL
    * If true, includes the alternative part of a multipart/alternative
    * message in the $in* array. If veiwing text/html part by default this
    * places the text/plain part in the $in* (inline attachment array).
    *
    * $args['retrieve_all'] BOOL
    * If true, gets all the message parts at once, this option will index
    * the entire message in the $in* and $attach* member variables regardless
    * of nesting (method indexes parts relevant to the current level of
    * nesting by default).
    *
    * @return BOOL|Array
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getParts
    * @access public
    * @since PHP 4.2.0
    */ public function getParts(&$messageId, $partId = '0', $return = false, $arguments = array()) { if (!isset($arguments['get_mime'])) { $arguments['get_mime'] = 'text/html'; } if (!isset($arguments['get_alternative'])) { $arguments['get_alternative'] = true; } $this->checkIfParsed($messageId, true, $arguments['get_mime']); if ($partId === '0') { $partId = $this->msg[$messageId]['pid']; } if (count($this->structure[$messageId]['pid']) == 1 && !isset($this->structure[$messageId]['fallback'][0])) { return true; }  if (false !== ($i = array_search((string) $partId, $this->structure[$messageId]['pid']))) { if (isset($arguments['retrieve_all']) && $arguments['retrieve_all']) { $this->scanMultipart($messageId, $partId, $i, $arguments['get_mime'], 'add', 'none', 2, $arguments['get_alternative']); } else { if ($partId == $this->msg[$messageId]['pid']) { $this->scanMultipart($messageId, $partId, $i, $arguments['get_mime'], 'add', 'top', 2, $arguments['get_alternative']); } else if ($this->structure[$messageId]['ftype'][$i] == 'message/rfc822') { $this->scanMultipart($messageId, $partId, $i, $arguments['get_mime'], 'add', 'all', 1, $arguments['get_alternative']); } } } else { $this->warning('The pid, '.$partId.' is not valid.', __FILE__, __LINE__); return false; } return ($return)? $this->msg[$messageId] : true; } /**
    * Finds message parts relevant to the message part currently being displayed or
    * looks through a message and determines which is the best body to display.
    *
    * @param int &$messageId message id
    * @param int &$partId part id
    * @param int $i offset indice correlating to the pid
    * @param str $MIME one of text/plain or text/html the default MIME to retrieve.
    * @param str $action one of add|get
    * @param str $look_for one of all|multipart|top|none
    * @param int $partId_add determines the level of nesting.
    * @param bool $get_alternative
    * Determines whether the program retrieves the alternative part in a
    * multipart/alternative message.
    *
    * @return string|false
    * @access private
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/_scanMultipart
    */ private function scanMultipart(&$messageId, &$partId, &$i, $mime, $action = 'add', $lookFor = 'all', $partIdAdd = 1, $getAlternative = true) {         if ($action == 'add') { $excludeMime = $mime; $mime = ($excludeMime == 'text/plain')? 'text/html' : 'text/plain'; $in = 0; $a = 0; } else if ($action == 'get') { $excludeMime = null; } $partIdLength = strlen($partId); $thisNesting = count(explode('.', $partId)); foreach ($this->structure[$messageId]['pid'] as $p => $id) {       $nesting = count(explode('.', $this->structure[$messageId]['pid'][$p])); switch ($lookFor) { case 'all': { $condition = (($nesting == ($thisNesting + 1)) && $partId == substr($this->structure[$messageId]['pid'][$p], 0, $partIdLength)); break; } case 'multipart': { $condition = (($nesting == ($thisNesting + 1)) && ($partId == substr($this->structure[$messageId]['pid'][$p], 0))); break; }  case 'none': { $condition = true; break; }  case 'top': default: { if ($this->isMultipart($messageId, 'related') || $this->isMultipart($messageId, 'mixed')) { $condition = ( !stristr($this->structure[$messageId]['pid'][$p], '.') || ($nesting == 2) && substr($this->msg[$messageId]['pid'], 0, 1) == substr($this->structure[$messageId]['pid'][$p], 0, 1) ); } else { $condition = (!stristr($this->structure[$messageId]['pid'][$p], '.')); } } } if ($condition) { if ($this->structure[$messageId]['ftype'][$p] == 'multipart/alternative' || $this->structure[$messageId]['ftype'][$p] == 'multipart/mixed') { foreach ($this->structure[$messageId]['pid'] as $mp => $mpid) {  $sub_nesting = count(explode('.', $this->structure[$messageId]['pid'][$p])); $getAlternative = ( $this->structure[$messageId]['ftype'][$mp] == $mime && $getAlternative && ($sub_nesting == ($thisNesting + $partIdAdd)) && ($partId == substr($this->structure[$messageId]['pid'][$mp], 0, strlen($this->structure[$messageId]['pid'][$p]))) ); if ($getAlternative) { if ($action == 'add') { $this->addPart($in, $messageId, $mp, 'in'); break; } else if ($action == 'get' && !isset($this->structure[$messageId]['fname'][$mp]) && empty($this->structure[$messageId]['fname'][$mp])) { return $this->structure[$messageId]['pid'][$mp]; } } else if ($this->structure[$messageId]['ftype'][$mp] == 'multipart/alternative' && $action == 'get') {  $partId = (string) $this->structure[$messageId]['pid'][$mp]; $partIdLength = strlen($partId); $thisNesting = count(explode('.', $partId)); $partIdAdd = 2; continue; } } } else if ($this->structure[$messageId]['disposition'][$p] == 'inline' && $this->structure[$messageId]['ftype'][$p] != 'multipart/related' && $this->structure[$messageId]['ftype'][$p] != 'multipart/mixed') { if ($action == 'add' && $partId != $this->structure[$messageId]['pid'][$p] && (($this->structure[$messageId]['ftype'][$p] != $excludeMime) || ($this->structure[$messageId]['ftype'][$p] == $excludeMime && isset($this->structure[$messageId]['fname'][$p])) || (isset($this->structure[$messageId]['fallback'][0])))) { $this->addPart($in, $messageId, $p, 'in'); } else if ($action == 'get' && $this->structure[$messageId]['ftype'][$p] == $mime && !isset($this->structure[$messageId]['fname'][$p])) { return $this->structure[$messageId]['pid'][$p]; } } else if ($action == 'add' && $this->structure[$messageId]['disposition'][$p] == 'attachment') { $this->addPart($a, $messageId, $p, 'at'); } } } return false; } /**
    * Determines whether a message contains a multipart/(insert subtype here) part.
    * Only called on by $this->_scanMultipart
    *
    * @return BOOL
    * @access private
    * @see _scanMultipart
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/isMultipart
    */ private function isMultipart($messageId, $subtype) { $return = $this->extractMIME($messageId, array('multipart/'.$subtype)); return (!empty($return) && is_array($return) && count($return) >= 1); } /**
    * Looks to see if this part has any inline parts associated with it.
    * It looks up the message tree for parts with CId entries and
    * indexes those entries, whereas an algorithm may be ran to replace
    * inline CIds with a part viewer.
    *
    * @param int &$messageId message id
    * @param string &$partId part id
    * @param array $secureMime array of acceptable CId MIME types.
    *
    * The $secureMime argument allows you to limit the types of files allowed
    * in a multipart/related message, for instance, to prevent a browser from
    * automatically initiating download of a part that could contain potentially
    * malicious code.
    *
    * Suggested MIME types:
    * text/plain, text/html, text/css, image/jpeg, image/pjpeg, image/gif
    * image/png, image/x-png, application/xml, application/xhtml+xml,
    * text/xml
    *
    * MIME types are not limited by default.
    *
    * @return array|false
    * On success returns an array of parts associated with the current message,
    * including the cid of the part, the part id and the MIME type.
    *
    * @access public
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getRelatedParts
    */ public function getRelatedParts(&$messageId, &$partId, $secureMime = array()) {  $this->checkIfParsed($messageId);         if (!empty($secureMime) && is_array($secureMime)) { $this->warning('Argument $secureMime is not an array.', __FILE__, __LINE__); return false; } $related = array(); if (isset($this->structure[$messageId]['pid']) && is_array($this->structure[$messageId]['pid'])) { if (strlen($partId) > 1) { $nesting = count(explode('.', $partId)); $compare = substr($partId, 0, -4); foreach ($this->structure[$messageId]['pid'] as $i => $rpid) {    if (count(explode('.', $rpid)) == ($nesting - 1) && substr($rpid, 0, -2) == $compare) { $this->getCIds($messageId, $i, $secureMime, $related); } } } else if (strlen($partId) == 1) {   foreach ($this->structure[$messageId]['pid'] as $i => $rpid) {   if (count(explode('.', $rpid)) == 2 && substr($rpid, 0, 1) == $partId) { $this->getCIds($messageId, $i, $secureMime, $related); } } } } else { $this->warning('Message structure does not exist.', __FILE__, __LINE__); } return (count($related) >= 1)? $related : false; } /**
    * Helper function for getRelatedParts
    *
    * @return void
    * @access private
    * @see getRelatedParts
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/_getCIds
    */ private function getCIds(&$messageId, &$i, &$secureMime, &$related) { if ((isset($this->structure[$messageId]['cid'][$i])) && (empty($secureMime) || is_array($secureMime) && in_array($this->structure[$messageId]['ftype'][$i], $secureMime))) { $related['cid'][] = $this->structure[$messageId]['cid'][$i]; $related['pid'][] = $this->structure[$messageId]['pid'][$i]; $related['ftype'][] = $this->structure[$messageId]['ftype'][$i]; } } /**
    * Destroys variables set by {@link getParts} and declareParts.
    *
    * @param integer &$messageId message id
    * @return void
    * @access public
    * @see getParts
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/unsetParts
    */ public function unsetParts(&$messageId) { unset($this->msg[$messageId]); unset($this->structure[$messageId]); return; } /**
    * Adds information to the member variable inline part 'in' and attachment 'at' arrays.
    *
    * @param int &$n offset part counter
    * @param int &$messageId message id
    * @param int &$i offset structure reference counter
    * @return void
    * @access private
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/addPart
    */ private function addPart(&$n, &$messageId, &$i, $part) { foreach ($this->fields as $field) { if (isset($this->structure[$messageId][$field][$i]) && !empty($this->structure[$messageId][$field][$i])) { $this->msg[$messageId][$part][$field][$n] = $this->structure[$messageId][$field][$i]; } } $n++; return; } /**
    * Returns entire unparsed message body. See {@link imap_body} for options.
    *
    * @param int &$messageId message id
    * @return string|null
    * @tutorial http://www.deadmarshes.com/index.php?content=Mail_IMAPv2/getRawMessage
    * @access public
    * @see imap_body
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getRawMessage
    */ public function getRawMessage(&$messageId) { return imap_body( $this->mailbox, $messageId, isset($this->option['body'])? $this->option['body'] : null ); } /**
    * Searches parts array set in $this->declareParts() for a displayable message.
    * If the part id passed is message/rfc822 looks in subparts for a displayable body.
    * Attempts to return a text/html inline message part by default. And will
    * automatically attempt to find a text/plain part if a text/html part could
    * not be found.
    *
    * Returns an array containing three associative indices; 'ftype', 'fname' and
    * 'message'. 'ftype' contains the MIME type of the message, 'fname', the original
    * file name, if any, empty string otherwise. And 'message', which contains the
    * message body itself which is returned decoded from base64 or quoted-printable if
    * either of those encoding types are specified, returns untouched otherwise.
    * Returns false on failure.
    *
    * @param int &$messageId message id
    * @param string $partId part id
    * @param int $action
    * (optional) options for body return. Set to one of the following:
    * Mail_IMAPv2_BODY (default), if part is message/rfc822 searches subparts for a
    * displayable body and returns the body decoded as part of an array.
    * Mail_IMAPv2_LITERAL, return the message for the specified $partId without searching
    * subparts or decoding the message (may return unparsed message) body is returned
    * undecoded as a string.
    * Mail_IMAPv2_LITERAL_DECODE, same as Mail_IMAPv2_LITERAL, except message decoding is
    * attempted from base64 or quoted-printable encoding, returns undecoded string
    * if decoding failed.
    *
    * @param string $getPart
    * (optional) one of text/plain or text/html, allows the specification of the default
    * part to return from multipart messages, text/html by default.
    *
    * @param int $attempt
    * (optional) used internally by getBody to track attempts at finding the
    * right part to display for the body of the message.
    *
    * @return array|string|false
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getBody
    * @access public
    * @see imap_fetchbody
    * @see $this->getParts
    * @since PHP 4.2.0
    */ public function getBody(&$messageId, $partId = '1', $get_mime = 'text/html', $attempt = 1) { if ($this->hMailIMAPGetBody('literal')) { return imap_fetchbody( $this->mailbox, $messageId, $partId, isset($this->option['fetchbody'])? $this->option['fetchbody'] : null ); } $this->checkIfParsed($messageId, true, $get_mime); if (false !== ($i = array_search((string) $partId, $this->structure[$messageId]['pid']))) { if ($this->hMailIMAPGetBody('literalDecode')) { $messageBody = imap_fetchbody( $this->mailbox, $messageId, $partId, isset($this->option['fetchbody'])? $this->option['fetchbody'] : null ); return $this->decodeMessage($messageBody, $this->structure[$messageId]['encoding'][$i]); }   switch ($this->structure[$messageId]['ftype'][$i]) { case 'message/rfc822': case 'multipart/related': case 'multipart/alternative': { if ($this->structure[$messageId]['ftype'][$i] == 'message/rfc822' || $this->structure[$messageId]['ftype'][$i] == 'multipart/related') { $new_pid = $this->scanMultipart($messageId, $partId, $i, $get_mime, 'get', 'all', 1); } else { $new_pid = $this->scanMultipart($messageId, $partId, $i, $get_mime, 'get', 'multipart', 1); }  switch(true) { case (!empty($new_pid)): { $partId = $new_pid; break; } case (empty($new_pid) && $get_mime == 'text/html'): { return ($attempt == 1)? $this->getBody($messageId, $partId, $action, 'text/plain', 2) : false; } case (empty($new_pid) && $get_mime == 'text/plain'): { return ($attempt == 1)? $this->getBody($messageId, $partId, $action, 'text/html', 2) : false; } } } }  if (!empty($new_pid) && false === ($i = array_search((string) $partId, $this->structure[$messageId]['pid']))) {  $this->warning('Unable to find a suitable replacement part Id. Message: may be poorly formed, corrupted, or not supported by the Mail_IMAPv2 parser.', __FILE__, __LINE__); return false; } $messageBody = imap_fetchbody( $this->mailbox, $messageId, $partId, isset($this->option['fetchbody'])? $this->option['fetchbody'] : null ); if (!$messageBody) { $this->warning('Message body is null.', __FILE__, __LINE__); return false; }    return array( 'message' => $this->decodeMessage( $messageBody, $this->structure[$messageId]['encoding'][$i], $this->structure[$messageId]['charset'][$i] ), 'ftype' => $this->structure[$messageId]['ftype'][$i], 'fname' => isset($this->structure[$messageId]['fname'][$i])? $this->structure[$messageId]['fname'][$i] : '', 'charset' => $this->structure[$messageId]['charset'][$i] ); } else { $this->warning('Invalid pid, '.$partId, __FILE__, __LINE__); return false; } return false; } public function getCharsetAndEncoding($messageId, $partId) { if (false !== ($offset = array_search((string) $partId, $this->structure[$messageId]['pid'], true))) { return array( 'charset' => $this->structure[$messageId]['charset'][$offset], 'encoding' => $this->structure[$messageId]['encoding'][$offset] ); } return array( 'charset' => 'ascii', 'encoding' => null ); } /**
    * Decode a string from quoted-printable or base64 encoding. If
    * neither of those encoding types are specified, returns string
    * untouched.
    *
    * @param string &$body string to decode
    * @param string &$encoding encoding to decode from.
    * @return string
    * @access private
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/decodeMessage
    */ private function decodeMessage(&$body, &$encoding, &$charset) { switch ($encoding) { case 'quoted-printable': { return ($charset == 'utf-8')? utf8_decode(imap_utf8(imap_qprint($body))) : imap_qprint($body); } case 'base64': { return imap_base64($body); } default: { return $body; } } } /**
    * Searches structure defined in $this->declareParts for the top-level default message.
    * Attempts to find a text/html default part, if no text/html part is found,
    * automatically attempts to find a text/plain part. Returns the part id for the default
    * top level message part on success. Returns false on failure.
    *
    * @param int &$messageId message id
    * @param string $getPart
    * (optional) default MIME type to look for, one of text/html or text/plain
    * text/html by default.
    * @param int $attempt
    * (optional) Used internally by _getDefaultPartId to track the method's attempt
    * at retrieving the correct default part to display.
    *
    * @return string
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/_getDefaultPartId
    * @access private
    */ private function getDefaultPartId(&$messageId, $get_mime = 'text/html', $attempt = 1) {  $this->checkIfParsed($messageId, false);   $part = array('text/html', 'text/plain'); if ($get_mime == 'text/plain') { $part = array_reverse($part); } foreach ($part as $mime) { if (0 !== count($msg_part = @array_keys($this->structure[$messageId]['ftype'], $mime))) { foreach ($msg_part as $i) { if ($this->structure[$messageId]['disposition'][$i] == 'inline' && !stristr($this->structure[$messageId]['pid'][$i], '.')) { $this->msg[$messageId]['pid'] = $this->structure[$messageId]['pid'][$i]; return $this->structure[$messageId]['pid'][$i]; } } } }   $mp_nesting = 1; $partIdLength = 1; if (is_array($this->structure[$messageId]['pid'])) { foreach ($this->structure[$messageId]['pid'] as $p => $id) { $nesting = count(explode('.', $this->structure[$messageId]['pid'][$p])); if (!isset($mpid)) { if ($nesting == 1 && isset($this->structure[$messageId]['ftype'][$p]) && ($this->structure[$messageId]['ftype'][$p] == 'multipart/related')) { $mp_nesting = 2; $partIdLength = 3; continue; } if ($nesting == $mp_nesting && isset($this->structure[$messageId]['ftype'][$p]) && ($this->structure[$messageId]['ftype'][$p] == 'multipart/alternative' || $this->structure[$messageId]['ftype'][$p] == 'multipart/mixed')) { $mpid = $this->structure[$messageId]['pid'][$p]; continue; } } if (isset($mpid) && $nesting == ($mp_nesting + 1) && $this->structure[$messageId]['ftype'][$p] == $get_mime && $mpid == substr($this->structure[$messageId]['pid'][$p], 0, $partIdLength)) { $this->msg[$messageId]['pid'] = $this->structure[$messageId]['pid'][$p]; return $this->structure[$messageId]['pid'][$p]; } } } else { $this->warning('Message structure does not exist.', __FILE__, __LINE__); }    switch ($get_mime) { case 'text/html': { $return = ($attempt == 1)? $this->getDefaultPartId($messageId, 'text/plain', 2) : false; break; } case 'text/plain': { $return = ($attempt == 1)? $this->getDefaultPartId($messageId, 'text/html', 2) : false; break; } default: { $return = false; } } if (!$return && $attempt == 2) { if (isset($this->structure[$messageId]['ftype'][0])) { $this->structure[$messageId]['fallback'][0] = true; } else { $this->warning('Message contains no MIME types.', __FILE__, __LINE__); } } $this->msg[$messageId]['pid'] = !$return? 1 : $return; return $this->msg[$messageId]['pid']; } /**
    * Searches all message parts for the specified MIME type. Use {@link getBody}
    * with $action option Mail_IMAPv2_LITERAL_DECODE to view MIME type parts retrieved.
    * If you need to access the MIME type with filename use normal {@link getBody}
    * with no action specified.
    *
    * Returns an array of part ids on success.
    * Returns false if MIME couldn't be found, or on failure.
    *
    * @param int &$messageId message id
    * @param string|array $MIMEs mime type to extract
    * @return array|false
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/extractMIME
    * @access public
    */ public function extractMIME(&$messageId, $MIMEs) { $this->checkIfParsed($messageId); $return = array(); if (is_array($this->structure[$messageId]['ftype'])) { if (is_array($MIMEs)) { foreach ($MIMEs as $MIME) { if (0 !== count($keys = array_keys($this->structure[$messageId]['ftype'], $MIME))) { foreach ($keys as $key) { $return[] = $this->structure[$messageId]['pid'][$key]; } } } } else { if (0 !== count($keys = array_keys($this->structure[$messageId]['ftype'], $MIMEs))) { foreach ($keys as $key) { $return[] = $this->structure[$messageId]['pid'][$key]; } } } } else { $this->warning('Member variable $this->structure[\'ftype\'] is not an array.', __FILE__, __LINE__); } return isset($return)? $return : false; } /**
    * Set member variable {@link $rawHeaders} to contain Raw Header information
    * for a part. Returns default header part id on success, returns false on failure.
    *
    * @param int &$messageId message_id
    * @param string $partId (optional) part id to retrieve headers for
    * @param bool $return
    * Decides what to return. One of true|false|return_pid
    * If true return the raw headers (returns the headers by default)
    *
    * @return string|false
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getRawHeaders
    * @access public
    * @see imap_fetchbody
    * @see getHeaders
    */ public function getRawHeaders(&$messageId, $partId = '0', $return = true, $partId_check = false) { $this->checkIfParsed($messageId); if ($partId == $this->msg[$messageId]['pid']) { $partId = (string) '0'; } if ($partId !== '0') { if (false === ($partId = $this->_defaultHeaderPid($messageId, $partId))) { $this->warning('pid, '.$partId.', is not valid.', __FILE__, __LINE__); return false; } } if ($partId === '0' && $partId_check) { return true; } else if ($partId_check) { $return = true; } if ($partId === '0') { $opt = (isset($this->option['fetchheader']))? $this->option['fetchheader'] : null; $rawHeaders = imap_fetchheader($this->mailbox, $messageId, $opt); } else { $opt = (isset($this->option['fetchbody']))? $this->option['fetchbody'] : null; $rawHeaders = imap_fetchbody($this->mailbox, $messageId, $partId, $opt); } if ($return) { return $rawHeaders; } else { $this->header[$messageId]['raw'] = $rawHeaders; return true; } } public function setFromLength($length) { $this->hMailIMAPFromLength = (int) $length; } public function setSubjectLength($length) { $this->hMailIMAPSubjectLength = (int) $length; } public function setDefaultHost($host) { $this->hMailIMAPDefaultHost = $host; } /**
    * Set member variable containing header information. Creates an array containing
    * associative indices referring to various header information. Use {@link}
    * or {@link print_r} on the {@link $header} member variable to view information
    * gathered by this function.
    *
    * If $ret is true, returns array containing header information on success and false
    * on failure.
    *
    * If $ret is false, adds the header information to the $header member variable
    * and returns BOOL.
    *
    * @param int &$messageId message id
    * @param string &$partId (optional) part id to retrieve headers for.
    * @param bool $return
    * (optional) If true return the headers, if false, assign to $header member variable.
    *
    * @param array $args
    * (optional) Associative array containing extra arguments.
    *
    * $args['from_length'] int
    * From field length for imap_headerinfo.
    *
    * $args['subject_length'] int
    * Subject field length for imap_headerinfo
    *
    * $args['default_host'] string
    * Default host for imap_headerinfo & imap_rfc822_parse_headers
    *
    * @return Array|BOOL
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getHeaders
    * @access public
    * @see getParts
    * @see imap_fetchheader
    * @see imap_fetchbody
    * @see imap_headerinfo
    * @see imap_rfc822_parse_headers
    */ public function getHeaders(&$messageId, $partId = '0', $return = true) { $this->checkIfParsed($messageId); if ($partId == $this->msg[$messageId]['pid']) { $partId = '0'; } if ($partId !== '0') { if (false === ($rawHeaders = $this->getRawHeaders($messageId, $partId, true, true))) { return false; } if ($rawHeaders === true) { $partId = '0'; } }  if ($partId === '0') { $headerInformation = imap_headerinfo( $this->mailbox, $messageId, $this->hMailIMAPFromLength(1024), $this->hMailIMAPSubjectLength(1024), $this->hMailIMAPDefaultHost($_SERVER['HTTP_HOST']) ); } else { $headerInformation = imap_rfc822_parse_headers( $rawHeaders, $this->hMailIMAPDefaultHost($_SERVER['HTTP_HOST']) ); }      if (!is_object($headerInformation)) { $this->warning('pid, '.$partId.', in not valid.', __FILE__, __LINE__); return false; } $headers = get_object_vars($headerInformation); foreach ($headers as $key => $value) { if (!is_object($value) && !is_array($value)) { $this->header[$messageId][$key] = iconv_mime_decode($value, 2, 'UTF-8'); } }  if (isset($headerInformation->udate) && !empty($headerInformation->udate)) { $this->header[$messageId]['udate'] = (int) $headerInformation->udate; } else { strtotime($headerInformation->Date); }  $line = array( 'from', 'reply_to', 'sender', 'return_path', 'to', 'cc', 'bcc' ); for ($i = 0; $i < count($line); $i++) { if (isset($headerInformation->$line[$i])) { $this->parseHeaderLine($messageId, $headerInformation->$line[$i], $line[$i]); } }  unset($headerInformation); return ($return)? $this->header[$messageId] : false; } /**
    * Parse header information from the given line and add it to the {@link $header}
    * array. This function is only used by {@link getRawHeaders}.
    *
    * @param string &$line
    * @param string $name
    * @return array
    * @access private
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/_parseHeaderLine
    */ private function parseHeaderLine(&$messageId, &$line, $name) { if (isset($line) && count($line) >= 1) { $i = 0; foreach ($line as $object) { if (isset($object->adl)) { $this->header[$messageId][$name.'_adl'][$i] = iconv_mime_decode($object->adl, 2, 'UTF-8'); } if (isset($object->mailbox)) { $this->header[$messageId][$name.'_mailbox'][$i] = iconv_mime_decode($object->mailbox, 2, 'UTF-8'); } if (isset($object->personal)) { $this->header[$messageId][$name.'_personal'][$i] = iconv_mime_decode($object->personal, 2, 'UTF-8'); } if (isset($object->host)) { $this->header[$messageId][$name.'_host'][$i] = iconv_mime_decode($object->host, 2, 'UTF-8'); } if (isset($object->mailbox) && isset($object->host)) { $this->header[$messageId][$name][$i] = iconv_mime_decode($object->mailbox, 2, 'UTF-8').'@'.iconv_mime_decode($object->host, 2, 'UTF-8'); } $i++; }  if (isset(${$name.'address'})) { $this->header[$messageId][$name.'address'][$i] = ${$name.'address'}; } } } /**
    * Finds and returns a default part id for headers and matches any sub message part to
    * the appropriate headers. Returns false on failure and may return a value that
    * evaluates to false, use the '===' operator for testing this function's return value.
    *
    * @param int &$messageId message id
    * @param string $partId part id
    * @return string|false
    * @access private
    * @see getHeaders
    * @see getRawHeaders
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/_defaultHeaderPid
    */ private function defaultHeaderPid(&$messageId, $partId) {  $this->checkIfParsed($messageId);  if (false !== ($i = array_search((string) $partId, $this->structure[$messageId]['pid']))) {  if ($this->structure[$messageId]['ftype'][$i] == 'message/rfc822') { $return = (string) $partId.'.0'; } else if ($partId == $this->msg[$messageId]['pid']) { $return = (string) '0'; } else { $partIdLength = strlen($partId); $this_nesting = count(explode('.', $partId));  if (!stristr($partId, '.') || ($this_nesting - 1) == 1) { $return = (string) '0'; } else if ($this_nesting > 2) {  for ($pos = $this_nesting - 1; $pos > 0; $pos -= 1) { foreach ($this->structure[$messageId]['pid'] as $p => $aid) { $nesting = count(explode('.', $this->structure[$messageId]['pid'][$p])); if ($nesting == $pos && ($this->structure[$messageId]['ftype'][$p] == 'message/rfc822' || $this->structure[$messageId]['ftype'][$p] == 'multipart/related')) {  return (string) $this->structure[$messageId]['pid'][$p].'.0'; } } } $return = ($partIdLength == 3)? (string) '0' : false; } else { $return = false; } } return $return; } else { $this->warning('pid, '.$partId.', is not valid.', __FILE__, __LINE__); return false; } } /**
    * Destroys variables set by {@link getHeaders}.
    *
    * @param int &$messageId message id
    * @return void
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/unsetHeaders
    * @access public
    * @see getHeaders
    */ public function unsetHeaders(&$messageId) { unset($this->header[$messageId]); return; } /**
    * Wrapper function for {@link imap_delete}. Sets the marked for deletion flag. Note: POP3
    * mailboxes do not remember flag settings between connections, for POP3 mailboxes
    * this function should be used in addtion to {@link expunge}.
    *
    * @param int &$messageId message id
    * @return BOOL
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/delete
    * @access public
    * @see imap_delete
    * @see expunge
    */ public function delete(&$messageId, $expunge = true) { if (!is_array($messageId)) { if (!imap_delete($this->mailbox, $messageId)) { $this->warning('Unable to mark message for deletion.', __FILE__, __LINE__); $return = false; } else { $return = true; } } else { foreach ($messageId as $id) { if (!imap_delete($this->mailbox, $id)) { $this->warning('Unable to mark message for deletion.', __FILE__, __LINE__); $return = false; } } $return = true; } if ($expunge) { $this->expunge(); } return $return; } /**
    * Wrapper function for {@link imap_expunge}. Expunges messages marked for deletion.
    *
    * @return BOOL
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/expunge
    * @access public
    * @see imap_expunge
    * @see delete
    */ public function expunge() { if (imap_expunge($this->mailbox)) { return true; } else { $this->warning('Unable to expunge mailbox.', __FILE__, __LINE__); return false; } } /**
    * Wrapper function for {@link imap_errors}. Implodes the array returned by imap_errors,
    * (if any) and returns the error text.
    *
    * @param bool $handler
    * How to handle the imap error stack, true by default. If true adds the errors
    * to the PEAR_ErrorStack object. If false, returns the imap error stack.
    *
    * @param string $seperator
    * (optional) Characters to seperate each error message. "<br />\n" by default.
    *
    * @return bool|string
    * @access public
    * @see imap_errors
    * @see alerts
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/errors
    */ public function getErrors($handler = true, $seperator = "\n") { $errors = imap_errors(); if (empty($errors)) { return false; } if ($handler) { $this->warning(implode($seperator, $errors), __FILE__, __LINE__); } return implode($seperator, $errors); } /**
    * Wrapper function for {@link imap_alerts}. Implodes the array returned by imap_alerts,
    * (if any) and returns the text.
    *
    * @param bool $handler
    * How to handle the imap error stack, true by default. If true adds the alerts
    * to the PEAR_ErrorStack object. If false, returns the imap alert stack.
    *
    * @param string $seperator Characters to seperate each alert message. '<br />\n' by default.
    * @return bool|string
    * @access public
    * @see imap_alerts
    * @see errors
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/alerts
    */ public function getAlerts($handler = true, $seperator = "\n") { $alerts = imap_alerts(); if (empty($alerts)) { return false; } if ($handler) { $this->warning(implode($seperator, $alerts), __FILE__, __LINE__); } return implode($seperator, $alerts); } /**
    * Retreives information about the current mailbox's quota. Rounds up quota sizes and
    * appends the unit of measurment. Returns information in a multi-dimensional associative
    * array.
    *
    * @param string $folder Folder to retrieve quota for.
    * @param BOOL $return
    * (optional) true by default, if true return the quota if false merge quota
    * information into the $mailboxInfo member variable.
    * @return array|false
    * @access public
    * @see imap_get_quotaroot
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getQuota
    */ public function getQuota($folder = null, $return = true) { if (empty($folder) && !isset($this->mailboxInfo['folder'])) { $folder = 'INBOX'; } else if (empty($folder) && isset($this->mailboxInfo['folder'])) { $folder = $this->mailboxInfo['folder']; } $q = imap_get_quotaroot($this->mailbox, $folder);    if (isset($q['STORAGE']['usage']) && isset($q['STORAGE']['limit'])) { $q['STORAGE']['usage'] = $this->convertBytes($q['STORAGE']['usage'] * 1024); $q['STORAGE']['limit'] = $this->convertBytes($q['STORAGE']['limit'] * 1024); } if (isset($q['MESSAGE']['usage']) && isset($q['MESSAGE']['limit'])) { $q['MESSAGE']['usage'] = $this->convertBytes($q['MESSAGE']['usage']); $q['MESSAGE']['limit'] = $this->convertBytes($q['MESSAGE']['limit']); } if (empty($q['STORAGE']['usage']) && empty($q['STORAGE']['limit'])) { $this->warning('Quota not available for this server.', __FILE__, __LINE__); return false; } else if ($return) { return $q; } else { $this->mailboxInfo = array_merge($this->mailboxInfo, $q); return true; } } /**
    * Wrapper function for {@link imap_setflag_full}. Sets various message flags.
    * Accepts an array of message ids and an array of flags to be set.
    *
    * The flags which you can set are "\\Seen", "\\Answered", "\\Flagged",
    * "\\Deleted", and "\\Draft" (as defined by RFC2060).
    *
    * Warning: POP3 mailboxes do not remember flag settings from connection to connection.
    *
    * @param array $messageIds Array of message ids to set flags on.
    * @param array $flags Array of flags to set on messages.
    * @param int $action Flag operation toggle one of set|clear
    * @param int $options
    * (optional) sets the forth argument of {@link imap_setflag_full} or {@imap_clearflag_full}.
    *
    * @return BOOL
    * @throws Message Ids and Flags are to be supplied as arrays. Remedy: place message ids
    * and flags in arrays.
    * @access public
    * @see imap_setflag_full
    * @see imap_clearflag_full
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/setFlags
    */ public function setFlags(array $messageIds, array $flags) { return imap_setflag_full( $this->mailbox, implode(',', $messageIds), implode(' ', $flags), $this->hMailIMAPSetFlagOptions(null) ); } public function clearFlags(array $messageIds, array $flags) { return imap_clearflag_full( $this->mailbox, implode(',', $messageIds), implode(' ', $flags), $this->hMailIMAPClearFlagOptions(null) ); } /**
    * Wrapper method for imap_list. Calling on this function will return a list of mailboxes.
    * This method receives the host argument automatically via $this->connect in the
    * $this->mailboxInfo['host'] variable if a connection URI is used.
    *
    * @param string (optional) host name.
    * @return array|false list of mailboxes on the current server.
    * @access public
    * @see imap_list
    * @tutorial http://www.deadmarshes.com/Mail_IMAP?content=Mail_IMAP/getMailboxes
    */ public function getMailboxes($host = null, $pattern = '*', $return = true) { if (empty($host) && !isset($this->mailboxInfo['host'])) { $this->warning('Supplied host is not valid!'); return false; } else if (empty($host) && isset($this->mailboxInfo['host'])) { $host = $this->mailboxInfo['host']; } if ($list = imap_list($this->mailbox, $host, $pattern)) { if (is_array($list)) { foreach ($list as $key => $val) { $mb[$key] = str_replace($host, '', imap_utf7_decode($val)); } } } else { $this->warning('Cannot fetch mailbox names.', __FILE__, __LINE__); return false; } if ($return) { return $mb; } else { $this->mailboxInfo = array_merge($this->mailboxInfo, $mb); } } } ?>