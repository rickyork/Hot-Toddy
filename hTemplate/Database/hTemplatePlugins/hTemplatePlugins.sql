CREATE TABLE `hTemplatePlugins` (

    `hTemplateId`
        int(11)
        NOT NULL
        default '0',

    `hPlugin`
        varchar(255)
        NOT NULL
        default '',

    KEY `hTemplateId` (
        `hTemplateId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;