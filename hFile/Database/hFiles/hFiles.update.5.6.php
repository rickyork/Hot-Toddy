<?php

class hFiles_5to6 extends hPlugin {

    public function hConstructor()
    {
        $this->hFiles->dropColumns(
            array(
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );
    }
}

?>