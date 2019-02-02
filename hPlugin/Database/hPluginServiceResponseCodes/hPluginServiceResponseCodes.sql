CREATE TABLE `hPluginServiceResponseCodes` (

    `hPluginServiceResponseCode`
        int(11)
        NOT NULL
        default '0',

    `hPluginServiceResponseText`
        text
        NOT NULL,

    PRIMARY KEY `hPluginServiceResponseCode` (
        `hPluginServiceResponseCode`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;