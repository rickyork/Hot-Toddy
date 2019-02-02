CREATE TABLE `hMailQueue` (
  `hMailQueueId` int(11) NOT NULL auto_increment,
  `hMailMIME`    MEDIUMTEXT NOT NULL DEFAULT '',
  `hMailLibrary` MEDIUMTEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (`hMailQueueId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
