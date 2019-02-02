<?php

class hFileIcons_4to5 extends hPlugin {

    public function hConstructor()
    {
        $this->hDatabase->query(
            "INSERT INTO `hFileIcons` (
                `hFileIconId`,
                `hFileMIME`,
                `hFileName`,
                `hFileICNS`,
                `hFileExtension`
            ) VALUES 
                (null, 'toolbar/movies', 'movies.png', 'ToolbarMovieFolderIcon.icns', ''),
                (null, 'toolbar/music', 'music.png', 'ToolbarMusicFolderIcon.icns', ''),
                (null, 'toolbar/pictures', 'pictures.png', 'ToolbarPicturesFolderIcon.icns', ''),
                (null, 'toolbar/library', 'library.png', 'ToolbarLibraryFolderIcon.icns', ''),
                (null, 'toolbar/public', 'pedestrian.png', 'ToolbarPublicFolderIcon.icns', ''),
                (null, 'toolbar/applications', 'applications.png', 'ToolbarAppsFolderIcon.icns', ''),
                (null, 'toolbar/downloads', 'downloads.png', 'ToolbarDownloadsFolderIcon.icns', ''),
                (null, 'toolbar/sites', 'sites.png', 'ToolbarSitesFolderIcon.icns', ''),
                (null, 'toolbar/info', 'sites.png', 'ToolbarInfo.icns', '')"
        );
    }
}

?>