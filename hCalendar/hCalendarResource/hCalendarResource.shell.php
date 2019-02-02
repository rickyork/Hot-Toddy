<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

class hCalendarResourceShell extends hShell {

    private $hCalendarDatabase;

    public function hConstructor()
    {
        if (isset($this->calendarResources) && is_array($this->calendarResources) && count($this->calendarResources))
        {
            $this->hCalendarDatabase = $this->database('hCalendar');

            foreach ($this->calendarResources as $resource)
            {
                $this->console($resource->calendar);

                $this->hCalendarDatabase->saveResource(
                    array(
                        'hCalendarId' => (int) $resource->calendarId,
                        'hCalendarCategoryId' => (int) $resource->calendarCategoryId,
                        'hCalendarResourceName' => $resource->calendar,
                        'hPluginPath' => $resource->pluginPath,
                        'hDirectoryPath' => $resource->directoryPath,
                        'hUserPermissionsOwner' => isset($resource->userPermissionsOwner) ? $resource->userPermissionsOwner : 'rw',
                        'hUserPermissionsWorld' => isset($resource->userPermissionsWorld) ? $resource->userPermissionsWorld : 'r',
                        'hUserPermissionsGroups' => isset($resource->userPermissionsGroups) ? $resource->userPermissionsGroups : array(),
                        'hUserPermissionsInherit' => isset($resource->userPermissionsInherit) ? $resource->userPermissionsInherit : 0
                    )
                );
            }
        }
    }
}

?>