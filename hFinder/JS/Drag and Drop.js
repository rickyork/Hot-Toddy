finder.dragDrop = {
    path : null,

    files : [],

    folders : [],

    ready : function()
    {

    },

    openProgressDialogue : function(files, path)
    {
        this.path = path;

        $('div#hFinderDnDDialogue').fadeIn('fast');
        this.getProgressDialogue(files);
    },

    files : [],

    getProgressDialogue : function(files)
    {
        this.files = [];

        $(files).each(
            function(key, file)
            {
                finder.dragDrop.addFileToQueue(file);
            }
        );

        if (this.files.length)
        {
            this.upload();
        }
        else
        {
            this.closeProgressDialogue();
        }
    },

    closeProgressDialogue : function()
    {
        $('div#hFinderDnDDialogue').fadeOut('fast');
        $('div#hFinderDnDDialogue tbody tr').not('.hFinderDnDTemplate').remove();
    },

    addFileToQueue : function(file)
    {
        if (!file.name && file.fileName)
        {
            file.name = file.fileName;
        }

        if (!file.size && file.fileSize)
        {
            file.size = file.fileSize;
        }

        var fileProperties = finder.fileExists(file.name, this.path);
        var replace = false;

        if (fileProperties.exists)
        {
            if (confirm('A file with the name, ' + file.name + ' already exists, would you like to replace it?'))
            {
                replace = true;
            }
        }

        //alert(file.type);

        if (!fileProperties.exists || replace)
        {
            this.files.push(file);

            var tr = $('tr.hFinderDnDTemplate').clone(true);

            tr.removeClass('hFinderDnDTemplate');

            if (file.type.match(new RegExp('/image\/.*/')) && FileReader)
            {
                var img = document.createElement('img');
                img.file = file;
                img.classList.add('hFinderDnDThumbnail');

                tr.find('td.hFinderDnDFileIcon').html(img);

                var reader = new FileReader();

                reader.onload = function(event)
                {
                    img.src = event.target.result;
                };

                reader.readAsDataURL(file);
            }

            tr.find('td.hFinderDnDFile').text(file.name);
            tr.find('td.hFinderDnDFileSize').text(this.getFileSize(file.size));
            tr.find('td.hFinderDnDFileActivity img').hide();
            tr.attr('title', file.name);

            $('div#hFinderDnDDialogue tbody').append(tr);
        }
    },

    upload : function()
    {
        if (hot.userAgent == 'webkit')
        {
            // This is a work-around for Safari occaisonally hanging when doing an file upload.
            // This prevent you from having to click the submit button twice.
            http.get('/hFile/blank');
        }

        var httpDnD = new XMLHttpRequest();

        if (httpDnD.upload && httpDnD.upload.addEventListener)
        {
            httpDnD.upload.addEventListener(
                'progress',
                function(event)
                {
                    if (event.lengthComputable)
                    {
                        var progress = Math.round((event.loaded * 100) / event.total);

                        $('div#hFinderDnDUploadProgress span').text(progress);
                        $('div#hFinderDnDUploadProgressMeter div').css('width', progress + '%');
                    }
                },
                false
            );

            httpDnD.upload.addEventListener(
                'load',
                function(event)
                {
                    $('div#hFinderDnDUploadProgress span').text(100);
                    $('div#hFinderDnDUploadProgressMeter div').css('width', '100%');
                }
            );
        }

        httpDnD.addEventListener(
            'load',
            function(event)
            {
                //$('div#hFinderDnDUploadProgress span').text(100);
                finder.dragDrop.closeProgressDialogue();

                if (finder.tree)
                {
                    finder.tree.refreshBranchByDirectoryPath(finder.dragDrop.path);
                }

                finder.refresh();

                var json = $.parseJSON(httpDnD.responseText);

                if (!finder.upload)
                {
                    finder.upload = {};
                }

                if (typeof(json) == 'object')
                {
                    finder.upload.duplicatePath = json.duplicatePath;
                    http.responseHasErrors(json.response, 'File Upload');
                }
            },
            false
        );

        if (typeof(FormData) != 'undefined')
        {
            var form = new FormData();

            form.append('path', this.path);

            if (typeof(get.hFileSystemAllowDuplicates) != 'undefined')
            {
                form.append(
                    'hFileSystemAllowDuplicates',
                    get.hFileSystemAllowDuplicates
                );
            }

            $(this.files).each(
                function(key, file)
                {
                    form.append('hFinderUploadFile[]', file);
                    form.append('hFinderUploadTitle[]', '');
                    form.append('hFinderUploadDescription[]', '');
                    form.append('hFinderUploadReplaceFile[]', 1);
                }
            );

            httpDnD.open('POST', '/hFile/upload?json=1');
            httpDnD.send(form);
        }
        else
        {
            dialogue.alert({
                title : 'Error',
                label : 'Your browser does not support standard HTML5 Drag and Drop'
            });

            this.closeProgressDialogue();
        }
    },

    getFileSize : function(bytes)
    {
        switch (true)
        {
            case (bytes < Math.pow(2,10)):
            {
                return bytes + ' Bytes';
            };
            case (bytes >= Math.pow(2,10) && bytes < Math.pow(2,20)):
            {
                return Math.round(bytes / Math.pow(2,10)) +' KB';
            };
            case (bytes >= Math.pow(2,20) && bytes < Math.pow(2,30)):
            {
                return Math.round((bytes / Math.pow(2,20)) * 10) / 10 + ' MB';
            };
            case (bytes > Math.pow(2,30)):
            {
                return Math.round((bytes / Math.pow(2,30)) * 100) / 100 + ' GB';
            };
        }
    }
};


$(document).ready(
    function()
    {
        finder.dragDrop.ready();
    }
);
