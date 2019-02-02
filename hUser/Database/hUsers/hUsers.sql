CREATE TABLE `hUsers` (

    `hUserId`
        INT(11)
        NOT NULL
        auto_increment,

    `hUserName`
        VARCHAR(255)
        default NULL,

    `hUserEmail`
        VARCHAR(255)
        default NULL,

    `hUserPassword`
        CHAR(35)
        NOT NULL,

    `hUserConfirmation`
        VARCHAR(35)
        default NULL,

    `hUserSecurityQuestionId`
        INT(3)
        NOT NULL
        default '0',

    `hUserSecurityAnswer`
        VARCHAR(75)
        default NULL,

    `hUserIsActivated`
        TINYINT(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hUserId` (
        `hUserId`
    ),

    UNIQUE KEY `hUserEmail` (
        `hUserEmail`
    ),

    UNIQUE KEY `hUserName` (
        `hUserName`
    ),

    KEY `hUserName_hUserEmail` (
        `hUserName` (100),
        `hUserEmail` (100)
    ),

    KEY `hUserId_hUserName` (
        `hUserId`,
        `hUserName`
    ),

    FULLTEXT KEY `FullText_hUserName_hUserEmail` (
        `hUserName`,
        `hUserEmail`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;