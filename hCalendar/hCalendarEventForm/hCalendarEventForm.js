calendar.ace = null;

calendar.form = {

    setUpAce : function()
    {
        if ($('div#hCalendarTextEditor').length && (calendar.ace == undefined || calendar.ace == null))
        {
            calendar.ace = ace.edit('hCalendarTextEditor');
            calendar.ace.setTheme('ace/theme/textmate');

            var editorMode = require('ace/mode/html').Mode;
            calendar.ace.getSession().setMode(new editorMode());
        }
    },

    directoryPathHasInitialValue : false,

    ready : function()
    {
        if ($('input#hDirectoryPath').val())
        {
            this.directoryPathHasInitialValue = true;
        }

        $('input#hCalendarEventFormSave').button(
            function(event)
            {
                event.preventDefault();
                calendar.form.save();
            }
        );

        $('input#hCalendarEventFormCancel').click(
            function(event)
            {
                event.preventDefault();
                calendar.form.close();
            }
        );

        $('input#hCalendarEventFormContinue').click(
            function(event)
            {
                event.preventDefault();
                $('li#hCalendarEventTab-Properties').click();
            }
        );

        $('input#hCalendarEventFormBack').click(
            function(event)
            {
                event.preventDefault();
                $('li#hCalendarEventTab-Content').click();
            }
        );

        $('input#hCalendarEventImportDocumentButton').click(
            function(event)
            {
                event.preventDefault();

                dialogue.confirm({
                    ok : "Import a Document",
                    cancel : "Don't Import a Document",
                    title : "Import a Document",
                    label :
                        "<p>" +
                            "<b>Warning:</b> Data from the document you import will replace the current " +
                            "Document, Description and Title data.  This cannot be undone." +
                        "</p>" +
                        "<p>" +
                            "Would you like to continue?" +
                        "</p>",
                    callback : {
                        fn : function(confirm)
                        {
                            if (confirm)
                            {
                                this.chooseFile('onImportDocument');
                            }
                        },
                        context : calendar.form
                    }
                });
            }
        );

        $('img#hCalendarFileCategoryAdd').click(
            function(event)
            {
                event.preventDefault();

                dialogue.prompt({
                    ok : "Create Category",
                    cancel : "Don't Create Category",
                    title : "Create a Category",
                    label : "New Category:",
                    callback : {
                        fn : function(categoryName)
                        {
                            if (categoryName && categoryName.length)
                            {
                                this.newFileCategory($('input#hCalendarFileCategoryId').val(), categoryName);
                            }
                        },
                        context : calendar.form
                    }
                });
            }
        );

        $('img#hCalendarFileCategoryRemove').click(
            function(event)
            {
                event.preventDefault();

                if ($('select#hCalendarFileCategories').length)
                {
                    dialogue.confirm({
                        ok : "Delete Categories",
                        cancel : "Don't Delete Categories",
                        title : "Delete Categories",
                        label :
                            "<p>" +
                                "Are you certain you want to PERMANENTLY delete the selected categories?" +
                            "</p>" +
                            "<p>" +
                                "This cannot be undone." +
                            "</p>",
                        callback : {
                            fn : function(confirm)
                            {
                                if (confirm)
                                {
                                    this.deleteFileCategories($('select#hCalendarFileCategories'));
                                }
                            },
                            context : calendar.form
                        }
                    });
                }
            }
        );

        $('img#hCalendarTagCategoryAdd').click(
            function(event)
            {
                // var categoryName = prompt('Create a Category:', '');
                //
                // if (categoryName && categoryName.length)
                // {
                //     calendar.form.newFileCategory($('input#hCalendarTagCategoryId').val(), categoryName);
                // }

                dialogue.prompt({
                    ok : "Create Tag",
                    cancel : "Don't Create Tag",
                    title : "Create a Tag",
                    label : "New Tag:",
                    callback : {
                        fn : function(categoryName)
                        {
                            if (categoryName && categoryName.length)
                            {
                                this.newFileCategory($('input#hCalendarTagCategoryId').val(), categoryName);
                            }
                        },
                        context : calendar.form
                    }
                });
            }
        );

        $('img#hCalendarTagCategoryRemove').click(
            function(event)
            {
                event.preventDefault();

                if ($('select#hCalendarTagCategories').length)
                {
                    dialogue.confirm({
                        ok : "Delete Tags",
                        cancel : "Don't Delete Tags",
                        title : "Delete Tags",
                        label :
                            "<p>" +
                                "Are you certain you want to PERMANENTLY delete the selected tags?" +
                            "</p>" +
                            "<p>" +
                                "This cannot be undone." +
                            "</p>",
                        callback : {
                            fn : function(confirm)
                            {
                                if (confirm)
                                {
                                    this.deleteFileCategories($('select#hCalendarTagCategories'));
                                }
                            },
                            context : calendar.form
                        }
                    });
                }
            }
        );

        $('input#hFileName').keyup(
            function()
            {
                // 190 == .
                if ($(this).val().indexOf('.') != -1)
                {
                    $('b#hFileNameExtension').hide();
                }
                else
                {
                    $('b#hFileNameExtension').show();
                }
            }
        );

        $('input#hCalendarChooseMovieFile').click(
            function(event)
            {
                event.preventDefault();
                calendar.form.chooseFile('onChooseMovieFile');
            }
        );

        $('input#hCalendarRemoveMovieFile').click(
            function(event)
            {
                event.preventDefault();
                $('div#hCalendarMovieFileInner').html('');
            }
        );

        $('input#hCalendarFileThumbnail').click(
            function(event)
            {
                event.preventDefault();
                calendar.form.chooseFile('onChooseThumbnailFile');
            }
        );

        $('input#hCalendarFileThumbnailRemove').click(
            function(event)
            {
                event.preventDefault();
                $('div#hCalendarFileThumbnail div').html('');
                $('span#hCalendarFileThumbnailId').html('');
                this.disabled = true;
            }
        );

        if ($('input#hCalendarCategoryId').length)
        {
            this.toggleDateInputsByCategory($('input#hCalendarCategoryId').val());
        }
        else
        {
            $('select#hCalendarCategoryId').change(
                function()
                {
                    calendar.form.toggleDateInputsByCategory($(this).val());
                }
            );
        }
    },

    openEventCallback : function()
    {
        calendar.form.setUpAce();

        if (calendar.eventFileId)
        {
            calendar.form.getEvent(calendar.eventFileId);
            calendar.eventFileId = 0;
        }
    },

    toggleDateInputsByCategory : function(calendarCategoryId)
    {
        switch (parseInt(calendarCategoryId))
        {
            case 2:
            {
                // Event
                // Hide Date Posted, Show Begin Time, Show End Time
                this.hideDateField();
                this.showEventFields();
                this.hideJobFields();

                $('input#hFileComments')
                    .parents('tr:first')
                    .hide();

                break;
            };
            case 6:
            {
                this.hideEventFields();
                this.showDateField();
                this.showJobFields();

                $('input#hFileComments')
                    .parents('tr:first')
                    .hide();

                break;
            };
            default:
            {
                // Blog, etc.
                // Hide Begin Time, Hide End Time, Show Date Posted
                this.showDateField();
                this.hideEventFields();
                this.hideJobFields();

                $('input#hFileComments')
                    .parents('tr:first')
                    .show();
            };
        }
    },

    hideJobFields : function()
    {
        $('input#hCalendarJobCompany')
            .parents('tr:first')
            .hide();

        $('input#hCalendarJobLocation')
            .parents('tr:first')
            .hide();
    },

    showJobFields : function()
    {
        $('input#hCalendarJobCompany')
            .parents('tr:first')
            .show();

        $('input#hCalendarJobLocation')
            .parents('tr:first')
            .show();
    },

    hideDateField : function()
    {
        $('input#hCalendarDate')
            .parents('tr:first')
            .hide();
    },

    showDateField : function()
    {
        $('input#hCalendarDate')
            .parents('tr:first')
            .show();
    },

    hideEventFields : function()
    {
        $('input#hCalendarBeginTime')
            .parents('tr:first')
            .hide();

        $('input#hCalendarEndTime')
            .parents('tr:first')
            .hide();
    },

    showEventFields : function()
    {
        $('input#hCalendarBeginTime')
            .parents('tr:first')
            .show();

        $('input#hCalendarEndTime')
            .parents('tr:first')
            .show();
    },

    thumb : '',

    onChooseThumbnailFile : function(fileId, filePath)
    {
        var img = new Image();

        this.thumb = fileId;

        img.src = filePath;

        img.onload = function()
        {
            if (this.height > 0 && this.width > 0)
            {
                var width = parseInt($('span#hCalendarFileThumbnailWidth').text());
                var height = parseInt($('span#hCalendarFileThumbnailHeight').text());

                if (isNaN(width))
                {
                    width = 0;
                }

                if (isNaN(height))
                {
                    height = 0;
                }

                if (width > 0 && height > 0 && width == this.width && height == this.height || !width && !height)
                {
                    $('div#hCalendarFileThumbnail div').html(
                        $("<img/>").attr({
                            src : this.src,
                            alt : 'Thumbnail'
                        })
                    );

                    $('span#hCalendarFileThumbnailId').html(calendar.form.thumb);

                    $('input#hCalendarFileThumbnailRemove').removeAttr('disabled');
                }
                else
                {
                    dialogue.alert({
                        title : "Select a Thumbnail Failed!",
                        label :
                            "<p>" +
                                " The selected image could not be used because its dimensions are not exactly " + width + "x" + height +
                            "</p>" +
                            "<p>" +
                                "Please select an image " + width + "x" + height + " in size." +
                            "</p>"
                    });
                }
            }
            else
            {
                dialogue.alert({
                    title : "Select a Thumbnail Failed!",
                    label :
                        "<p>" +
                            "The selected file could not be used for a thumbnail because it is not an image file." +
                        "</p>"
                });
            }
        }
    },

    onChooseMovieFile : function(fileId, filePath)
    {
        $('div#hCalendarMovieFileInner').load(
            hot.path('/hFile/getFileInformation'), {
                hFileId: fileId,
                hFileUnique: 'Movie'
        });
    },

    newFileCategory : function(categoryParentId, categoryName)
    {
        http.get(
            '/hCategory/newCategory', {
                operation : 'Create Event Tag',
                hCategoryParentId : categoryParentId,
                hCategoryName : categoryName
            },
            function(json)
            {
                if (json.hCategoryId)
                {
                    var select = '';

                    if (parseInt(json.hCategoryParentId) == parseInt($('input#hCalendarFileCategoryId').val()))
                    {
                        select = $('select#hCalendarFileCategories');
                    }

                    if (parseInt(json.hCategoryParentId) == parseInt($('input#hCalendarTagCategoryId').val()))
                    {
                        select = $('select#hCalendarTagCategories');
                    }

                    if (select && select.length)
                    {
                        select.append(
                            $("<option/>")
                                .val(json.hCategoryId)
                                .text(json.hCategoryName)
                        );
                    }
                    else
                    {
                        dialogue.alert({
                            ok : "OK",
                            label :
                                "<p>" +
                                    "No select control could be found to append the category to." +
                                "</p>"
                        });
                    }
                }
            }
        );
    },

    fileCategorySelect : '',

    deleteFileCategories : function(select)
    {
        this.fileCategorySelect = select;

        var categories = '';

        select.find('option:selected').each(
            function()
            {
                categories += '&hCategories[]=' + $(this).val();
            }
        );

        http.get(
            {
                url : '/hCategory/deleteCategories',
                operation : 'Delete Event Tag'
            },
            'deleteCategory=1' + categories,
            function(json)
            {
                calendar.form.fileCategorySelect.find('option:selected').remove();
            }
        );
    },

    onImportDocument : function(fileId, filePath)
    {
        http.get(
            '/hCalendar/hCalendarEventForm/getImportedDocument', {
                operation : 'Import Document',
                hFileId : fileId
            },
            function(json)
            {
                calendar.form.setEvent(json);
            }
        );
    },

    chooseFile : function(callback)
    {
        this.choose = window.open(
            hot.path(
                '/Applications/Finder/index.html', {
                    dialogue : 'Choose',
                    onChooseFile : 'calendar.form.' + callback,
                    dialogueButtons : 1
                }
            ),
            'hCalendarChooseDocument',
            'width=800,height=500,scrollbars=no,resizable=yes'
        );

        this.choose.moveTo((window.screen.width - 800) / 2, (window.screen.height - 500) / 2);
        this.choose.focus();
    },

    close : function()
    {
        $('div#hCalendarEvent').fadeOut('slow');
        this.reset();
    },

    reset : function()
    {
        $('li#hCalendarEventTab-Content').click();
        $('div#hCalendarEventForm form').get(0).reset();

        $('input#hFileName').val('');

        if (!this.directoryPathHasInitialValue)
        {
            $('input#hDirectoryPath').val('');
        }

        $('input#hFileId').val(0);

        if ($('span#hCalendarFileThumbnailId').length)
        {
            $('span#hCalendarFileThumbnailId').text('');
            $('div#hCalendarFileThumbnail div').html('');
        }

        calendar.form.setUpAce();

        if (typeof hWYSIWYG !== 'undefined' && hWYSIWYG.editors && hWYSIWYG.editors.hFileDocument)
        {
            // Using FCKEditor
            FCKeditorAPI.GetInstance('hFileDocument').SetHTML('');
        }
        else if (calendar.ace)
        {
            calendar.ace.getSession().setValue('');
        }
        else
        {
            $('textarea#hFileDocument').val('');
        }

        if (typeof hWYSIWYG !== 'undefined' && hWYSIWYG.editors && hWYSIWYG.editors.hFileDescription)
        {
            // Using FCKEditor
            FCKeditorAPI.GetInstance('hFileDescription').SetHTML('');
        }
        else
        {
            $('textarea#hFileDescription').val('');
        }

        $('div#hCalendarMovieFileInner').html('');

        $('b#hFileNameExtension').show();

        if ($('select#hCalendarFileCategories').length)
        {
            if ($('select#hCalendarFileCategories option').length == 1)
            {
                $('select#hCalendarFileCategories option').attr('selected', true);
            }
        }
    },

    getEvent : function(fileId)
    {
        this.reset();

        http.get(
            '/hCalendar/hCalendarEventForm/getEvent', {
                operation : 'Get Event',
                hFileId : fileId
            },
            function(json)
            {
                calendar.form.setEvent(json);
            }
        );
    },

    setEvent : function(json)
    {
        if (!json.hFileHasWriteAccess)
        {
            $('input#hCalendarEventFormSave').attr('disabled', true);
            $('div#hCalendarEventForm').find('input, select, textarea').attr('readonly', true);
        }
        else
        {
            $('input#hCalendarEventFormSave').removeAttr('disabled');
            $('div#hCalendarEventForm').find('input, select, textarea').removeAttr('readonly');
        }

        $('input#hFileTitle').val(json.hFileTitle);
        $('input#hFileHeadingTitle').val(json.hFileHeadingTitle);

        calendar.form.setUpAce();

        if (calendar.ace)
        {
            calendar.ace
                .getSession()
                .setValue(json.hFileDocument);
        }
        else if (typeof hWYSIWYG !== 'undefined' && hWYSIWYG.editors && hWYSIWYG.editors.hFileDocument)
        {
            // Using FCKEditor
            FCKeditorAPI
                .GetInstance('hFileDocument')
                .SetHTML(json.hFileDocument);
        }
        else
        {
            $('textarea#hFileDocument')
                .val(json.hFileDocument);
        }

        if (typeof hWYSIWYG !== 'undefined' && hWYSIWYG.editors && hWYSIWYG.editors.hFileDescription)
        {
            FCKeditorAPI
                .GetInstance('hFileDescription')
                .SetHTML(json.hFileDescription);
        }
        else
        {
            $('textarea#hFileDescription').val(json.hFileDescription);
        }

        if ($('input#hCalendarLink').length)
        {
            $('input#hCalendarLink').val(json.hCalendarLink);
        }

        if (calendar)
        {
            if ($('select#hCalendarId').length)
            {
                $('select#hCalendarId').val(json.hCalendarId);
            }

            if ($('#hCalendarCategoryId').length)
            {
                $('#hCalendarCategoryId').val(json.hCalendarCategoryId);
                this.toggleDateInputsByCategory(json.hCalendarCategoryId);
            }

            $('input#hCalendarDate').val(json.hCalendarDateFormatted);
            $('input#hCalendarBegin').val(json.hCalendarBeginFormatted);
            $('input#hCalendarEnd').val(json.hCalendarEndFormatted);

            $('input#hCalendarBeginTime').val(json.hCalendarBeginTimeFormatted);
            $('select#hCalendarBeginTimeHour').val(json.hCalendarBeginTimeHour);
            $('select#hCalendarBeginTimeMinute').val(json.hCalendarBeginTimeMinute);
            $('select#hCalendarBeginTimeMeridiem').val(json.hCalendarBeginTimeMeridiem);

            $('input#hCalendarEndTime').val(json.hCalendarEndTimeFormatted);
            $('select#hCalendarEndTimeHour').val(json.hCalendarEndTimeHour);
            $('select#hCalendarEndTimeMinute').val(json.hCalendarEndTimeMinute);
            $('select#hCalendarEndTimeMeridiem').val(json.hCalendarEndTimeMeridiem);
        }

        if ($('input#hCalendarJobLocation').length)
        {
            $('input#hCalendarJobLocation').val(json.hCalendarJobLocation);
        }

        if ($('input#hCalendarJobCompany').length)
        {
            $('input#hCalendarJobCompany').val(json.hCalendarJobCompany);
        }

        $('input#hUserName').val(json.hUserName);

        if ($('input#hFileName').length)
        {
            $('input#hFileName').val(json.hFileName);
        }

        if ($('b#hFileNameExtension').length)
        {
            if (json.hFileName.indexOf('.') != -1)
            {
                $('b#hFileNameExtension').hide();
            }
            else
            {
                $('b#hFileNameExtension').show();
            }
        }

        if ($('input#hDirectoryPath').length && !this.directoryPathHasInitialValue)
        {
            $('input#hDirectoryPath').val(json.hDirectoryPath);
        }

        if (json.hUserPermissionsWorld == 1)
        {
            $('input#hUserPermissionWorldRead').attr('checked', true);
        }
        else
        {
            $('input#hUserPermissionWorldRead').removeAttr('checked');
        }

        if (parseInt(json.hFileCommentsEnabled) > 0)
        {
            $('input#hFileComments').attr('checked', true);
        }
        else
        {
            $('input#hFileComments').removeAttr('checked');
        }

        if (json.hCategories)
        {
            if ($('select#hCalendarFileCategories').length)
            {
                $('select#hCalendarFileCategories').val(json.hCategories);
            }

            if ($('select#hCalendarTagCategories'))
            {
                $('select#hCalendarTagCategories').val(json.hCategories);
            }
        }

        if (json.hFileMovieId)
        {
            this.onChooseMovieFile(json.hFileMovieId, null);
        }

        if (json.hCalendarThumbnailId)
        {
            this.onChooseThumbnailFile(json.hCalendarThumbnailId, json.hCalendarThumbnailPath);
        }

        $('input#hFileId').val(json.hFileId);
    },

    onSaveAs : function(directoryPath, fileName, replaceExisting)
    {
        $('input#hFileName').val(fileName);
        $('input#hDirectoryPath').val(directoryPath);
        $('input#hFileReplaceExisting').val(replaceExisting? 1 : 0);

        calendar.form.save();
    },

    save : function()
    {
        // Required Fields...
        //    Title, Document, Date
        var error = '';

        var post = $('div#hCalendarEventForm form').serialize();

        var hFileDocument = '';
        var hFileDescription = '';

        if (typeof hWYSIWYG !== 'undefined' && hWYSIWYG.editors && hWYSIWYG.editors.hFileDocument)
        {
            // Using FCKEditor
            hFileDocument =
                FCKeditorAPI
                    .GetInstance('hFileDocument')
                    .GetHTML();
        }
        else if (calendar.ace)
        {
            hFileDocument =
                calendar.ace
                    .getSession()
                    .getValue();
        }
        else
        {
            hFileDocument = $('textarea#hFileDocument').val();
        }

        if (typeof hWYSIWYG !== 'undefined' && hWYSIWYG.editors && hWYSIWYG.editors.hFileDocument)
        {
            // Using FCKEditor
            hFileDescription =
                FCKeditorAPI
                    .GetInstance('hFileDescription')
                    .GetHTML();
        }
        else
        {
            hFileDescription = $('textarea#hFileDescription').val();
        }

        post +=
            '&hFileDocument=' + encodeURIComponent(hFileDocument) +
            '&hFileDescription=' + encodeURIComponent(hFileDescription);

        if (!$('input#hFileTitle').val().length)
        {
            error = 'Title';
        }

        if (!$('input#hCalendarDate').val().length)
        {
            error = 'Date Posted';
        }

        if ($('span.hFileMovieId').length)
        {
            var hFileMovieId = parseInt($('span.hFileMovieId').text());

            if (hFileMovieId > 0)
            {
                post += '&hFileMovieId=' + hFileMovieId;
            }
        }
        else if ($('div#hCalendarMovieFile').length)
        {
            post += '&hFileMovieId=0';
        }

        if ($('span#hCalendarFileThumbnailId').length)
        {
            post += '&hCalendarFileThumbnailId=' + $('span#hCalendarFileThumbnailId').text();
        }
        else
        {
            post += '&hCalendarFileThumbnailId=0';
        }

        if (!error)
        {
            if ((!$('input#hFileName').val() || !$('input#hDirectoryPath').val()) && $('input#hCalendarPathEnabled').val() != '0')
            {
                calendar.saveAs();
                return;
            }

            application.status.message('Saving Event...');

            http.post(
                {
                    url : '/hCalendar/hCalendarEventForm/save',
                    operation : 'Save Event'
                },
                post,
                function(json)
                {
                    application.status.message('Event Saved!', true);

                    this.getEvents();
                    this.form.close();
                },
                calendar
            );
        }
        else
        {
            application.status.message('Save Failed! Required field: <b>' + error + '</b> is not filled in.');
        }
    }
};

$(window).load(
    function()
    {
        calendar.form.ready();
    }
);