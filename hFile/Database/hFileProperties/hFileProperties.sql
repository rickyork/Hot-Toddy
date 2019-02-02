CREATE TABLE `hFileProperties` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFileIconId`
        int(11)
        NOT NULL
        default '0',

    `hFileMIME`
        varchar(100)
        NOT NULL
        default '',

    `hFileSize`
        int(22)
        NOT NULL
        default '0',

    `hFileDownload`
        tinyint(1)
        NOT NULL
        default '0',

    `hFileIsSystem`
        tinyint(1)
        NOT NULL
        default '0',

    `hFileSystemPath`
        text
        NOT NULL
        default '',

    `hFileMD5Checksum`
        char(32)
        NOT NULL
        default '',

    `hFileLabel`
        varchar(50)
        NOT NULL
        default '',

    UNIQUE KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;