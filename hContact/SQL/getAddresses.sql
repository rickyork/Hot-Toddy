    SELECT `hContactAddresses`.`hContactAddressId`,
           `hContactAddresses`.`hContactFieldId`,
           `hContactAddresses`.`hContactAddressStreet`,
           `hContactAddresses`.`hContactAddressCity`,
           `hContactAddresses`.`hLocationStateId`,
           `hContactAddresses`.`hContactAddressPostalCode`,
           `hContactAddresses`.`hLocationCountyId`,
           `hContactAddresses`.`hLocationCountryId`,
           `hContactAddresses`.`hContactAddressLatitude`,
           `hContactAddresses`.`hContactAddressLongitude`,
           `hContactAddresses`.`hContactAddressIsDefault`,
           `hLocationCountries`.`hLocationCountryName`,
           `hLocationCountries`.`hLocationCountryISO2`,
           `hLocationCountries`.`hLocationCountryISO3`,
           `hLocationCountries`.`hContactAddressTemplateId`,
           `hLocationCountries`.`hLocationStateLabel`,
           `hLocationCountries`.`hLocationUseStateCode`,
           `hLocationCounties`.`hLocationCounty` AS `hLocationCountyName`,
           `hContactAddressTemplates`.`hContactAddressTemplate`,
           `hLocationStates`.`hLocationStateCode`,
           `hLocationStates`.`hLocationStateName`,
           `hContactFields`.`hContactField` AS `hContactFieldName`,
           `hLocationZipCodes`.`hLocationCity`,
           `hLocationZipCodes`.`hLocationCounty`,
           `hLocationZipCodes`.`hLocationSequenceNumber`,
           `hLocationZipCodes`.`hLocationAcceptable`
      FROM `hContactAddresses`
 LEFT JOIN `hLocationCountries`
        ON `hLocationCountries`.`hLocationCountryId` = `hContactAddresses`.`hLocationCountryId`
 LEFT JOIN `hContactAddressTemplates`
        ON `hLocationCountries`.`hContactAddressTemplateId` = `hContactAddressTemplates`.`hContactAddressTemplateId`
 LEFT JOIN `hLocationStates`
        ON `hLocationStates`.`hLocationStateId` = `hContactAddresses`.`hLocationStateId`
 LEFT JOIN `hLocationCounties`
        ON `hLocationCounties`.`hLocationCountyId` = `hContactAddresses`.`hLocationCountyId`
 LEFT JOIN `hLocationZipCodes`
        ON `hLocationZipCodes`.`hLocationZipCode` = `hContactAddresses`.`hContactAddressPostalCode`
 LEFT JOIN `hContactFields`
        ON `hContactFields`.`hContactFieldId` = `hContactAddresses`.`hContactFieldId`
     WHERE `hContactAddresses`.`hContactId` = {contactId}
 {contactFieldId?  
       AND `hContactAddresses`.`hContactFieldId` = {contactFieldId}
 }
