<?php
  class hFinderDesktop extends hPlugin { private $hDesktopApplication; public function hConstructor() { if ($this->isLoggedIn()) { if ($this->inGroup('root')) { $this->getFinder(); } else { $this->notAuthorized(); } } else { $this->notLoggedIn(); } } public function getFinder() { $this->hDesktopApplication = $this->library('hDesktopApplication');           $hUserPassword = $this->hUsers->selectColumn('hUserPassword', 1); $path = 'http://'.$this->hServerHost.'/Applications/Finder?'.$this->getQueryString( array( 'path' => '/', 'hUserAuthenticationToken' => '1,'.$hUserPassword, 'hDesktopApplication' => 1 ) ); $document = file_get_contents($path); $this->hDesktopApplication->makePackage( $document, 'hFinder', null  ); $this->hDesktopApplication->addDocumentToPackage( '/Applications/Finder/Desktop%20Login.html', 'index' );  $this->hFileDocument = $this->getTemplate( 'Desktop', array( 'hFinderDesktopPackagePath' => $this->hDesktopApplication->getPackagePath() ) ); } } ?>