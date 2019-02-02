  SELECT `hCalendarEnd`
    FROM `hCalendarFiles`
   WHERE `hCalendarId` = {hCalendarId}
     AND `hCalendarCategoryId` = {hCalendarCategoryId}
     AND `hCalendarEnd` > {php.time()}
ORDER BY `hCalendarEnd` ASC 
   LIMIT 1
