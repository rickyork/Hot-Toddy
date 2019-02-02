CREATE TABLE `hTicketStatuses` (

    `hTicketStatusId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hTicketStatus`
        varchar(25)
        NULL,

    `hTicketStatusDescription`
        text,

    `hTicketStatusCreated`
        int(32)
        NOT NULL
        default '0',

    `hTicketStatusLastModified`
        int(32)
        NOT NULL
        default '0',

    `hTicketStatusLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hTicketStatusId` (
        `hTicketStatusId`
    )

)
ENGINE=MyISAM
DEFAULT CHARSET=utf8;