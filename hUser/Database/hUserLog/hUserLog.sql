CREATE TABLE `hUserLog` (

    `hUserId`
        int(11)
        NOT NULL
        default '0',

    `hUserLoginCount`
        int(11)
        NOT NULL
        default '0',

    `hUserFailedLoginCount`
        int(11)
        NOT NULL
        default '0',

    `hUserCreated`
        int(32)
        NOT NULL
        default '0',

    `hUserLastLogin`
        int(32)
        NOT NULL
        default '0',

    `hUserLastFailedLogin`
        int(32)
        NOT NULL
        default '0',

    `hUserLastModified`
        int(32)
        NOT NULL
        default '0',

    `hUserLastModifiedBy`
        int(11)
        NOT NULL
        default '0',

    `hUserReferredBy`
        int(11)
        NOT NULL,

    `hUserRegistrationTrackingId`
        int(11)
        NOT NULL,

    `hFileId`
        int(11)
        NOT NULL,

    PRIMARY KEY `hUserId` (
        `hUserId`
    )

) ENGINE=MyISAM DEFAULT CHARSET=utf8;