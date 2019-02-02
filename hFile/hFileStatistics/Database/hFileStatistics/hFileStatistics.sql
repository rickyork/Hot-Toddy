CREATE TABLE `hFileStatistics` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFileAccessCount`
        int(11)
        NOT NULL
        default '0',

    `hFileLastAccessed`
        int(32)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;