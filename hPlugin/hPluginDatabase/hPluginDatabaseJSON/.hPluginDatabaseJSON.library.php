<?php
  class hPluginDatabaseJSONLibrary extends hPlugin { public function register($jsonPath, $isPrivate, $plugin, $pluginName = null, $pluginPath = null) { $this->console( "Unable to install plugin: The configuration file for {$jsonPath} must be updated to use the Hot Toddy JSON2 configuration file." ); } } ?>