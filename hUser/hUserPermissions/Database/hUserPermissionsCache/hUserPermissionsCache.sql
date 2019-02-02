CREATE TABLE `hUserPermissionsCache` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hUserPermissionsType`
        varchar(30)
        NOT NULL
        default '',

    `hUserPermissionsVariable`
        varchar(255)
        NOT NULL
        default '',

    `hUserPermissionsValue`
        tinyint(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hUserId_hUserPermissionsType_hUserPermissionsVariable` (
        `hUserId`,
        `hUserPermissionsType`,
        `hUserPermissionsVariable`
    ),

    KEY `hUserId` (
        `hUserId`
    ),

    KEY `hUserId_hUserPermissionsType` (
        `hUserId`,
        `hUserPermissionsType`
    ),

    KEY `hUserId_hUserPermissionsVariable` (
        `hUserId`,
        `hUserPermissionsVariable`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;