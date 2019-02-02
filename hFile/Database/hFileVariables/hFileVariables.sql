CREATE TABLE `hFileVariables` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFileVariable`
        varchar(100)
        NOT NULL
        default '',

    `hFileValue`
        text
        NOT NULL,

    KEY `hFileId` (
        `hFileId`
    ),

    KEY `hFileId_hFileVariable` (
        `hFileId`,
        `hFileVariable` (50)
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;