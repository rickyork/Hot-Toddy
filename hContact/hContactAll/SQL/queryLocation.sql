    SELECT {select}
      FROM {from}
     WHERE `hContacts`.`hContactId` = `hContactAddresses`.`hContactId`
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
{county?
       AND `hContactAddresses`.`hLocationStateId` = `hLocationCounties`.`hLocationStateId`
       AND `hLocationCounties`.`hLocationCounty` = '{county}'
}
       AND ({where})
{constrainTime}
{sort?
  ORDER BY {sort} {sortOrientation}
}
