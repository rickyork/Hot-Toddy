CREATE TABLE `hContactAddressBooks` (

    `hContactAddressBookId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hContactAddressBookName`
        varchar(255)
        NOT NULL
        default '',

    `hPlugin`
        varchar(255)
        NOT NULL
        default '',

    `hContactAddressBookIsDefault`
        tinyint(1)
        NOT NULL
        default '0',

    `hContactAddressBookCreated`
        int(32)
        NOT NULL
        default '0',

    `hContactAddressBookLastModified`
        int(32)
        NOT NULL
        default '0',

    `hContactAddressBookLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hContactAddressBookId` (
        `hContactAddressBookId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;