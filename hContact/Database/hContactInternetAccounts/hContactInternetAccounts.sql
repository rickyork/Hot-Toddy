CREATE TABLE `hContactInternetAccounts` (

    `hContactId`
        int(11)
        NOT NULL
        default '0',

    `hContactInternetAccountId`
        int(11)
        NOT NULL
        auto_increment,

    `hContactAddressId`
        int(11)
        NOT NULL
        default '0',

    `hContactFieldId`
        int(3)
        NOT NULL
        default '0',

    `hContactInternetAccount`
        varchar(255)
        NOT NULL
        default '',

    `hContactInternetAccountCreated`
        int(32)
        NOT NULL
        default '0',

    `hContactInternetAccountLastModified`
        int(32)
        NOT NULL
        default '0',

    `hContactInternetAccountLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hContactInternetAccountId` (
        `hContactInternetAccountId`
    ),

    KEY `hContactId` (
        `hContactId`
    ),

    KEY `hContactAddressId` (
        `hContactAddressId`
    ),

    KEY `hContactId_hContactAddressId` (
        `hContactId`,
        `hContactAddressId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;