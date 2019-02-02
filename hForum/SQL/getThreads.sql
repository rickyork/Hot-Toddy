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
           `hForumPosts`.`hForumPostIsApproved`
      FROM `hForumPosts`
INNER JOIN `hForumTopics`
        ON (`hForumTopics`.`hForumTopicId` = `hForumPosts`.`hForumTopicId`)
{checkPermissions?
 LEFT JOIN `hUserPermissions`
        ON `hUserPermissions`.`hFrameworkResourceKey` = `hForumTopics`.`hForumTopicId`
       
 LEFT JOIN `hUserPermissionsGroups`
        ON (`hUserPermissionsGroups`.`hUserPermissionsId` = `hUserPermissions`.`hUserPermissionsId`)
}
      WHERE `hForumPosts`.`hForumTopicId` = {hForumTopicId}
      {hForumPostId?
        AND (`hForumPosts`.`hForumPostId` = {hForumPostId} OR `hForumPosts`.`hForumPostRootId` = {hForumPostId})
      }
      {!$hForumPostId?
        AND `hForumPosts`.`hForumPostIsSticky` = {hForumPostIsSticky}
        AND `hForumPosts`.`hForumPostParentId` = {hForumPostParentId}
      }
      {hForumPostIsApproved?
        AND `hForumPosts`.`hForumPostIsApproved` = {hForumPostIsApproved}
      }
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
     ORDER BY {hForumPostId?`hForumPosts`.`hForumPostDate`}{!hForumPostId?`hForumPosts`.`hForumPostLastResponse`} {sort}
    {hForumPostLimit? LIMIT {hForumPostLimit}}
