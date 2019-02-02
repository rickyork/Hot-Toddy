CREATE TABLE `hContacts` (

    `hContactAddressBookId`
        int(11)
        NOT NULL
        default '0',

    `hContactId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hContactFirstName`
        varchar(100)
        NOT NULL
        default '',

    `hContactMiddleName`
        varchar(100)
        NOT NULL
        default '',

    `hContactLastName`
        varchar(100)
        NOT NULL
        default '',

    `hContactDisplayName`
        varchar(100)
        NOT NULL
        default '',

    `hContactNickName`
        varchar(25)
        NOT NULL
        default '',

    `hContactWebsite`
        varchar(255)
        NOT NULL
        default '',

    `hContactCompany`
        varchar(200)
        NOT NULL
        default '',

    `hContactTitle`
        varchar(100)
        NOT NULL
        default '',

    `hContactDepartment`
        varchar(100)
        NOT NULL
        default '',

    `hContactGender`
        tinyint(1)
        NOT NULL
        default '-1',  # 0 = female, 1 = male

    `hContactDateOfBirth`
        int(32)
        NOT NULL
        default '0',

    `hContactCreated`
        int(32)
        NOT NULL
        default '0',

    `hContactLastModified`
        int(32)
        NOT NULL
        default '0',

    `hContactLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hContactId` (
        `hContactId`
    ),

    KEY `hContactAddressBookId`  (
        `hContactAddressBookId`
    ),

    KEY `hContactAddressBookId_hContactFirstName_hContactLastName` (
        `hContactAddressBookId`,
        `hContactFirstName` (15),
        `hContactLastName` (15)
    ),

    KEY `hContactAddressBookId_hUserId` (
        `hContactAddressBookId`,
        `hUserId`
    ),

    FULLTEXT KEY `hContactFirstNameLastNameCompanyTitleDepartment` (
        `hContactFirstName`,
        `hContactLastName`,
        `hContactCompany`,
        `hContactTitle`,
        `hContactDepartment`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;