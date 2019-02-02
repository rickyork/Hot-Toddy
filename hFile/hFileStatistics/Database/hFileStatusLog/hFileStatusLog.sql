CREATE TABLE `hFileStatusLog` (

    `hFileStatusLogId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hFileStatusPath`
        varchar(200)
        NOT NULL
        default '',

    `hFileStatusCode`
        int(3)
        NOT NULL
        default '0',

    `hFileStatusReferrerPath`
        varchar(200)
        NOT NULL
        default '',

    `hFileStatusAccessCount`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileStatusLogId` (
        `hFileStatusLogId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;