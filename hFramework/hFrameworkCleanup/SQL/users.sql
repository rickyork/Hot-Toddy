DELETE 
  FROM `{table}` 
 WHERE `{column}` NOT IN (SELECT `hUserId` FROM `hUsers`)
