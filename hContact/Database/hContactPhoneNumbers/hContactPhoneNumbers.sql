CREATE TABLE `hContactPhoneNumbers` (

    `hContactId`
        int(11)
        NOT NULL
        default '0',

    `hContactPhoneNumberId`
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

    `hContactPhoneNumber`
        varchar(255)
        NOT NULL
        default '',

    `hContactPhoneNumberCreated`
        int(32)
        NOT NULL
        default '0',

    `hContactPhoneNumberLastModified`
        int(32)
        NOT NULL
        default '0',

    `hContactPhoneNumberLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hContactPhoneNumberId` (
        `hContactPhoneNumberId`
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