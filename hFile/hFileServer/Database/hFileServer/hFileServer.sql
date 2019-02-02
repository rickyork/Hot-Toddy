CREATE TABLE `hFileServer` (

    `hFileServerId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hFileServerPath`
        text
        NOT NULL,

    `hFileServerTitle`
        varchar(255)
        NOT NULL
        default '',

    `hFileServerDescription`
        text
        NOT NULL,

    `hFileServerCreated`
        int(32)
        NOT NULL
        default '0',

    `hFileServerLastModified`
        int(32)
        NOT NULL
        default '0',

    `hFileServerLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileServerId` (
        `hFileServerId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;