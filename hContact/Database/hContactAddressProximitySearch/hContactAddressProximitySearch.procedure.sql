DELIMITER $$
CREATE PROCEDURE hContactAddressProximitySearch(IN zipCode int, IN withinMiles int, IN addressBookId int) BEGIN

  DECLARE zipCodeLatitude double;
  DECLARE zipCodeLongitude double;
  DECLARE lon1 float;
  DECLARE lon2 float;
  DECLARE lat1 float;
  DECLARE lat2 float;

  -- get the original lon and lat for the userid
  SELECT `hLocationZipCodeLatitude`,
         `hLocationZipCodeLongitude`
    INTO  zipCodeLatitude,
          zipCodeLongitude
    FROM `hLocationZipCodes`
   WHERE `hLocationZipCode` = zipCode
   LIMIT 1;

  -- calculate lon and lat for the rectangle:
  SET lon1 = zipCodeLongitude - withinMiles / abs(cos(radians(zipCodeLatitude)) * 69);
  SET lon2 = zipCodeLongitude + withinMiles / abs(cos(radians(zipCodeLatitude)) * 69);

  SET lat1 = zipCodeLatitude - (withinMiles / 69);
  SET lat2 = zipCodeLatitude + (withinMiles / 69);

  -- run the query:
  SELECT `hContacts`.`hContactId`,
         `hContacts`.`hUserId`,
         `hContactAddresses`.`hContactAddressId`,
         -- An implementation of the haversine formula in MySQL
         3956 * 2 *
         ASIN(
           SQRT(
             POWER(
               SIN(
                 (`hLocationZipCodes`.`hLocationZipCodeLatitude` - `hContactAddresses`.`hContactAddressLatitude`) * pi() / 180 / 2
               ), 2
             ) +
             COS(`hLocationZipCodes`.`hLocationZipCodeLatitude` * pi() / 180) * 
             COS(`hContactAddresses`.`hContactAddressLatitude` * pi() / 180) * 
             POWER(
               SIN(
                 (`hLocationZipCodes`.`hLocationZipCodeLongitude` - `hContactAddresses`.`hContactAddressLongitude`) * pi() / 180 / 2
               ), 2
             )
           )
         ) AS `distance`
    FROM `hLocationZipCodes`,
         `hContacts`,
         `hContactAddresses`
   WHERE `hLocationZipCodes`.`hLocationZipCode` = zipCode
     AND `hContacts`.`hContactId` = `hContactAddresses`.`hContactId`
     AND `hContacts`.`hContactAddressBookId` = addressBookId
     AND `hContactAddresses`.`hContactAddressLongitude` BETWEEN lon1 AND lon2
     AND `hContactAddresses`.`hContactAddressLatitude`  BETWEEN lat1 AND lat2
  HAVING `distance` <= withinMiles 
ORDER BY `distance`;

END $$
DELIMITER ;