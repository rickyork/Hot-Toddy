CREATE TABLE `hUserAuthenticationLog` (

    `hUserId`
        INT(11)
        NOT NULL
        default '0',

    `hUserName`
        VARCHAR(50)
        default NULL,

    `hUserEmail`
        VARCHAR(50)
        default NULL,

    `hUserAuthenticationError`
        MEDIUMTEXT
        NOT NULL,

    `hUserAuthenticationTime`
        INT(32)
        NOT NULL
        default '0',

    KEY `hUserId` (
        `hUserId`
    ),

    KEY `hUserName` (
        `hUserName`
    ),

    KEY `hUserEmail` (
        `hUserEmail`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;