CREATE TABLE IF NOT EXISTS `hContactVariables` (

    `hContactId`
        int(11)
        NOT NULL
        default '0',

    `hContactVariable`
        varchar(50)
        NOT NULL
        default '',

    `hContactValue`
        text
        NOT NULL,

    KEY `hContactId_hContactVariable` (
        `hContactId`,
        `hContactVariable`
    ),

    KEY `hContactVariable` (
        `hContactVariable`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;