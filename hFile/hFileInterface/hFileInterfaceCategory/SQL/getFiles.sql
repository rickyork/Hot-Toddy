    SELECT DISTINCT
           `hFiles`.`hFileId`,
           `hFiles`.`hDirectoryId`,
           `hFiles`.`hFileName`,
           `hFiles`.`hFileLastModified`,
           `hFiles`.`hFileCreated`,
           `hFileDocuments`.`hFileTitle`,
           `hFileDocuments`.`hFileDescription`,
           `hFileProperties`.`hFileIconId`,
           `hFileProperties`.`hFileMIME`,
           `hFileProperties`.`hFileSize`,
           `hFileProperties`.`hFileDownload`,
           `hFileProperties`.`hFileLabel`,
           LENGTH(`hFileDocuments`.`hFileDocument`) AS `hFileDocumentSize`,
           REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`,
           `hCategoryFiles`.`hCategoryFileSortIndex`

      FROM `hCategoryFiles`
INNER JOIN `hFiles`
       ON (`hCategoryFiles`.`hFileId` = `hFiles`.`hFileId`)
INNER JOIN `hFileDocuments`
        ON (`hFiles`.`hFileId` = `hFileDocuments`.`hFileId`)
{checkPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`hFiles`.`hFileId` = `hUserPermissions`.`hFrameworkResourceKey`)
 LEFT JOIN `hUserPermissionsGroups`
        ON (`hUserPermissions`.`hUserPermissionsId` = `hUserPermissionsGroups`.`hUserPermissionsId`)
}
{checkWorldPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`hFiles`.`hFileId` = `hUserPermissions`.`hFrameworkResourceKey`)
}
 LEFT JOIN `hFileProperties`
        ON (`hFiles`.`hFileId` = `hFileProperties`.`hFileId`)
     WHERE `hCategoryFiles`.`hCategoryId` = {categoryId}
     {checkPermissions?
       AND `hUserPermissions`.`hFrameworkResourceId` = 1
       AND (
              (`hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%')
        {userId?
           OR (`hFiles`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
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
  ORDER BY `hCategoryFiles`.`hCategoryFileSortIndex` ASC
