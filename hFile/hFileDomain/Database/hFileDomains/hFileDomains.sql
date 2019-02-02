CREATE TABLE IF NOT EXISTS `hFileDomains` (

    `hFileDomainId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hFileDomain`
        varchar(255)
        NOT NULL
        DEFAULT '',

    `hFileId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hFrameworkSite`
        varchar(255)
        NOT NULL
        DEFAULT '',

    `hTemplateId`
        int(11)
        NOT NULL
        DEFAULT '1',

    `hFileDomainIsDefault`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    PRIMARY KEY `hFileDomainId` (
        `hFileDomainId`
    ),

    KEY `hFileDomain` (
        `hFileDomain`
    ),

    KEY `hFileDomainIsDefault` (
        `hFileDomainIsDefault`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
