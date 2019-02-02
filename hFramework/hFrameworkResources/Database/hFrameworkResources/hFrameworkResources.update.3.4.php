<?php

class hFrameworkResources_3to4 extends hPlugin {

    public function hConstructor()
    {
        $this->hFrameworkResources
            ->appendColumn('hFrameworkResourceLastModifiedColumn', hDatabase::varCharTemplate(100))
            ->appendColumn('hFrameworkResourceLastModifiedByColumn', hDatabase::varCharTemplate(100))
            ->appendColumn('hFrameworkResourceLastModified', hDatabase::time)
            ->appendColumn('hFrameworkResourceLastModifiedBy', hDatabase::id);

        $columns = array(
            1   => array(
                'hFileLastModified',
                'hFileLastModifiedBy'
            ),
            2   => array(
                'hDirectoryLastModified',
                'hDirectoryLastModifiedBy'
            ),
            3   => array(
                'hForumLastModified',
                'hForumLastModifiedBy'
            ),
            4   => array(
                'hForumTopicLastModified',
                'hForumTopicLastModifiedBy'
            ),
            5   => array(
                'hContactLastModified',
                'hContactLastModifiedBy'
            ),
            6   => array(
                'hCalendarLastModified',
                'hCalendarLastModifiedBy'
            ),
            7   => array(
                'hContactAddressBookLastModified',
                'hContactAddressBookLastModifiedBy'
            ),
            8   => array(
                'hContactAddressLastModified',
                'hContactAddressLastModifiedBy'
            ),
            9   => array(
                'hContactEmailAddressLastModified',
                'hContactEmailAddressLastModifiedBy'
            ),
            10  => array(
                'hContactInternetAccountLastModified',
                'hContactInternetAccountLastModifiedBy'
            ),
            11  => array(
                'hContactPhoneNumberLastModified',
                'hContactPhoneNumberLastModifiedBy'
            ),
            13  => array(
                'hForumPostLastModified',
                'hForumPostLastModifiedBy'
            ),
            14  => array(
                'hFileServerLastModified',
                'hFileServerLastModifiedBY'
            ),
            16  => array(
                'hLocationCountryLastModified',
                'hLocationCountryLastModifiedBy'
            ),
            17  => array(
                'hLocationStateLastModified',
                'hLocationStateLastModifiedBy'
            ),
            18  => array(
                'hLocationZipCodeLastModified',
                'hLocationZipCodeLastModifiedBy'
            ),
            19  => array(
                'hLocationCountyLastModified',
                'hLocationCountyLastModifiedBy'
            ),
            20  => array(
                'hCategoryLastModified',
                'hCategoryLastModifiedBy'
            ),
            21  => array(
                'hCalendarFileLastModified',
                'hCalendarFileLastModifiedBy'
            ),
            22  => array(
                'hCalendarResourceLastModified',
                'hCalendarResourceLastModifiedBy'
            )
        );

        foreach ($columns as $frameworkResourceId => $column)
        {
            $this->hFrameworkResources->update(
                array(
                    'hFrameworkResourceLastModifiedColumn' => $column[0],
                    'hFrameworkResourceLastModifiedByColumn' => $column[1]
                ),
                $frameworkResourceId
            );
        }

        // Fix 'hContacts' table being incorrectly stored as 'hContact'
        $this->hFrameworkResources->update(
            array(
                'hFrameworkResourceTable' => 'hContacts'
            ),
            5
        );
    }

    public function undo()
    {
        $this->hFrameworkResources->dropColumns(
            'hFrameworkResourceLastModifiedColumn',
            'hFrameworkResourceLastModifiedByColumn',
            'hFrameworkResourceLastModified',
            'hFrameworkResourceLastModifiedBy'
        );
    }
}

?>