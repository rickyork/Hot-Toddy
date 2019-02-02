CREATE TABLE `hFilePathWildcards` (

    `hFilePathWildcard`
        varchar(100)
        NOT NULL
        default '0',

    `hFileId`
        int(22)
        NOT NULL
        default '0',

    KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;