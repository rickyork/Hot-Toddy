CREATE TABLE `hLocationStates` (
  `hLocationStateId`                    int(11)             NOT NULL auto_increment,
  `hLocationCountryId`                  int(11)             NOT NULL default '0',
  `hLocationStateCode`                  varchar(100)        DEFAULT NULL,
  `hLocationStateName`                  varchar(255)        DEFAULT NULL,
  `hLocationStateCreated`               int(32)             NOT NULL default '0',
  `hLocationStateLastModified`          int(32)             NOT NULL default '0',
  `hLocationStateLastModifiedBy`        int(11)             NOT NULL default '0',
  PRIMARY KEY  (`hLocationStateId`),
  KEY `hLocationCountryId` (`hLocationCountryId`),
  KEY `hLocationStateCode` (`hLocationStateCode`(2)),
  KEY `hLocationCountryId2` (`hLocationCountryId`,`hLocationStateCode`(2))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;