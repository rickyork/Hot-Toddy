CREATE TABLE `hFrameworkErrors` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hFrameworkError`
        text
        NOT NULL,

    `hFilePath`
        text
        NOT NULL,

    `hUserAgentReferrer`
        text
        NOT NULL,

    `hPluginPath`
        text
        NOT NULL,

    `hPluginLine`
        int(11)
        NOT NULL
        default '0',

    `hFrameworkBackTrace`
        text
        NOT NULL,

    `hFrameworkErrorDate`
        int(32)
        NOT NULL
        default '0',

    `hUserAgent`
        varchar(200)
        NOT NULL
        default '',

    `hUserRemoteIP`
        varchar(25)
        NOT NULL
        default ''

) ENGINE=MyISAM DEFAULT CHARSET=utf8;