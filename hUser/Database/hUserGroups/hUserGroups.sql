CREATE TABLE `hUserGroups` (

    `hUserGroupId`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    KEY `hUserGroupId` (
        `hUserGroupId`
    ),

    KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;