<?php


class hLocationZipCodes_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hLocationZipCodes
            ->addColumn('hLocationZipCodeLatitude', hDatabase::latitudeLongitude, 'hLocationAcceptable')
            ->addColumn('hLocationZipCodeLongitude', hDatabase::latitudeLongitude, 'hLocationZipCodeLatitude')
            ->addColumn('hLocationZipCodeTimeZone', hDatabase::tinyIntTemplate(3), 'hLocationZipCodeLongitude')
            ->addColumn('hLocationZipCodeHasDaylightSavings', hDatabase::is, 'hLocationZipCodeTimeZone');

        $path = dirname(__FILE__).'/CSV/zipcode.csv';
        
        $file = fopen($path, 'r');
        
        $i = 0;

        while (false !== ($data = fgetcsv($file, 1000, ',')))
        {
            if ($i == 0)
            {
                // discard the first row
                $i++;
                continue;
            }

            if (isset($data[3]))
            {
                $zipcode   = $data[0];
                $latitude  = $data[3];
                $longitude = $data[4];
                $timezone  = $data[5];
                $dst       = $data[6];

                $this->hLocationZipCodes->update(
                    array(
                        'hLocationZipCodeLatitude' => (float) $latitude,
                        'hLocationZipCodeLongitude' => (float) $longitude,
                        'hLocationZipCodeTimeZone'  => $timezone,
                        'hLocationZipCodeHasDaylightSavings' => $dst
                    ),
                    array(
                        'hLocationZipCode' => $zipcode
                    )
                );
            }

            $i++;
        }

        fclose($file);

        $this->hLocationZipCodes->update(
            array(
                'hLocationZipCodeHasDaylightSavings' => 1
            ),
            array(
                'hLocationStateCode' => 'IN'
            )
        );
    }
}

?>