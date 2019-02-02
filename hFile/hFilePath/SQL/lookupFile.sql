SELECT `hFiles`.`hFileId`,
       `hFiles`.`hDirectoryId`,
       `hDirectories`.`hDirectoryPath`
  FROM `hFiles`,
       `hDirectories`
 WHERE `hFiles`.`hDirectoryId` = `hDirectories`.`hDirectoryId`
   AND `hFiles`.`hFileName`    = '{hFileName}'
   AND (
       `hDirectories`.`hDirectoryPath` = '{hDirectoryPath}'
    OR `hDirectories`.`hDirectoryPath` = '/{hFrameworkSite}{!hDirectoryPathIsRoot?{hDirectoryPath}}'
   )