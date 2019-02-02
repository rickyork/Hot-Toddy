CREATE TABLE `hContactAddressTemplates` (

    `hContactAddressTemplateId`
        int(11)
        NOT NULL
        auto_increment,

    `hContactAddressTemplate`
        varchar(255)
        NOT NULL
        default '',

    PRIMARY KEY `hContactAddressTemplateId` (
        `hContactAddressTemplateId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;