  SELECT `hCalendarBegin`
    FROM `hCalendarFiles`
   WHERE `hCalendarId` = {hCalendarId}
     AND `hCalendarCategoryId` = {hCalendarCategoryId}
     AND `hCalendarBegin` > {php.time()}
ORDER BY `hCalendarBegin` ASC
   LIMIT 1
