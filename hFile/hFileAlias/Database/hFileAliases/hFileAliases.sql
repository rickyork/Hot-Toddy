CREATE TABLE `hFileAliases` (

    `hFileAliasId`
        int(11)
        NOT NULL
        auto_increment,

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFileAliasPath`
        varchar(255)
        NULL,

    `hFileAliasDestination`
        varchar(255)
        NULL,

    `hFileAliasRedirect`
        tinyint(1)
        NOT NULL
        default '0',

    `hFileAliasCreated`
        int(32)
        NOT NULL
        default '0',

    `hFileAliasExpires`
        tinyint(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileAliasId` (
        `hFileAliasId`
    ),

    KEY `hFileAliasPath` (
        `hFileAliasPath`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;