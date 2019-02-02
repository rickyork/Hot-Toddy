    SELECT {select}
      FROM {from}
     WHERE `hUsers`.`hUserId` = `hContacts`.`hUserId`
{includeUserLog?
       AND `hUsers`.`hUserId` = `hUserLog`.`hUserId`
}
       AND `hContacts`.`hContactAddressBookId` = 1
       AND `hContacts`.`hContactId` = `hContactAddresses`.`hContactId`
       AND ({where})
{city?
       AND `hContactAddresses`.`hContactAddressCity` = '{city}'
}
{postalCode?
       AND `hContactAddresses`.`hContactAddressPostalCode` = '{postalCode}'
}
{stateId?
       AND `hContactAddresses`.`hLocationStateId` = {stateId}
}
{countryId?
       AND `hContactAddresses`.`hLocationCountryId` = {countryId}
}
{constrainTime}
{sort?
  ORDER BY {sort} {sortOrientation}
}