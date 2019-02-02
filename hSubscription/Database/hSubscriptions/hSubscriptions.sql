CREATE TABLE `hSubscriptions` (

    `hSubscriptionId`
        int(11)
        NOT NULL
        auto_increment,

    `hFrameworkResourceId`
        int(11)
        NOT NULL
        default '0',

    `hFrameworkResourceKey`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hSubscriptionId` (
        `hSubscriptionId`
    ),

    KEY `hFrameworkResourceId` (
        `hFrameworkResourceId`
    ),

    KEY `hFrameworkResourceId_hFrameworkResourceKey` (
        `hFrameworkResourceId`,
        `hFrameworkResourceKey`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;