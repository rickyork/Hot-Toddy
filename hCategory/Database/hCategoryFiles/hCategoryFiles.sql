CREATE TABLE `hCategoryFiles` (

    `hCategoryId`
        int(11)
        NOT NULL
        default '0',

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hCategoryFileSortIndex`
        tinyint(2)
        NOT NULL
        default '0',

    KEY `hCategoryId` (
        `hCategoryId`
    ),

    KEY `hCategoryId_hFileId` (
        `hCategoryId`,
        `hFileId`
    ),

    KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;