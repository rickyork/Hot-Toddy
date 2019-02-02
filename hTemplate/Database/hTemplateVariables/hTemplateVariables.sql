CREATE TABLE `hTemplateVariables` (

    `hTemplateId`
        int(11)
        NOT NULL
        default '0',

    `hTemplateVariable`
        varchar(255)
        default NULL,

    `hTemplateValue`
        text
        NOT NULL,

    KEY `hTemplateId` (
        `hTemplateId`
    ),

    KEY `hTemplateId_hTemplateVariable` (
        `hTemplateId`,
        `hTemplateVariable`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;