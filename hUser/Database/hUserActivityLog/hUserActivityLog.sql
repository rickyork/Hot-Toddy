CREATE TABLE `hUserActivityLog` (

    `hUserId`
        int(11)
        DEFAULT '0',

    `hUserActivity`
        varchar(255)
        DEFAULT '',

    `hUserActivityComponent`
        varchar(50)
        DEFAULT '',

    `hUserActivityTime`
        int(22)
        DEFAULT '0',

    `hUserActivityIP`
        varchar(50)
        DEFAULT '',

    KEY `hUserId` (
        `hUserId`
    ),

    KEY `hUserActivityTime` (
        `hUserActivityTime`
    )

) ENGINE=MyISAM  DEFAULT CHARSET=utf8;