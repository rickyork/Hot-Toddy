CREATE TABLE IF NOT EXISTS `hFileUserStatistics` (

    `hUserId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hFileId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hFileAccessCount`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hFileLastAccessed`
        int(32)
        NOT NULL
        DEFAULT '0',

    KEY `hUserId_hFileId` (
        `hUserId`,
        `hFileId`
    ),

    KEY `hUserId` (
        `hUserId`
    ),

    KEY `hFileLastAccessed` (
        `hFileLastAccessed`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;
