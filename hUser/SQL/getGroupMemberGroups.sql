     SELECT `hUserGroups`.`hUserId`
       FROM `hUserGroups`
  LEFT JOIN `hUsers`
         ON `hUserGroups`.`hUserId` = `hUsers`.`hUserId`
      WHERE `hUserGroups`.`hUserId` IN (SELECT `hUserId` FROM `hUserGroupProperties`)
        AND `hUserGroups`.`hUserGroupId` = {userGroupId}
   ORDER BY `hUsers`.`hUserName` ASC