     SELECT `hCalendarFileDates`.`hCalendarDate`
       FROM `hCalendarFiles`,
            `hCalendarFileDates`
      WHERE `hCalendarFiles`.`hCalendarFileId`     = `hCalendarFileDates`.`hCalendarFileId`
        AND `hCalendarFiles`.`hCalendarId`         = {hCalendarId}
        AND `hCalendarFiles`.`hCalendarCategoryId` = {hCalendarCategoryId}
        AND `hCalendarFileDates`.`hCalendarDate`   > 0
   ORDER BY `hCalendarFileDates`.`hCalendarDate` {sort}
     LIMIT 1