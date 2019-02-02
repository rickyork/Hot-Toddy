      SELECT `hFiles`.`hFileId`,
             `hFiles`.`hFileName`,
             REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '//', '/') AS `hFilePath`,
              LENGTH(`hFilesDocuments`.`hFileDocument`) AS `hFileSize`
        FROM `hFiles`
   LEFT JOIN `hFileDocuments`
          ON `hFileDocuments`.`hFileId` = `hFiles`.`hFileId`
   LEFT JOIN `hDirectories`
          ON `hDirectories`.`hDirectoryId` = `hFiles`.`hDirectoryId`
       WHERE `hDirectories`.`hDirectoryPath` = '{path}'
          OR `hDirectories`.`hDirectoryPath` LIKE '{path}/%'
