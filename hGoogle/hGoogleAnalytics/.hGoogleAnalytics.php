<?php
  class hGoogleAnalytics extends hPlugin { public function hConstructor() { $this->hFileDocument .= $this->getTemplate( 'Analytics', array( 'hGoogleAnalytics' => $this->hGoogleAnalytics ) ); } } ?>