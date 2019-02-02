<?php

class hUserPermissions_1to2 extends hPlugin {

    public function hConstructor()
    {
        // Add two columns...
        $this->hDatabase->query(
            "ALTER TABLE `hUserPermissions` ADD UNIQUE (
                `hFrameworkResourceId`,
                `hFrameworkResourceKey`
            )"
        );
        
        $this->hDatabase->query(
            "ALTER TABLE `hUserPermissions` ADD UNIQUE (
                `hUserPermissionsId`,
                `hFrameworkResourceId`,
                `hFrameworkResourceKey`,
                `hUserPermissionsWorld`
            )"
        );
        
        $this->hDatabase->query(
            "ALTER TABLE `hUserPermissions` ADD UNIQUE (
                `hUserPermissionsId`,
                `hFrameworkResourceId`,
                `hFrameworkResourceKey`,
                `hUserPermissionsOwner`,
                `hUserPermissionsWorld`
            )"
        );
    }
}

?>