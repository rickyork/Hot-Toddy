CREATE TABLE `hFileLog` (

    `hFileLogId`
        int(11)
        NOT NULL
        auto_increment,

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hUserIP`
        varchar(35)
        NOT NULL
        default '',

    `hUserISP`
        varchar(50)
        NOT NULL
        default '',

    `hUserAgent`
        varchar(100)
        NOT NULL
        default '',

    `hFileAccessCount`
        int(11)
        NOT NULL
        default '0',

    `hFileLastAccessed`
        int(32)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileLogId` (
        `hFileLogId`
    ),

    KEY `hFileId_hUserIP_hUserISP_hUserAgent` (
        `hFileId`,
        `hUserIP`,
        `hUserISP`,
        `hUserAgent`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;