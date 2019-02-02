<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Spotlight
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
#//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| http://www.hframework.com/license
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hSpotlightLibrary extends hPlugin {

    private $hDialogue;
    private $hSpotlightSearch;
    private $hForm;

    public function hConstructor()
    {
        $this->hForm = $this->library('hForm');

        $this->hTemplatePath = '/hSpotlight/hSpotlight.template.php';
        $this->getPluginFiles();
        $this->hDialogue = $this->library('hDialogue');

        $this->hSpotlightSearch = $this->library('hSpotlight/hSpotlightSearch');
    }

    public function getSearch($id, $advancedSearch = true)
    {
        # @return HTML

        # @description
        # <h2>Getting Search Form</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getTemplate(
            'Spotlight',
            array(
                'hSpotlightId' => $id,
                'hSpotlightAction' => $this->hFilePath,
                'hSpotlightSearchExtended' => $this->getExtendedSearch($id),
                'hSpotlightAdvancedSearch' => $advancedSearch
            )
        );
    }

    private function getExtendedSearch($id)
    {
        # @return HTML

        # @description
        # <h2>Getting Extended Search Form</h2>
        # <p>
        #
        # </p>
        # @end

        $searchColumns = $this->hSpotlightSearch->getDefaultColumns();
        $timeColumns   = $this->hSpotlightSearch->getTimeColumns();

        $html = '';

        if (is_array($searchColumns))
        {
            $this->hForm->hFormElement = false;

            $this->hForm
                ->addDiv('hSpotlightExtendedSearchDiv:hSpotlightExtendedSearchDiv-'.$id)
                ->addFieldset('', '100%', '128px,auto');

            $options = array();

            foreach ($searchColumns as $column)
            {
                $options[$column] = $this->hSpotlightSearch->getColumnLabel($column);
            }

            $this->hForm->addSelectInput(
                array(
                    'class' => 'hSpotlightSearchExtendedColumn',
                    'id' => 'hSpotlightSearchExtendedColumn-'.$id,
                    'multiple' => 'multiple'
                ),
                'Search What?<span class="hSpotlightExtendedSearchColumnLabel">Select all that apply</span> -L',
                $options,
                5
            );

            if (count($timeColumns))
            {
                $this->hForm
                    ->setVariable('hFormCheckboxReverseLabel', 1)
                    ->addCheckboxInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedToggleTime',
                            'id' => 'hSpotlightSearchExtendedToggleTime-'.$id,
                        ),
                        'Constrain Time?',
                        0
                    )

                    ->addDiv('hSpotlightExtendedTimeOptions:hSpotlightExtendedTimeOptions-'.$id)
                    ->addFieldset(
                        'Time Options',
                        '100%',
                        '128px,auto'
                    )

                    ->addSelectInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedTimeRange',
                            'id' => 'hSpotlightSearchExtendedTimeRange-'.$id
                        ),
                        'To When? -L',
                        array(
                            -30 => 'Last 30 Days',
                            -60 => 'Last 60 Days',
                            -90 => 'Last 90 Days',
                            -365 => 'Last Year',
                            0 => 'Custom'
                        ),
                        1,
                        -30
                    )

                    ->addDiv('hSpotlightExtendedTimeRange:hSpotlightExtendedTimeRange-'.$id)
                    ->addFieldset(
                        'Time Options',
                        '100%',
                        '128px,auto'
                    )

                    ->defineCell(
                        array(
                            'class' => 'hFormLabel hSpotlightSearchExtendedDateStart'
                        )
                    )

                    ->addTextInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedDateStart',
                            'id' => 'hSpotlightSearchExtendedDateStart-'.$id
                        ),
                        'From Date:'
                    )

                    ->defineCell(
                        array(
                            'class' => 'hFormLabel hSpotlightSearchExtendedDateEnd'
                        )
                    )
                    ->addTextInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedDateEnd',
                            'id' => 'hSpotlightSearchExtendedTimeColumn-'.$id
                        ),
                        'To Date:'
                    )

                    ->addDiv('hSpotlightExtendedTimeColumn:hSpotlightExtendedTimeColumn-'.$id)
                    ->addFieldset(
                        'Time Column',
                        '100%',
                        '128px,auto'
                    );

                $options = array();

                if (is_array($timeColumns))
                {
                    foreach ($timeColumns as $column)
                    {
                        $options[$column] = $this->hSpotlightSearch->getColumnLabel($column);
                    }
                }

                $this->hForm->addSelectInput(
                    array(
                        'class' => 'hSpotlightSearchExtendedTimeColumn',
                        'id' => 'hSpotlightSearchExtendedTimeColumn-'.$id
                    ),
                    'To what?',
                    $options,
                    3
                );
            }

            if ($this->hSpotlightConstrainLocation(true))
            {
                $this->hForm
                    ->addDiv('hSpotlightExtendedLocation:hSpotlightExtendedLocation-'.$id)
                    ->addFieldset(
                        'Location',
                        '100%',
                        '128px,auto'
                    )

                    ->setVariable('hFormCheckboxReverseLabel', 1)
                    ->addCheckboxInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedToggleLocation',
                            'id' => 'hSpotlightSearchExtendedToggleLocation-'.$id
                        ),
                        'Constrain Location?',
                        0
                    )

                    ->addDiv('hSpotlightExtendedLocationOptions:hSpotlightExtendedLocationOptions-'.$id)
                    ->addFieldset(
                        'Location Options',
                        '100%',
                        '128px,auto'
                    )

                    ->addSelectCountry(
                        array(
                            'class' => 'hSpotlightSearchExtendedCountryId',
                            'id' => 'hSpotlightSearchExtendedCountryId-'.$id
                        ),
                        'Country:'
                    )
                    ->addSelectState(
                        array(
                            'class' => 'hSpotlightSearchExtendedStateId',
                            'id' => 'hSpotlightSearchExtendedStateId-'
                        ),
                        'State:'
                    )
                    ->addTextInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedCity',
                            'id' => 'hSpotlightSearchExtendedCity'
                        ),
                        'City:',
                        20
                    )
                    ->addTextInput(
                        array(
                            'class' => 'hSpotlightSearchExtendedPostalCode',
                            'id' => 'hSpotlightSearchExtendedPostalCode-'.$id
                        ),
                        'Postal Code:',
                        10
                    );
                #$this->hForm->addTextInput('hSpotlightSearchExtendedCounty:hSpotlightSearchExtendedCounty-'.$id, 'County:', 15);
            }

            $this->hForm
                ->addDiv('hSpotlightExtendedButtons:hSpotlightExtendedButtons-'.$id)
                ->addFieldset(
                    'Buttons',
                    '100%',
                    '128px,auto'
                )
                ->addSubmitButton(
                    array(
                        'class' => 'hSpotlightSearchExtendedButton',
                        'id' => 'hSpotlightSearchExtendedButton-'.$id
                    ),
                    'Search',
                    2
                );

            $html =
                $this->hForm->getForm().
                "\n<!-- End Spotlight Extended Search -->\n";

            $this->hForm->resetForm();
        }
        else
        {
            $this->warning('Argument $searchFields must be an array.', __FILE__, __LINE__);
        }

        return $html;
    }

    public function getRolodex($spotlightId, $spotlightNumbers = false)
    {
        # @return HTML

        # @description
        # <h2>Getting a Rolodex</h2>
        # <p>
        #
        # </p>
        # @end

        return $this->getTemplate(
            'Rolodex',
            array(
                'hSpotlightId' => $spotlightId,
                'hSpotlightNumbers' => $spotlightNumbers
            )
        );
    }
}

?>