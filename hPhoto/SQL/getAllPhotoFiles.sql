   SELECT `hFiles`.`hFileId`
     FROM `hFiles`
LEFT JOIN `hFileProperties`
       ON `hFileProperties`.`hFileId` = `hFiles`.`hFileId`
    WHERE (
          `hFiles`.`hFileName` LIKE '%.jpg'
       OR `hFiles`.`hFileName` LIKE '%.jpeg'
       OR `hFiles`.`hFileName` LIKE '%.jpe'
       OR `hFiles`.`hFileName` LIKE '%.bmp'
       OR `hFiles`.`hFileName` LIKE '%.png'
       OR `hFiles`.`hFileName` LIKE '%.gif'
       OR `hFiles`.`hFileName` LIKE '%.psd'
    ) OR `hFileProperties`.`hFileMIME` LIKE 'image/%'