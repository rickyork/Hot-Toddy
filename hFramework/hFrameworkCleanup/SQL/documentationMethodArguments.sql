DELETE 
  FROM `hDocumentationMethodArguments`
 WHERE `hDocumentationMethodId` NOT IN (SELECT `hDocumentationMethodId` FROM `hDocumentationMethods`)
