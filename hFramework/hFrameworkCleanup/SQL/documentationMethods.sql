DELETE
  FROM `hDocumentationMethods` 
 WHERE `hDocumentationFileId` NOT IN (SELECT `hDocumentationFileId` FROM `hDocumentationFiles`)
