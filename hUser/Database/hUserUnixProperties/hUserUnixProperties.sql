CREATE TABLE `hUserUnixProperties` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hUserUnixUId`
        int(11)
        NOT NULL
        default '0',

    `hUserUnixGId`
        int(11)
        NOT NULL
        default '0',

    `hUserUnixHome`
        varchar(30)
        NOT NULL
        default '',

    `hUserUnixShell`
        varchar(30)
        NOT NULL
        default '',

    UNIQUE KEY `hUserId` (
        `hUserId`
    ),

    UNIQUE KEY `hUserUnixUId` (
        `hUserUnixUId`
    ),

    UNIQUE KEY `hUserUnixGId` (
        `hUserUnixGId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;