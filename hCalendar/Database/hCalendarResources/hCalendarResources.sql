CREATE TABLE `hCalendarResources` (

    `hCalendarResourceId`
        int(11)
        NOT NULL
        auto_increment,

    `hCalendarId`
        int(11)
        NOT NULL
        default '0',

    `hCalendarCategoryId`
        int(11)
        NOT NULL
        default '0',

    `hCalendarResourceName`
        varchar(255)
        NOT NULL
        default '',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hPlugin`
        varchar(255)
        NOT NULL
        default '',

    `hDirectoryId`
        int(11)
        NOT NULL
        default '0',

    `hUserPermissionsOwner`
        varchar(3)
        NOT NULL
        default '',

    `hUserPermissionsWorld`
        varchar(3)
        NOT NULL
        default '0',

    `hUserPermissionsInherit`
        tinyint(1)
        NOT NULL
        default '0',

    `hCalendarResourceCreated`
        int(32)
        NOT NULL
        default '0',

    `hCalendarResourceLastModified`
        int(32)
        NOT NULL
        default '0',

    `hCalendarResourceLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    `hCalendarResourceCacheExpires`
        int(32)
        NOT NULL
        default '0',

    PRIMARY KEY `hCalendarResourceId` (
        `hCalendarResourceId`
    ),

    KEY `hCalendarId_hCalendarCategoryId` (
        `hCalendarId`,
        `hCalendarCategoryId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;
