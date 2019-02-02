    SELECT
  DISTINCT `hFiles`.`hFileId`,
           `hFiles`.`hFileName`,
           `hFileDocuments`.`hFileTitle`,
           `hFileDocuments`.`hFileDescription`,
           REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`,
           (SELECT `hFileValue` FROM `hFileVariables` WHERE `hFileVariable` = 'hFileHeadingTitle' AND `hFileId` = `fileId`) AS `hFileHeadingTitle`
      FROM `hFiles`
INNER JOIN `hCategoryFiles`
        ON `hCategoryFiles`.`hFileId` = `hFiles`.`hFileId`
INNER JOIN `hFileDocuments`
        ON `hFileDocuments`.`hFileId` = `hFiles`.`hFileId`
{checkPermissions?
 LEFT JOIN `hUserPermissions`
        ON `hUserPermissions`.`hFrameworkResourceKey` = `hFiles`.`hFileId` AND `hUserPermissions`.`hFrameworkResourceId` = 1
 LEFT JOIN `hUserPermissionsGroups`
        ON `hUserPermissionsGroups`.`hUserPermissionsId` = `hUserPermissions`.`hUserPermissionsId`
}
     WHERE `hCategoryFiles`.`hCategoryId` > -1
    {categoryId?
       AND `hCategoryFiles`.`hCategoryId` = {categoryId}
    }
    {checkPermissions?
       AND (
            `hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%'
         OR (`hFiles`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
         OR (
            (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
            {userGroups[]?
              {userGroupId? OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')}
            }
         )
       )
    }
   ORDER BY {categoryFileSort}
