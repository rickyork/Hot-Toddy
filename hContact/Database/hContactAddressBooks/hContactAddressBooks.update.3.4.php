<?php

class hContactAddressBooks_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactAddressBooks->addColumn(
            'hPlugin',
            hDatabase::varCharTemplate(255, ''),
            'hPluginIdIsPrivate'
        );

        $query = $this->hContactAddressBooks->select(
            array(
                'hContactAddressBookId',
                'hPluginId',
                'hPluginIdIsPrivate'
            )
        );

        foreach ($query as $data)
        {
            $contactAddressBookId = (int) $data['hContactAddressBookId'];
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

                    $this->hContactAddressBooks->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        $contactAddressBookId
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

                    $this->hContactAddressBooks->update(
                        array(
                            'hPlugin' => $pluginPath
                        ),
                        $contactAddressBookId
                    );
                }
            }
        }
    }

    public function undo()
    {
        $this->hContactAddressBooks->dropColumn('hPlugin');
    }
}

?>