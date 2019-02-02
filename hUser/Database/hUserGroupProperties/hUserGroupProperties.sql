CREATE TABLE `hUserGroupProperties` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hUserGroupOwner`
        int(11)
        NOT NULL
        default '0',

    `hUserGroupIsElevated`
        tinyint(1)
        NOT NULL
        default '0',

    `hUserGroupPassword`
        char(35)
        NOT NULL,

    `hUserGroupLoginEnabled`
        tinyint(1)
        NOT NULL
        default '0',

    UNIQUE KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;