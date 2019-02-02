CREATE TABLE `hSubscriptionUsers` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hSubscriptionId`
        int(11)
        NOT NULL
        default '0',

    KEY `hUserId` (
        `hUserId`
    ),

    KEY `hUserId_hSubscriptionId` (
        `hUserId`,
        `hSubscriptionId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;