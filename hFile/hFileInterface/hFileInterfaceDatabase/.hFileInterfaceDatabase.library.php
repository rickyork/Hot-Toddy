<?php
  class hFileInterfaceDatabaseLibrary extends hFileInterface { private $hSubscription; private $hFileDatabase; private $hFileSpotlightMD; private $hFileConvert; private $hPhotoDatabase; private $hMovieDatabase; private $hSmartyPants; public $filterPaths = array(); public $fileTypes = array(); public $methodsWereAdded = false; public function shouldBeCalled() {            return !$this->isServerPath && !$this->isCategory; } public function getMethods() {                return array( 'shouldBeCalled', 'getMIMEType', 'getTitle', 'import', 'upload', 'getMetaData', 'deleteMetaData', 'getSize', 'getDescription', 'getLastModified', 'getCreated', 'hasChildren', 'getDirectories', 'getLabel', 'setLabel', 'getProperty', 'setProperty', 'getFiles', 'search', 'searchByPreset', 'rename', 'delete', 'newFolder', 'newDirectory', 'makePath', 'move', 'copy', 'touch' ); } public function getMIMEType() {                if ($this->isFolder) { return $this->getDirectoryPseudoMIME($this->filePath); } else { $mime = $this->hFileMIME('', $this->fileId); if (empty($mime)) { $this->hDatabase->setDefaultResult('text/plain'); return $this->hFileIcons->selectColumn( 'hFileMIME', array( 'hFileExtension' => $this->hFileExtension ) ); } return $mime; } } public function getTitle() {         return $this->isDirectory? '' : $this->getFileTitle($this->hFileSymbolicLink? $this->hFileSymbolicLink : $this->fileId); } public function import($files) {                                                                           $basePath = $this->getConcatenatedPath($this->hFrameworkFileSystemPath, $this->filePath); $this->makeServerPath($basePath); $this->hFileDatabase = $this->database('hFile'); $this->hFileConvert = $this->library('hFile/hFileConvert'); $this->hSubscription = $this->library('hSubscription'); $this->hSmartyPants = $this->library('hSmartyPants'); $allowDuplicates = $this->hFileSystemAllowDuplicates(false);  foreach ($files as $index => $file) { if (!isset($file['hDirectoryId'])) { $file['hDirectoryId'] = $this->directoryId; }  $file['hFileName'] = str_replace(array("/", '\\'), '', $file['hFileName']); $file['hFileName'] = hString::entitiesToUTF8($file['hFileName'], false);  $file['hFileName'] = $this->hSmartyPants->get($file['hFileName']); $file['hFileName'] = hString::escapeAndEncode($file['hFileName']); $this->console("Imported directory path is: {$this->filePath}"); $savePath = $this->getConcatenatedPath($this->filePath, $file['hFileName']); $fileSystemPath = $this->getConcatenatedPath($basePath, $file['hFileName']); $this->console("Importing file to '{$savePath}'"); if ($this->exists($savePath)) { $this->query($savePath); if ($file['hFileReplace']) { $file['hFileMD5Checksum'] = md5_file($file['hFileTempPath']); if (!$allowDuplicates && $this->duplicateFileExists($file['hFileMD5Checksum'])) { $this->hFileDuplicatePath = $this->getDuplicatePath($file['hFileMD5Checksum']); if ($savePath != $this->hFileDuplicatePath) { return -32; } } $this->deleteMetaData($this->fileId); $GLOBALS['hFramework']->move($file['hFileTempPath'], $fileSystemPath); if (!file_exists($fileSystemPath)) { return -31; } $file['hFileDocument'] = hString::escapeAndEncode(trim($this->hFileConvert->getPlainText($fileSystemPath))); unset($file['hFileTempPath']); $this->hFileDatabase->save($file); if ($this->isImage($file['hFileName'], $file['hFileMIME'])) { $this->hPhotoDatabase = $this->database('hPhoto'); $this->hPhotoDatabase->addPhoto($this->fileId); } if ($this->isVideo($file['hFileName'], $file['hFileMIME'])) { $this->hMovieDatabase = $this->database('hMovie'); $this->hMovieDatabase->addMovie($this->fileId); } $this->hFiles->activity('Replaced File: '.$this->getFilePathByFileId($this->fileId)); } else { return -3; } } else { $file['hFileMD5Checksum'] = md5_file($file['hFileTempPath']); if (!$allowDuplicates && $this->duplicateFileExists($file['hFileMD5Checksum'])) { $this->hFileDuplicatePath = $this->getDuplicatePath($file['hFileMD5Checksum']); if ($savePath != $this->hFileDuplicatePath) { return -32; } } $file['hFileId'] = 0; $GLOBALS['hFramework']->move($file['hFileTempPath'], $fileSystemPath); if (!file_exists($fileSystemPath)) { return -31; } $file['hFileDocument'] = hString::escapeAndEncode(trim($this->hFileConvert->getPlainText($fileSystemPath))); unset($file['hFileTempPath']); $file['hFileId'] = $this->hFileDatabase->save($file); if ($this->isImage($file['hFileName'], $file['hFileMIME'])) { $this->hPhotoDatabase = $this->database('hPhoto'); $this->hPhotoDatabase->addPhoto($file['hFileId']); } if ($this->isVideo($file['hFileName'], $file['hFileMIME'])) { $this->hMovieDatabase = $this->database('hMovie'); $this->hMovieDatabase->addMovie($file['hFileId']); } $this->hFiles->activity('Uploaded File: '.$this->getFilePathByFileId($file['hFileId'])); } } return 1; } public function upload($files) {        return $this->import($files); } public function getMetaData() {            $data = array(); if ($this->isDirectory) { $directory = $this->getDirectories(true, false); $data = array( 'DisplayName' => $this->filePath == '/'? $this->hFinderDiskName($this->hServerHost) : $this->fileName, 'ContentCreationDate' => $directory['hFileCreated'], 'ContentModifiedDate' => $directory['hFileLastModified'], 'FSInvisible' => substr($this->fileName, 0, 1) == '.'? 1 : 0, 'FSName' => $this->filePath == '/'? $this->hFinderDiskName($this->hServerHost) : $this->fileName, 'FSSize' => !empty($directory['hFileSize'])? $directory['hFileSize'] : '--', 'Kind' => 'Folder', 'kMDItemFSLabel' => $directory['hFileLabel'] ); $data = array_merge( $data, $this->hDirectoryProperties->selectAssociative( array( 'hDirectoryIsApplication', 'hDirectoryLabel', 'hFileIconId' ), $this->directoryId ) ); } else { if (file_exists($this->hFrameworkFileSystemPath.$this->filePath)) { if ($this->hOS == 'Darwin') { $this->hFileSpotlightMD = $this->library('hFile/hFileSpotlight/hFileSpotlightMD'); $data = $this->hFileSpotlightMD->get($this->hFrameworkFileSystemPath.$this->filePath); } else { } } else { $file = $this->getFiles(true, true, false); $data = array( 'DisplayName' => $this->fileName, 'ContentCreationDate' => $file['hFileCreated'], 'ContentModifiedDate' => $file['hFileLastModified'], 'FSInvisible' => substr($this->fileName, 0, 1) == '.'? 1 : 0, 'FSName' => $this->fileName, 'FSSize' => !empty($file['hFileSize'])? $this->bytes($file['hFileSize']) : '--', 'Kind' => 'Website Document', 'kMDItemFSLabel' => $file['hFileLabel'] ); } $data['ContentAccessedDate'] = $this->hDatabase->selectColumn( 'hFileLastAccessed', 'hFileStatistics', $this->fileId ); $data = array_merge( $data, $this->hFileProperties->selectAssociative( array( 'hFileIconId', 'hFileDownload', 'hFileIsSystem', 'hFileLabel' ), $this->fileId ) ); } return $data; } public function getSize() {         if ($this->isDirectory) { return 0;                        } else { if (file_exists($this->hFrameworkFileSystemPath.$this->filePath)) { return $this->bytes( filesize($this->hFrameworkFileSystemPath.$this->filePath) ); } else { return $this->bytes( strlen( $this->hFileDocuments->selectColumn( 'hFileDocument', array( 'hFileId' => (int) $this->fileId ) ) ) ); } } } public function getDescription() {            if ($this->isDirectory) { $description = 'Folder containing '. $this->hDirectories->selectCount( 'hDirectoryId', array( 'hDirectoryParentId' => $this->directoryId ) ).' folder(s) and '. $this->hFiles->selectCount( 'hFileId', array( 'hDirectoryId' => $this->directoryId ) ).' file(s)'; } else { $description = $this->getFileDescription($this->hFileSymbolicLink? $this->hFileSymbolicLink : $this->fileId); if (empty($description) && file_exists($this->hFrameworkFileSystemPath.$this->filePath)) { $description = $this->command('file -b '.escapeshellarg($this->hFrameworkFileSystemPath.$this->filePath)); } } return $description; } public function getLastModified() {          if ($this->isDirectory) { return $this->hDirectories->selectColumn( 'hDirectoryLastModified', (int) $this->directoryId ); } else { return $this->hFiles->selectColumn( 'hFileLastModified', (int) $this->fileId ); } } public function getCreated() {          if ($this->isDirectory) { return $this->hDirectories->selectColumn( 'hDirectoryCreated', (int) $this->directoryId ); } else { return $this->hFiles->selectColumn( 'hFileCreated', (int) $this->fileId ); } } public function hasChildren($countFiles = false) {           if ($this->isDirectory) { $hasDirectories = $this->hDirectories->selectExists( 'hDirectoryId', array( 'hDirectoryParentId' => (int) $this->directoryId ) ); $hasFiles = $this->hFiles->selectExists( 'hFileId', array( 'hDirectoryId' => (int) $this->directoryId ) ); return ($hasDirectories || ($countFiles && $hasFiles)); } else { return $this->hFiles->selectExists( 'hFileId', array( 'hFileParentId' => (int) $this->fileId ) ); } } public function getDirectories($checkPermissions = true, $queryParent = true) {                                                                       $directories = array(); $query = $this->hDatabase->getResults( $this->getTemplateSQL( dirname(__FILE__).'/SQL/getDirectories', array_merge( $this->getPermissionsVariablesForTemplate($checkPermissions, false), array( 'directoryId' => (int) $this->directoryId, 'queryParent' => $queryParent ) ) ) ); foreach ($query as $data) { $name = basename($data['hDirectoryPath']); if (!in_array($data['hDirectoryPath'], $this->filterPaths)) { if ($data['hDirectoryPath'] == '/Categories') { $data['hDirectoryCount'] = $this->hCategories->selectCount('hCategoryId', 0); $data['hFileCount'] = $this->hCategoryFiles->selectCount('hCategoryId', 0); } $directories[$name] = array( 'hFileInterfaceObjectId' => $data['hDirectoryId'], 'hFileName' => $name, 'hDirectoryName' => $name, 'hFilePath' => $data['hDirectoryPath'], 'hDirectoryPath' => $data['hDirectoryPath'], 'hFileIsServer' => false, 'hDirectoryId' => (int) $data['hDirectoryId'], 'hDirectoryIsApplication' => (bool) $data['hDirectoryIsApplication'], 'hFileIconId' => (int) $data['hFileIconId'], 'hFileCreated' => (int) $data['hDirectoryCreated'], 'hFileLastModified' => (int) $data['hDirectoryLastModified'], 'hFileMIME' => 'directory', 'hFileLabel' => $data['hDirectoryLabel'], 'hFileSize' => 0, 'hDirectoryCount' => $data['hDirectoryCount'], 'hFileCount' => $data['hFileCount'], 'hCategoryFileSortIndex' => 0 ); } } return $queryParent? $directories : array_pop($directories); } public function getLabel() {         return $this->getProperty('h'.($this->isDirectory? 'Directory' : 'File').'Label'); } public function setLabel($label) {         $this->setProperty('h'.($this->isDirectory? 'Directory' : 'File').'Label', $label); } public function getProperty($field) {                                                         if ($this->isDirectory) { return $this->hDirectoryProperties->selectColumn( $field, array( 'hDirectoryId' => (int) $this->directoryId ) ); } else { return $this->hFileProperties->selectColumn( $field, array( 'hFileId' => (int) $this->fileId ) ); } } public function setProperty($field, $value) {            $columns[$field] = $value; if ($this->isDirectory) { $this->hDirectories->modifyResource($this->directoryId); $columns['hDirectoryId'] = (int) $this->directoryId; $this->hDirectoryProperties->save($columns); } else { $this->hFiles->modifyResource($this->fileId); $columns['hFileId'] = (int) $this->fileId; $this->hFileProperties->save($columns); } } public function getFiles($includeMetaData = true, $checkPermissions = true, $queryByDirectory = true) {                                                                                     $files = array(); if ($this->isDirectory || !$queryByDirectory) { $orderBy = $this->hFileOrderBy('hFileName'); if (!is_array($orderBy) && strstr($orderBy, '`') || strstr($orderBy, '(')) { switch (true) { case strstr($orderBy, '`hFileName`'): { $orderBy = 'hFileName'; break; } case strstr($orderBy, '`hFileSortIndex`'): { $orderBy = 'hFileSortIndex'; break; } case stristr($orderBy, 'RAND'): { $orderBy = 'RAND()'; break; } } } $sql = $this->getTemplateSQL( dirname(__FILE__).'/SQL/getFiles', array_merge( $this->getPermissionsVariablesForTemplate($checkPermissions, false), array( 'fileId' => (int) $this->fileId, 'directoryId' => (int) $this->directoryId, 'queryByDirectory' => $queryByDirectory, 'orderBy' => $orderBy, 'limit' => $this->hFileLimit(nil), 'sortRandom' => $orderBy == 'RAND()', 'categoryFileSortIndex' => 0, ) ) ); $query = $this->hDatabase->getResults($sql); $files = $this->getFileResults($query); } return $queryByDirectory? $files : array_pop($files); } public function search($searchTerms, $checkPermissions = true) {          $sql = $this->getTemplateSQL( dirname(__FILE__).'/SQL/search', array_merge( $this->getPermissionsVariablesForTemplate($checkPermissions, false), array(   'searchTerms' => str_replace('&quot;', '"', $searchTerms) ) ) ); return $this->getFileResults( $this->hDatabase->getResults($sql) ); } public function searchByPreset($preset, $time = nil, $checkPermissions = true) {                                         $startTime = 0; $stopTime = 0; $sql = ''; if (!is_array($preset)) {  switch ($preset) { case 'Pictures': case 'Images': { $sql = "`hFileProperties`.`hFileMIME` LIKE 'image/%'"; break; } case 'Video': case 'Movies': { $sql = "`hFileProperties`.`hFileMIME` LIKE 'video/%'"; break; } case 'Music': case 'Audio': { $sql = "`hFileProperties`.`hFileMIME` LIKE 'audio/%'"; break; } case 'Documents': { $mimes = array( 'application/pdf', 'application/msword', 'applicaiton/mspowerpoint', 'application/vnd.ms-powerpoint', 'applicatoin/msexcel', 'application/x-excel', 'application/excel', 'application/x-msexcel', 'application/vnd.ms-excel' ); $sql = array(); foreach ($mimes as $mime) { $sql[] = "`hFileProperties`.`hFileMIME` = '{$mime}'"; } break; } case 'Time': {  switch (trim($time)) { case 'Today': { $startTime = strtotime('-1 day'); break; } case 'Yesterday': { $startTime = strtotime('-2 days'); $stopTime = strtotime('-1 day'); break; } case 'Past Week': { $startTime = strtotime('-7 days'); $stopTime = strtotime('-5 days'); break; } } break; } } } else { $sql = array(); foreach ($preset as $mime) { $sql[] = "`hFileProperties`.`hFileMIME` = '{$mime}'"; } } $sql = $this->getTemplateSQL( dirname(__FILE__).'/SQL/searchByPreset', array_merge( $this->getPermissionsVariablesForTemplate($checkPermissions, false), array(   'fileMIME' => is_array($sql)? implode(' OR ', $sql) : $sql, 'startTime' => $startTime, 'stopTime' => $stopTime ) ) ); return $this->getFileResults( $this->hDatabase->getResults( $sql ) ); } public function rename($newName, $replace = false) {              $newName = str_replace("/", '', $newName); $newPath = $this->getConcatenatedPath($this->parentDirectoryPath, $newName); $this->hFiles->activity('Renamed File: '.$this->filePath.' to '.$newName);     if ($this->exists($newPath) && strToLower($newPath) != strToLower($this->filePath)) { if (!$replace) { return -3; } else { $this->hFile->delete($newPath); } } if ($this->isDirectory) { if (file_exists($this->hFrameworkFileSystemPath.$this->filePath)) {   $GLOBALS['hFramework']->rename( $this->hFrameworkFileSystemPath.$this->filePath, $this->hFrameworkFileSystemPath.$newPath ); } $query = $this->hDirectories->select( array( 'hDirectoryId', 'hDirectoryPath' ), array( 'hDirectoryPath' => array( array('=', $this->filePath), array('LIKE', $this->filePath.'/%') ) ), 'OR', 'hDirectoryPath' ); foreach ($query as $data) { if ($data['hDirectoryPath'] == $this->filePath) { $path = $newPath; } else { $path = $this->getConcatenatedPath( $newPath, substr( $data['hDirectoryPath'], strlen($this->filePath) ) ); }  $this->hDirectories->update( array( 'hDirectoryPath' => $path ), $data['hDirectoryId'] ); $this->hDirectories->modifyResource($data['hDirectoryId']); } } else { $this->hFiles->update( array( 'hFileName' => $newName, 'hFileLastModifiedBy' => isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 1 ), (int) $this->fileId ); $this->hFiles->modifyResource($this->fileId); $thumbnailPath = $this->hFile->getThumbnailPath($this->filePath); if (file_exists($thumbnailPath)) { $this->rm($thumbnailPath); } if (file_exists($this->hFrameworkFileSystemPath.$this->filePath)) { $GLOBALS['hFramework']->rename( $this->hFrameworkFileSystemPath.$this->filePath, $this->hFrameworkFileSystemPath.$newPath ); } } $this->unsetPath($this->filePath); $this->unsetPath($newPath); return 1; } public function deleteMetaData($fileId) {                 $this->hDatabase->delete( array( 'hFileVariables', 'hFileStatistics', 'hFileUserStatistics', 'hListFiles', 'hCalendarFiles', 'hCategoryFiles', 'hFilePasswords', 'hFileAliases', 'hFileComments', 'hFileDomains', 'hFileDocuments', 'hFileHeaders', 'hFileProperties',  'hFileUserStatistics', 'hFilePathWildcards' ), 'hFileId', $fileId ); $this->hListFiles->delete('hListFileId', $fileId);  $forumQuery = $this->hForums->selectQuery( 'hForumId', array( 'hFileId' => (int) $fileId ) ); if ($this->hDatabase->resultsExist($forumQuery)) { while ($forumData = $this->hDatabase->getAssociativeResults($forumQuery)) { $topicQuery = $this->hForumTopics->selectQuery( 'hForumTopicId', array( 'hForumId' => (int) $forumData['hForumId'] ) ); if ($this->hDatabase->resultsExist($topicQuery)) { while ($topicData = $this->hDatabase->getAssociativeResults($topicQuery)) { $this->hSubscription->delete( 'hForumTopics', $topicData['hForumTopicId'] ); $postQuery = $this->hForumPosts->selectQuery( 'hForumPostId', array( 'hForumTopicId' => $topicData['hForumTopicId'] ) ); while ($postData = $this->hDatabase->getAssociativeResults($postQuery)) { $this->hSubscription->delete( 'hForumPosts', $postData['hForumPostId'] ); } $this->hDatabase->closeResults($postQuery); $this->hForumPosts->delete( 'hForumTopicId', $topicData['hForumTopicId'] ); } $this->hDatabase->closeResults($topicQuery); } } $this->hDatabase->closeResults($forumQuery); } $this->hSubscription->delete( 'hForums', $fileId ); $this->hForums->delete( 'hFileId', $fileId ); $this->deleteCachedDocuments($fileId); return true; } public function delete() {         $this->hSubscription = $this->library('hSubscription'); $this->hFiles->activity('Deleted File: '.$this->filePath); $files = array(); if ($this->isDirectory) { $directories = $this->getAllDirectoriesInPath($this->filePath); $directoryCounter = 0; foreach ($directories as $name => $data) { $directory = $this->hFile->getFiles($data['hFilePath']); if (count($directory)) { $files[$directoryCounter] = $directory; $directoryCounter++; } } } else { $files[0] = array( $this->fileName => array( 'hFileId' => $this->fileId, 'hFilePath' => $this->filePath ) ); } if (isset($files) && is_array($files)) {  foreach ($files as $file) { foreach ($file as $name => $data) { $this->deleteMetaData($data['hFileId']);  $this->hFiles->deletePermissions($data['hFileId']); if (file_exists($this->hFrameworkFileSystemPath.$data['hFilePath'])) { $this->rm($this->hFrameworkFileSystemPath.$data['hFilePath'], true); } $thumbnailPath = $this->hFile->getThumbnailPath($data['hFilePath']); if (file_exists($thumbnailPath)) { $this->rm($thumbnailPath); }  $this->hFiles->delete('hFileId', $data['hFileId']); } } $this->hFiles->modifyResource(); } if ($this->isDirectory) { foreach ($directories as $name => $data) { $this->hDirectories->deletePermissions($data['hDirectoryId']); $this->hTemplateDirectories->delete( 'hDirectoryId', (int) $data['hDirectoryId'] ); if ($data['hFilePath'] != '/') { if (file_exists($this->hFrameworkFileSystemPath.$data['hFilePath'])) { $this->rm($this->hFrameworkFileSystemPath.$data['hFilePath']); } } $this->hDirectories->delete( 'hDirectoryId', (int) $data['hDirectoryId'] ); } $this->hDirectories->modifyResource(); } $this->unsetPath($this->filePath); return true; } public function newFolder($newFolderName, $userId = 0) {         $permissions = 0; if (!empty($userId)) { $permissions['hUserId'] = (int) $userId; } return $this->hFile->makePath( $this->getConcatenatedPath( $this->filePath, $newFolderName ), $permissions ); } public function newDirectory($newDirectoryName, $userId = 0) {         $permissions = array(); if (!empty($userId)) { $permissions['hUserId'] = (int) $userId; } return $this->hFile->makePath( $this->getConcatenatedPath( $this->filePath, $newDirectoryName ), $permissions ); } public function makePath($permissions = array()) {                                      $directoryId = 0; $path = $this->filePath; $folders = explode('/', $path); $currentPath = '/'; foreach ($folders as $folder) { if (!empty($folder)) { $currentPath .= ($currentPath == '/')? $folder : '/'.$folder; if (!$this->exists($currentPath)) { $parentDirectoryId = $this->getDirectoryId(dirname($currentPath)); $directoryId = $this->hDirectories->insert( array( 'hDirectoryId' => nil, 'hDirectoryParentId' => (int) $parentDirectoryId, 'hUserId' => isset($permissions['hUserId'])? (int) $permissions['hUserId'] : 1, 'hDirectoryPath' => $currentPath, 'hDirectoryCreated' => time(), 'hDirectoryLastModified' => 0 ) ); if (!empty($parentDirectoryId) && (empty($permissions) || is_array($permissions) && !count($permissions) || !is_array($permissions))) { $this->hDirectories->inheritPermissionsFrom($parentDirectoryId); $this->hDirectories->savePermissions($directoryId); } else if (!empty($directoryId)) { if (isset($permissions['hUserPermissionsGroups']) && is_array($permissions['hUserPermissionsGroups'])) { foreach ($permissions['hUserPermissionsGroups'] as $group => $level) { $this->hDirectories->addGroup($group, $level); } } $this->hDirectories->savePermissions( $directoryId, isset($permissions['hUserPermissionsOwner'])? $permissions['hUserPermissionsOwner'] : 'rw', isset($permissions['hUserPermissionsWorld'])? $permissions['hUserPermissionsWorld'] : '' ); } } } } $this->hDirectories->modifyResource();  return $directoryId; } public function move($sourcePath, $replace = false) {                                                                if (!$this->isDirectory) { return -20;  } else { $destination = $this->filePath; $directoryId = $this->directoryId; $path = $this->getConcatenatedPath( $this->filePath, basename($sourcePath) ); $exists = $this->exists($path); if ($exists && !$replace) { return -3;  } if (!$exists || $exists && $replace) { $this->hFile = $this->library('hFile'); $this->hFile->query($sourcePath); if ($exists) { $this->hFile->delete($path); } if ($this->isDirectory) { if ($destination == $this->filePath) { return -18; }  if ($this->beginsPath($destination, $this->filePath)) { return -21; } $this->hDirectories->update( array( 'hDirectoryParentId' => (int) $directoryId ), array( 'hDirectoryPath' => $this->filePath ) ); $query = $this->hDirectories->selectQuery( array( 'hDirectoryId', 'hDirectoryPath' ), array( 'hDirectoryPath' => array( array( '=', $this->filePath ), array( 'LIKE', $this->filePath.'/%' ) ) ), 'OR', 'hDirectoryPath' );    $sliceStart = strrpos($this->filePath, '/'); for ($pathCounter = 0; $data = $this->hDatabase->getAssociativeResults($query); $pathCounter++) { $slice = substr($data['hDirectoryPath'], $sliceStart); $newPath = $this->getConcatenatedPath($destination, $slice); $newPath = str_replace('//', '/', $newPath); if (!$pathCounter && file_exists($this->hFrameworkFileSystemPath.$sourcePath)) { $destinationDirectory = dirname($this->hFrameworkFileSystemPath.$newPath); if (!file_exists($destinationDirectory)) { $this->hFile->makeServerPath($destinationDirectory); } $GLOBALS['hFramework']->rename( $this->hFrameworkFileSystemPath.$sourcePath, $this->hFrameworkFileSystemPath.$newPath ); } $this->hDirectories->update( array( 'hDirectoryPath' => $newPath ), (int) $data['hDirectoryId'] ); $this->hDirectories->modifyResource($data['hDirectoryId']); } $this->hDatabase->closeResults($query); } else {  $this->hFiles->update( array( 'hDirectoryId' => (int) $directoryId, 'hFileLastModifiedBy' => isset($_SESSION['hUserId'])? (int) $_SESSION['hUserId'] : 1 ), (int) $this->fileId ); $this->hFiles->modifyResource($data['hFileId']); $thumbnailPath = $this->hFile->getThumbnailPath($this->filePath); if (file_exists($thumbnailPath)) { $this->rm($thumbnailPath, true); } if (file_exists($this->hFrameworkFileSystemPath.$this->filePath)) { $newPath = $this->getFilePathByFileId($this->fileId); $destinationDirectory = dirname($this->hFrameworkFileSystemPath.$newPath); if (!file_exists($destinationDirectory)) { $this->hFile->makeServerPath($destinationDirectory); } $GLOBALS['hFramework']->rename( $this->hFrameworkFileSystemPath.$this->filePath, $this->hFrameworkFileSystemPath.$newPath ); } } } } $this->unsetPath($sourcePath); $this->unsetPath($this->filePath); return 1; } public function copy($destination = nil) {           if (!$this->isDirectory) { return 0; } $this->hFileDatabase = $this->database('hFile'); $file = $this->hFiles->selectAssociative( array( 'hLanguageId', 'hDirectoryId', 'hUserId', 'hFileParentId', 'hFileName', 'hPlugin', 'hFileSortIndex' ), $this->fileId ); $file['hFileId'] = 0; $this->hFiles->modifyResource(); $this->hDirectories->modifyResource($file['hDirectoryId']); $directoryPath = $this->getDirectoryPath($file['hDirectoryId']);      if (empty($destination)) { $fileExtension = $this->getExtension($file['hFileName']); $name = $file['hFileName']; $pathCounter = 0; do { $file['hFileName'] = substr_replace( $name, ' Copy'.($pathCounter > 0? " {$pathCounter}" : '').'.'.$fileExtension, -(strlen($fileExtension) + 1) ); $path = $this->getConcatenatedPath($directoryPath, $file['hFileName']); $pathCounter++; } while($this->exists($path)); } else { $file['hDirectoryId'] = $this->getDirectoryId(dirname($destination)); $file['hFileName'] = basename($destination); $path = $destination; } $fileDocument = $this->hFileDocuments->selectAssociative( array( 'hFileDescription', 'hFileKeywords', 'hFileTitle', 'hFileDocument' ), array( 'hFileId' => $this->fileId ) ); $fileHeaders = $this->hFileHeaders->selectAssociative( array( 'hFileCSS', 'hFileJavaScript' ), $this->fileId ); $fileProperties = $this->hFileProperties->selectAssociative( array( 'hFileIconId', 'hFileMIME', 'hFileSize', 'hFileDownload', 'hFileIsSystem', 'hFileLabel' ), $this->fileId );  $query = $this->hFileVariables->select( array( 'hFileVariable', 'hFileValue' ), $this->fileId ); foreach ($query as $data) { $file[$data['hFileVariable']] = $data['hFileValue']; } $calendarFiles = array(); $query = $this->hDatabase->select( array( 'hCalendarFiles' => array( 'hCalendarId', 'hCalendarCategoryId', 'hCalendarBegin', 'hCalendarEnd', 'hCalendarRange' ), 'hCalendarFileDates' => array( 'hCalendarDate', 'hCalendarBeginTime', 'hCalendarEndTime', 'hCalendarAllDay' ) ), array( 'hCalendarFiles', 'hCalendarFileDates' ), array( 'hCalendarFiles.hCalendarFileId' => 'hCalendarFileDates.hCalendarFileId', 'hCalendarFiles.hFileId' => $this->fileId ) ); foreach ($query as $data) { $calendarFiles['hFileCalendarId'][] = $data['hCalendarId']; $calendarFiles['hFileCalendarCategoryId'] = $data['hCalendarCategoryId']; $calendarFiles['hFileCalendarBegin'] = $data['hCalendarBegin']; $calendarFiles['hFileCalendarEnd'] = $data['hCalendarEnd']; $calendarFiles['hFileCalendarRange'] = $data['hCalendarRange']; $calendarFiles['hFileCalendarDate'][] = $data['hCalendarDate']; $calendarFiles['hFileCalendarBeginTime'][] = $data['hCalendarBeginTime']; $calendarFiles['hFileCalendarEndTime'][] = $data['hCalendarEndTime']; $calendarFiles['hFileCalendarAllDay'][] = $data['hCalendarAllDay']; } $userPermissions = array(); $permissions = $this->hFiles->getPermissions($this->fileId); $userPermissions['hUserPermissions'] = true; $userPermissions['hUserPermissionsWorld'] = $permissions['hUserPermissionsWorld']; $userPermissions['hUserPermissionsOwner'] = $permissions['hUserPermissionsOwner']; $userPermissions['hUserPermissionsGroups'] = array(); if (isset($permissions['hUserGroups']) && is_array($permissions['hUserGroups'])) { foreach ($permissions['hUserGroups'] as $userGroupId => $userPermissionsGroup) { $userPermissions['hUserPermissionsGroups'][$userGroupId] = $userPermissionsGroup; } } if (isset($permissions['hUsers']) && is_array($permissions['hUsers'])) { foreach ($permissions['hUsers'] as $userId => $userPermissionsGroup) { $userPermissions['hUserPermissionsGroups'][$userId] = $userPermissionsGroup; } } $fileId = $this->hFileDatabase->save( array_merge( $file, $fileDocument, $fileHeaders, $fileProperties, $calendarFiles, $userPermissions ) );     if (file_exists($this->hFrameworkFileSystemPath.$this->filePath)) { $GLOBALS['hFramework']->copy( $this->hFrameworkFileSystemPath.$this->filePath, $this->hFrameworkFileSystemPath.$path ); } return $fileId; } public function touch($permissions = array()) {                                           if ($this->exists($this->filePath) || !$this->exists($this->parentDirectoryPath)) { return 0; } $this->hFileDatabase = $this->database('hFile'); $directoryId = $this->getDirectoryId($this->parentDirectoryPath); $fileId = $this->hFileDatabase->save( array( 'hFileId' => 0, 'hDirectoryId' => $directoryId, 'hFileName' => $this->fileName ) ); $this->hDirectories->modifyResource($directoryId); $this->hFiles->modifyResource(); if (empty($permissions) || is_array($permissions) && !count($permissions) || !is_array($permissions)) { $this->hDirectories->inheritPermissionsFrom($this->directoryId); $this->hFiles->savePermissions($fileId); } else { if (isset($permissions['hUserPermissionsGroups']) && is_array($permissions['hUserPermissionsGroups'])) { foreach ($permissions['hUserPermissionsGroups'] as $group => $level) { $this->hFiles->addGroup($group, $level); } } $this->hFiles->savePermissions( $fileId, isset($permissions['hUserPermissionsOwner'])? $permissions['hUserPermissionsOwner'] : 'rw', isset($permissions['hUserPermissionsWorld'])? $permissions['hUserPermissionsWorld'] : '' ); } $this->unsetPath($this->filePath); return $fileId; } } ?>