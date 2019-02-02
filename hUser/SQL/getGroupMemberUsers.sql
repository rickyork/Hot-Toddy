     SELECT `hUserGroups`.`hUserId`
       FROM `hUserGroups`
  LEFT JOIN `hContacts`
         ON `hContacts`.`hUserId` = `hUserGroups`.`hUserId`
      WHERE `hUserGroups`.`hUserId` NOT IN (SELECT `hUserId` FROM `hUserGroupProperties`)
        AND `hUserGroups`.`hUserGroupId` = {userGroupId}
        AND `hContacts`.`hContactAddressBookId` = 1
   ORDER BY `hContacts`.`hContactLastName` ASC
