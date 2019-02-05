CREATE TABLE `hUserPermissionsGroups` (

    `hUserPermissionsId`
        int(11)
        NOT NULL
        default '0',

    `hUserGroupId`
        int(11)
        NOT NULL
        default '0',

    `hUserPermissionsGroup`
        varchar(3)
        default NULL,

    KEY `hUserPermissionsId` (
        `hUserPermissionsId`
    ),

    KEY `hUserGroupId` (
        `hUserGroupId`
    ),

    KEY `hUserPermissionsId_hUserGroupId` (
        `hUserPermissionsId`,
        `hUserGroupId`
    ),

    KEY `hUserPermissionsId_hUserGroupId_hUserPermissionsGroup` (
        `hUserPermissionsId`,
        `hUserGroupId`,
        `hUserPermissionsGroup`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;