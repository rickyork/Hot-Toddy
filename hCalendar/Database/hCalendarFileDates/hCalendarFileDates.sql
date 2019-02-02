CREATE TABLE `hCalendarFileDates` (

    `hCalendarFileDateId`
        int(11)
        NOT NULL
        auto_increment,

    `hCalendarFileId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hCalendarDate`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCalendarBeginTime`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCalendarEndTime`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCalendarAllDay`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hCalendarFileDateCreated`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCalendarFileDateLastModified`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCalendarFileDateLastModifiedBy`
        int(11)
        NOT NULL
        DEFAULT '0',

    PRIMARY KEY `hCalendarFileDateId` (
        `hCalendarFileDateId`
    ),

    KEY `hCalendarDate` (
        `hCalendarDate`
    ),

    KEY `hCalendarFileId_hCalendarDate` (
        `hCalendarFileId`,
        `hCalendarDate`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;