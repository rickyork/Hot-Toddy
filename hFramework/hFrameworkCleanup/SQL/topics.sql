DELETE 
  FROM `hForumTopics` 
 WHERE `hForumId` NOT IN (SELECT `hForumId` FROM `hForums`)
