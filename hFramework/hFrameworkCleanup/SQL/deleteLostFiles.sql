    DELETE 
      FROM `hFiles`
     WHERE `hFileName` = ''
        OR `hFileName` = NULL
        OR `hDirectoryId` = 0
        OR `hDirectoryId` = NULL
        OR `hDirectoryId` = ''
