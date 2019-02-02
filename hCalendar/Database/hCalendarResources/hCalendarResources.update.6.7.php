<?php

class hCalendarResources_6to7 extends hPlugin {

    public function hConstructor()
    {
        $this->hCalendarResources->dropColumns(
            array(
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );
    }

    public function undo()
    {

    }
}

?>