INSERT INTO `hContactAddressTemplates` (
    `hContactAddressTemplateId`,
    `hContactAddressTemplate`
) VALUES
(1, '{$street}|{$city}, {$state} {$postalCode}|{$country}'),
(2, '{$street}|{$postalCode} {$city}|{$country}'),
(3, '{$street}|{$postalCode} {$city}, {$state}|{$country}'),
(4, '{$street}|{$postalCode} {$city}|{$state}|{$country}'),
(5, '{$street}|{$postalCode}|{$country}'),
(6, '{$street}|{$state}|{$city} {$postalCode}|{$country}'),
(7, '{$street}|{$city}|{$state} {$postalCode}|{$country}'),
(8, '{$postalCode}|{$state}, {$city}|{$street}|{$country}'),
(9, '{$street},{$state}|{$postalCode} {$city}|{$country}'),
(10, '{$country},{$postalCode}|{$state}, {$city}|{$street}'),
(11, '{$street}|{$city}|{$state}|{$postalCode}|{$country}'),
(12, '{$street}|{$city}, {$state}|{$postalCode}|{$country}'),
(13, '{$street}|{$city}|{$state}|{$postalCode}|{$country}');