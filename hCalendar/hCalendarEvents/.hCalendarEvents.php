<?php
  class hCalendarEvents extends hPlugin { private $hCalendarEvents; public function hConstructor() { $this->hCalendarEvents = $this->library('hCalendar/hCalendarEvents'); if ($this->hCalendarEventPost(false)) { $this->hCalendarEvents->setSingleFile($this->hFileId, $this->hCalendarEventsPath('/Events.html')); } $this->hFileDocument = $this->hCalendarEvents->get( $this->hCalendarId(1), $this->hCalendarCategoryId(2), isset($_GET['hCalendarDate'])? (int) $_GET['hCalendarDate'] : null, $this->hCalendarEventCount(10) ); } } ?>