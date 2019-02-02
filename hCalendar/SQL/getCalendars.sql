    SELECT DISTINCT
           `hCalendars`.`hCalendarId`,
           `hCalendars`.`hCalendarName`
      FROM `hCalendars`
{checkWorldPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`hCalendars`.`hCalendarId` = `hUserPermissions`.`hFrameworkResourceKey`)
       AND (`hUserPermissions`.`hFrameworkResourceId` = 6)
}
{checkPermissions?
 LEFT JOIN `hUserPermissions`
        ON (`hCalendars`.`hCalendarId` = `hUserPermissions`.`hFrameworkResourceKey`)
       AND (`hUserPermissions`.`hFrameworkResourceId` = 6)
 LEFT JOIN `hUserPermissionsGroups`
        ON (`hUserPermissions`.`hUserPermissionsId` = `hUserPermissionsGroups`.`hUserPermissionsId`)
}
      WHERE `hCalendars`.`hCalendarId` > 0
    {checkPermissions?
       AND (
              (`hUserPermissions`.`hUserPermissionsWorld` = '{level}')
        {userId?
           OR (`hCalendars`.`hUserId` = {userId} AND `hUserPermissions`.`hUserPermissionsOwner` = '{level}')
           OR (
             (`hUserPermissionsGroups`.`hUserGroupId` = {userId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` = '{level}')
             {userGroups[]?
               {userGroupId? OR (`hUserPermissionsGroups`.`hUserGroupId` = {userGroupId} AND `hUserPermissionsGroups`.`hUserPermissionsGroup` = '{level}')}
             }
           )
         }
       )
    }
    {checkWorldPermissions?
       AND `hUserPermissions`.`hUserPermissionsWorld` = '{level}'
    }
  ORDER BY `hCalendars`.`hCalendarName` ASC
