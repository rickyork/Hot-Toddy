CREATE TABLE `hDocumentationMethodArguments` (

    `hDocumentationMethodArgumentId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hDocumentationMethodId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodArgumentIndex`
        tinyint(2)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodArgumentName`
        varchar(100)
        NOT NULL
        DEFAULT '',

    `hDocumentationMethodArgumentDescription`
        text
        NOT NULL,

    `hDocumentationMethodArgumentType`
        varchar(50)
        NOT NULL
        DEFAULT '',

    `hDocumentationMethodArgumentDefault`
        varchar(100)
        NOT NULL
        DEFAULT '',

    `hDocumentationMethodArgumentIsOptional`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodArgumentByReference`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    PRIMARY KEY `hDocumentationMethodArgumentId` (
        `hDocumentationMethodArgumentId`
    ),

    KEY `hDocumentationMethodId` (
        `hDocumentationMethodId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;