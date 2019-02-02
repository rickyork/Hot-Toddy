CREATE TABLE `hForums` (

    `hForumId`
        int(11)
        NOT NULL
        auto_increment,

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hForum`
        text
        NOT NULL,

    `hForumSortIndex`
        int(2)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hForumCreated`
        int(32)
        NOT NULL
        default '0',

    `hForumLastModified`
        int(32)
        NOT NULL
        default '0',

    `hForumLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hForumId` (
        `hForumId`
    ),

    KEY `hFileId` (
        `hFileId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;