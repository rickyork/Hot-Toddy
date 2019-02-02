CREATE TABLE `hTickets` (

    `hTicketId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hTicketSubject`
        varchar(255)
        NULL,

    `hTicketBody`
        mediumtext,

    `hTicketIsOpen`
        tinyint(1)
        NOT NULL
        default '0',

    `hTicketSeverity`
        tinyint(3)
        NOT NULL
        default '0',

    `hTicketCreated`
        int(32)
        NOT NULL
        default '0',

    `hTicketLastModified`
        int(32)
        NOT NULL
        default '0',

    `hTicketLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hTicketId` (
        `hTicketId`
    ),

    KEY `hTicketId_hTicketCreated` (
        `hTicketId`,
        `hTicketCreated`
    ),

    KEY `hTicketId_Time` (
        `hTicketId`,
        `hTicketCreated`,
        `hTicketLastModified`
    )

)
ENGINE=MyISAM
DEFAULT CHARSET=utf8;