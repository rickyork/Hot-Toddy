CREATE TABLE `hForumTopics` (

    `hForumTopicId`
        int(11)
        NOT NULL
        auto_increment,

    `hForumId`
        int(11)
        NOT NULL
        default '0',

    `hForumTopic`
        text
        NOT NULL,

    `hForumTopicDescription`
        text
        NOT NULL,

    `hForumTopicSortIndex`
        int(2)
        NOT NULL
        default '0',

    `hForumTopicIsLocked`
        tinyint(1)
        NOT NULL
        default '0',

    `hForumTopicIsModerated`
        tinyint(1)
        NOT NULL
        default '0',

    `hForumTopicLastResponse`
        int(32)
        NOT NULL
        default '0',

    `hForumTopicLastResponseBy`
        int(11)
        NOT NULL
        default '0',

    `hForumTopicResponseCount`
        int(11)
        NOT NULL
        default '0',

    `hUserId`
         int(11)
         NOT NULL
         default '0',

    `hForumTopicCreated`
        int(32)
        NOT NULL
        default '0',

    `hForumTopicLastModified`
        int(32)
        NOT NULL
        default '0',

    `hForumTopicLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hForumTopicId` (
        `hForumTopicId`
    ),

    KEY `hForumId` (
        `hForumId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;