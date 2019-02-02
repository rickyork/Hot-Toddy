<?php
  class hGoogleSiteMap extends hPlugin { public function hConstructor() { $this->hTemplatePath = ''; $this->hFileMIME = 'application/xml'; $query = $this->hDatabase->query( "SELECT `hFiles`.`hFileId`,
                    `hFiles`.`hFileLastModified`,
                    `hFiles`.`hFileCreated`,
                    REPLACE(CONCAT((SELECT `hDirectoryPath` FROM `hDirectories` WHERE `hDirectoryId` = `hFiles`.`hDirectoryId`), '/', `hFiles`.`hFileName`), '{$this->hFrameworkSite}', '') AS `hFilePath`
               FROM `hFiles`,
                    `hDirectories`,
                    `hUserPermissions`
              WHERE `hUserPermissions`.`hFrameworkResourceId` = 1
                AND `hFiles`.`hFileId` = `hUserPermissions`.`hFrameworkResourceKey`
                AND `hFiles`.`hDirectoryId` = `hDirectories`.`hDirectoryId`
                AND (`hDirectories`.`hDirectoryPath` = '/{$this->hFrameworkSite}' OR `hDirectories`.`hDirectoryPath` LIKE '/{$this->hFrameworkSite}/%')
                AND `hUserPermissions`.`hUserPermissionsWorld` LIKE 'r%'
                AND (`hFiles`.`hFileName` LIKE '%.html'
                 OR  `hFiles`.`hFileName` LIKE '%.htm'
                 OR  `hFiles`.`hFileName` LIKE '%.product'
                 OR  `hFiles`.`hFileName` LIKE '%.pdf'
               )" ); $hFiles = array(); while ($data = $this->hDatabase->getAssociativeResults($query)) { $variables = $this->getFileVariables($data['hFileId']); $hFiles['hFilePath'][] = $data['hFilePath']; $hFiles['hFileId'][] = $data['hFileId']; $hFiles['hFileLastModified'][] = date('Y-m-d', empty($data['hFileLastModified'])? $data['hFileCreated'] : $data['hFileLastModified']); if (isset($variables['hGoogleChangeFrequency'])) { $hFiles['hGoogleChangeFrequency'][] = $variables['hGoogleChangeFrequency']; } if (isset($variables['hGooglePriority'])) { $hFiles['hGooglePriority'][] = $variables['hGooglePriority']; } } $this->hDatabase->closeResults($query); $this->hFileDocument = $this->getTemplateXML( 'Sitemap', array( 'hFiles' => $hFiles, 'hServerHost' => $this->hServerHost, 'benchmark' => $this->getBenchmark() ) ); } } ?>