<?php

# @description
# <h1>Documentation Files Update</h1>
# <ul>
#   <li>Adds an index to <var>hDocumentationFileTitle</var> to speed up title 
#   searches.</li>
#   <li>Makes <var>hDocumentationFileDescription</var> mediumtext instead of text</li>
#   <li>Adds <var>hDocumentationFileClosingDescription</var></li>
# </ul>
# @end

class hDocumentationFiles_1to2 extends hPlugin {
    
    public function hConstructor()
    {
        $this->hDocumentationFiles
            ->addKey('hDocumentationFileTitle')
            ->modifyColumn('hDocumentationFileDescription', hDatabase::mediumText)
            ->addColumn('hDocumentationFileClosingDescription', hDatabase::mediumText, 'hDocumentationFileDescription');
    }
    
    public function undo()
    {
        $this->hDocumentationFiles
            ->dropKey('hDocumentationFileTitle')
            ->modifyColumn('hDocumentationFileDescription', hDatabase::text)
            ->dropColumn('hDocumentationFileClosingDescription');
    }
}

?>