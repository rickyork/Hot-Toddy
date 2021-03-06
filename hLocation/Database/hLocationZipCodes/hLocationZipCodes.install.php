<?php

class hLocationZipCodesInstall extends hPlugin {
    
    public function hConstructor()
    {
        $path = dirname(__FILE__).'/CSV/zipCodes.csv';
        
        $file = fopen($path, 'r');

        for ($i = 0; false !== ($data = fgetcsv($file, 1000, ',')); $i++)
        {
            $zipCode    = (int) $data[0];
            $state      = $data[1];
            $city       = $data[2];
            $county     = $data[3];
            $seqNumber  = $data[4];
            $acceptable = $data[5];
            $latitude   = (float) $data[6];
            $longitude  = (float) $data[7];
            $timeZone   = (int) $data[8];
            $dst        = $data[9];
            
            $where = array(
                'hLocationZipCode'   => $zipCode,
                'hLocationStateCode' => $state,
                'hLocationCity'      => $city,
                'hLocationCounty'    => $county    
            );
            
            $zipCodeExists = $this->hLocationZipCodes->selectExists('hLocationZipCode', $where);

            if ($zipCodeExists)
            {
                $this->hLocationZipCodes->update(
                    array(
                        'hLocationSequenceNumber'               => $seqNumber,
                        'hLocationAcceptable'                   => $acceptable,
                        'hLocationZipCodeLatitude'              => $latitude,
                        'hLocationZipCodeLongitude'             => $longitude,
                        'hLocationZipCodeTimeZone'              => $timeZone,
                        'hLocationZipCodeHasDaylightSavings'    => $dst
                    ),
                    $where
                );
            }
            else
            {
                $this->hLocationZipCodes->insert(
                    array(
                        'hLocationZipCode'                      => $zipCode,
                        'hLocationStateCode'                    => $state,
                        'hLocationCity'                         => $city,
                        'hLocationCounty'                       => $county,
                        'hLocationSequenceNumber'               => $seqNumber,
                        'hLocationAcceptable'                   => $acceptable,
                        'hLocationZipCodeLatitude'              => $latitude,
                        'hLocationZipCodeLongitude'             => $longitude,
                        'hLocationZipCodeTimeZone'              => $timeZone,
                        'hLocationZipCodeHasDaylightSavings'    => $dst  
                    )
                );
            }
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