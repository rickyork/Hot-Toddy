<?php
  class hFileDomain extends hPlugin { private $hJSON; public function hConstructor() {       if (!$this->hFrameworkConfigurationRoot) { $this->hFrameworkConfigurationRoot = '/Configuration'; } if (!$this->hFrameworkConfigurationPath) { $this->hFrameworkConfigurationPath = $this->hFrameworkPath.$this->hFrameworkConfigurationRoot; } $exists = false; $host = ''; if (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) { $host = str_replace('www.', '', $_SERVER['HTTP_HOST']); } if ($this->shellArgumentExists('site', '--site')) { $this->hFrameworkSite = $this->getShellArgumentValue('site', '--site'); $host = str_replace('www.', '', $this->hFrameworkSite); } if ($this->shellArgumentExists('hFrameworkSite', '--hFrameworkSite')) { $this->hFrameworkSite = $this->getShellArgumentValue( 'hFrameworkSite', '--hFrameworkSite' ); $host = str_replace( 'www.', '', $this->hFrameworkSite ); } if (!empty($host)) { $exists = $this->hFileDomains->selectExists( 'hFileDomainId', array( 'hFileDomain' => $host ) ); } if (!$exists || $this->hServerHostIsIP(false)) { $where['hFileDomainIsDefault'] = 1; } else { $where['hFileDomain'] = $host; } $domain = $this->hFileDomains->selectAssociative( array( 'hFileDomainId', 'hFileId', 'hFrameworkSite', 'hTemplateId' ), $where ); if (isset($_SERVER['REQUEST_URI'])) { @$uri = parse_url($_SERVER['REQUEST_URI']); } else { $uri['path'] = '/'; } if (!isset($uri['path'])) { $uri['path'] = '/'; } if (count($domain)) { $this->hFileDomainId = $domain['hFileDomainId']; if (!empty($domain['hFrameworkSite'])) { $this->hFrameworkSite = $domain['hFrameworkSite']; } $this->hTemplateId = (int) $domain['hTemplateId']; if ($uri['path'] == '/') { $this->setPath($this->getFilePathByFileId($domain['hFileId'])); } }  $path = $this->hFrameworkConfigurationPath.'/'.$this->hFrameworkSite.'.json'; if (file_exists($path)) {    if (!class_exists('hJSONLibrary')) { include_once $this->hServerDocumentRoot.'/hJSON/hJSON.library.php'; } $this->hJSON = new hJSONLibrary('/hJSON/hJSON.library.php');  $json = $this->hJSON->getJSON($path); $this->setVariable('hFrameworkSiteJSON', $json); $this->setVariables($json); }                       if (!$this->hFrameworkRoot) { $this->hFrameworkRoot = '/Hot Toddy'; }      if (!$this->hFrameworkApplicationRoot) { $this->hFrameworkApplicationRoot = '/Applications'; }      if (!$this->hFrameworkApplicationPath) { $this->hFrameworkApplicationPath = $this->hFrameworkPath.$this->hFrameworkApplicationRoot; }      if (!$this->hFrameworkTemporaryRoot) { $this->hFrameworkTemporaryRoot = '/Temporary'; }      if (!$this->hFrameworkTemporaryPath) { $this->hFrameworkTemporaryPath = $this->hFrameworkPath.$this->hFrameworkTemporaryRoot; }      if (!$this->hFrameworkCompiledRoot) { $this->hFrameworkCompiledRoot = '/Compiled'; }      if (!$this->hFrameworkCompiledPath) { $this->hFrameworkCompiledPath = $this->hFrameworkPath.$this->hFrameworkCompiledRoot; }      if (!$this->hFrameworkLibraryRoot) { $this->hFrameworkLibraryRoot = '/Library'; }      if (!$this->hFrameworkLibraryPath) { $this->hFrameworkLibraryPath = $this->hFrameworkPath.$this->hFrameworkLibraryRoot; }      if (!$this->hFrameworkIconRoot) { $this->hFrameworkIconRoot = '/Icons'; }      if (!$this->hFrameworkIconPath) { $this->hFrameworkIconPath = $this->hFrameworkPath.$this->hFrameworkIconRoot; }      if (!$this->hFrameworkPluginRoot) { $this->hFrameworkPluginRoot = '/Plugins'; }      if (!$this->hFrameworkPluginPath) { $this->hFrameworkPluginPath = $this->hFrameworkPath.$this->hFrameworkPluginRoot; }      if (!$this->hFrameworkFileSystemRoot) { $this->hFrameworkFileSystemRoot = '/HtFS'; }      if (!$this->hFrameworkFileSystemPath) { $this->hFrameworkFileSystemPath = $this->hFrameworkPath.$this->hFrameworkFileSystemRoot; }      if (!$this->hFileSystemPath) { $this->hFileSystemPath = $this->hFrameworkPath.$this->hFrameworkFileSystemRoot; }      if (!$this->hFrameworkPicturesRoot) { $this->hFrameworkPicturesRoot = '/Template/Pictures'; }      if (!$this->hFrameworkPicturesPath) { $this->hFrameworkPicturesPath = $this->hFrameworkPath.'/Pictures'; }      if (!$this->hDirectoryTemplatePictures) { $this->hDirectoryTemplatePictures = $this->hFrameworkPath.'/Pictures'; }      if (!$this->hFrameworkLogRoot) { $this->hFrameworkLogRoot = '/Log'; }      if (!$this->hFrameworkLogPath) { $this->hFrameworkLogPath = $this->hFrameworkPath.$this->hFrameworkLogRoot; }    } } ?>