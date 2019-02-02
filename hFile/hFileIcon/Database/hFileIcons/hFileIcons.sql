CREATE TABLE `hFileIcons` (

    `hFileIconId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hFileMIME`
        varchar(100)
        NOT NULL
        default '',

    `hFileName`
        varchar(100)
        NOT NULL
        default '',

    `hFileICNS`
        varchar(100)
        NOT NULL
        default '',

    `hFileExtension`
        varchar(20)
        NOT NULL
        default '',

    PRIMARY KEY `hFileIconId` (
        `hFileIconId`
    ),

    KEY `hFileExtension` (
        `hFileExtension`
    ),

    KEY `hFileMIME` (
        `hFileMIME`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;