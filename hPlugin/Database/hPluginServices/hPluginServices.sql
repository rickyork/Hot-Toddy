CREATE TABLE `hPluginServices` (

    `hPluginServiceMethod`
        varchar(65)
        default
        NULL,

    `hPlugin`
        varchar(255)
        NOT NULL
        default '',

    KEY `hPlugin` (
        `hPlugin`
    ),

    KEY `hPluginServiceMethod_hPlugin` (
        `hPluginServiceMethod`,
        `hPlugin`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;