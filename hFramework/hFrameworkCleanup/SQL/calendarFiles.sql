    DELETE 
      FROM `hCalendarFiles`
     WHERE `hCalendarId` NOT IN (SELECT `hCalendarId` FROM `hCalendars`)
        OR `hCalendarCategoryId` NOT IN (SELECT `hCalendarCategoryId` FROM `hCalendarCategories`)
