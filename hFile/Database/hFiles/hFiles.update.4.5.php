<?php

class hFiles_4to5 extends hPlugin {

    public function hConstructor()
    {
        $this->hFiles->addColumn(
            'hPlugin',
            hDatabase::varCharTemplate(255, ''),
            'hPluginIdIsPrivate'
        );

        $query = $this->hFiles->select(
            array(
                'hFileId',
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );

        foreach ($query as $data)
        {
            $fileId = (int) $data['hFileId'];
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

                    $this->hFiles->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        $fileId
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

                    $this->hFiles->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        $fileId
                    );
                }
            }
        }
    }
}

?>