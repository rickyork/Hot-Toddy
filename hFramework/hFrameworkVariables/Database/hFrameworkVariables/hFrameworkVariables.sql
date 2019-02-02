CREATE TABLE `hFrameworkVariables` (

    `hFrameworkVariable`
        varchar(255)
        NOT NULL
        default '',

    `hFrameworkValue`
        text
        NOT NULL,

    PRIMARY KEY `hFrameworkVariable` (
        `hFrameworkVariable`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;