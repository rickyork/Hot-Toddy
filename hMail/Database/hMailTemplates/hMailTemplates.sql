CREATE TABLE `hMailTemplates` (
  `hMailTemplateId`           int(11) NOT NULL auto_increment,
  `hMailTemplateName`         varchar(50) default NULL,
  `hMailTemplateDescription`  text    NOT NULL,
  `hMailSubject`              varchar(175) default NULL,
  `hMailTo`                   text    NOT NULL,
  `hMailCc`                   text    NOT NULL,
  `hMailBcc`                  text    NOT NULL,
  `hMailFrom`                 text    NOT NULL,
  `hMailReplyTo`              text    NOT NULL,
  `hMailHTML`                 text    NOT NULL,
  `hMailText`                 text    NOT NULL,
  `hMailJSONLastModified`     int(32) NOT NULL,
  PRIMARY KEY  (`hMailTemplateId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;