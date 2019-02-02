CREATE TABLE `hTicketResponses` (

    `hTicketId`
        int(11)
        NOT NULL
        default '0',

    `hTicketResponseId`
        int(11)
        NOT NULL
        auto_increment,

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hTicketResponse`
        mediumtext,

    `hTicketResponseCreated`
        int(32)
        NOT NULL
        default '0',

    `hTicketResponseLastModified`
        int(32)
        NOT NULL
        default '0',

    `hTicketResponseLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    PRIMARY KEY `hTicketResponseId` (
        `hTicketResponseId`
    ),

    KEY `hTicketId` (
        `hTicketId`
    ),

    KEY `hTicketId_hTicketResponseCreated` (
        `hTicketId`,
        `hTicketResponseCreated`
    )

)
ENGINE=MyISAM
DEFAULT CHARSET=utf8;