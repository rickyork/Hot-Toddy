<?php
 class hLocationStates_1to2 extends hPlugin { public function hConstructor() { $this->hLocationStates ->appendColumn('hLocationStateCreated', hDatabase::time) ->appendColumn('hLocationStateLastModified', hDatabase::time) ->appendColumn('hLocationStateLastModifiedBy', hDatabase::id); } public function undo() { $this->hLocationStates->dropColumns( 'hLocationStateCreated', 'hLocationStateLastModified', 'hLocationStateLastModifiedBy' ); } } ?>