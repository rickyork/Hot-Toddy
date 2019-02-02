CREATE TABLE `hPluginRepository` (

    `hPlugin`
        varchar(255)
        NOT NULL
        DEFAULT '',

    `hPluginType`
        varchar(11)
        NOT NULL
        DEFAULT '',

    `hPluginRepositoryUser`
        varchar(50)
        NOT NULL
        DEFAULT '',

    `hPluginRepositoryPassword`
        varchar(50)
        NOT NULL
        DEFAULT '',

    `hPluginRepositoryCheckout`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hPluginRepositoryReadonly`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hPluginRepositoryRevision`
        INT(11)
        NOT NULL
        DEFAULT '0',

    `hPluginRepositorySoftware`
        varchar(10)
        NOT NULL
        DEFAULT '',

    `hPluginRepositoryBaseURI`
        varchar(255)
        NOT NULL
        DEFAULT '',

    `hPluginRepositoryPath`
        varchar(255)
        NOT NULL
        DEFAULT '',

    KEY `hPlugin_hPluginType` (
        `hPlugin`,
        `hPluginType`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;
