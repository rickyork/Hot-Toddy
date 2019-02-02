<?php

class hCalendarResources_5to6 extends hPlugin {

    public function hConstructor()
    {
        $this->hCalendarResources->addColumn(
            'hPlugin',
            hDatabase::varCharTemplate(255, ''),
            'hPluginIdIsPrivate'
        );

        $query = $this->hCalendarResources->select(
            array(
                'hCalendarResourceId',
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );

        foreach ($query as $data)
        {
            $calendarResourceId = (int) $data['hCalendarResourceId'];
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

                    $this->hCalendarResources->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        $calendarResourceId
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

                    $this->hCalendarResources->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        $calendarResourceId
                    );
                }
            }
        }
    }

    public function undo()
    {
        $this->hCalendarResources->dropColumn('hPlugin');
    }
}

?>