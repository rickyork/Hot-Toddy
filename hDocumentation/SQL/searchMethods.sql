    SELECT `hDocumentationMethods`.`hDocumentationMethodId`,
           `hDocumentationMethods`.`hDocumentationMethodName`,
           `hDocumentationMethods`.`hDocumentationMethodSignature`,
           `hDocumentationMethods`.`hDocumentationMethodBody`,
           `hDocumentationMethods`.`hDocumentationMethodDescription`,
           `hDocumentationMethods`.`hDocumentationMethodIsProtected`,
           `hDocumentationMethods`.`hDocumentationMethodIsPrivate`,
           `hDocumentationMethods`.`hDocumentationMethodIsStatic`,
           `hDocumentationMethods`.`hDocumentationMethodIsOverloaded`,
           `hDocumentationMethods`.`hDocumentationMethodReturnsReference`,
           `hDocumentationMethods`.`hDocumentationMethodReturnType`,
           `hDocumentationMethods`.`hDocumentationMethodReturnDescription`,
           `hDocumentationFiles`.`hDocumentationFile`,
           `hDocumentationFiles`.`hDocumentationFileId`,
           `hDocumentationFiles`.`hDocumentationFileTitle`
      FROM `hDocumentationMethods`
INNER JOIN `hDocumentationFiles`
        ON `hDocumentationFiles`.`hDocumentationFileId` = `hDocumentationMethods`.`hDocumentationFileId`
     WHERE `hDocumentationMethods`.`hDocumentationMethodName` LIKE '%{search}%'
  ORDER BY `hDocumentationMethods`.`hDocumentationMethodName` ASC
