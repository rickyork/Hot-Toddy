<?php
  class hBlog extends hPlugin { private $hBlog; public function hConstructor() { $this->hBlog = $this->library('hBlog'); $this->hFileDocument = $this->hBlog->getBlog(); } } ?>