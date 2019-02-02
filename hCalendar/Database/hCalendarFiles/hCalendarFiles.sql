CREATE TABLE `hCalendarFiles` (

    `hCalendarFileId`
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

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hCalendarBegin`
        int(22)
        NOT NULL,

    `hCalendarEnd`
        int(22)
        NOT NULL,

    `hCalendarRange`
        tinyint(1)
        NOT NULL,

    `hCalendarFileCreated`
        int(32)
        NOT NULL
        default '0',

    `hCalendarFileLastModified`
        int(32)
        NOT NULL
        default '0',

    `hCalendarFileLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hCalendarFileId` (
        `hCalendarFileId`
    ),

    KEY `hCalendarCategoryId` (
        `hCalendarCategoryId`
    ),

    KEY `hFileId` (
        `hFileId`
    ),

    KEY `hCalendarId_hCalendarCategoryId` (
        `hCalendarId`,
        `hCalendarCategoryId`
    ),

    KEY `hCalendarId` (
        `hCalendarId`
    ),

    KEY `hCalendarId_hCalendarCategoryId_hFileId` (
        `hCalendarId`,
        `hCalendarCategoryId`,
        `hFileId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;