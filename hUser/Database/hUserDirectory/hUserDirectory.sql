CREATE TABLE `hUserDirectory` (

    `hUserId`
        INT(11)
        default '0',

    `hUserDirectoryId`
        INT(64)
        default NULL,

    UNIQUE KEY `hUserDirectoryId` (
        `hUserDirectoryId`
    ),

    UNIQUE KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;