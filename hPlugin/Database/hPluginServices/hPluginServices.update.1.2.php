<?php

class hPluginServices_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hPluginServices
            ->truncate()
            ->dropColumns(
                array(
                    'hPluginServiceMethodId',
                    'hPluginId'
                )
            )
            ->addColumn(
                'hPlugin',
                hDatabase::varCharTemplate(255),
                'hPluginServiceMethod'
            )
            ->addKey('hPlugin')
            ->addKey(
                array(
                    'hPluginServiceMethod',
                    'hPlugin'
                )
            );
    }
}

?>