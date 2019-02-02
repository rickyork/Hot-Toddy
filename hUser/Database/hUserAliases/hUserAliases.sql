CREATE TABLE `hUserAliases` (

    `hUserId`
        INT(11)
        default '0',

    `hUserNameAlias`
        VARCHAR(50)
        default NULL,

    UNIQUE KEY `hUserNameAlias` (
        `hUserNameAlias`
    ),

    KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;