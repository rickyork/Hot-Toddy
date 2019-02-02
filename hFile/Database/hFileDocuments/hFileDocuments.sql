CREATE TABLE `hFileDocuments` (

    `hFileId`
        int(11)
        NOT NULL
        default '0',

    `hFileDocumentId`
        int(11)
        NOT NULL
        auto_increment,

    `hFileDescription`
        text
        NOT NULL,

    `hFileKeywords`
        text
        NOT NULL,

    `hFileTitle`
        text
        NOT NULL,

    `hFileDocument`
        mediumtext
        NOT NULL,

    `hFileComments`
        text
        NOT NULL,

    `hFileDocumentCreated`
        int(32)
        NOT NULL
        default '0',

    `hFileDocumentLastModified`
        int(32)
        NOT NULL
        default '0',

    PRIMARY KEY  (
        `hFileDocumentId`
    ),

    UNIQUE KEY `hFileId` (
        `hFileId`
    ),

    FULLTEXT KEY `hFileDocument_hFileTitle_hFileKeywords_hFileDescription` (
        `hFileDocument`,
        `hFileTitle`,
        `hFileKeywords`,
        `hFileDescription`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;