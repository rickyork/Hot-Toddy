CREATE TABLE `hDirectoryProperties` (

    `hDirectoryId`
        int(11)
        NOT NULL
        default '0',

    `hFileIconId`
        int(11)
        NOT NULL
        default '0',

    `hDirectoryIsApplication`
        tinyint(1)
        NOT NULL
        default '0',

    `hDirectoryLabel`
        varchar(50)
        NOT NULL
        default '',

    UNIQUE KEY `hDirectoryId` (
        `hDirectoryId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0;
