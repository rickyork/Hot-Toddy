<?php
  class hMovieLibrary extends hPlugin { private $hFile; private $hCategoryDatabase; private $hFinderTree; public function hConstructor() { $this->getPluginFiles(); if (!$this->categoryExists('/Categories/.Movies')) { $this->library('hMovie/hMovieInstall'); } } public function getTree() {         $this->hFinderTreeLoadPluginFiles = false; $this->hFinderTree = $this->plugin('hFinder/hFinderTree'); $this->hFinderTreeDefaultPath = '/Categories/.Movies'; $this->hFinderTreeHomeDirectory = false; $this->hFinderTreeRootOverrideDefaultPath = false; $this->hFinderCategoriesDiskName = 'Movies'; return $this->getTemplate( 'Tree', array( 'tree' => $this->hFinderTree->getTree() ) ); } public function getView() {         return $this->getTemplate('View'); } public function getMovies($filePath) {          $categoryId = $this->getCategoryIdFromPath($filePath); if (!empty($categoryId)) { $this->hCategoryDatabase = $this->database('hCategory'); $this->hCategoryDatabase->setDatabaseReturnFormat('getResultsForTemplate'); $thumbnailGenerator = $this->getFilePathByPlugin('hFile/hFileThumbnail'); $this->hCategoryDatabase->setCategoryFileSort('`hFiles`.`hFileName` ASC'); $files = $this->hCategoryDatabase->getCategoryFiles($categoryId); if (is_array($files) && count($files)) { foreach ($files['hFilePath'] as $i => $data) { $files['hFilePathEncoded'][$i] = urlencode($data); $caption = ''; if (!empty($files['hFileTitle'][$i])) { $caption = $files['hFileTitle'][$i]; } else { $bits = explode('.', $files['hFileName'][$i]); $caption = array_shift($bits); } $files['hMovieCaption'][$i] = $caption; } return $this->getTemplate( 'Movies', array( 'hFiles' => $files, 'thumbnailGenerator' => $thumbnailGenerator ) ); } else { return ''; } } else { $this->warning( "Category path, ".$filePath." does not exist.", __FILE__, __LINE__ ); } } } ?>