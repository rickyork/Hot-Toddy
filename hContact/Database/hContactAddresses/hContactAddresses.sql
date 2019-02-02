CREATE TABLE `hContactAddresses` (

    `hContactId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hContactAddressId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hContactFieldId`
        int(3)
        NOT NULL
        DEFAULT '0',

    `hContactAddressStreet`
        text
        NOT NULL,

    `hContactAddressCity`
        varchar(100)
        NOT NULL
        DEFAULT '',

    `hLocationStateId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hContactAddressPostalCode`
        varchar(15)
        NOT NULL
        DEFAULT '',

    `hLocationCountyId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hLocationCountryId`
        int(3)
        NOT NULL
        DEFAULT '0',

    `hContactAddressLatitude`
        float(32, 12)
        NOT NULL
        DEFAULT '0',

    `hContactAddressLongitude`
        float(32, 12)
        NOT NULL
        DEFAULT '0',

    `hFileId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hContactAddressOperatingHours`
        text
        NOT NULL,

    `hContactAddressIsDefault`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hContactAddressCreated`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hContactAddressLastModified`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hContactAddressLastModifiedBy`
        int(11)
        NOT NULL
        DEFAULT '0',

    PRIMARY KEY `hContactAddressId` (
        `hContactAddressId`
    ),

    KEY `hContactId` (
        `hContactId`
    ),

    KEY `hContactId_hContactAddressIsDefault` (
        `hContactId`,
        `hContactAddressIsDefault`
    ),

    KEY `hLocationStateId` (
        `hLocationStateId`
    ),

    KEY `hContactAddressLatitude_hContactAddressLongitude` (
        `hContactAddressLatitude`,
        `hContactAddressLongitude`
    ),

    KEY `hContactId_hLocationStateId` (
        `hContactId`,
        `hLocationStateId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;