DELETE
  FROM `{table}`
 WHERE `{column}` NOT IN (SELECT `hDirectoryId` FROM `hDirectories`)
