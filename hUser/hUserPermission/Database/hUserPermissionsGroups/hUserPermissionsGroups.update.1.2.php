<?php

class hUserPermissionsGroups_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hUserPermissionsGroups
                ->dropKey('hUserPermissionsID')
                ->dropKey('hUserPermissionsGroup')
                ->addKey(
                    'hUserPermissionsId'
                )
                ->addKey(
                    'hUserGroupId'
                )
                ->addKey(
                    array(
                        'hUserPermissionsId',
                        'hUserGroupId'
                    )
                )
                ->addKey(
                    array(
                        'hUserPermissionsId',
                        'hUserGroupId',
                        'hUserPermissionsGroup'
                    )
                );
    }
}

?>