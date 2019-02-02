<?php
  class hCodeStyle extends hPlugin { public function hConstructor() { $this->getPluginJavaScript('template'); $this->hFileDocument = $this->getTemplate('Test'); } } ?>