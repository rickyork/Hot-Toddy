<?php


class hFrameworkResources_6to7 extends hPlugin {

    public function hConstructor()
    {
        $this->hFrameworkResources->delete('hFrameworkResourceId', 23);
        $this->hFrameworkResources->delete('hFrameworkResourceId', 24);
    }
}

?>