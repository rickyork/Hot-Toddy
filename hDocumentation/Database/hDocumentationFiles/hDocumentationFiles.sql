CREATE TABLE `hDocumentationFiles` (

    `hDocumentationFileId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hDocumentationFile`
        text
        NOT NULL,

    `hDocumentationFileTitle`
        varchar(100)
        NOT NULL
        DEFAULT '',

    `hDocumentationFileDescription`
        mediumtext
        NOT NULL,

    `hDocumentationFileClosingDescription`
        mediumtext
        NOT NULL,

    PRIMARY KEY `hDocumentationFileId` (
        `hDocumentationFileId`
    ),

    KEY `hDocumentationFile` (
        `hDocumentationFile` (255)
    ),

    KEY `hDocumentationFileTitle` (
        `hDocumentationFileTitle`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;
