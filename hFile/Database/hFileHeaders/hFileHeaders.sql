CREATE TABLE `hFileHeaders` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFileCSS`
        text
        NOT NULL,

    `hFileJavaScript`
        text
        NOT NULL,

    UNIQUE KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;