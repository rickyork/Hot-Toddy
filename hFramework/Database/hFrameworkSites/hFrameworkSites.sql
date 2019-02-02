CREATE TABLE `hFrameworkSites` (

    `hFrameworkSiteId`
        int(11)
        NOT NULL
        auto_increment,

    `hFrameworkSite`
        varchar(255)
        NOT NULL
        default '',

    `hFrameworkSitePath`
        varchar(255)
        NOT NULL
        default '',

    `hFrameworkSiteIsDefault`
        tinyint(1)
        NOT NULL
        default '0',

    `hFrameworkSiteCreated`
        int(32)
        NOT NULL
        default '0',

    `hFrameworkSiteLastModified`
        int(32)
        NOT NULL
        default '0',

    `hFrameworkSiteLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hFrameworkSiteId` (
        `hFrameworkSiteId`
    ),

    KEY `hFrameworkSite` (
        `hFrameworkSite`
    ),

    KEY `hFrameworkSite_hFrameworkSiteIsDefault` (
        `hFrameworkSite`,
        `hFrameworkSiteIsDefault`
    ),

    KEY `hFrameworkSitePath` (
        `hFrameworkSitePath`
    ),

    KEY `hFrameworkSiteIsDefault` (
        `hFrameworkSiteIsDefault`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
