   SELECT `hDocumentationFileId`,
          `hDocumentationFile`,
          `hDocumentationFileTitle`,
          `hDocumentationFileDescription`,
          `hDocumentationFileClosingDescription`
     FROM `hDocumentationFiles`
    WHERE `hDocumentationFileTitle` LIKE '{search}%'
 ORDER BY `hDocumentationFile` ASC 
