    SELECT 
  DISTINCT SQL_CALC_FOUND_ROWS
           `hForumTopics`.`hForumTopicId`,
           `hForumTopics`.`hForumTopic`,
           `hForumTopics`.`hForumTopicDescription`,
           `hForumTopics`.`hForumTopicIsLocked`,
           `hForumTopics`.`hForumTopicIsModerated`,
           `hForumTopics`.`hForumTopicLastResponse`,
           `hForumTopics`.`hForumTopicLastResponseBy`,
           `hForumTopics`.`hForumTopicResponseCount`
      FROM `hForumTopics`
{checkPermissions?
 LEFT JOIN `hUserPermissions`
        ON `hUserPermissions`.`hFrameworkResourceKey` = `hForumTopics`.`hForumTopicId`
 LEFT JOIN `hUserPermissionsGroups`
        ON `hUserPermissionsGroups`.`hUserPermissionsId` = `hUserPermissions`.`hUserPermissionsId`
}
     WHERE `hForumTopics`.`hForumId` = {forumId}
      {checkPermissions?
        AND `hUserPermissions`.`hFrameworkResourceId` = 4
        AND (
             (`hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%')
          {userId?
            OR (`hForumTopics`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
            OR (
               (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
               {userGroups[]?
                 {userGroupId?OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')}
               }
            )
          }
        )
      }
  ORDER BY `hForumTopics`.`hForumTopicSortIndex` ASC
