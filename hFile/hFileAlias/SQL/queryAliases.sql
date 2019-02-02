   SELECT `hFileAliases`.`hFileAliasId`,
          `hFileAliases`.`hFileAliasRedirect`,
          `hFileAliases`.`hFileAliasDestination`,
          `hDirectories`.`hDirectoryPath`,
          `hFiles`.`hFileName`
     FROM `hFileAliases`
LEFT JOIN `hFiles`
       ON `hFiles`.`hFileId` = `hFileAliases`.`hFileId`
LEFT JOIN `hDirectories`
       ON `hFiles`.`hDirectoryId` = `hDirectories`.`hDirectoryId`
    WHERE `hFileAliases`.`hFileAliasPath` = '{alias}'
       OR `hFileAliases`.`hFileAliasPath` = '{path}'
       OR `hFileAliases`.`hFileAliasPath` = '{path}/index.html'
       OR `hFileAliases`.`hFileAliasPath` = '{request}'
    LIMIT 1
