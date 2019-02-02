<?php

#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
#//\\\       \\\\\\\\|
#//\\\ @@    @@\\\\\\| Hot Toddy Framework
#//\\ @@@@  @@@@\\\\\|
#//\\\@@@@| @@@@\\\\\|
#//\\\ @@ |\\@@\\\\\\| https://github.com/rickyork/Hot-Toddy
#//\\\\  ||   \\\\\\\| © Copyright 2019 Richard York, All rights Reserved
#//\\\\  \\_   \\\\\\|
#//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
#//\\\\\  ----  \@@@@| https://github.com/rickyork/Hot-Toddy/blob/master/License
#//@@@@@\       \@@@@|
#//@@@@@@\     \@@@@@|
#//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
# @description
# <h1>File Listener API</h1>
#
# @end

class hFileService extends hService {

    private $methods = array(
        'rename' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_GET' => array(
                    'rename',
                    'replace'
                )
            )
        ),
        'delete' => array(
            'authenticate' => 'rw'
        ),
        'getDirectory' => array(
            'authenticate' => '',
            'isset' => array(
                '_GET' => array('view')
            )
        ),
        'newDirectory' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_GET' => array(
                    'directory',
                    'replace'
                )
            )
        ),
        'symbolicLink' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_POST' => array('link')
            )
        ),
        'getCopy' => array(
            'authenticate' => 'r'
        ),
        'getProperties' => array(
            'authenticate' => 'r'
        ),
        'saveProperties' => array(
            'authenticate' => 'rw'
        ),
        'upload' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_POST' => array(
                    'hFinderUploadTitle',
                    'hFinderUploadDescription'
                ),
                '_FILES' => array(
                    'hFinderUploadFile'
                )
            )
        ),
        'move' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_GET' => array(
                    'sourcePath'
                )
            )
        ),
        'setLabel' => array(
            'authenticate' => 'rw'
        ),
        'getLabel' => array(
            'authenticate' => 'r'
        ),
        'duplicate' => array(
            'authenticate' => 'rw'
        ),
        'touch' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_GET' => array(
                    'replace',
                    'file'
                )
            )
        ),
        'removeFileFromCategory' => array(
            'authenticate' => 'rw',
            'isset' => array(
                '_GET' => array(
                    'file'
                )
            )
        ),
        'exists' => array(

        ),
        'unzip' => array(
            'authenticate' => 'r'
        )
    );

    private $hFinder;
    private $hFile;
    private $hFileIcon;
    private $hFileDatabase;
    private $hUserPermissions;
    private $hPluginInstall;
    private $hCategoryDatabase;
    private $hFileZip;

    public function hConstructor()
    {
        # @description
        # <h2>Constructor</h2>
        # <p>
        #    The class constructor includes some libraries such as
        #    <a href='/Hot Toddy/Documentation?hFinder/hFinder.library.php'>hFinderLibrary</a>,
        #    <a href='/Hot Toddy/Documentation?hFile/hFile.library.php'>hFileLibrary</a>,
        #    and <a href='/Hot Toddy/Documentation?hUser/hUserPermissions/hUserPermissions.library.php'>hUserPermissionsLibrary</a>.
        # </p>
        # <p>
        #    If you are in the <i>root</i> user group, you are allowed to turn off the "duplicate
        #    files restriction" using a <var>GET</var> parameter called <var>hFileSystemAllowDuplicates</var>
        # </p>
        # <p>
        #    Paths passed in by <var>GET</var> or <var>POST</var> as <var>path</var> are passed to
        #    <a href='/Hot Toddy/Documentation?hString#safelyDecodeURL'>hString::safelyDecodeURL()</a>
        # </p>
        # <p>
        #    Then validation for each method defined in the <var>methods</var> property is checked.  This
        #    validation specifies what level of access the user must have to the <var>path</var>, and
        #    what <var>GET</var> or <var>POST</var> parameters are required for each method.
        # </p>
        # @end

        if (!isset($_GET['fileActivityId']))
        {
            $this->hFinder = $this->library('hFinder');
            $this->hFile = $this->library('hFile');
            $this->hUserPermissions = $this->library('hUser/hUserPermissions');

            if ($this->inGroup('root'))
            {
                if (isset($_GET['hFileSystemAllowDuplicates']))
                {
                    $this->hFileSystemAllowDuplicates = (int) $this->get(
                        'fileSystemAllowDuplicates',
                        false
                    );
                }

                if (isset($_POST['hFileSystemAllowDuplicates']))
                {
                    $this->hFileSystemAllowDuplicates = (int) $this->post(
                        'fileSystemAllowDuplicates',
                        false
                    );
                }
            }

            hString::safelyDecodeURL($_GET['path']);
            hString::safelyDecodeURL($_POST['path']);

            if (array_key_exists($this->hServiceMethod, $this->methods))
            {
                if (($json = $this->hFile->listenerValidation($this->methods, $this->hServiceMethod)) <= 0)
                {
                    if ($this->hListenerMethod == 'upload')
                    {
                        $this->setUploadResponse($json);
                        return;
                    }
                    else
                    {
                        $this->JSON($json);
                        return;
                    }
                }
            }
        }
    }

    public function rename()
    {
        # @return JSON
        # @service rename
        # @description
        # <h2>Renaming a File or Folder</h2>
        # <p>
        #    Renaming a file or folder requires the following <var>GET</var> parameters:
        # </p>
        # <ul>
        #    <li>(string) <var>path</var> - the path you want to rename.</li>
        #    <li>(string) <var>rename</var> - the new name of the file or folder you want to rename.</li>
        #    <li>(boolean) <var>replace</var> - if the path already exists, whether or not it should be replaced.</li>
        # </ul>
        # <p>
        #    All three <var>GET</var> parameters are required.
        # </p>
        # <p>
        #    <var>/hFile/rename</var> requires the user to be logged in and to have <b>read &amp; write</b> access to the
        #    path specified in <var>path</var>.
        # </p>
        # @end

        $this->JSON(
            (string) $this->hFile->rename(
                $this->get('path'),
                $this->get('rename'),
                $this->get('replace') == 1
            )
        );
    }

    public function delete()
    {
        # @return JSON
        # @service delete
        # @description
        # <h2>Deleting a File or Folder</h2>
        # <p>
        #    Deleting a file or folder requires the following <var>GET</var> parameters:
        # </p>
        # <ul>
        #    <li>(string) <var>path</var> - the path you want to delete.</li>
        # </ul>
        # <p>
        #   <var>/hFile/delete</var> requires the user to be logged in and to have
        #   <b>read &amp; write</b> access to the path specified in <var>path</var>.
        # </p>
        # @end

        $this->JSON(
            (string) $this->hFile->delete(
                $this->hFile->filePath
            )
        );
    }

    public function newDirectory()
    {
        # @return JSON

        # @description
        # <h2>Creating a New Folder</h2>
        # <p>
        #    Creating a folder requires the following <var>GET</var> parameters:
        # </p>
        # <ul>
        #    <li>(string) <var>path</var> - the path where you want to create the directory.</li>
        #    <li>(string) <var>directory</var> - the name of the directory you wish to create.</li>
        #    <li>(boolean) <var>replace</var> - if the directory already exists, whether or not it should be replaced.</li>
        # </ul>
        # <p>
        #    All three <var>GET</var> parameters are required.
        # </p>
        # <p>
        #   <var>/hFile/newDirectory</var> requires the user to be logged in and to have
        #   <b>read &amp; write</b> access to the path specified in <var>path</var>.
        # </p>
        # @end

        $path = $this->getConcatenatedPath(
            $this->get('path'),
            $this->get('directory')
        );

        $replace = $this->get('replace', false);

        if (!$replace && $this->hFile->exists($path))
        {
            $this->hFile->delete($path);
        }

        $json = $this->hFile->newDirectory(
            $this->get('path'),
            $this->get('directory')
        );

        $this->JSON((string) $json);
    }

    public function symbolicLink()
    {
        # @return JSON

        # @description
        # <h2>Creating a Symbolic Link</h2>
        # <p class='hDocumentationWarning'>
        #    This functionality is not working at this time.
        # </p>
        # <p>
        #    Creating a symbolic link requires the following <var>GET</var> parameters:
        # </p>
        # <ul>
        #    <li>(string) <var>path</var> - the path where you want to create the symbolic link.</li>
        #    <li>(integer) <var>link</var> - the fileId of the file you want to create a symbolic link of.</li>
        # </ul>
        # <p>
        #    Both <var>GET</var> parameters are required.
        # </p>
        # <p>
        #    <var>/hFile/symbolicLink</var> requires the user to be logged in and to have
        #    <b>read &amp; write</b> access to the path specified in <var>path</var>.
        # </p>
        # @end

        hString::scrubArray($_POST);

        $this->JSON(
            (string) $this->hFile->newSymbolicLink(
                $this->hFile->filePath,
                $this->post('link')
            )
        );
    }

    public function getCopy()
    {
        # @return HTML | Text

        # @description
        # <h2>Getting a File's Copy</h2>
        # <p>
        #   Returns the copy for the file specified in <var>path</var>. The <var>path</var>
        #   <var>GET</var> parameter is required. The user must have <b>read</b> access to
        #   the document.
        # </p>
        # @end

        $this->HTML(
            $this->getFileDocument($this->hFile->fileId)
        );
    }

    public function upload()
    {
        # @return HTML | JSON

        # @description
        # <h2>Uploading Documents</h2>
        # <p>
        #   Files are uploaded using <var>POST</var>. The uploaded file field must be named
        #   <var>hFinderUploadFile</var>, this field must be posted as an array, since
        #   <var>upload</var> can handle just one uploaded file, or multiple. The array
        #   should be numerically offset.
        # </p>
        # <p>
        #   Along with each file being uploaded, the following fields must also exist in the
        #   <var>POST</var> data:
        # </p>
        # <ul>
        #   <li>
        #       <var>hFinderUploadTitle</var> - This becomes the file's <var>hFileTitle</var>.
        #   </li>
        #   <li>
        #       <var>hFinderUploadDescription</var> - This becomes the file's
        #       <var>hFileDescription</var>.
        #   </li>
        #   <li>
        #       <var>hFinderUploadWorldRead</var> - This is a boolean (0 or 1) that dictates
        #       whether the file should be publicly accessible.
        #   </li>
        #   <li>
        #       <var>hFinderUploadReplaceFile</var> - This is a boolean (0 or 1) that dictates
        #       whether or not existing files should be replaced.
        #   </li>
        # </ul>
        # <p>
        #   The preceding <var>POST</var> variables should be provided in an array, offset to
        #   match the file each set of data is intended to go with.
        # </p>
        # <p>
        #   This information is then passed on to one of the following:
        #   <a href='/Hot Toddy/Documentation?hFile/hFileInterface/hFileInterfaceDatabase/hFileInterfaceDatabase.library.php#upload'>hFileInterfaceDatabaseLibrary::upload()</a>
        #   or <a href='/Hot Toddy/Documentation?hFile/hFileInterface/hFileInterfaceUnix/hFileInterfaceUnix.library.php#upload'>hFileInterfaceUnixLibrary::upload()</a>
        # </p>
        # <p>
        #   The response is HTML, by default, but if the <var>json</var> <var>GET</var> parameter is set to <var>1</var>,
        #   then the response will be JSON instead.
        # </p>
        # @end

        $files = array();

        if (is_array($_FILES['hFinderUploadFile']['tmp_name']))
        {
            $n = 0;

            foreach ($_FILES['hFinderUploadFile']['tmp_name'] as $uploadCounter => $tempPath)
            {
                $path = $_FILES['hFinderUploadFile']['tmp_name'][$uploadCounter];
                $name = $_FILES['hFinderUploadFile']['name'][$uploadCounter];
                $mime = $_FILES['hFinderUploadFile']['type'][$uploadCounter];
                $size = $_FILES['hFinderUploadFile']['size'][$uploadCounter];

                if (!empty($path))
                {
                    $extension = $this->getExtension($name);

                    if ($extension == 'hot')
                    {
                        $this->hPluginInstall = $this->library(
                            'hPlugin/hPluginInstall',
                            array(
                                'path' => $path,
                                'name' => $name,
                                'mime' => $mime
                            )
                        );

                        continue;
                    }

                    if (!empty($_POST['hFinderUploadReplaceFile'][$uploadCounter]))
                    {
                        $path = $this->getConcatenatedPath(
                            $this->post('path'),
                            $name
                        );

                        $this->hFile->query($path);

                        if ($this->hFile->isDirectory)
                        {
                            $this->hFile->delete($path);
                        }
                    }

                    $files[$n] = array(
                        'hFileTempPath'           => $path,
                        'hFileMIME'               => $mime,
                        'hFileName'               => hString::escapeAndEncode($name),
                        'hFileSize'               => $size,
                        'hFileTitle'              => $_POST['hFinderUploadTitle'][$uploadCounter],
                        'hFileDescription'        => $_POST['hFinderUploadDescription'][$uploadCounter],
                        'hUserPermissions'        => true,
                        'hUserPermissionsWorld'   => !empty($_POST['hFinderUploadWorldRead'][$uploadCounter])? 'r' : '',
                        'hUserPermissionsInherit' => true,
                        'hFileReplace'            => !empty($_POST['hFinderUploadReplaceFile'][$uploadCounter])
                    );

                    $n++;
                }
            }

            $this->setUploadResponse(
                $this->hFile->upload(
                    $this->post('path'),
                    $files
                )
            );

            return;
        }
        else
        {
            $this->setUploadResponse(0);
        }
    }

    private function setUploadResponse($response)
    {
        # @return JSON | HTML

        # @description
        # <h2>Setting the Response for Uploaded File(s)</h2>
        # <p>
        #   This method handles sending the response for uploaded file(s), if
        #   the <var>json</var> <var>GET</var> parameter is <var>1</var>, the
        #   response will be <var>JSON</var>. If the <var>json</var>
        #   <var>GET</var> parameter is <var>0</var> or does not exist, then
        #   the response will be <var>HTML</var>.
        # </p>
        # @end

        $sendJSON = $this->get('json');

        if ($sendJSON)
        {
            $this->JSON(
                array(
                    'response' => $response,
                    'duplicatePath' => $this->hFileDuplicatePath(nil)
                )
            );
        }
        else
        {
            $this->HTML(
                $this->getTemplate(
                    'Upload',
                    array(
                        'response' => $response,
                        'duplicatePath' => $this->hFileDuplicatePath(null)
                    )
                )
            );
        }
    }

    public function getProperties()
    {
        # @return JSON

        # @description
        # <h2>Getting File Properties</h2>
        # <p>
        #    Returns file properties in JSON. The following properties are returned:
        # </p>
        # <table>
        #    <tbody>
        #        <tr>
        #            <td class='code'>hFileTitle</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hFileDescription</td>
        #        </tr>
        #        <tr>
        #            <td class='code'>hUserPermissionsWorldRead</td>
        #        </tr>
        #    </tbody>
        # </table>
        # @end

        $fileId = $this->hFile->fileId;

        $permissions = $this->hFiles->getPermissions($fileId);

        $this->JSON(
            array(
                'hFileTitle' => $this->getFileTitle($fileId),
                'hFileDescription' => hString::decodeHTML($this->getFileDescription($fileId)),
                'hUserPermissionsWorldRead' => $permissions['hUserPermissionsWorld']
            )
        );
    }

    public function saveFinderProperties()
    {
        # @return HTML

        # @description
        # <h2>Saving Finder Properties</h2>
        # <p>
        #
        # </p>
        # @end

        $fileId = (int) $this->post('finderEditFileId');

        $files = array();

        $file = array(
            'hFileId' => $fileId,
            'hFileTitle' => $this->post('finderEditFileTitle'),
            'hFileDescription' => $this->post('finderEditFileDescription'),
            'hUserPermissions' => true,
            'hUserPermissionsWorld' => ($this->post('finderEditFileWorldRead') == 1)
        );

        if (isset($_POST['hUserPermissionsGroups']))
        {
            foreach ($file['hUserPermissionsGroups'] as $group)
            {
                $file['hUserPermissionsGroups'][$group] = 'r';
            }
        }

        $temporaryName = $this->fileUpload(
            'finderEditFileUpload',
            'temporaryName'
        );

        if (!empty($temporaryName))
        {
            $files[0] = array(
                'hFileTempPath' => $temporaryName,
                'hFileMIME' => $this->fileUpload(
                    'finderEditFileUpload',
                    'type'
                ),
                'hFileName' => $this->getFileName($fileId),
                'hFileSize' => $this->fileUpload(
                    'finderEditFileUpload',
                    'size'
                ),
                'hFileReplace'  => 1
            );

            $files[0] = array_merge($files[0], $file);

            $response = $this->hFile->upload(
                urldecode(
                    $this->post('path')
                ),
                $files
            );
        }
        else
        {
            $this->hFileDatabase = $this->database('hFile');
            $this->hFileDatabase->save($file);
            $response = 1;
        }

        $this->HTML(
            $this->getTemplate(
                'Save Finder Properties',
                array(
                    'onSaveProperties' => $this->get(
                        'onSaveProperties',
                        'finder.editFile.processResponse'
                    ),
                    'response' => $response,
                    'duplicatePath' => $this->hFileDuplicatePath(null)
                )
            )
        );
    }

    public function saveProperties()
    {
        # @return HTML

        # @description
        # <h2>Saving File Properties</h2>
        # <p>
        #
        # </p>
        # @end

        $fileId = (int) $this->post('fileId', 0);

        $files = array();

        $file = array(
            'hFileTitle' => $this->post('fileTitle'),
            'hFileDescription' => $this->post('fileDescription')
        );

        if (isset($_POST['hFileDocument']))
        {
            $file['hFileDocument'] = $_POST['hFileDocument'];
        }

        if (isset($_POST['hFileKeywords']))
        {
            $file['hFileKeywords'] = $_POST['hFileKeywords'];
        }

        if (isset($_POST['hCategories']))
        {
            $file['hCategories'] = $_POST['hCategories'];
        }

        if (isset($_POST['hCategories']) && !is_array($_POST['hCategories']))
        {
            $file['hCategories'] = array();
        }

        if ($this->hEditorPropertiesSetPermissions(false))
        {
            $file['hUserPermissions'] = true;

            if (isset($_POST['hUserPermissionsGroups']))
            {
                foreach ($_POST['hUserPermissionsGroups']  as $group)
                {
                    $file['hUserPermissionsGroups'][$group] = 'r';
                }
            }

            if (isset($_POST['hUserPermissionsWriteGroups']))
            {
                foreach ($_POST['hUserPermissionsWriteGroups'] as $group)
                {
                    $file['hUserPermissionsGroups'][$group] = 'rw';
                }
            }

            $file['hUserPermissionsWorld'] = (empty($_POST['hUserPermissionsWorldRead'])? '' : 'r');
        }

        if (!empty($_FILES['hFileUpload']['tmp_name']))
        {
            $files[0] = array(
                'hFileTempPath' => $_FILES['hFileUpload']['tmp_name'],
                'hFileMIME' => $_FILES['hFileUpload']['type'],
                'hFileName' => $this->getFileName($fileId),
                'hFileSize' => $_FILES['hFileUpload']['size'],
                'hFileReplace' => 1
            );

            $files[0] = array_merge($files[0], $file);

            $response = $this->hFile->upload(
                urldecode($_POST['path']),
                $files
            );
        }
        else
        {
            $file['hFileId'] = $fileId;
            $this->hFileDatabase = $this->database('hFile');
            $this->hFileDatabase->save($file);
            $response = 1;
        }

        $this->HTML(
            $this->getTemplate(
                'Save Properties',
                array(
                    'onSaveProperties' => isset($_GET['onSaveProperties'])? $_GET['onSaveProperties'] : 'finder.editFile.processResponse',
                    'response' => $response,
                    'duplicatePath' => $this->hFileDuplicatePath(null)
                )
            )
        );
    }

    public function move()
    {
        # @return JSON

        # @description
        # <h2>Moving a File</h2>
        # <p>
        #
        # </p>
        # @end

        //$this->hFile->query(urldecode($_GET['sourcePath']));
        $sourcePath = urldecode(
            $this->get('sourcePath')
        );

        hString::safelyDecodeURL($sourcePath);

        $this->JSON(
            (string) $this->hFile->move(
                $this->get('path'),
                $sourcePath,
                $this->get('replace', false) == 1
            )
        );
    }

    public function getFileInformation()
    {
        # @return HTML

        # @description
        # <h2>Getting File Information (HTML)</h2>
        # <p>
        #
        # </p>
        # @end

        $fileId = $this->post('fileId');

        if (!$fileId)
        {
            $this->JSON(-5);
            return;
        }

        hString::scrubArray($_POST);

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        $this->HTML(
            $this->getTemplate(
                'Information',
                array(
                    'hFileUnique' => $this->post('fileUnique', nil),
                    'hFileId' => $fileId,
                    'hFileIconPath' => $this->hFileIcon->getFileIconPath($fileId),
                    'hFileTitle' => $this->getFileTitle($fileId),
                    'hFilePath' => $this->getFilePathByFileId($fileId)
                )
            )
        );
    }

    public function getFileInformationJSON()
    {
        # @return JSON

        # @description
        # <h2>Getting File Information (JSON)</h2>
        # <p>
        #
        # </p>
        # @end

        $fileId = (int) $this->get('fileId');

        if (!$fileId)
        {
            $this->JSON(-5);
            return;
        }

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        $this->JSON(
            array(
                'hFileId' => $fileId,
                'hFileIconPath' => $this->hFileIcon->getFileIconPath($fileId),
                'hFilePath' => $this->getFilePathByFileId($fileId),
                'hFileTitle' => hString::entitiesToUTF8(
                    $this->getFileTitle($fileId)
                )
            )
        );
    }

    public function getFileInformationXML()
    {
        # @return XML

        # @description
        # <h2>Getting File Information (XML)</h2>
        # <p>
        #
        # </p>
        # @end

        $fileId = (int) $this->get('fileId');

        if (!$fileId)
        {
            $this->XML(-5);
            return;
        }

        $this->hFileIcon = $this->library('hFile/hFileIcon');

        $this->XML(
            $this->getTemplate(
                'Information.xml',
                array(
                    'hFileId' => $fileId,
                    'hFileIconPath' => $this->hFileIcon->getFileIconPath($fileId),
                    'hFilePath' => $this->getFilePathByFileId($fileId),
                    'hFileTitle' => hString::entitiesToUTF8(
                        $this->getFileTitle($fileId)
                    )
                )
            )
        );
    }

    public function getFilePath()
    {
        # @return JSON

        # @description
        # <h2>Getting a File's Path</h2>
        # <p>
        #   <var>/hFile/getFilePath</var> takes the <var>hFileId</var> passed in by <var>GET</var> and returns
        #   the <var>hFilePath</var> by <var>JSON</var>.
        # </p>
        # @end

        $this->JSON(
            $this->getFilePathByFileId(
                (int) $this->get('fileId', 1)
            )
        );
    }

    public function blank()
    {
        # @return HTML

        # @description
        # <h2>Getting a Totally Blank Page</h2>
        # <p>
        #    <var>/hFile/blank</var> can be used for the default path of an <var>&lt;iframe&gt;</var> or
        #    another element.  This is done when an element has some sort of dynamic interaction, or
        #    will be loaded later.
        # </p>
        # @end

        $this->HTML('');
    }

    public function setLabel()
    {
        # @return JSON

        # @description
        # <h2>Setting a File or Folder's Label</h2>
        # <p>
        #    <var>/hFile/setLabel</var> sets the color label for a file or folder.  The <var>GET</var> <var>path</var>
        #    argument is required, and the usergmust have <b>read &amp; write</b> access
        #    to the path being modified.
        # </p>
        # @end

        $label = $this->get('fileLabel', 'none');

        $this->hFile->setLabel(
            $this->get('path'),
            $label
        );

        $this->JSON(1);
    }

    public function getLabel()
    {
        # @return JSON

        # @description
        # <h2>Getting a Label</h2>
        # <p>
        #    <var>/hFile/getLabel</var> fets the color label for a file or folder.  The <var>GET</var> <var>path</var>
        #    argument is required, and the user must have <b>read</b> access
        #    to the path.
        # </p>
        # @end

        $this->JSON(
            ucwords(
                $this->hFile->getLabel(
                    $this->get('path')
                )
            )
        );
    }

    public function duplicate()
    {
        # @return JSON

        # @description
        # <h2>Duplicating a File</h2>
        # <p>
        #    <var>/hFile/duplicate</var> duplicates the file passed in <var>GET</var>
        #    <var>path</var> (directories are not supported).  The user must have <b>read &amp; write</b> access to
        #    the file being copied.
        # </p>
        # @end

        $this->JSON(
            $this->hFile->copy(
                $this->get('path')
            )
        );
    }

    public function touch()
    {
        # @return JSON

        # @description
        # <h2>Creating a File</h2>
        # <p>
        #   <var>/hFile/touch</var> creates a file passed in <var>GET</var>
        #   <var>file</var> at <var>path</var>.  <var>replace</var> indicates
        #   whether or not the new file should replace a file that already
        #   exists at that location, if there is a file that already exists
        #   at that location.  The user must have <b>read &amp; write</b> access to
        #   the directory where the file is being created.
        # </p>
        # @end

        $path = $this->getConcatenatedPath(
            $this->get('path'),
            $this->get('file')
        );

        if (!empty($_GET['replace']) && $this->hFile->exists($path))
        {
            $this->hFile->delete($path);
        }

        $this->JSON(
            $this->hFile->touch($path)
        );
    }

    public function removeFileFromCategory()
    {
        # @return JSON

        # @description
        # <h2>Removing a File From a Category</h2>
        # <p>
        #   <var>/hFile/removeFileFromCategory</var> removes the file specified in <var>file</var>
        #   from the category path specified in <var>path</var>.  Both parameters are provided
        #   as <var>GET</var> arguments and both parameters are required.  The user must have
        #   <b>read &amp; write</b> access to the category the file is being removed from to
        #   perform this action.
        # </p>
        # @end

        hString::safelyDecodeURL(
            $this->get('file')
        );

        $categoryId = $this->getCategoryIdFromPath(
            $this->get('path')
        );

        $fileId = $this->getFileIdByFilePath(
            $this->get('file')
        );

        $this->hCategoryDatabase = $this->database('hCategory');

        $this->hCategoryDatabase->removeFileFromCategory(
            $fileId,
            $categoryId
        );

        $this->JSON(1);
    }

    public function exists()
    {
        # @return JSON

        # @description
        # <h2>Determining if a Path Exists</h2>
        # <p>
        #   <var>/hFile/exists</var> simply reports whether or not the specified <var>path</var>
        #   (a <var>GET</var> parameter) exists.
        # </p>
        # @end

        $this->JSON(
            $this->hFile->exists(
                $this->get('path')
            )
        );
    }

    public function unzip()
    {
        # @return JSON

        # @description
        # <h2>Unzipping a Zipped File</h2>
        # <p>
        #    <var>/hFile/unzip</var> unzips the zipped file in the folder the action is called upon.
        #    The zipped file is passed in the <var>path</var> parameter (<var>GET</var>).  To
        #    perform this action, the user must have <b>read</b> access to the zip file and
        #    <b>read &amp; write</b> access to the directory.
        # </p>
        # @end

        $this->hFileZip = $this->library('hFile/hFileZip');

        $path = $this->get('path');

        $directory = dirname($path);
        $directoryId = $this->getDirectoryId($directory);

        if ($this->hDirectories->hasWritePermission($directoryId))
        {
            $this->hFileZip->unzipToHtFSFromHtFS($path);
            $this->JSON(1);
        }
        else
        {
            $this->JSON(-1);
        }
    }

    public function activity()
    {
        # @return JSON

        # @description
        # <h2>Logging Analytics</h2>
        # <p>
        #    <var>/hFile/activity</var> logs some metrics to the <var>hFileActivity</var> database table,
        #    this information is passed in using <var>POST</var>.
        # </p>
        # <ul>
        #    <li><var>networkBenchmark</var> - How long it took the page to load including network latency.</li>
        #    <li><var>pageLoadBenchmark</var> - How long it took the server to create the page.</li>
        #    <li><var>screenResolution</var> - How big the user's screen is.</li>
        #    <li><var>colorDepth</var> - How many colors the user's screen is capable of displaying.</li>
        # </ul>
        # @end

        if (!empty($_GET['fileActivityId']))
        {
            $networkBenchmark = (int) $this->post('networkBenchmark');
            $pageLoadBenchmark =  (int) $this->post('pageLoadBenchmark');

            if ($networkBenchmark < 0)
            {
                $networkBenchmark = -$networkBenchmark;
            }

            if ($pageLoadBenchmark < 0)
            {
                $pageLoadBenchmark = -$pageLoadBenchmark;
            }

            $this->hFileActivity->update(
                array(
                    'hUserScreenResolution' => $this->post['screenResolution'],
                    'hUserScreenColorDepth' => (int) $this->post('colorDepth'),
                    'hFileNetworkBenchmark' => $networkBenchmark,
                    'hFilePageLoadBenchmark' => $pageLoadBenchmark
                ),
                (int) $_GET['fileActivityId']
            );

            $this->JSON(1);
        }
        else
        {
            $this->JSON(-5);
        }
    }
}

?>