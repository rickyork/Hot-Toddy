<?php
  class hFinderCategories extends hPlugin { private $hFinder; public function hConstructor() { $categoryButtons = true; if (isset($_GET['path']) && !empty($_GET['setDefaultPath']) && $this->isCategoryPath($_GET['path'])) { if ($this->hFinderBodyClass) { $this->hFinderBodyClass .= ' hFinderCategories'; } else { $this->hFinderBodyClass = 'hFinderCategories'; } $this->hFinderHasSearch = false;  $this->hFinderCategoriesEnabled = false; $this->hFinderSideColumnFilterCategories = false; $categoryButtons = false; } if ($this->beginsPath($this->hFinderPath, '/Categories') && !strstr($this->hFinderBodyClass, ' hFinderCategories')) { $this->hFinderBodyClass .= ' hFinderCategories'; } $this->hFinder = $this->library('hFinder'); $this->jQuery('Sortable'); $this->getPluginFiles(); } } ?>