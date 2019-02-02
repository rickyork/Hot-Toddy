DELETE 
  FROM `hContacts`
 WHERE `hContactAddressBookId` NOT IN (SELECT `hContactAddressBookId` FROM `hContactAddressBooks`)
