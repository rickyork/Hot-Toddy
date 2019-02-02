CREATE TABLE `hTemplates` (

    `hTemplateId`
        int(11)
        NOT NULL
        auto_increment,

    `hTemplatePath`
        varchar(255)
        default NULL,

    `hTemplateName`
        varchar(255)
        default NULL,

    `hTemplateDescription`
        text
        NOT NULL,

    `hTemplateToggleVariables`
        tinyint(1)
        NOT NULL
        default '0',

    `hTemplateCascadeVariables`
        tinyint(1)
        NOT NULL
        default '0',

    `hTemplateMergeVariables`
        tinyint(1)
        NOT NULL
        default '0',

    PRIMARY KEY `hTemplateId` (
        `hTemplateId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;