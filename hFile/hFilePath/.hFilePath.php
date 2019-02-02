<?php
  class hFilePath extends hFrameworkVariables { private $modifiedTimes = array(); public function setPath($path) {                               if (is_numeric($path)) { $path = $this->getFilePathByFileId($path); } $baseName = basename($path); if (empty($baseName) || empty($path) || substr($path, -1) == '/') { $baseName = 'index.html'; $path .= $baseName; } $this->hFileBasePath = dirname($path); $this->hFileName = $baseName; $this->hFilePath = $path; $this->fire->setFilePath($path);  } protected function inspectFilePath() {        $this->getFileFromDatabase( $this->hFileBasePath, $this->hFileName ); if (!$this->hFileId) { $this->getFileFromDatabase( $this->hFilePath, $this->hDirectoryIndex('index.html') ); } if (!$this->hFileId) { $this->hDirectoryId = $this->getDirectoryId($this->hFilePath); if ($this->hDirectoryId) { $this->hFileDirectoryIndexPath = $this->hFilePath; $this->hFileDirectoryIndexId = $this->hDirectoryId; $this->setPath( $this->getFilePathByPlugin('hFile/hFileDirectoryIndex') ); $this->hDirectoryId = $this->getDirectoryId('/System/Applications/'); } else { $this->hFileStatusPath = $this->hFilePath; $this->hFileStatusCode = 404; $fileId = $this->getFileIdByFilePath('/System/Applications/Status Code.html'); $this->setPath( empty($fileId)? $this->getFilePathByPlugin('hFile/hFileStatusCode') : '/System/Applications/Status Code.html' ); $this->hDirectoryId = $this->getDirectoryId('/System/Applications/'); } $this->getFileFromDatabase($this->hFileBasePath, $this->hFileName); }  if ($this->beginsPath($this->hFilePath, '/Volumes')) { $this->hFileSystemDocumentIsVolume = true; $this->hFileSystemDocument = true; } else { $this->hFileSystemDocument = $this->hFileServerPath || file_exists($this->hFileSystemPath.$this->hFilePath); }  } protected function getFileFromDatabase($path, $name) {         if (empty($name)) { $name = $this->hDirectoryIndex('index.html'); } $file = $this->hDatabase->getAssociativeResults( $this->getTemplate( dirname(__FILE__).'/SQL/lookupFile.sql', array( 'hFileName' => $name, 'hDirectoryPath' => $path, 'hFrameworkSite' => $this->hFrameworkSite, 'hDirectoryPathIsRoot' => $path == '/' ) ) ); if (count($file)) { $this->setPath( $this->getConcatenatedPath($file['hDirectoryPath'], $name) ); $this->setVariables($file); }  } public function getFilePathByFileId($fileId, $path = false) {              $filePath = $this->hFiles->selectColumn( array('hFilePath'), (int) $fileId ); if (!empty($filePath)) { return $path? dirname($filePath) : $filePath; } return false; } public function getFileName($fileId = 0) {         if (empty($fileId)) { return $this->hFileName; } return $this->hFiles->selectColumn( 'hFileName', (int) $fileId ); } public function getShortPath($fileId) {            if (!is_numeric($fileId)) { $fileId = $this->getFileIdByFilePath($fileId); } return '/'.$fileId; } public function getFilePathByPlugin($plugin) { return $this->getFilePathByFileId( $this->getFileIdByPlugin($plugin) ); } public function getFileIdByPlugin($plugin) { return $this->hFiles->selectColumn( 'hFileId', array( 'hPlugin' => $plugin ) ); } public function getFileIdByFilePath($filePath) {        $directoryId = $this->getDirectoryId(dirname($filePath)); if ($directoryId > 0) { return (int) $this->hFiles->selectColumn( 'hFileId', array( 'hDirectoryId' => $directoryId, 'hFileName' => basename($filePath) ) ); } return 0; } public function getDirectoryId($directoryPath) {         if (strlen($directoryPath) > 1 && substr($directoryPath, -1, 1) == '/') { $directoryPath = substr($directoryPath, 0, -1); } return (int) $this->hDirectories->selectColumn( 'hDirectoryId', array( 'hDirectoryPath' => $directoryPath ) ); } public function getDirectoryPath($directoryId) {        return $this->hDirectories->selectColumn( 'hDirectoryPath', (int) $directoryId ); } public function getConcatenatedPath($path, $name) {          return ( $path. (substr($name, 0, 1) !== '/' && substr($path, -1, 1) !== '/' ? '/' : ''). ($name == '/'? '' : $name) ); } public function getIncludePath($path) {                     if (!file_exists($path)) { $endOfPath = $this->getEndOfPath($path, $this->hServerDocumentRoot); if (file_exists($endOfPath)) { return $endOfPath; } if ($this->beginsPath($endOfPath, '/Pictures')) { $endOfPath = $this->hFrameworkPluginRoot.$endOfPath; } $path = $this->hFrameworkPath.$endOfPath; if (!file_exists($path)) {    if ($this->beginsPath($endOfPath, '/Plugins') && $this->hFrameworkPluginRoot != '/Plugins') { $endOfPath = $this->getEndOfPath($endOfPath, '/Plugins'); } $path = $this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').$endOfPath; if (!file_exists($path)) { $path = $this->hFrameworkPath.$this->hFrameworkApplicationRoot('/Applications').$endOfPath; if (!file_exists($path)) { return false; } } } } return $path; } public function isListenerPath($uri) {            $path = dirname($uri); if ($path != '/') { $method = basename($uri); if (!empty($method)) { $listener = $this->getListenerPath($uri); $this->hFrameworkListenerPlugin = $listener; $this->hFrameworkListenerMethod = $method; $pluginPath = $path.'/'.basename($path).'.listener.php'; $fileExists = ( file_exists($this->hServerDocumentRoot.$pluginPath) || file_exists($this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').$pluginPath) ); if ($fileExists) { if (!$this->isListenerMethod($listener, $method)) { $this->registerPlugin($listener); } return $this->isListenerMethod($listener, $method); } } } return false; } public function isServicePath($uri) {            $path = dirname($uri); if ($path != '/') { $method = basename($uri); if (!empty($method)) { $service = $this->getServicePath($uri); $this->hFrameworkServicePlugin = $service; $this->hFrameworkServiceMethod = $method; $pluginPath = $path.'/'.basename($path).'.service.php'; $fileExists = ( file_exists($this->hServerDocumentRoot.$pluginPath) || file_exists($this->hFrameworkPath.$this->hFrameworkPluginRoot('/Plugins').$pluginPath) ); if ($fileExists) { if (!$this->isServiceMethod($service, $method)) { $this->registerPlugin($service); } return $this->isServiceMethod($service, $method); } } } return false; } public function getListenerPath($uri) {          $bits = explode('/', $uri); array_shift($bits); $method = array_pop($bits); $bits = array_reverse($bits); $bits = array_reverse($bits); $path = implode('/', $bits); $name = strstr($path, '/')? basename($path) : $path; return $path.'/'.$name.'.listener.php'; } public function getServicePath($uri) {          $bits = explode('/', $uri); array_shift($bits); $method = array_pop($bits); $bits = array_reverse($bits); $bits = array_reverse($bits); $path = implode('/', $bits); $name = strstr($path, '/')? basename($path) : $path; return $path.'/'.$name.'.service.php'; } public function beginsPath($pathHaystack, $pathNeedles) {                       if (!is_array($pathNeedles)) { return ( substr($pathHaystack, 0, strlen($pathNeedles.'/')) == $pathNeedles.'/' || $pathHaystack == $pathNeedles ); } else { foreach ($pathNeedles as $pathNeedle) { $condition = ( substr($pathHaystack, 0, strlen($pathNeedle.'/')) == $pathNeedle.'/' || $pathHaystack == $pathNeedle ); if ($condition) { return true; } } return false; } } public function inPath($path, $file) {        return $file == basename($path); } public function getEndOfPath($path, $beginning) {                  if ($path == $beginning) { return nil; } return substr( $path, strlen($beginning) ); } public function splitPath($path, $beginning) {       return $this->getEndOfPath( $path, $beginning ); } public function isDocumentRootPath($path) {         return $this->beginsPath( $path, $this->hServerDocumentRoot ); } public function isFrameworkRootPath($path) {          return $this->beginsPath( $path, $this->hFrameworkPath ); } public function getFileSystemPath() {          if ($this->hFileSystemDocumentIsVolume) { $volumeName = explode('/', $this->hFilePath); if (isset($volumeName[2])) { return ( $this->hFileSystemPath. $this->getEndOfPath($this->hFilePath, '/Volumes/'.$volumeName[2]) ); } else { $this->notice('Volume name is not in volume path '.$this->hFilePath.'.', __FILE__, __LINE__); } } if ($this->hFileSystemThumbnailPath) { return $this->hFileSystemThumbnailPath; } return $this->hFileSystemPath.$this->hFilePath; } public function expandDocumentIds($fileDocument) {                 return preg_replace_callback( '/\{[\$]?hFileId\:(\d*)\}/iUx', array( $this, 'getFileIdPath' ), $fileDocument ); } public function getFileIdPath($matches) {            if ($matches[1] > 0) { $path = $this->getFilePathByFileId($matches[1]); if ($this->hFileDocumentExpandIdWithLastModified(true) && file_exists($this->hFileSystemPath.$path)) { $path .= '?hFileLastModified='.filemtime($this->hFileSystemPath.$path); } return $this->cloakSitesPath($path); } else { return '#'; } return $matches[0]; } public function cloakSitesPath($path) {              if ($this->beginsPath($path, '/'.$this->hFrameworkSite)) { return $this->getEndOfPath($path, '/'.$this->hFrameworkSite); } if ($this->beginsPath($path, 'http://'.$this->hServerHost.'/'.$this->hFrameworkSite)) { return 'http://'.$this->hServerHost.$this->getEndOfPath($path, 'http://'.$this->hServerHost.'/'.$this->hFrameworkSite); } if ($this->beginsPath($path, 'https://'.$this->hServerHost.'/'.$this->hFrameworkSite)) { return 'https://'.$this->hServerHost.$this->getEndOfPath($path, 'https://'.$this->hServerHost.'/'.$this->hFrameworkSite); } return $path; } public function getExtension($path) {            if (!strstr($path, '.')) { return ''; } return strtolower(substr($path, strrpos($path, '.') + 1)); } public function insertSubExtension($path, $subExtension, $condition = true) {         if ($condition && !stristr($path, '.'.$subExtension.'.') && strstr($path, '.')) { $pathWithSubExtension = substr($path, 0, strrpos($path, '.') + 1).$subExtension.'.'.substr($path, strrpos($path, '.') + 1); if (file_exists($pathWithSubExtension)) { return $pathWithSubExtension; } else if (file_exists($this->getConcatenatedPath($this->hFrameworPath.'/Hot Toddy', $pathWithSubExtension))) { return $pathWithSubExtension; } else if (file_exists($this->getConcatenatedPath($this->hFrameworkPluginPath, $pathWithSubExtension))) { return $pathWithSubExtension; } else { return $path; } } return $path; } public function href($path = nil, $arguments = array(), $sessionId = true) {          if (empty($path)) { $path = $this->hFilePath; } if ($this->hPath != '/') { $path = $this->hPath.$path; } return( $this->cloakSitesPath($path). (count($arguments)? (strstr($path, '?')? '&' : '?') : ''). $this->getQueryString($arguments). ($sessionId? $this->getSessionId(!empty($arguments)) : '') ); } public function image($path) {         if ($this->hPath != '/') { $path = $this->hPath.$path; } if ($this->beginsPath($path, $this->hFrameworkPicturesRoot)) { $sourcePath = $this->hDirectoryTemplatePictures.$this->getEndOfPath($path, $this->hFrameworkPicturesRoot); return $path.'?hFileLastModified='.filemtime($sourcePath); } return $path; } public function getQueryString($arguments) {         if (!empty($arguments) && is_array($arguments)) { $parameters = array(); foreach ($arguments as $argument => $value) { $parameters[] = $argument.'='.$value; } return implode('&', $parameters); } } public function makeFrameworkPath($path, $encodeAmpersands = false) {         if ($this->hPath('/') != '/') { $path = $this->hPath.$path; } $http = false; $https = false; if ($this->beginsPath($path, 'http://'.$this->hServerHost)) { $http = true; $path = $this->getEndOfPath($path, 'http://'.$this->hServerHost); } if ($this->beginsPath($path, 'https://'.$this->hServerHost)) { $https = true; $path = $this->getEndOfPath($path, 'https://'.$this->hServerHost); } switch (true) { case $this->beginsPath($path, $this->hFrameworkLibraryRoot): { $serverPath = $this->hFrameworkLibraryPath.$this->getEndOfPath($path, $this->hFrameworkLibraryRoot); break; } case $this->beginsPath($path, ''): { $ext = $this->getExtension($path);   $serverPath = $this->getIncludePath($this->hServerDocumentRoot.$path);  break; } case $this->beginsPath($path, $this->hFrameworkPicturesRoot): { $serverPath = $this->hFrameworkPicturesPath.$this->getEndOfPath($path, $this->hFrameworkPicturesRoot); break; } case $this->beginsPath($path, '/images/icons'): { $serverPath = $this->hFrameworkPath.$this->getEndOfPath($path, '/images'); break; } default: { $ext = $this->getExtension($path); switch ($ext) { case 'jpg': case 'jpeg': case 'jpe': case 'gif': case 'png': case 'mp4': case 'swf': case 'flv': case 'pdf': case 'xls': case 'doc': { $serverPath = $this->hFileSystemPath.$path; if (!file_exists($serverPath)) { $serverPath = $this->hFileSystemPath.'/'.$this->hFrameworkSite.$path; } break; } } } }  if (!empty($serverPath) && file_exists($serverPath)) { $mTime = filemtime($serverPath); array_push($this->modifiedTimes, $mTime); $path .= '?hFileLastModified='.$mTime; } if ($this->beginsPath($path, '/'.$this->hFrameworkSite)) { $path = $this->getEndOfPath($path, '/'.$this->hFrameworkSite); } if ($encodeAmpersands && !strstr($path, '&amp;')) {  } if (!strstr($path, '%20') && !strstr($path, '+') && !strstr($path, 'mailto:')) { $matches = array(); $path = preg_replace_callback('/\{[\$]?hFileId\:(\d*)\}/iUx', array($this, 'getFileIdPath'), $path); if ($path == '#') { return '#'; } $path = hString::entitiesToUTF8($path, false); $fragment = ''; if (strstr($path, '#')) { $bits = explode('#', $path); $path = $bits[0]; $fragment = $bits[1]; } $queryString = ''; if (strstr($path, '?')) { $bits = explode('?', $path); $path = $bits[0]; $queryString = $bits[1]; } $pathBits = explode('/', $path); $fileName = array_pop($pathBits); foreach ($pathBits as $i => $directory) { $pathBits[$i] = urlencode($directory); } $path = implode('/', $pathBits).'/'.urlencode($fileName); if (!empty($queryString)) { $path .= '?'.$queryString; } if (!empty($fragment)) { $path .= '#'.$fragment; } } if ($http) { $path = 'http://'.$this->hServerHost.$path; } if ($https) { $path = 'https://'.$this->hServerHost.$path; } return str_replace(' ', '+', $path); } public function getModifiedTimes() {        return $this->modifiedTimes; } public function redirectIfSecureIsEnabled() {        if (!$this->isSSLEnabled()) { header('Location: '.$this->href('https://'.$this->hFrameworkSite.$this->hFilePath, $_GET)); exit; } } public function isFrameworkPath($path) {         if ($this->beginsPath($path, 'http://'.$this->hServerHost)) { return true; } if ($this->beginsPath($path, 'https://'.$this->hServerHost)) { return true; } if (substr($path, 0, 9) == '{hFileId:') { return true; }            $matches = array(); preg_match( '/^(.*\:\/\/|javascript\:|about\:|\/\/|\#)(.*)$/iU', $path, $matches ); return empty($matches[1]); } private function getSessionId($separator = true) {       return ($this->isLoggedIn()? ($this->hSessionIncludeURLId(false)? ($separator? '&' : '?') .session_name().'='.session_id() : '') : ''); } public function getPathMIME($path, &$mime) { if (file_exists($path) && empty($mime)) { $mime = $this->getMIMEType($path); } return $mime; } public function isImage($file, $mime = nil) {         $extension = $this->getExtension($file); if (!empty($mime) && substr($mime, 0, 6) == 'image/') { return true; } return in_array( $extension, array( 'png', 'jpg', 'jpeg', 'jpe', 'gif', 'ai', 'psd', 'bmp', 'tif', 'tiff', 'svg' ) ); } public function isAudio($file, $mime = nil) {         $this->getPathMIME($file, $mime); $extension = $this->getExtension($file); if (!empty($mime) && substr($mime, 0, 6) == 'audio/') { return true; } return in_array( $extension, array( 'mp3', 'wav', 'aac', 'aif', 'm4a', 'mpa', 'ogg', 'ra', 'wma' ) ); } public function isVideo($file, $mime = nil) {         $this->getPathMIME($file, $mime); $extension = $this->getExtension($file); if (!empty($mime) && substr($mime, 0, 6) == 'video/') { return true; } return in_array( $extension, array( 'mov', 'qt', 'movie', 'flv', 'f4v', 'f4p', 'swf', 'mpa', 'mpeg', 'mpg', 'mpe', 'mp2', 'mp4', 'm4v', 'mpv2', 'avi', 'wmv', 'asf', 'asx', 'asr', 'rm' ) ); } } ?>