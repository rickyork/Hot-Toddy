<?php

class hFileComments_2to3 extends hPlugin {

    public function hConstructor()
    {
        $this->hFileComments->addColumn(
            'hFileCommentIsAuthor',
            hDatabase::is,
            'hFileCommentIsApproved'
        );
    }

    public function undo()
    {
        $this->hFileComments->dropColumn(
            'hFileCommentIsAuthor'
        );
    }
}

?>