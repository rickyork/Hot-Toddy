CREATE TABLE `hLists` (

    `hListId`
        int(11)
        NOT NULL
        auto_increment,

    `hListName`
        text
        NOT NULL,

    `hListCategoryId`
        int(11)
        NOT NULL
        default '0',

    `hListSortIndex`
        int(5)
        NOT NULL
        default '0',

    PRIMARY KEY `hListId` (
        `hListId`
    ),

    KEY `hListCategoryId` (
        `hListCategoryId`
    ),

    KEY `hListId_hListCategoryId` (
        `hListId`,
        `hListCategoryId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;