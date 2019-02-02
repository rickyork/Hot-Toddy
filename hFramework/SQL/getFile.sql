     SELECT `hFiles`.`hUserId`,
            `hFiles`.`hFileParentId`,
            `hFiles`.`hPlugin`,
            `hFiles`.`hFileCreated`,
            `hFiles`.`hFileLastModified`,
            `hFileDocuments`.`hFileDescription`,
            `hFileDocuments`.`hFileKeywords`,
            `hFileDocuments`.`hFileTitle`,
            `hFileDocuments`.`hFileDocument`,
            `hFileHeaders`.`hFileCSS`,
            `hFileHeaders`.`hFileJavaScript`,
            `hFileProperties`.`hFileMIME`,
            `hFileProperties`.`hFileSize`,
            `hFileProperties`.`hFileDownload`,
            `hFileProperties`.`hFileIsSystem`,
            `hFileProperties`.`hFileSystemPath`
       FROM `hFiles`
  LEFT JOIN `hFileDocuments`
         ON `hFiles`.`hFileId` = `hFileDocuments`.`hFileId`
  LEFT JOIN `hFileHeaders`
         ON `hFiles`.`hFileId` = `hFileHeaders`.`hFileId`
  LEFT JOIN `hFileProperties`
         ON `hFiles`.`hFileId` = `hFileProperties`.`hFileId`
      WHERE `hFiles`.`hFileId` = {hFileId}
      LIMIT 1