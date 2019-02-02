<?php
  class hPluginTemplateLibrary extends hPlugin { private $options = array( 'hPluginIsPrivate', 'hPluginIsReusable', 'hPluginLibrary', 'hPluginListener', 'hPluginShell', 'hPluginDaemon', 'hPluginCSS', 'hPluginJS', 'hPluginIECSS', 'hPluginIE6CSS', 'hPluginIE7CSS', 'hPluginIE8CSS', 'hPluginHTML', 'hPluginMail', 'hPluginDocumentation' ); private $hPluginDatabase; public function hConstructor() { } public function scaffold($options) { foreach ($this->options as $option) { $options[$option] = isset($options[$option])? (int) $options[$option] : 0; $$option = $options[$option]; } $pluginName = $options['hPluginName']; $pluginPath = $options['hPluginPath']; $plugin = basename($pluginPath); $hasOther = ($pluginLibrary || $pluginListener || $pluginShell || $pluginDaemon); $pluginLibraries = isset($options['hPluginLibraries']) && is_array($options['hPluginLibraries'])? $options['hPluginLibraries'] : array(); $pluginPrivateLibraries = isset($options['hPluginPrivateLibraries']) && is_array($options['hPluginPrivateLibraries'])? $options['hPluginPrivateLibraries'] : array(); if (isset($pluginLibraries[0]) && empty($pluginLibraries[0])) { unset($pluginLibraries[0]); } if (isset($pluginPrivateLibraries[0]) && empty($pluginPrivateLibraries[0])) { unset($pluginPrivateLibraries[0]); } if (!empty($options['hFrameworkDeveloper'])) { $copyright = "© Copyright ".date('Y')." ".$options['hFrameworkDeveloper'].", All Rights Reserved"; } if (!empty($options['hPluginPlugin'])) { $this->console('Creating template for plugin: '.$plugin); $properties = ''; $statements = ''; if ($pluginLibrary) { $this->console('Adding properties and statements for plugin library: '.$plugin); $properties .= "    private \$".$plugin."\n"; $statements .= "        \$this->{$plugin} = \$this->library('{$pluginPath}');\n"; } $this->console('Assembling remaining properties and statements for plugin: '.$plugin.' (if any)'); if (count($pluginLibraries)) { $this->assemblePropertiesAndStatements($properties, $statements, $pluginLibraries, false); } if (!empty($properties)) { $properties = "// Libraries\n".$properties; } if (!empty($statements)) { $statements = "// Libraries\n".$statements; } if (count($pluginPrivateLibraries)) { $properties .= (!empty($properties)? '    ' : '')."// Private Libraries\n"; $statements .= (!empty($statements)? '        ' : '')."// Private Libraries\n"; } $this->assemblePropertiesAndStatements($properties, $statements, $pluginPrivateLibraries, true); switch (true) { case $pluginCSS && $pluginJS: { $this->console('Adding statements for CSS and JavaScript for: '.$plugin); $statements .= "\n        \$this->getPluginFiles();\n"; $this->console('Making a JavaScript template for: '.$plugin); $js = $this->getJavaScriptTemplate($plugin); break; } case $pluginCSS: { $this->console('Adding statement for CSS for: '.$plugin); $statements .= "\n        \$this->getPluginCSS();\n"; break; } case $pluginJS: { $this->console('Adding statement for JavaScript for: '.$plugin); $statements .= "\n        \$this->getPluginJavaScript();\n"; $this->console('Making a JavaScript template for: '.$plugin); $js = $this->getJavaScriptTemplate($plugin); break; } } if ($pluginIECSS) { $this->console('Adding CSS for Internet Explorer (non-version specific) for: '.$plugin); $statements .= "        \$this->getPluginCSS('ie');\n"; } if ($pluginIE6CSS) { $this->console('Adding CSS for Internet Explorer 6 for: '.$plugin); $statements .= "        \$this->getPluginCSS('ie6');\n"; } if ($pluginIE7CSS) { $this->console('Adding CSS for Internet Explorer 7 for: '.$plugin); $statements .= "        \$this->getPluginCSS('ie7');\n"; } if ($pluginIE8CSS) { $this->console('Adding CSS for Internet Explorer 8 for: '.$plugin); $statements .= "        \$this->getPluginCSS('ie8');\n"; } if ($options['hPluginExtends'] === 'hPlugin') { $options['hPluginExtends'] = 0; } if ($options['hPluginExtends'] === 'hFrameworkApplication') { $options['hPluginExtends'] = 1; } $this->console('Making a plugin template for: '.$plugin); $plugin = $this->getTemplate( 'hPlugin.php', array( 'hPluginName' => $pluginName, 'hPluginCopyright' => $copyright, 'hPluginClass' => $plugin, 'hPluginExtends' => !empty($options['hPluginExtends'])? 'hFrameworkApplication' : 'hPlugin', 'hPluginProperties' => $properties, 'hPluginStatements' => $statements, 'hPluginMethods' => '' ) ); } if ($pluginLibrary) { $this->console('Making a plugin library template for: '.$plugin); $library = $this->getTemplate( 'hPlugin.php', array( 'hPluginName' => $pluginName.' Library', 'hPluginCopyright' => $copyright, 'hPluginClass' => $plugin.'Library', 'hPluginExtends' => 'hPlugin', 'hPluginProperties' => '', 'hPluginStatements' => '', 'hPluginMethods' => '' ) ); } if ($pluginShell) { $this->console('Making a plugin shell template for: '.$plugin); $shell = $this->getTemplate( 'hPlugin.php', array( 'hPluginName' => $pluginName.' Shell', 'hPluginCopyright' => $copyright, 'hPluginClass' => $plugin.'Shell', 'hPluginExtends' => 'hPlugin', 'hPluginProperties' => '', 'hPluginStatements' => '', 'hPluginMethods' => '' ) ); } if ($pluginDaemon) { $this->console('Making a plugin daemon template for: '.$plugin); $daemon = $this->getTemplate( 'hPlugin.php', array( 'hPluginName' => $pluginName.' Daemon', 'hPluginClass' => $plugin.'Daemon', 'hPluginCopyright' => $copyright, 'hPluginExtends' => 'hPlugin', 'hPluginProperties' => '', 'hPluginStatements' => '', 'hPluginMethods' => '' ) ); } if ($pluginListener) { $methods = ''; $options['hListenerMethods']['hListenerMethod'] = array(); if (isset($options['hPluginMethods']) && is_array($options['hPluginMethods'])) { foreach ($options['hPluginMethods'] as $method) { $options['hListenerMethods']['hListenerMethod'][] = trim($method); $this->console('Adding listener method: '.trim($method).' for: '.$plugin); $methods .= "    \n". "    public function ".trim($method)."()\n". "    {\n". "    }\n"; } } $this->console('Making a plugin listener template for '.$plugin); $listener = $this->getTemplate( 'hPlugin.php', array( 'hPluginName' => $pluginName.' Listener', 'hPluginClass' => $plugin.'Listener', 'hPluginCopyright' => $copyright, 'hPluginExtends' => 'hListenerPlugin', 'hPluginProperties' => '', 'hPluginStatements' => '', 'hPluginMethods' => $methods ) ); } $pluginsFolder = $pluginIsPrivate? $this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins') : $this->hFrameworkPath.'/Hot Toddy'; if (file_exists($pluginsFolder)) { $pluginFolder = $pluginsFolder.'/'.$pluginPath; if (!file_exists($pluginFolder)) { $this->console('Creating plugin folder: '.$pluginFolder); `mkdir {$pluginFolder}`; } else { if ($this->hShellCLI(false)) { switch (true) { case $this->shellArgumentExists('-sf', '--scaffold-force'): { $this->console('Creating plugin folder: '.$pluginFolder); `mkdir {$pluginFolder}`; break; } case $this->shellArgumentExists('-sd', '--scaffold-delete'); { $this->console('Deleting plugin folder: '.$pluginFolder); `rm -rf {$pluginFolder}`; $this->console('Creating plugin folder: '.$pluginFolder); `mkdir {$pluginFolder}`; break; } default: { $this->console('Plugin folder: '.$pluginFolder.' not created, it already exists.'); } } } else { $this->console('Plugin folder: '.$pluginFolder.' not created, it already exists.'); } } if (file_exists($pluginFolder)) { $pluginPath = $pluginFolder.'/'.$plugin; $this->saveFile( $pluginPath, '.xml', $this->getTemplateXML('hPlugin', $options) ); $this->saveFile($pluginPath, '.php', $plugin); $this->saveFile($pluginPath, '.library.php', $library); $this->saveFile($pluginPath, '.shell.php', $shell); $this->saveFile($pluginPath, '.daemon.php', $daemon); $this->saveFile($pluginPath, '.listener.php', $listener); if ($pluginCSS) { $css = !empty($css)? $css : ' '; $this->saveFile($pluginPath, '.css', $css); } if ($pluginIECSS) { $css = ' '; $this->saveFile($pluginPath, '.ie.css', $css); } if ($pluginIE6CSS) { $css = ' '; $this->saveFile($pluginPath, '.ie6.css', $css); } if ($pluginIE7CSS) { $css = ' '; $this->saveFile($pluginPath, '.ie7.css', $css); } if ($pluginIE8CSS) { $css = ' '; $this->saveFile($pluginPath, '.ie8.css', $css); } if ($pluginJS) { $this->saveFile($pluginPath, '.js', $js); } if ($pluginMail) { $mail = $this->getTemplate('Mail'); $this->saveFile($pluginPath, '.mail.html', $mail); $mail = $this->getTemplateTXT('Mail'); $this->saveFile($pluginPath, '.mail.txt', $mail); } if ($pluginHTML && !file_exists("{$pluginFolder}/HTML")) { $this->console('Creating folder: '.$pluginFolder.'/HTML'); `mkdir {$pluginFolder}/HTML`; } if ($pluginDocumentation) { if (!file_exists("{$pluginFolder}/Documentation")) { $this->console('Creating folder: '.$pluginFolder.'/Documentation'); `mkdir {$pluginFolder}/Documentation`; } $docFile = $plugin; if (substr($plugin, 0, 1) === 'h') { $docFile = substr($plugin, 1); } if (!file_exists("{$pluginFolder}/Documentation/{$docFile}.html")) { $this->console("Creating file: {$pluginFolder}/Documentation/{$docFile}.html"); file_put_contents("{$pluginFolder}/Documentation/{$docFile}.html", ""); } } $this->hPluginDatabase = $this->database('hPlugin'); $this->hPluginDatabase->register(null, $plugin, $pluginPath); `chmod -R 775 {$pluginFolder}`; } else { $this->warning('Plugin folder: '.$pluginFolder.' could not be created.', __FILE__, __LINE__); } } else { $this->warning('Plugins folder: '.$pluginsFolder.' does not exist.', __FILE__, __LINE__); } } private function saveFile($path, $extension, &$file) { if (!empty($file)) { if ($this->hShellCLI(false)) { switch (true) { case $this->shellArgumentExists('-sf', '--scaffold-force'): { $this->console('Saving (and possibly overwriting) template to: '.$path.$extension); file_put_contents($path.$extension, $file); break; } default: { if (!file_exists($path.$extension)) { $this->console('Saving template (it doesn\'t exist) to: '.$path.$extension); file_put_contents($path.$extension, $file); } else { $this->console('File: '.$path.$extension.' was not saved, it already exists. Delete to reinstall.'); } } } } else { if (!file_exists($path.$extension)) { $this->console('Saving template (it doesn\'t exist) to: '.$path.$extension); file_put_contents($path.$extension, $file); } else { $this->console('File: '.$path.$extension.' was not saved, it already exists. Delete to reinstall.'); } } } } private function getJavaScriptTemplate($plugin) { if (substr($plugin, 0, 1) === 'h') { $plugin = substr($plugin, 1); } return $this->getTemplate( 'hPlugin.js', array( 'hPluginName' => $plugin ) ); } private function assemblePropertiesAndStatements(&$properties, &$statements, $plugins, $pluginIsPrivate) { } } ?>