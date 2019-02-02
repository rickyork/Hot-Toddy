DELETE 
  FROM `hForumPosts` 
 WHERE `hForumTopicId` NOT IN (SELECT `hForumTopicId` FROM `hForumTopics`)
