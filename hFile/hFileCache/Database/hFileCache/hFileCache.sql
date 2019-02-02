CREATE TABLE `hFileCache` (

    `hFileCacheId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hLanguageId`
        int(3)
        NOT NULL
        DEFAULT '0',

    `hFileCacheResourceId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hFileCacheResource`
        varchar(50)
        NOT NULL
        DEFAULT '',

    `hFileCacheResourcePath`
        varchar(255)
        NOT NULL
        DEFAULT '',

    `hFileCacheDocument`
        mediumtext
        NOT NULL,

    `hFileCacheLastModified`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hFileCacheExpires`
        int(32)
        NOT NULL
        DEFAULT '0',

    PRIMARY KEY `hFileCacheId` (
        `hFileCacheId`
    ),

    KEY `hLanguageId_hFileCacheResourceId_hFileCacheResource` (
        `hLanguageId`,
        `hFileCacheResourceId`,
        `hFileCacheResource`
    ),

    KEY `hLanguageId_hFileCacheResource_hFileCacheResourcePath` (
        `hLanguageId`,
        `hFileCacheResource`,
        `hFileCacheResourcePath`
    ),

    KEY `hFileCacheResource_hFileCacheResourcePath` (
        `hFileCacheResource`,
        `hFileCacheResourcePath`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8