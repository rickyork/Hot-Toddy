DELETE
  FROM `{table}`
 WHERE `{column}` NOT IN (SELECT `hContactId` FROM `hContacts`)
