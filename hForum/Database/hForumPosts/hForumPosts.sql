CREATE TABLE `hForumPosts` (

    `hForumPostId`
        int(11)
        NOT NULL
        auto_increment,

    `hForumTopicId`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hForumPostSubject`
        text
        NOT NULL,

    `hForumPost`
        text
        NOT NULL,

    `hForumPostInputMethod`
        varchar(25)
        NOT NULL
        default '',

    `hForumPostParentId`
        int(11)
        NOT NULL
        default '0',

    `hForumPostRootId`
        int(11)
        NOT NULL
        default '0',

    `hForumPostIsSticky`
        tinyint(1)
        NOT NULL
        default '0',

    `hForumPostIsLocked`
        tinyint(1)
        NOT NULL
        default '0',

    `hForumPostDate`
        int(32)
        NOT NULL
        default '0',

    `hForumPostLastResponse`
        int(32)
        NOT NULL
        default '0',

    `hForumPostLastResponseBy`
        int(11)
        NOT NULL
        default '0',

    `hForumPostResponseCount`
        int(5)
        NOT NULL
        default '0',

    `hForumPostCreated`
        int(32)
        NOT NULL
        default '0',

    `hForumPostLastModified`
        int(32)
        NOT NULL
        default '0',

    `hForumPostLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    `hForumPostIsApproved`
        tinyint(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hForumPostId` (
        `hForumPostId`
    ),

    KEY `hForumTopicId` (
        `hForumTopicId`
    ),

    KEY `hForumPostDate` (
        `hForumPostDate`
    ),

    KEY `hForumPostRootId` (
        `hForumPostRootId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;