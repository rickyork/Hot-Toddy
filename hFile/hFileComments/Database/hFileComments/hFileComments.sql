CREATE TABLE `hFileComments` (

    `hFileCommentId`
        int(11)
        NOT NULL
        auto_increment,

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hFileCommentName`
        varchar(255)
        NOT NULL
        default '',

    `hFileCommentEmail`
        varchar(255)
        NOT NULL
        default '',

    `hFileCommentWebsite`
        varchar(255)
        NOT NULL
        default '',

    `hFileComment`
        text
        NOT NULL,

    `hFileCommentPosted`
        int(22)
        NOT NULL
        default '0',

    `hFileCommentIsApproved`
        tinyint(1)
        NOT NULL
        default '1',

    `hFileCommentIsAuthor`
        tinyint(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hFileCommentId` (
        `hFileCommentId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;