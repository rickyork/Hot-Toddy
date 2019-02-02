CREATE TABLE `hLocationCountries` (
  `hLocationCountryId`                  int(11)         NOT NULL auto_increment,
  `hLocationCountryName`                varchar(64)     default NULL,
  `hLocationCountryISO2`                varchar(2)      default NULL,
  `hLocationCountryISO3`                varchar(3)      default NULL,
  `hContactAddressTemplateId`           int(4)          NOT NULL default '0',
  `hLocationStateLabel`                 varchar(25)     NOT NULL default '',
  `hLocationUseStateCode`               tinyint(1)      NOT NULL default '0',
  `hLocationCountryCreated`             int(32)         NOT NULL default '0',
  `hLocationCountryLastModified`        int(32)         NOT NULL default '0',
  `hLocationCountryLastModifiedBy`      int(11)         NOT NULL default '0',
  PRIMARY KEY  (`hLocationCountryId`),
  KEY `IdX_COUNTRIES_NAME` (`hLocationCountryName`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=241;
