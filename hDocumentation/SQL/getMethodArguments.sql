  SELECT `hDocumentationMethodArgumentId`,
         `hDocumentationMethodArgumentName`,
         `hDocumentationMethodArgumentDescription`,
         `hDocumentationMethodArgumentType`,
         `hDocumentationMethodArgumentDefault`,
         `hDocumentationMethodArgumentIsOptional`,
         `hDocumentationMethodArgumentByReference`
    FROM `hDocumentationMethodArguments`
   WHERE `hDocumentationMethodId` = {methodId}
ORDER BY `hDocumentationMethodArgumentIndex` ASC
