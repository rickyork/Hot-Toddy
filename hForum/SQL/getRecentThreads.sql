     SELECT
   DISTINCT SQL_CALC_FOUND_ROWS
            `hForumPosts`.`hForumPostId`,
            `hForumPosts`.`hUserId`,
            `hForumPosts`.`hForumPostSubject`,
            `hForumPosts`.`hForumPost`,
            `hForumPosts`.`hForumPostInputMethod`,
            `hForumPosts`.`hForumPostParentId`,
            `hForumPosts`.`hForumPostRootId`,
            `hForumPosts`.`hForumPostIsSticky`,
            `hForumPosts`.`hForumPostIsLocked`,
            `hForumPosts`.`hForumPostDate`,
            `hForumPosts`.`hForumPostLastResponse`,
            `hForumPosts`.`hForumPostLastResponseBy`,
            `hForumPosts`.`hForumPostResponseCount`,
            `hForumPosts`.`hForumPostIsApproved`,
            `hForumTopics`.`hForumTopicId`,
            `hForumTopics`.`hForumTopic`
       FROM `hForumPosts`
 INNER JOIN `hForumTopics`
         ON `hForumTopics`.`hForumTopicId` = `hForumPosts`.`hForumTopicId`
 INNER JOIN `hForums`
         ON `hForums`.`hForumId` = `hForumTopics`.`hForumId`
{checkPermissions?
 INNER JOIN `hUserPermissions`
         ON `hUserPermissions`.`hFrameworkResourceKey` = `hForumTopics`.`hForumTopicId`
  LEFT JOIN `hUserPermissionsGroups`
         ON `hUserPermissionsGroups`.`hUserPermissionsId` = `hUserPermissions`.`hUserPermissionsId`
}
      WHERE `hForumPosts`.`hForumPostRootId` = 0
        AND `hForumPosts`.`hForumPostLastResponse` > {time}
        AND `hForums`.`hFileId` = {hFileId}
{approved? AND `hForumPosts`.`hForumPostIsApproved` = 1}
    {checkPermissions?
      AND `hUserPermissions`.`hFrameworkResourceId` = 4
      AND (
            (`hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%')
         {userId?
           OR (`hForumPosts`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
           OR (
             (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
             {userGroups[]?
               {userGroupId?OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')}
             }
           )
         }
      )
    }
   GROUP BY `hForumPosts`.`hForumTopicId`,
            `hForumPosts`.`hForumPostDate` {sort}
      LIMIT {limit}