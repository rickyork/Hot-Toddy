<?php
  class hFilePHPCompressLibrary extends hPlugin { private $hFileUtilities; public function hConstructor() { } public function &all() {        $this->hFileUtilities = $this->library( 'hFile/hFileUtilities', array( 'autoScanEnabled' => true, 'fileTypes' => array( 'php' ) ) ); $files = $this->hFileUtilities->getFiles(); foreach ($files as $file) { $this->console('Tokenizing: '.$file); $this->tokenize($file); } return $this; } public function tokenize($file) { $cachePath = dirname($file).'/Cache'; $cachedFilePath = dirname($file).'/.'.basename($file); $cachedFileExists = file_exists($cachedFilePath); if ($cachedFileExists) { $cachedFileMTime = filemtime($cachedFilePath); } $sourceFileMTime = filemtime($file); if (!$cachedFileExists || $sourceFileMTime > $cachedFileMTime || $this->shellArgumentExists('force', '--force')) { $tokens = token_get_all( file_get_contents($file) ); $buffer = ''; while (list($i, $token) = each($tokens)) { if (is_array($token)) { $name = $token[0]; $source = $token[1]; switch ($name) { case T_COMMENT: { break; } case T_WHITESPACE: { $buffer .= ' '; break; } default: { $buffer .= $source; } } } else { $buffer .= $token; } } $this->console("Cached to: {$cachedFilePath}");  file_put_contents($cachedFilePath, $buffer); } } public function fixWhitespace($matches) { return ' '; } } ?>