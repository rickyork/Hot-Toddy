<?php

class hContactFields_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 22,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'Main',
                'hContactFieldSortIndex' => 7
            )
        );
    
        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 23,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'Toll-Free',
                'hContactFieldSortIndex' => 8
            )
        );

        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 24,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'Appointments',
                'hContactFieldSortIndex' => 9
            )
        );
        
        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 25,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'iPhone',
                'hContactFieldSortIndex' => 10
            )
        );
        
        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 26,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'Home Fax',
                'hContactFieldSortIndex' => 11
            )
        );
        
        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 27,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'Work Fax',
                'hContactFieldSortIndex' => 12
            )
        );
        
        $this->hContactFields->insert(
            array(
                'hContactFieldId'        => 27,
                'hFrameworkResourceId'   => 11,
                'hContactField'          => 'Other Fax',
                'hContactFieldSortIndex' => 13
            )
        );

        $this->hContactFields->update(
            array(
                'hContactFieldSortIndex' => 14
            ),
            array(
                'hContactFieldId' => 11
            )
        );
    }
}

?>