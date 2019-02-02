<?php

class hFileComments_1to2 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileComments->addColumn(
            'hFileCommentIsApproved',
            hDatabase::is,
            'hFileCommentPosted'
        );
    }

    public function undo()
    {
        $this->hFileComments->dropColumn(
            'hFileCommentIsApproved'
        );
    }
}

?>