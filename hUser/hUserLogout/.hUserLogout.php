<?php
  class hUserLogout extends hPlugin { private $hUserLogout; public function hConstructor() { $this->hUserLogout = $this->library('hUser/hUserLogout'); $this->hUserLogout->logout(); if (!isset($_GET['redirect'])) { $_GET['redirect'] = '/'; } $this->hFileDocument = $this->getTemplate('Logout'); $this->hFileJavaScript .= $this->getTemplate( 'Redirect', array( 'hUserLogoutRedirectPath' => $this->href($_GET['redirect']) ) ); } } ?>