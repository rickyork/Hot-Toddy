CREATE TABLE `hContactFields` (

    `hContactFieldId`
        int(3)
        NOT NULL
        auto_increment,

    `hFrameworkResourceId`
        int(3)
        NOT NULL
        default '0',

    `hContactField`
        varchar(50)
        NOT NULL
        default '',

    `hContactFieldSortIndex`
        int(3)
        NOT NULL
        default '0',

    `hContactFieldName`
        varchar(50)
        NOT NULL
        default '',

    PRIMARY KEY `hContactFieldId` (
        `hContactFieldId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1000;
