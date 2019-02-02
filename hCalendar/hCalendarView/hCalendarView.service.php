<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Calendar View Listener
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
# @description
# <h1>Calendar View Listener API</h1>
# <p>
#   Provides a service for getting the HTML of the calendar view alone, without having to 
#   download the entire HTML page.
# </p>
# @end

class hCalendarViewService extends hService {

    private $hCalendarView;

    public function hConstructor()
    {
        # @return void
    
        # @description
        # <h2>Class Constructor</h2>
        # <p>
        #   Includes the 
        #   <a href='/Hot Toddy/Documentation?hCalendar/hCalendarView/hCalendarView.library.php'>hCalendarViewLibrary</a> 
        # </p>
        # @end

        $this->hCalendarView = $this->library('hCalendar/hCalendarView');
    }

    public function get()
    {
        # @return HTML
        
        # @description
        # <h2>Getting a Calendar View</h2>
        # <p>
        #   The <var>get()</var> method is called by accessing the 
        #   following URL: <a href='/hCalendar/hCalendarView/get' class='code'>/hCalendar/hCalendarView/get</a>.
        # </p>
        # <p>
        #   Along with the URL, you can pass several <var>GET</var> arguments that in turn customize the
        #   view returned:
        # </p>
        # <table>
        #   <thead>
        #       <tr>
        #           <th>GET</th>
        #           <th>Required?</th>
        #           <th>Default</th>
        #           <th>Description</th>
        #       </tr>
        #   </thead>
        #   <tbody>
        #       <tr>
        #           <td class='code'>hCalendarWeekday</td>
        #           <td>No</td>
        #           <td class='code'>l (lowercase L)</td>
        #           <td>How the day of the week should be displayed.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarMonth</td>
        #           <td>No</td>
        #           <td class='code'>F</td>
        #           <td>How the name of the month should be displayed.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarDay</td>
        #           <td>No</td>
        #           <td class='code'>j</td>
        #           <td>How the day of the month is displayed.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarYear</td>
        #           <td>No</td>
        #           <td class='code'>Y</td>
        #           <td>How the year is displayed.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarDate</td>
        #           <td>No</td>
        #           <td class='code'><a href='http://www.php.net/mktime'>mktime(
        #   0, 0, 0, 
        #   date('n') + (int) $calendarOffset, 
        #   1,
        #   date('Y')
        #)</a></td>
        #           <td>A unix timestamp representing the month.</td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarUniqueId</td>
        #           <td>No</td>
        #           <td class='code'>hCalendar</td>
        #           <td>
        #               A string that makes the calendar's <var>id</var> attributes contain unique ids when 
        #               multiple calendar view are used in the same page.
        #           </td>
        #       </tr>
        #       <tr>
        #           <td class='code'>hCalendarOffset</td>
        #           <td>No</td>
        #           <td class='code'>0</td>
        #           <td>
        #               A positive or negative number that adjusts the month displayed relative to the 
        #               unix timestamp provided in <var>$_GET['hCalendarDate']</var>.
        #           </td>
        #       </tr>
        #   </tbody>
        # </table>
        # <p>
        #   See: <a href='/Hot Toddy/Documentation?hCalendar/hCalendarView/hCalendarView.library.php'>hCalendarViewLibrary</a> 
        #   and <a href='/Hot Toddy/Documentation?hCalendar/hCalendarView'>hCalendarView</a> 
        # </p>
        # @end
        
    
        if (isset($_GET['hCalendarWeekday']))
        {
            $this->hCalendarWeekday = $_GET['hCalendarWeekday'];
        }

        if (isset($_GET['hCalendarMonth']))
        {
            $this->hCalendarMonth = substr($_GET['hCalendarMonth'], 0, 1);
        }

        if (isset($_GET['hCalendarDay']))
        {
            $this->hCalendarDay = substr($_GET['hCalendarDay'], 0, 1);
        }

        if (isset($_GET['hCalendarYear']))
        {
            $this->hCalendarYear = substr($_GET['hCalendarYear'], 0, 1);
        }

        $this->HTML(
            $this->hCalendarView->get(
                isset($_GET['hCalendarDate'])?     $_GET['hCalendarDate']     : null,
                isset($_GET['hCalendarUniqueId'])? $_GET['hCalendarUniqueId'] : 'hCalendar',
                isset($_GET['hCalendarOffset'])?   $_GET['hCalendarOffset']   : 0
            )
        );
    }    
}

?>