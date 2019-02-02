  SELECT `hFileCommentId`,
         `hFileCommentName`,
         `hFileCommentWebsite`,
         `hFileComment`,
         `hFileCommentPosted`
    FROM `hFileComments`
   WHERE `hFileId` = {$hFileId}
ORDER BY `hFileCommentPosted` ASC