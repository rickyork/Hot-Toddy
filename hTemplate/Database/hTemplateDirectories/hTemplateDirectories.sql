CREATE TABLE `hTemplateDirectories` (

    `hTemplateId`
        int(11)
        NOT NULL
        default '0',

    `hDirectoryId`
        int(11)
        NOT NULL
        default '0',

    KEY `hTemplateId` (
        `hTemplateId`
    ),

    UNIQUE KEY `hDirectoryId` (
        `hDirectoryId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;