CREATE TABLE `hFrameworkEvents` (

    `hFrameworkEventId`
        int(11)
        NOT NULL
        auto_increment,

    `hFrameworkEvent`
        varchar(50)
        NOT NULL
        default '',

    `hPlugin`
        varchar(255)
        NOT NULL
        default '',

    `hPluginMethod`
        varchar(255)
        NOT NULL
        default '',

    PRIMARY KEY `hFrameworkEventId` (
        `hFrameworkEventId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;