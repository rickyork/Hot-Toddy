<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar Resource Shell
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