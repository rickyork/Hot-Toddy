   SELECT `hFiles`.`hFileId`
     FROM `hFiles`
LEFT JOIN `hFileProperties`
       ON `hFileProperties`.`hFileId` = `hFiles`.`hFileId`
    WHERE (
          `hFiles`.`hFileName` LIKE '%.mov'
       OR `hFiles`.`hFileName` LIKE '%.flv'
       OR `hFiles`.`hFileName` LIKE '%.swf'
       OR `hFiles`.`hFileName` LIKE '%.mp4'
       OR `hFiles`.`hFileName` LIKE '%.m4v'
       OR `hFiles`.`hFileName` LIKE '%.avi'
       OR `hFiles`.`hFileName` LIKE '%.wmv'
       OR `hFiles`.`hFileName` LIKE '%.asf'
       OR `hFiles`.`hFileName` LIKE '%.rm'
    ) OR `hFileProperties`.`hFileMIME` LIKE 'video/%'