<?php

class hTemplatePlugins_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hTemplatePlugins->dropColumns(
            array(
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );
    }
}

?>