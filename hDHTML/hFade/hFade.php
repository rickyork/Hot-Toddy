<?php

class hFade extends hPlugin {

    public function hConstructor()
    {
        $this->getPluginFiles();
        $this->getPluginCSS('ie');
    }
}

?>