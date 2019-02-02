    DELETE 
      FROM `hCalendarFileDates`
     WHERE `hCalendarFileId` NOT IN (SELECT `hCalendarFileId` FROM `hCalendarFiles`)
