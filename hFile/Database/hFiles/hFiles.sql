CREATE TABLE `hFiles` (

    `hFileId`
        int(11)
        NOT NULL
        auto_increment,

    `hLanguageId`
        int(3)
        NOT NULL
        default '1',

    `hDirectoryId`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hFileParentId`
        int(11)
        NOT NULL
        default '0',

    `hFileName`
        varchar(255)
        NOT NULL
        default '',

    `hPlugin`
        varchar(255)
        NOT NULL
        default '',

    `hFileSortIndex`
        int(3)
        NOT NULL
        default '0',

    `hFileCreated`
        int(32)
        NOT NULL
        default '0',

    `hFileLastModified`
        int(32)
        NOT NULL
        default '0',

    `hFileLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileId` (
        `hFileId`
    ),

    KEY `hFileParentId` (
        `hFileParentId`
    ),

    KEY `hDirectoryId_hFileName` (
        `hDirectoryId`,
        `hFileName`
    ),

    KEY `hDirectoryId` (
        `hDirectoryId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
