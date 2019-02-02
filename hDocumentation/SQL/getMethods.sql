   SELECT `hDocumentationMethodId`,
          `hDocumentationMethodName`,
          `hDocumentationMethodSignature`,
          `hDocumentationMethodBody`,
          `hDocumentationMethodDescription`,
          `hDocumentationMethodIsProtected`,
          `hDocumentationMethodIsPrivate`,
          `hDocumentationMethodIsStatic`,
          `hDocumentationMethodIsOverloaded`,
          `hDocumentationMethodReturnsReference`,
          `hDocumentationMethodReturnType`,
          `hDocumentationMethodReturnDescription`
     FROM `hDocumentationMethods`
    WHERE `hDocumentationFileId` = {documentationFileId}
 ORDER BY `hDocumentationMethodName` ASC
