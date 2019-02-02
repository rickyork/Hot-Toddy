CREATE TABLE `hFilePasswords` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFilePassword`
        varchar(25)
        NOT NULL
        default '',

    `hFilePasswordLifetime`
        tinyint(3)
        NOT NULL
        default '0',

    `hFilePasswordExpirationAction`
        tinyint(1)
        NOT NULL
        default '0',

    `hFilePasswordRequired`
        tinyint(1)
        NOT NULL
        default '0',

    `hFilePasswordCreated`
        int(32)
        NOT NULL
        default '0',

    `hFilePasswordExpires`
        int(32)
        NOT NULL
        default '0',

    KEY `hFileId` (
        `hFileId`
    ),

    KEY `hFileId_hFilePasswordRequired` (
        `hFileId`,
        `hFilePasswordRequired`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;