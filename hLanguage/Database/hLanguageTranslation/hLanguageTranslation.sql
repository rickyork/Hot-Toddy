CREATE TABLE `hLanguageTranslation` (

    `hLanguageTranslationId`
        int(22)
        NOT NULL
        auto_increment,

    `hLanguageTextFrom`
        int(22)
        default
        NULL,

    `hLanguageTextTo`
        int(22)
        default NULL,

    PRIMARY KEY `hLanguageTranslationId` (
        `hLanguageTranslationId`
    ),

    KEY `hLanguageTextFrom` (
        `hLanguageTextFrom`
    ),

    KEY `hLanguageTextTo` (
        `hLanguageTextTo`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;