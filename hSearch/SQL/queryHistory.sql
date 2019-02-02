    SELECT SQL_CALC_FOUND_ROWS
           DISTINCT
           `hFiles`.`hUserId`,
           `hFiles`.`hFileName`,
           `hFiles`.`hFileCreated`,
           `hFiles`.`hFileLastModified`,
           `hFiles`.`hFileLastModifiedBy`,
           `hFileDocuments`.`hFileId`,
           `hFileDocuments`.`hFileTitle`,
           `hFileDocuments`.`hFileDescription`,
           `hFileDocuments`.`hFileDocument`,
           `hFileUserStatistics`.`hFileAccessCount`,
           `hFileUserStatistics`.`hFileLastAccessed`,
           REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`
    {searchTerms?
       , MATCH (
          `hFiles`.`hFileName`,
          `hDirectories`.`hDirectoryPath`,
          `hFileDocuments`.`hFileDescription`,
          `hFileDocuments`.`hFileKeywords`,
          `hFileDocuments`.`hFileTitle`,
          `hFileDocuments`.`hFileDocument`
       )
       AGAINST ('{searchTerms}' IN BOOLEAN MODE)
            AS `hSearchRelevance`
    }
      FROM `hFiles`
 LEFT JOIN `hCalendarFiles`
        ON `hCalendarFiles`.`hFileId` = `hFiles`.`hFileId`
INNER JOIN `hFileDocuments` 
        ON `hFileDocuments`.`hFileId` = `hFiles`.`hFileId`
INNER JOIN `hFileUserStatistics`
        ON `hFileUserStatistics`.`hFileId` = `hFiles`.`hFileId`
INNER JOIN `hDirectories`
        ON `hDirectories`.`hDirectoryId` = `hFiles`.`hDirectoryId`
{categories?
 LEFT JOIN `hCategoryFiles`
        ON `hCategoryFiles`.`hFileId` = `hFiles`.`hFileId`
}
{checkPermissions?
INNER JOIN `hUserPermissions`
        ON `hUserPermissions`.`hFrameworkResourceKey` = `hFiles`.`hFileId`
 LEFT JOIN `hUserPermissionsGroups`
        ON `hUserPermissionsGroups`.`hUserPermissionsId` = `hUserPermissions`.`hUserPermissionsId`
}
     WHERE 
     {searchTerms?     
       MATCH (
        `hFiles`.`hFileName`,
        `hDirectories`.`hDirectoryPath`,
        `hFileDocuments`.`hFileDescription`,
        `hFileDocuments`.`hFileKeywords`,
        `hFileDocuments`.`hFileTitle`,
        `hFileDocuments`.`hFileDocument`
       )
       AGAINST ('{searchTerms}' IN BOOLEAN MODE) AND
      }
      `hFileUserStatistics`.`hUserId` = {userId}
    {.hSearchLimitFileType(true)?
      AND (
           `hFiles`.`hFileName` LIKE '%.html'
        OR `hFiles`.`hFileName` LIKE '%.htm'
        OR `hFiles`.`hFileName` LIKE '%.pdf'
        OR `hFiles`.`hFileName` LIKE '%.doc'
        OR `hFiles`.`hFileName` LIKE '%.product'
      )
    }
      AND (
             (`hCalendarFiles`.`hCalendarBegin` IS NULL OR `hCalendarFiles`.`hCalendarBegin` = 0 OR `hCalendarFiles`.`hCalendarBegin` <= {php.time()})
         AND (`hCalendarFiles`.`hCalendarEnd`   IS NULL OR `hCalendarFiles`.`hCalendarEnd`   = 0 OR `hCalendarFiles`.`hCalendarEnd`   >= {php.time()})
      )
    {searchDirectory?
      AND (
           `hDirectories`.`hDirectoryPath` = '{searchDirectory}' 
        OR `hDirectories`.`hDirectoryPath` LIKE '{searchDirectory}/%'
        {searchDirectories?  
          {searchDirectories[]?
            OR `hDirectories`.`hDirectoryPath` = '{searchDirectory}'
          }
          {searchDirectories[]?
            OR `hDirectories`.`hDirectoryPath` LIKE '{searchDirectory}/%'
          }
        }
      )
    }
    {searchDirectories?
       AND ({searchDirectories})
    }
    {categories?
      AND (
        {categories}    
      )
    }
    {checkPermissions?
       AND `hUserPermissions`.`hFrameworkResourceId` = 1
       AND (
            (`hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%')
         {userId?
           OR (`hFiles`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
           OR (
             (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
             {userGroups[]?
               {userGroupId?OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')}
             }
           )
         }
       )
    }
    {!searchTerms?
      {.hSearchHistoryRecent(false)?
        ORDER BY `hFileUserStatistics`.`hFileLastAccessed` DESC
      }
      {!.hSearchHistoryRecent(false)?
        ORDER BY `hFileUserStatistics`.`hFileAccessCount` DESC
      }
    }
    LIMIT {searchLimit}
