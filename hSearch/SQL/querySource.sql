 SELECT SQL_CALC_FOUND_ROWS
        `f`.`hFileName`,
        `fd`.`hFileId`,
        `fd`.`hFileTitle`,
        `fd`.`hFileDescription`,
        `fd`.`hFileDocument`
   FROM `hFileDocuments` `fd`,
        `hFiles` `f`
  WHERE `fd`.`hFileId` = `f`.`hFileId`
    AND `fd`.`hFileDocument` LIKE '%{searchTerms}%'
    AND (
        `f`.`hFileName` LIKE '%.html'
     OR `f`.`hFileName` LIKE '%.htm'
     OR `f`.`hFileName` LIKE '%.pdf'
     OR `f`.`hFileName` LIKE '%.product'
    )
  LIMIT {searchLimit}
