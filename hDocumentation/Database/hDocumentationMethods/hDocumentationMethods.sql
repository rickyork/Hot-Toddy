CREATE TABLE `hDocumentationMethods` (

    `hDocumentationMethodId`
        int(11)
        NOT NULL
        AUTO_INCREMENT,

    `hDocumentationFileId`
        int(11)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodName`
        varchar(100)
        NOT NULL
        DEFAULT '',

    `hDocumentationMethodSignature`
        text
        NOT NULL,

    `hDocumentationMethodBody`
        mediumtext
        NOT NULL,

    `hDocumentationMethodDescription`
        mediumtext
        NOT NULL,

    `hDocumentationMethodIsProtected`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodIsPrivate`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodIsStatic`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodIsOverloaded`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodReturnsReference`
        tinyint(1)
        NOT NULL
        DEFAULT '0',

    `hDocumentationMethodReturnType`
        varchar(100)
        NOT NULL
        DEFAULT '',

    `hDocumentationMethodReturnDescription`
        text
        NOT NULL,

    PRIMARY KEY `hDocumentationMethodId` (
        `hDocumentationMethodId`
    ),

    KEY `hDocumentationFileId` (
        `hDocumentationFileId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;