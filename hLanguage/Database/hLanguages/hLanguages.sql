CREATE TABLE `hLanguages` (

    `hLanguageId`
        int(3)
        NOT NULL
        auto_increment,

    `hLanguageCode`
        char(2)
        NOT NULL
        default '',

    `hLanguageLocalization`
        char(5)
        NOT NULL
        default '',

    `hLanguageCharset`
        varchar(20)
        NOT NULL
        default '',

    `hLanguageDescription`
        varchar(50)
        NOT NULL
        default '',

    `hLanguageName`
        varchar(50)
        NOT NULL
        default '',

    PRIMARY KEY `hLanguageId` (
        `hLanguageId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;