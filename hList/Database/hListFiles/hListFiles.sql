CREATE TABLE IF NOT EXISTS `hListFiles` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hListId`
        int(11)
        NOT NULL
        default '0',

    `hListFileId`
        int(11)
        NOT NULL
        default '0',

    `hListFileSortIndex`
        tinyint(2)
        NOT NULL
        default '0',

    KEY `hFileId` (
        `hFileId`
    ),

    KEY `hListId` (
        `hListId`
    ),

    KEY `hListFileId` (
        `hListFileId`
    ),

    KEY `hListId_hListFileId` (
        `hListId`,
        `hListFileId`
    ),

    KEY `hFileId_hListId` (
        `hFileId`,
        `hListId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;