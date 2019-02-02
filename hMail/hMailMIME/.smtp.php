<?php
  define('SMTP_STATUS_NOT_CONNECTED', 1, true); define('SMTP_STATUS_CONNECTED', 2, true); class smtp { /**
    * This file is part of the htmlMimeMail5 package (http://www.phpguru.org/)
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
    */ private $authenticated; private $connection; private $recipients; private $headers; private $timeout; private $errors; private $status; private $body; private $from; private $host; private $port; private $helo; private $auth; private $user; private $pass; /**
    * Constructor function. Arguments:
    * $params - An assoc array of parameters:
    *
    *   host    - The hostname of the smtp server        Default: localhost
    *   port    - The port the smtp server runs on        Default: 25
    *   helo    - What to send as the HELO command        Default: localhost
    *             (typically the hostname of the
    *             machine this script runs on)
    *   auth    - Whether to use basic authentication    Default: false
    *   user    - Username for authentication            Default: <blank>
    *   pass    - Password for authentication            Default: <blank>
    *   timeout - The timeout in seconds for the call    Default: 5
    *             to fsockopen()
    */ public function __construct($params = array()) { if (!defined('CRLF')) { define('CRLF', "\r\n", TRUE); } $this->authenticated = false; $this->timeout = 5; $this->status = SMTP_STATUS_NOT_CONNECTED; $this->host = 'localhost'; $this->port = 25; $this->helo = 'localhost'; $this->auth = false; $this->user = ''; $this->pass = ''; $this->errors = array(); foreach ($params as $key => $value) { $this->$key = $value; } } /**
    * Connect function. This will, when called
    * statically, create a new smtp object,
    * call the connect function (ie this function)
    * and return it. When not called statically,
    * it will connect to the server and send
    * the HELO command.
    */ public function connect($params = array()) { if (!isset($this->status)) { $obj = new smtp($params); if ($obj->connect()) { $obj->status = SMTP_STATUS_CONNECTED; } return $obj; } else { $this->connection = fsockopen( $this->host, $this->port, $errno, $errstr, $this->timeout ); if (function_exists('socket_set_timeout')) { @socket_set_timeout($this->connection, 5, 0); } $greeting = $this->get_data(); if (is_resource($this->connection)) { return $this->auth ? $this->ehlo() : $this->helo(); } $this->errors[] = 'Failed to connect to server: '.$errstr; return false; } } /**
    * Function which handles sending the mail.
    * Arguments:
    * $params    - Optional assoc array of parameters.
    *            Can contain:
    *              recipients - Indexed array of recipients
    *              from       - The from address. (used in MAIL FROM:),
    *                           this will be the return path
    *              headers    - Indexed array of headers, one header per array entry
    *              body       - The body of the email
    *            It can also contain any of the parameters from the connect()
    *            function
    */ public function send($params = array()) { foreach ($params as $key => $value) { $this->set($key, $value); } if ($this->is_connected()) {  if ($this->auth && !$this->authenticated && !$this->auth()) { return false; } $this->mail($this->from); if (is_array($this->recipients)) { foreach ($this->recipients as $value) { $this->rcpt($value); } } else { $this->rcpt($this->recipients); } if (!$this->data()) { return false; }  $headers = str_replace(CRLF.'.', CRLF.'..', trim(implode(CRLF, $this->headers))); $body = str_replace(CRLF.'.', CRLF.'..', $this->body); $body = substr($body, 0, 1) == '.' ? '.'.$body : $body; $this->send_data($headers); $this->send_data(''); $this->send_data($body); $this->send_data('.'); $result = (substr(trim($this->get_data()), 0, 3) === '250');  return $result; } $this->errors[] = 'Not connected!'; return false; } /**
    * Function to implement HELO cmd
    */ private function helo() { $condition = ( is_resource($this->connection) && $this->send_data('HELO '.$this->helo) && substr(trim($error = $this->get_data()), 0, 3) === '250' ); if ($condition) { return true; } $this->errors[] = 'HELO command failed, output: ' . trim(substr(trim($error),3)); return false; } /**
    * Function to implement EHLO cmd
    */ private function ehlo() { $condition = ( is_resource($this->connection) && $this->send_data('EHLO '.$this->helo) && substr(trim($error = $this->get_data()), 0, 3) === '250' ); if ($condition) { return true; } $this->errors[] = 'EHLO command failed, output: ' . trim(substr(trim($error),3)); return false; } /**
    * Function to implement RSET cmd
    */ private function rset() { $condition = ( is_resource($this->connection) && $this->send_data('RSET') && substr(trim($error = $this->get_data()), 0, 3) === '250' ); if ($condition) { return true; } $this->errors[] = 'RSET command failed, output: ' . trim(substr(trim($error),3)); return false; } /**
    * Function to implement QUIT cmd
    */ private function quit() { $condition = ( is_resource($this->connection) && $this->send_data('QUIT') && substr(trim($error = $this->get_data()), 0, 3) === '221' ); if ($condition) { fclose($this->connection); $this->status = SMTP_STATUS_NOT_CONNECTED; return true; } $this->errors[] = 'QUIT command failed, output: ' . trim(substr(trim($error),3)); return false; } /**
    * Function to implement AUTH cmd
    */ private function auth() { $condition = ( is_resource($this->connection) && $this->send_data('AUTH LOGIN') && substr(trim($error = $this->get_data()), 0, 3) === '334' && $this->send_data(base64_encode($this->user)) &&  substr(trim($error = $this->get_data()),0,3) === '334' && $this->send_data(base64_encode($this->pass)) &&  substr(trim($error = $this->get_data()),0,3) === '235' ); if ($condition) { $this->authenticated = true; return true; } $this->errors[] = 'AUTH command failed: ' . trim(substr(trim($error),3)); return false; } /**
    * Function that handles the MAIL FROM: cmd
    */ private function mail($from) { return ( $this->is_connected() && $this->send_data('MAIL FROM:<'.$from.'>') && substr(trim($this->get_data()), 0, 2) === '250' ); } /**
    * Function that handles the RCPT TO: cmd
    */ private function rcpt($to) { $condition = ( $this->is_connected() && $this->send_data('RCPT TO:<'.$to.'>') && substr(trim($error = $this->get_data()), 0, 2) === '25' ); if ($condition) { return true; } $this->errors[] = trim(substr(trim($error), 3)); return false; } /**
    * Function that sends the DATA cmd
    */ private function data() { $condition = ( $this->is_connected() && $this->send_data('DATA') && substr(trim($error = $this->get_data()), 0, 3) === '354' ); if ($condition) { return true; } $this->errors[] = trim(substr(trim($error), 3)); return false; } /**
    * Function to determine if this object
    * is connected to the server or not.
    */ private function is_connected() { return ( is_resource($this->connection) && ($this->status === SMTP_STATUS_CONNECTED) ); } /**
    * Function to send a bit of data
    */ private function send_data($data) { if (is_resource($this->connection)) { return fwrite($this->connection, $data.CRLF, strlen($data) + 2); } return false; } /**
    * Function to get data.
    */ private function get_data() { $return = ''; $line = ''; $loops = 0; if (is_resource($this->connection)) { while ((strpos($return, CRLF) === false || substr($line,3,1) !== ' ') && $loops < 100) { $line = fgets($this->connection, 512); $return .= $line; $loops++; } return $return; } return false; } /**
    * Sets a variable
    */ public function set($var, $value) { $this->$var = $value; return true; } /**
    * Function to return the errors array
    */ public function getErrors() { return $this->errors; } } ?>