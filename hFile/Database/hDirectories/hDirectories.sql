CREATE TABLE `hDirectories` (

    `hDirectoryId`
        int(11)
        NOT NULL
        auto_increment,

    `hDirectoryParentId`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hDirectoryPath`
        text
        NOT NULL,

    `hDirectoryCreated`
        int(32)
        NOT NULL
        default '0',

    `hDirectoryLastModified`
        int(32)
        NOT NULL
        default '0',

    `hDirectoryLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY (
        `hDirectoryId`
    ),

    KEY `hDirectoryParentId` (
        `hDirectoryParentId`
    ),

    KEY `hDirectoryPath` (
        `hDirectoryPath` (100)
    ),

    FULLTEXT KEY `hDirectoryPathFullText` (
        `hDirectoryPath`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0;