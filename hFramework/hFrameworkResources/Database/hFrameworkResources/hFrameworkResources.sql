CREATE TABLE `hFrameworkResources` (

    `hFrameworkResourceId`
        int(11)
        NOT NULL
        auto_increment,

    `hFrameworkResourceTable`
        varchar(100)
        NOT NULL
        default '',

    `hFrameworkResourcePrimaryKey`
        varchar(100)
        NOT NULL
        default '',

    `hFrameworkResourceNameColumn`
        varchar(100)
        NOT NULL
        default '',

    `hFrameworkResourceLastModifiedColumn`
        varchar(100)
        NOT NULL
        default '',

    `hFrameworkResourceLastModifiedByColumn`
        varchar(100)
        NOT NULL
        default '',

    `hFrameworkResourceLastModified`
        int(32)
        NOT NULL
        default '0',

    `hFrameworkResourceLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hFrameworkResourceId` (
        `hFrameworkResourceId`
    ),

    KEY `hFrameworkResourceTable` (
        `hFrameworkResourceTable`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;
