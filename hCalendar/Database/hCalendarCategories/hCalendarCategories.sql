CREATE TABLE `hCalendarCategories` (

    `hCalendarCategoryId`
        int(11)
        NOT NULL
        auto_increment,

    `hCalendarCategoryName`
        varchar(255)
        default NULL,

    PRIMARY KEY `hCalendarCategoryId` (
        `hCalendarCategoryId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25;
