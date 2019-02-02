CREATE TABLE `hUserVariables` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hUserVariable`
        varchar(100)
        default NULL,

    `hUserValue`
        text
        NOT NULL,

    KEY `hUserId` (
        `hUserId`
    ),

    KEY `hUserId_hUserVariable` (
        `hUserId`,
        `hUserVariable`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;