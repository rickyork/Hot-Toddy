<?php
 class hCalendarFiles_1to2 extends hPlugin { public function hConstructor() {  $this->hDatabase->uses('hCalendarFileDates'); $this->hCalendarFileDates->truncate(); $this->hCalendarFiles->prependColumn('hCalendarFileId', hDatabase::autoIncrement); $this->hCalendarFiles->appendColumn('hCalendarRange', hDatabase::is);  $query = $this->hCalendarFiles->select(); foreach ($query as $data) { $this->hCalendarFileDates->insert( array( 'hCalendarFileId' => (int) $data['hCalendarFileId'], 'hCalendarDate' => (int) $data['hCalendarDate'], 'hCalendarBeginTime' => (int) $data['hCalendarBeginTime'], 'hCalendarEndTime' => (int) $data['hCalendarEndTime'], 'hCalendarAllDay' => 0 ) ); } $this->hCalendarFiles->deleteColumns('hCalendarDate', 'hCalendarBeginTime', 'hCalendarEndTime'); } public function undo() { $this->hCalendarFiles->addColumns( array( array( 'column' => 'hCalendarDate', 'type' => hDatabase::time, 'after' => 'hFileId' ), array( 'column' => 'hCalendarBeginTime', 'type' => hDatabase::time, 'after' => 'hFileId' ), array( 'column' => 'hCalendarEndTime', 'type' => hDatabase::time, 'after' => 'hCalendarBeginTime' ) ) ); $query = $this->hCalendarFileDates->select(); foreach ($query as $data) { $this->hCalendarFiles->update( array( 'hCalendarDate' => $data['hCalendarDate'], 'hCalendarBeginTime' => $data['hCalendarBeginTime'], 'hCalendarEndTime' => $data['hCalendarEndTime'] ), array( 'hCalendarFileId' => $data['hCalendarFileId'] ) ); } $this->hCalendarFiles->dropColumns('hCalendarFileId', 'hCalendarRange'); } } ?>