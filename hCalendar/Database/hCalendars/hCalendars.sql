CREATE TABLE `hCalendars` (

    `hCalendarId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hCalendarName`
        text
        NOT NULL,

    `hCalendarCreated`
        int(32)
        NOT NULL
        default '0',

    `hCalendarLastModified`
        int(32)
        NOT NULL
        default '0',

    `hCalendarLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hCalendarId` (
        `hCalendarId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;