   SELECT DISTINCT   
          `hCategories`.`hCategoryId`,
          `hCategories`.`hCategoryId` AS `hFileInterfaceObjectId`,
          `hCategories`.`hCategoryName`,
          `hCategories`.`hCategoryParentId`,
          `hCategories`.`hCategoryLastModified`,
          `hCategories`.`hCategoryCreated`,
          `hCategories`.`hCategorySortIndex` AS `hCategoryFileSortIndex`,
          (SELECT COUNT(*) FROM `hCategories` WHERE `hCategoryParentId` = `hFileInterfaceObjectId`) AS `hDirectoryCount`,
          (SELECT COUNT(*) FROM `hCategoryFiles` WHERE `hCategoryId` = `hFileInterfaceObjectId`) AS `hFileCount`
     FROM `hCategories`
{checkPermissions?
LEFT JOIN `hUserPermissions`
       ON (`hCategories`.`hCategoryId` = `hUserPermissions`.`hFrameworkResourceKey`)
LEFT JOIN `hUserPermissionsGroups`
       ON (`hUserPermissions`.`hUserPermissionsId` = `hUserPermissionsGroups`.`hUserPermissionsId`)
}
{checkWorldPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`hCategories`.`hCategoryId` = `hUserPermissions`.`hFrameworkResourceKey`)
}
    WHERE `hCategories`.`hCategoryParentId` = {categoryId}
    {checkPermissions?
       AND `hUserPermissions`.`hFrameworkResourceId` = 20
       AND (
              (`hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%')
        {userId?
           OR (`hCategories`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
           OR (
             (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
             {userGroups[]?
               OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
             }
           )
         }
       )
    }
    {checkWorldPermissions?
      AND `hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%'
    }
ORDER BY `hCategories`.`hCategorySortIndex` ASC
