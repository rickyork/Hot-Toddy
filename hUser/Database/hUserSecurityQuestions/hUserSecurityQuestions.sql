CREATE TABLE `hUserSecurityQuestions` (

    `hUserSecurityQuestionId`
        INT(11)
        NOT NULL
        auto_increment,

    `hUserSecurityQuestion`
        VARCHAR(75)
        default NULL,

    PRIMARY KEY `hUserSecurityQuestionId` (
        `hUserSecurityQuestionId`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;