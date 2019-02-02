CREATE TABLE `hCalendarResourcePermissionsGroups` (

    `hCalendarResourceId`
        int(11)
        NOT NULL
        default '0',

    `hUserGroupId`
        int(11)
        NOT NULL
        default '0',

    `hUserPermissionsGroup`
        varchar(3)
        NOT NULL
        default '0',

    KEY `hCalendarResourceId` (
        `hCalendarResourceId`
    ),

    KEY `hCalendarResourceId_hUserGroupId` (
        `hCalendarResourceId`,
        `hUserGroupId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
