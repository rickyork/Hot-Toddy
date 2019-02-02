CREATE TABLE `hUserPermissions` (

    `hUserPermissionsId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hFrameworkResourceId`
        int(6)
        NOT NULL
        DEFAULT '0',

    `hFrameworkResourceKey`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hUserPermissionsOwner`
        varchar(3)
        DEFAULT NULL,

    `hUserPermissionsWorld`
        varchar(3)
        DEFAULT NULL,

    PRIMARY KEY `hUserPermissionsId` (
        `hUserPermissionsId`
    ),

    UNIQUE KEY `hUserPermissionsIdWorld_hFrameworkResourceIdKey` (
        `hUserPermissionsId`,
        `hFrameworkResourceId`,
        `hFrameworkResourceKey`,
        `hUserPermissionsWorld`
    ),

    KEY `hFrameworkResourceId_hUserPermissionsWorld` (
        `hFrameworkResourceId`,
        `hUserPermissionsWorld`
    ),

    UNIQUE KEY `hFrameworkResourceId_hFrameworkResourceKey` (
        `hFrameworkResourceId`,
        `hFrameworkResourceKey`
    ),

    UNIQUE KEY `hFrameworkResourceIdKey_hUserPermissionsWorld` (
        `hFrameworkResourceId`,
        `hFrameworkResourceKey`,
        `hUserPermissionsWorld`
    ),

    KEY `hFrameworkResourceId_hUserPermissionsOwner` (
        `hFrameworkResourceId`,
        `hUserPermissionsOwner`
    ),

    UNIQUE KEY `hUserPermissionsIdOwnerWorld_hFrameworkResourceIdKey` (
        `hUserPermissionsId`,
        `hFrameworkResourceId`,
        `hFrameworkResourceKey`,
        `hUserPermissionsOwner`,
        `hUserPermissionsWorld`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8