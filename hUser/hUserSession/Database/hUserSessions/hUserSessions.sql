CREATE TABLE `hUserSessions` (
  `hUserSessionId` varchar(32) NOT NULL default '',
  `hUserSessionData` text NOT NULL,
  `hUserSessionLastAccessed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `hUserSessionExpires` int(22) NOT NULL,
  KEY `hUserSessionId` (`hUserSessionId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;