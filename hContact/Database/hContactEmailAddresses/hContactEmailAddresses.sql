CREATE TABLE `hContactEmailAddresses` (

    `hContactId`
        int(11)
        NOT NULL
        default '0',

    `hContactEmailAddressId`
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

    `hContactEmailAddress`
        varchar(255)
        NOT NULL
        default '',

    `hContactEmailAddressCreated`
        int(32)
        NOT NULL
        default '0',

    `hContactEmailAddressLastModified`
        int(32)
        NOT NULL
        default '0',

    `hContactEmailAddressLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hContactEmailAddressId` (
        `hContactEmailAddressId`
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

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;