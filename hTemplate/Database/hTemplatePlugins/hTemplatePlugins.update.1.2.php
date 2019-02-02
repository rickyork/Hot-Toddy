<?php

class hTemplatePlugins_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hTemplatePlugins->addColumn(
            'hPlugin',
            hDatabase::varCharTemplate(255, ''),
            'hPluginIdIsPrivate'
        );

        $query = $this->hTemplatePlugins->select(
            array(
                'hTemplateId',
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );

        foreach ($query as $data)
        {
            $templateId = (int) $data['hTemplateId'];
            $isPrivate = (int) $data['hPluginIdIsPrivate'];
            $pluginId = (int) $data['hPluginId'];

            if ($pluginId > 0)
            {
                if (!$isPrivate)
                {
                    $pluginPath = $this->hPlugins->selectColumn(
                        'hPluginPath',
                        array(
                            'hPluginId' => $pluginId
                        )
                    );

                    $this->hTemplatePlugins->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        array(
                            'hTemplateId' => (int) $templateId
                        )
                    );
                }
                else
                {
                    $pluginPath = $this->hPluginsPrivate->selectColumn(
                        'hPluginPath',
                        array(
                            'hPluginId' => $pluginId
                        )
                    );

                    $this->hTemplatePlugins->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        array(
                            'hTemplateId' => (int) $templateId
                        )
                    );
                }
            }
        }
    }
}

?>