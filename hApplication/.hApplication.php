<?php
  class hApplication extends hPlugin { public function hConstructor() { $this->plugin('hTemplate/hTemplateMinimum'); $this->hFileCSS = ''; $this->hFileJavaScript = ''; $this->hFrameworkToolboxLoad = false; $this->hFileTitlePrepend = ''; $this->hFileTitleAppend = ''; } } ?>