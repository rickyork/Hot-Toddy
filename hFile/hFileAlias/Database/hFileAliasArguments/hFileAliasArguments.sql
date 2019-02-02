CREATE TABLE `hFileAliasArguments` (

    `hFileAliasId`
        int(11)
        NOT NULL
        default '0',

    `hFileAliasArgument`
        varchar(50)
        NOT NULL
        default '',

    `hFileAliasArgumentValue`
        varchar(255)
        NOT NULL
        default ''

) ENGINE=MyISAM DEFAULT CHARSET=utf8;