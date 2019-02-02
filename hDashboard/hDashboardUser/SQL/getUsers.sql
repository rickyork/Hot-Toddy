    SELECT 
  DISTINCT SQL_CALC_FOUND_ROWS 
           `hUsers`.`hUserId`,
           `hUsers`.`hUserName`,
           `hUsers`.`hUserEmail`,
           `hContacts`.`hContactFirstName`,
           `hContacts`.`hContactLastName`
      FROM `hUsers`
 LEFT JOIN `hContacts`
        ON `hContacts`.`hUserId` = `hUsers`.`hUserId`
{group?
 LEFT JOIN `hUserGroups`
        ON `hUserGroups`.`hUserId` = `hUsers`.`hUserId`
}
        
     WHERE `hUsers`.`hUserId` NOT IN (SELECT `hUserId` FROM `hUserGroupProperties`)
       AND `hContacts`.`hContactAddressBookId` = 1
     {letter?
       AND `hContacts`.`hContactLastName` LIKE '{letter}%'
     }
     {group?
       AND `hUserGroups`.`hUserGroupId` = {group}
     }
  ORDER BY `hContacts`.`hContactLastName` ASC
     LIMIT {limit}
