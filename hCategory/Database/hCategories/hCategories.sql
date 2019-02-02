CREATE TABLE `hCategories` (

    `hCategoryId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hCategoryName`
        varchar(255)
        DEFAULT NULL,

    `hFileIconId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hCategoryParentId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hCategoryRootId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hCategoryLastModified`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCategoryLastModifiedBy`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hCategoryCreated`
        int(32)
        NOT NULL
        DEFAULT '0',

    `hCategorySortIndex`
        tinyint(2)
        NOT NULL
        DEFAULT '0',

    PRIMARY KEY `hCategoryId` (
        `hCategoryId`
    ),

    KEY `hCategoryParentId` (
        `hCategoryParentId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;