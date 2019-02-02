    SELECT {select}
      FROM `hUsers`
 LEFT JOIN `hContacts`
        ON `hUsers`.`hUserId` = `hContacts`.`hUserId`
 LEFT JOIN `hUserGroups`
        ON `hUsers`.`hUserId` = `hUserGroups`.`hUserId`
{includeUserLog?
 LEFT JOIN `hUserLog`
        ON `hUsers`.`hUserId` = `hUserLog`.`hUserId`
}
     WHERE `hUserGroups`.`hUserGroupId` = {groupId}
       AND `hContacts`.`hContactAddressBookId` = 1
{!sort?
  ORDER BY `hContacts`.`hContactLastName` ASC
}
{sort?
  ORDER BY {sort} {sortOrientation}
}
