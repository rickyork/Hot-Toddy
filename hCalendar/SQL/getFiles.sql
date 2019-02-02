    SELECT
  DISTINCT SQL_CALC_FOUND_ROWS
           `hCalendarFiles`.`hCalendarId`,
           `hCalendarFiles`.`hCalendarCategoryId`,
           `hCalendarFiles`.`hFileId`,
           `hCalendarCategories`.`hCalendarCategoryName`,
           `hCalendarFileDates`.`hCalendarFileId`,
           `hCalendarFileDates`.`hCalendarDate`,
           `hCalendarFiles`.`hCalendarBegin`,
           `hCalendarFiles`.`hCalendarEnd`,
           `hCalendarFileDates`.`hCalendarBeginTime`,
           `hCalendarFileDates`.`hCalendarEndTime`,
           `hCalendarFileDates`.`hCalendarAllDay`,
           `hCalendarFiles`.`hCalendarRange`,
           `hFileDocuments`.`hFileDescription`,
           `hFileDocuments`.`hFileTitle`,
           `hFileDocuments`.`hFileDocument`,
           `hFiles`.`hUserId`,
           `hFiles`.`hDirectoryId`,
           `hFiles`.`hPlugin`,
           `hFiles`.`hFileLastModified`,
           `hUsers`.`hUserName`,
           `hContacts`.`hContactDisplayName`,
           `hContacts`.`hContactFirstName`,
           REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`,
           (SELECT COUNT(*)
              FROM `hFileComments`
             WHERE `hFileComments`.`hFileId` = `hCalendarFiles`.`hFileId`
             {hFileCommentModeration?
                AND `hFileComments`.`hFileCommentIsApproved` = 1
             }
             ) AS `hFileCommentCount`,
           (SELECT `hFileVariables`.`hFileValue`
              FROM `hFileVariables`
             WHERE `hFileVariables`.`hFileVariable` = 'hFileCommentsEnabled'
               AND `hFileVariables`.`hFileId` = `hCalendarFiles`.`hFileId`) AS `hFileCommentsEnabled`
      FROM `hCalendarFiles`
INNER JOIN `hCalendarCategories`
        ON (`hCalendarCategories`.`hCalendarCategoryId` = `hCalendarFiles`.`hCalendarCategoryId`)
INNER JOIN `hCalendarFileDates`
        ON (`hCalendarFileDates`.`hCalendarFileId` = `hCalendarFiles`.`hCalendarFileId`)
INNER JOIN `hFiles`
        ON (`hCalendarFiles`.`hFileId` = `hFiles`.`hFileId`)
INNER JOIN `hFileDocuments`
        ON (`hCalendarFiles`.`hFileId` = `hFileDocuments`.`hFileId`)
 LEFT JOIN `hUsers`
        ON `hUsers`.`hUserId` = `hFiles`.`hUserId`
 LEFT JOIN `hContacts`
        ON `hUsers`.`hUserId` = `hContacts`.`hUserId`
       AND `hContacts`.`hContactAddressBookId` = 1
{categoryId?
  INNER JOIN `hCategoryFiles`
          ON (`hCalendarFiles`.`hFileId` = `hCategoryFiles`.`hFileId`)
}
{checkWorldPermissions?
INNER JOIN `hUserPermissions`
        ON (`hCalendarFiles`.`hFileId` = `hUserPermissions`.`hFrameworkResourceKey`)
       AND (`hUserPermissions`.`hFrameworkResourceId` = 1)
}
{checkPermissions?
INNER JOIN `hUserPermissions`
        ON (`hCalendarFiles`.`hFileId` = `hUserPermissions`.`hFrameworkResourceKey`)
       AND (`hUserPermissions`.`hFrameworkResourceId` = 1)
 LEFT JOIN `hUserPermissionsGroups`
        ON (`hUserPermissions`.`hUserPermissionsId` = `hUserPermissionsGroups`.`hUserPermissionsId`)

}
     WHERE `hCalendarFiles`.`hCalendarFileId` > 0
    {!multipleCalendars?
      {calendarId? AND `hCalendarFiles`.`hCalendarId` = {calendarId}}
      {!calendarId? AND `hCalendarFiles`.`hCalendarId` > 0}
    }
    {fileCalendarSQL? AND ({fileCalendarSQL})}
    {multipleCalendars?
      AND ({multipleCalendars})
    }
    {calendarCategoryId? AND `hCalendarFiles`.`hCalendarCategoryId` = {calendarCategoryId}}
    {calendarCategories?
      AND (
        {calendarCategories}
      )
    }
    {fileId? AND `hCalendarFiles`.`hFileId` = {fileId}}
    {withinTimeBoundaries?
        AND (`hCalendarFiles`.`hCalendarBegin` <= {time} OR `hCalendarFiles`.`hCalendarBegin` = 0)
        AND (`hCalendarFiles`.`hCalendarEnd` >= {time} OR `hCalendarFiles`.`hCalendarEnd` = 0)
    }
    {categoryId? AND `hCategoryFiles`.`hCategoryId` = {categoryId}}
    {timeRange? AND `hCalendarFileDates`.`hCalendarDate` {timeRangeOperator} {timeRange}}
    {timeRange2? AND `hCalendarFileDates`.`hCalendarDate` {timeRangeOperator2} {timeRange2}}
    {checkPermissions?
       AND (
              (`hUserPermissions`.`hUserPermissionsWorld` = 'r' OR `hUserPermissions`.`hUserPermissionsWorld` = 'rw')
        {userId?
           OR (      `hFiles`.`hUserId` = {userId}
                AND (
                    `hUserPermissions`.`hUserPermissionsOwner` = 'r'
                 OR `hUserPermissions`.`hUserPermissionsOwner` = 'rw'
                )
           )
           OR (
             (       `hUserPermissionsGroups`.`hUserGroupId` = {userId}
                AND (
                    `hUserPermissionsGroups`.`hUserPermissionsGroup` = 'r'
                 OR `hUserPermissionsGroups`.`hUserPermissionsGroup` = 'rw'
                )
             )
             {userGroups[]?
               {userGroupId?
                OR (
                      `hUserPermissionsGroups`.`hUserGroupId` = {userGroupId}
                  AND (
                       `hUserPermissionsGroups`.`hUserPermissionsGroup` = 'r'
                    OR `hUserPermissionsGroups`.`hUserPermissionsGroup` = 'rw'
                  )
                )
               }
             }
           )
         }
       )
    }
    {checkWorldPermissions?
       AND (`hUserPermissions`.`hUserPermissionsWorld` = 'r' OR `hUserPermissions`.`hUserPermissionsWorld` = 'rw')
    }
    {sort?
      ORDER BY `hCalendarFileDates`.`hCalendarDate` {sort}
    }
    {customSort?
      ORDER BY {customSort}
    }
    {limit?
      LIMIT {limit}
    }
