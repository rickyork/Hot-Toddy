    SELECT DISTINCT
           `d`.`hDirectoryId`,
           `d`.`hDirectoryPath`,
           `d`.`hDirectoryCreated`,
           `d`.`hDirectoryLastModified`,
           `hDirectoryProperties`.`hDirectoryIsApplication`,
           `hDirectoryProperties`.`hFileIconId`,
           `hDirectoryProperties`.`hDirectoryLabel`,
           (SELECT COUNT(*) FROM `hDirectories` WHERE `hDirectoryParentId` = `d`.`hDirectoryId`) AS `hDirectoryCount`,
           (SELECT COUNT(*) FROM `hFiles` WHERE `hDirectoryId` = `d`.`hDirectoryId`) AS `hFileCount`
      FROM `hDirectories` `d`
 LEFT JOIN `hDirectoryProperties`
        ON (`hDirectoryProperties`.`hDirectoryId` = `d`.`hDirectoryId`)
{checkPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`d`.`hDirectoryId` = `hUserPermissions`.`hFrameworkResourceKey`)
 LEFT JOIN `hUserPermissionsGroups`
        ON (`hUserPermissions`.`hUserPermissionsId` = `hUserPermissionsGroups`.`hUserPermissionsId`)
}
{checkWorldPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`d`.`hDirectoryId` = `hUserPermissions`.`hFrameworkResourceKey`)
}
     WHERE `d`.`hDirectory{queryParent?Parent}Id` = {directoryId}
    {checkPermissions?
       AND `hUserPermissions`.`hFrameworkResourceId` = 2
       AND (
              (`hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%')
        {userId?
           OR (`d`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` LIKE 'r%')
           OR (
             (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')
             {userGroups[]?
               {userGroupId? OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` LIKE 'r%')}
             }
           )
         }
       )
    }
    {checkWorldPermissions?
       AND `hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%'
    }
  ORDER BY `hDirectoryPath` ASC
