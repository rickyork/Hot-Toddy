CREATE TABLE `hContactFiles` (

    `hContactId`
        int(11)
        NOT NULL
        default '0',

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hContactFileCategoryId`
        int(5)
        NOT NULL
        default '0',

    `hContactIsProfilePhoto`
        tinyint(1)
        default '0',

    `hContactIsDefaultProfilePhoto`
        tinyint(1)
        default '0',

    KEY `hContactId` (
        `hContactId`
    ),

    KEY `hContactId_hFileId` (
        `hContactId`,
        `hFileId`
    ),

    KEY `hContactId_hContactFileCategoryId` (
        `hContactId`,
        `hContactFileCategoryId`
    ),

    KEY `hContactId_hContactIsProfilePhoto` (
        `hContactId`,
        `hContactIsProfilePhoto`
    ),

    KEY `hContactId_hContactIsProfilePhoto_hContactIsDefaultProfilePhoto` (
        `hContactId`,
        `hContactIsProfilePhoto`,
        `hContactIsDefaultProfilePhoto`
    ),

    KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
