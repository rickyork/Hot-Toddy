     SELECT DISTINCT
            `hCategories`.`hCategoryId`,
            `hCategories`.`hCategoryName`,
            `hCategories`.`hFileIconId`
       FROM `hCategories`
{checkPermissions?
  LEFT JOIN `hUserPermissions`
         ON `hCategories`.`hCategoryId` = `hUserPermissions`.`hFrameworkResourceKey` AND `hUserPermissions`.`hFrameworkResourceId` = 20
  LEFT JOIN `hUserPermissionsGroups`
         ON `hUserPermissions`.`hUserPermissionsId` = `hUserPermissionsGroups`.`hUserPermissionsId`
}
      WHERE `hCategories`.`hCategoryParentId` = {categoryId}
        AND `hCategories`.`hCategoryId` > -1
    {checkPermissions?
       AND (
           `hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%'
        {userId?
           OR (`hCategories`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
           OR (
             (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
             {userGroups[]?
               {userGroupId? OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')}
             }
           )
         }
       )
    }
   ORDER BY `hCategories`.`hCategorySortIndex` ASC
