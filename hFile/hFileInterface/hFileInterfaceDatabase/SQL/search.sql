    SELECT SQL_CALC_FOUND_ROWS
           DISTINCT
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
           REPLACE(CONCAT((SELECT `hDirectories`.`hDirectoryPath` FROM `hDirectories` WHERE `hDirectories`.`hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`,
     MATCH (
        `hFileDocuments`.`hFileDescription`,
        `hFileDocuments`.`hFileKeywords`,
        `hFileDocuments`.`hFileTitle`,
        `hFileDocuments`.`hFileDocument`
     )
   AGAINST ('{hFileSearchTerms}' IN BOOLEAN MODE) AS `hFileSearchRelevance`
      FROM `hFiles`
INNER JOIN `hFileDocuments`
        ON (`hFiles`.`hFileId` = `hFileDocuments`.`hFileId`)
INNER JOIN `hDirectories`
        ON (`hFiles`.`hDirectoryId` = `hDirectories`.`hDirectoryId`)
 LEFT JOIN `hFileProperties`
        ON (`hFiles`.`hFileId` = `hFileProperties`.`hFileId`)
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
     WHERE 
     MATCH (
        `hFiles`.`hFileName`,
        `hDirectories`.`hDirectoryPath`,
        `hFileDocuments`.`hFileDescription`,
        `hFileDocuments`.`hFileKeywords`,
        `hFileDocuments`.`hFileTitle`,
        `hFileDocuments`.`hFileDocument`
     )
     AGAINST ('{hFileSearchTerms}' IN BOOLEAN MODE)
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
