CREATE TABLE `hContactUsers` (

    `hContactId`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    KEY `hContactId` (
        `hContactId`
    ),

    KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;