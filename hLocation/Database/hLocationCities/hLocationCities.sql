CREATE TABLE `hLocationCities` (
  `hLocationCityId`                 int(32)         NOT NULL AUTO_INCREMENT,
  `hLocationCity`                   varchar(255)    NOT NULL default '0',
  `hLocationCountyId`               int(11)         NOT NULL default '0',
  `hLocationStateId`                int(11)         NOT NULL default '0',
  `hLocationCountryId`              int(11)         NOT NULL default '0',
  `hLocationCityLatitude`           float(32,12)    NOT NULL default '0',
  `hLocationCityLongitude`          float(32,12)    NOT NULL default '0',
  `hLocationCityCreated`            int(32)         NOT NULL default '0',
  `hLocationCityLastModified`       int(32)         NOT NULL default '0',
  `hLocationCityLastModifiedBy`     int(11)         NOT NULL default '0',
  PRIMARY KEY  (`hLocationCityId`),
  KEY `hLocationCity_hLocationStateId_hLocationCountryId` (`hLocationCity`, `hLocationStateId`, `hLocationCountryId`),
  KEY `hLocationCountyId` (`hLocationCountyId`),
  KEY `hLocationStateId` (`hLocationStateId`),
  KEY `hLocationCountryId` (`hLocationCountryId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;