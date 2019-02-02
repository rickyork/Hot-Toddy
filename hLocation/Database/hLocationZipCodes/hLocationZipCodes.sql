CREATE TABLE `hLocationZipCodes` (
  `hLocationZipCode`                    mediumint(5)        NOT NULL DEFAULT '0',
  `hLocationStateCode`                  enum('AA','AE','AK','AL','AP','AR','AS','AZ','CA','CO','CT','DC','DE','FL','GA','GU','HI','IA','Id','IL','IN','KS','KY','LA','MA','MD','ME','MI','MN','MO','MP','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY','OH','OK','OR','PA','PR','PW','RI','SC','SD','TN','TX','UT','VA','VI','VT','WA','WI','WV','WY') NOT NULL,
  `hLocationCity`                       varchar(35)         NOT NULL DEFAULT '',
  `hLocationCounty`                     varchar(35)         NOT NULL DEFAULT '',
  `hLocationSequenceNumber`             enum('1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31') NOT NULL,
  `hLocationAcceptable`                 enum('A','N','P')   NOT NULL,
  `hLocationZipCodeLatitude`            float(32,12)        NOT NULL DEFAULT '0',
  `hLocationZipCodeLongitude`           float(32,12)        NOT NULL DEFAULT '0',
  `hLocationZipCodeTimeZone`            tinyint(3)          NOT NULL DEFAULT '0',
  `hLocationZipCodeHasDaylightSavings`  tinyint(1)          NOT NULL DEFAULT '0',
  `hLocationZipCodeCreated`             int(32)             NOT NULL DEFAULT '0',
  `hLocationZipCodeLastModified`        int(32)             NOT NULL DEFAULT '0',
  `hLocationZipCodeLastModifiedBy`      int(11)             NOT NULL DEFAULT '0',
  KEY `hLocationZipCode` (`hLocationZipCode`),
  KEY `hLocationZipCode2` (`hLocationZipCode`,`hLocationStateCode`),
  KEY `hLocationStateCode` (`hLocationStateCode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
