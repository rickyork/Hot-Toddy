CREATE TABLE `hTicketsToStatuses` (

    `hTicketId`
        int(11)
        NOT NULL
        default '0',

    `hTicketStatusId`
        int(11)
        NOT NULL
        default '0',

    KEY `hTicketId` (
        `hTicketId`
    ),

    KEY `hTicketStatusId` (
        `hTicketStatusId`
    ),

    KEY `hTicketId_hTicketStatusId` (
        `hTicketId`,
        `hTicketStatusId`
    )
)
ENGINE=MyISAM
DEFAULT CHARSET=utf8;