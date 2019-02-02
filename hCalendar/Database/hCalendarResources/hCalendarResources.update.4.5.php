<?php

class hCalendarResources_4to5 extends hPlugin {

    public function hConstructor()
    {
        $this->hCalendarResources->dropColumn('hCalendarResourceName');

        $this->hCalendarResources
            ->addColumn('hCalendarResourceName', hDatabase::name, 'hCalendarCategoryId')
            ->addColumn('hPluginId', hDatabase::id, 'hUserId')
            ->addColumn('hPluginIdIsPrivate', hDatabase::is, 'hPluginId')
            ->addColumn('hDirectoryId', hDatabase::id, 'hPluginIdIsPrivate')
            ->addColumn('hUserPermissionsOwner', hDatabase::varCharTemplate(3), 'hDirectoryId')
            ->addColumn('hUserPermissionsWorld', hDatabase::varCharTemplate(3), 'hUserPermissionsOwner')
            ->addColumn('hUserPermissionsInherit', hDatabase::is, 'hUserPermissionsWorld');
    }

    public function undo()
    {
        $this->hCalendarResources->dropColumns(
            array(
                'hCalendarResourceName',
                'hPluginId',
                'hPluginIdIsPrivate',
                'hDirectoryId',
                'hUserPermissionsOwner',
                'hUserPermissionsWorld',
                'hUserPermissionsInherit'
            )
        );
    }
}

?>