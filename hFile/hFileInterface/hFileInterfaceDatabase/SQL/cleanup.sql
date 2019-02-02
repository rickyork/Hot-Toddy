DELETE 
  FROM `{table}` 
 WHERE `{column}` NOT IN (SELECT `hFileId` FROM `hFiles`)
