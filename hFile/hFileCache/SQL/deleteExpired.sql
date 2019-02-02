  DELETE
    FROM `hFileCache`
   WHERE `hFileCacheExpires` < {php.time()}
     AND `hFileCacheExpires` > 0
