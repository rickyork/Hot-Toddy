CREATE TABLE `hLanguageText` (

    `hLanguageTextId`
        int(22)
        NOT NULL
        auto_increment,

    `hLanguageId`
        int(3)
        NOT NULL
        default '0',

    `hLanguageText`
        text
        NOT NULL,

    PRIMARY KEY `hLanguageTextId` (
        `hLanguageTextId`
    ),

    KEY `hLanguageText` (
        `hLanguageText` (100)
    ),

    KEY `hLanguageTextId` (
        `hLanguageTextId`,
        `hLanguageId`
    ),

    KEY `hLanguageId_hLanguageText` (
        `hLanguageId`,
        `hLanguageText` (100)
    ),

    FULLTEXT KEY `FullText_hLanguageText` (
        `hLanguageText`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;