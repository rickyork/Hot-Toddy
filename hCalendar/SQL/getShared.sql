     SELECT
   DISTINCT `c`.`hCalendarId`,
            `c`.`hCalendarName`
       FROM `hCalendars` `c`,
            `hUserPermissions` `p`,
            `hUserPermissionsGroups` `g`,
            `hUserGroups` `u`
      WHERE `c`.`hCalendarId`           = `p`.`hFrameworkResourceKey` 
        AND `p`.`hFrameworkResourceId`  = {hFrameworkResourceId}
        AND `g`.`hUserPermissionsId`    = `p`.`hUserPermissionsId`            
        AND `g`.`hUserPermissionsGroup` LIKE 'r%'
        AND (
             `g`.`hUserGroupId` = {hUserId}
          OR `g`.`hUserGroupId` = `u`.`hUserGroupId`
         AND `u`.`hUserId`      = {hUserId}
          OR `p`.`hUserPermissionsWorld` LIKE 'r%'
        )
    ORDER BY `c`.`hCalendarName` ASC
