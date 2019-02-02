CREATE TABLE `hUserNewsletter` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hUserNewsletter`
        tinyint(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;