CREATE TABLE `hLocationCounties` (
  `hLocationCountyId`               int(11)         NOT NULL auto_increment,
  `hLocationCounty`                 varchar(100)    NOT NULL default '',
  `hLocationStateId`                int(11)         NOT NULL default '0',
  `hLocationCountyCreated`          int(32)         NOT NULL default '0',
  `hLocationCountyLastModified`     int(32)         NOT NULL default '0',
  `hLocationCountyLastModifiedBy`   int(11)         NOT NULL default '0',
  PRIMARY KEY  (`hLocationCountyId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;