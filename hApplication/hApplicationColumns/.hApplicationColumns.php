<?php
  class hApplicationColumns extends hPlugin { private $hApplicationColumns; public function hConstructor() { $this->hApplicationColumns = $this->library('hApplication/hApplicationColumns'); $this->hFileDocument = $this->hApplicationColumns->get(null, null); } } ?>