hot.dialogueAutoSelect = false;

$.fn.extend({
    getDivision : function()
    {
        return this.parents('.hFormDivision');
    },

    selectPanel : function()
    {
        editor.document.unselectPanel();
        editor.document.selectedPanel = this;
        this.addClass('hEditorDocumentSelectedPanel');

        return this;
    },

    toggleSelectedListItem : function()
    {
        if (editor.document.selectedListItem)
        {
            editor.document.selectedListItem.removeClass('hListFileSelected');
        }

        editor.document.selectedListItem = this;
        editor.document.selectedListItem.addClass('hListFileSelected');
    },

    selectPanelDivision : function()
    {
        editor.document.unselectPanelDivision();

        editor.document.selectedPanelDivision = this;
        editor.document.selectedPanelDivision.addClass('hEditorDocumentSelectePanelDivision');
    }
});

if (editor == undefined)
{
    var editor = {};
}

editor.document = {
    selectedPanel : null,
    selectedPanelDivision : null,
    panelActive: false,
    panelTimer: false,
    selectedListItem : null,

    hListId: 0,
    hEditorSelect: null,

    hFile : [
        'hFileId',
        'hDirectoryId',
        'hUserId',
        'hFileParentId',
        'hFileName',
        'hFileSortIndex'
    ],

    hFileDocument : [
        'hFileDescription',
        'hFileKeywords',
        'hFileTitle'
    ],

    hFileHeaders : [
        'hFileCSS',
        'hFileJavaScript'
    ],

    hFileProperties : [
        'hFileIconId',
        'hFileMIME'
    ],

    hFileVariables : [
        'hGoogleSitemapPriority',
        'hGoogleSitemapChangeFrequency'
    ],

    hCalendarFiles : [
        'hFileCalendarId',
        'hFileCalendarCategoryId',
        'hFileId',
        'hFileCalendarDate'
    ],

    post : null,

    ready : function()
    {
        var mode = $('div#hEditorDocumentTextEditor').data('editor-mode');

        editor.ace = ace.edit('hEditorDocumentTextEditor');

        editor.ace
            .setTheme('ace/theme/textmate');

        editor.ace
            .getSession()
            .setMode('ace/mode/' + mode);

        editor.ace
            .getSession()
            .setUseSoftTabs(true);

        top.$('input#hEditorSave').removeAttr('disabled');
        top.$('input#hEditorSaveAs').removeAttr('disabled');

        if (get.path == '')
        {
            $('input#hFileId').val(0);
        }

        top.editor.frameStatus[get.id] = true;

        $('div.hFormDivisionLegend')
            .hover(
                function()
                {
                    $(this).getDivision().addClass('hEditorDocumentPanelOn');
                },
                function()
                {
                    $(this).getDivision().removeClass('hEditorDocumentPanelOn');
                }
            )
            .click(
                function()
                {
                    $(this).getDivision().SelectPanel();
                }
            );

        $('select#hLists').change(
            function()
            {
                editor.document.getListFiles(this.value);
            }
        );

        $('img#hListFileAdd').click(
            function()
            {
                editor.document.addListFile();
            }
        );

        $('img#hListFileRemove').click(
            function()
            {
                if (editor.document.selectedListItem)
                {
                    editor.document.selectedListItem.remove();
                }
            }
        );

        $('img.hFileDocumentAddButton')
            .mousedown(
                function()
                {
                    $(this).attr('src', hot.path('/images/themes/aqua/icons/misc/add_pressed.png'));
                }
            )
            .mouseup(
                function()
                {
                    $(this).attr('src', hot.path('/images/themes/aqua/icons/misc/add.png'));
                }
            );

        $('input#hEditorChooseParentFile').click(
            function(event)
            {
                event.preventDefault();
                //editor.document.RemoveParentDocument();
                editor.document.openChooser('onChooseParentFile');
            }
        );

        $('input#hEditorRemoveParentFile').click(
            function(event)
            {
                event.preventDefault();
                editor.document.removeParentDocument();
            }
        );

        $('img.hFileDocumentRemoveButton')
            .mousedown(
                function()
                {
                    $(this).attr('src', hot.path('/images/themes/aqua/icons/misc/remove_pressed.png'));
                }
            )
            .mouseup(
                function()
                {
                    this.src = hot.path('/images/themes/aqua/icons/misc/remove.png');
                },
                false
            );

        $('div#hEditorSelectDirectory').click(
            function()
            {
                editor.document.selectDirectory();
            }
        );

        this.panelTracker();

        $('input#hEditorDocumentAddVariable').click(
            function(event)
            {
                event.preventDefault();
                editor.document.addVariable();
            }
        );

        $('input.hFileVariable, input.hFileValue').focus(
            function(event)
            {
                event.preventDefault();

                $(this).parents('tr')
                       .select('hFileVariable');
            }
        );

        $('input#hEditorDocumentDeleteVariable').click(
            function(event) {
                event.preventDefault();

                if (hot.selected('hFileVariable').length)
                {
                    if ($(this).parents('tr').prevAll().length > 1)
                    {
                        hot.selected('hFileVariable').remove();

                        var i = 0;

                        $(this).parents('tbody').find('tr').each(
                            function()
                            {
                                editor.document.renumberVariableRow($(this), i);
                                i++;
                            }
                        );
                    }
                    else
                    {
                        editor.document.renumberVariableRow($(this).parents('tr').prev(), 0, true);
                    }
                }
            }
        );

        $('span#hEditorDocumentClose').click(
            function()
            {
                top.editor.closeDocument();
            }
        );

        $('div.hFormDivision:not(div#hEditorDocumentContent)').hide();

        if (top.$('li.hEditorActionSelected').length)
        {
            top.$('li.hEditorActionSelected').toggleSelectedPanel();
        }
        else
        {
            top.$('li#hEditorActionEdit').toggleSelectedPanel();
        }

        $('form#hEditorDocumentDialogue').show();

        $(document)
            .mousemove(
                function(event)
                {
                    if (top.editor.resizeIsActive)
                    {
                        top.editor.onResize(event, true);
                    }
                }
            )
            .mouseup(
                function(event)
                {
                    if (top.editor.resizeIsActive)
                    {
                        top.editor.resizeIsActive = false;
                        top.editor.saveSize();
                    }
                }
            );
    },

    find : function(find, options)
    {
        // Options {
        //  backwards : <boolean>,
        //  wrap : <boolean>,
        //  caseSensitive : <boolean>,
        //  wholeWord : <boolean>,
        //  regExp : <boolean>
        // }
        editor.ace.find(find, options);
    },

    findNext : function()
    {
        editor.ace.findNext();
    },

    findPrevious : function()
    {
        editor.ace.findPrevious();
    },

    replace : function(replace)
    {
        editor.ace.replace(replace);
    },

    replaceAll : function(replace)
    {
        editor.ace.replaceAll(replace);
    },

    removeParentDocument : function()
    {
        $('div#hFileParentIdInner').html('');
    },

    addVariable : function()
    {
        // Get the id of the last variable added.
        var id = parseInt(
            $('input#hEditorDocumentAddVariable')
                .parents('tr:first')
                .prev()
                    .find('input.hFileVariable')
                    .splitId()
        );

        id++;

        // Clone the variable row.
        var tr =
            $('input#hFileVariable-0')
                .parents('tr:first')
                .clone(true)
                .removeClass('hFileVariableSelected');

        this.renumberVariableRow(tr, id, true);

        $('input#hEditorDocumentAddVariable')
            .parents('tr:first')
            .before(tr);
    },

    renumberVariableRow : function(tr, id)
    {
        tr.find('label').attr('for', 'hFileVariable-' + id).removeAttr('accesskey');

        tr.find('input.hFileVariable').attr('id', 'hFileVariable-' + id);

        if (arguments[2])
        {
            tr.find('input.hFileVariable').val('');
        }

        tr.find('input.hFileValue').attr('id', 'hFileValue-' + id);

        if (arguments[2])
        {
            tr.find('input.hFileValue').val('');
        }
    },

    getListFiles : function(listId)
    {
        if (this.listId)
        {
            $('ul#hListId-' + this.listId).removeClass('hListFilesSelected');
        }

        this.listId = listId;

        if (!$('ul#hListId-' + this.listId).length)
        {
            http.get(
                '/hList/getListFilesAsList', {
                    operation : 'Get List Files',
                    hListId : this.listId,
                    hFileId : $('#hFileId').val()
                },
                function(html)
                {
                    $('div#hListFilesInner').append(html);
                    editor.document.listEvents();
                    editor.document.selectList();
                }
            );
        }
        else
        {
            this.selectList();
        }
    },

    addListFile : function()
    {
        if (this.listId)
        {
            this.openChooser('onChooseListFile');
        }
    },

    openChooser : function(callback)
    {
        this.editorChooseDialogue = hot.window(
            '/Applications/Finder/index.html', {
                dialogue : 'Choose',
                onChooseFile : 'editor.document.' + callback
            },
            600,
            400,
            'hEditorChoose', {
                scrollbars : false,
                resizable : true
            }
        );
    },

    selectDirectory : function()
    {
        this.editorSelectDirectoryDialogue = hot.window(
            '/Applications/Finder', {
                dialogue : 'Directory',
                onSelectDirectory : 'editor.document.onSelectDirectory'
            },
            600,
            400,
            'hEditorSelectDirectory', {
                scrollbars : false,
                resizable : true
            }
        );
    },

    onSelectDirectory : function(directoryId, directoryPath)
    {
        $('div#hDirectoryInner span').text(directoryPath);
        //this.hEditorSelect.close();
    },

    onChooseParentFile : function(fileId)
    {
        $('div#hFileParentIdInner').load(
            hot.path('/hFile/getFileInformation'), {
                hFileId : fileId,
                hFileUnique : 'Parent'
        });
    },

    selectList : function()
    {
        $('ul#hListId-' + this.listId).addClass('hListFilesSelected');
    },

    listEvents : function()
    {
        $('ul.hListFiles li').click(
            function()
            {
                $(this).toggleSelectedListItem();
            }
        );

        $('ul.hListFiles a').attr('target', '_blank');
    },

    onChooseListFile : function(fileId)
    {
        http.get(
            '/hList/getListFileHTML', {
                operation : 'Get List HTML',
                hFileId : fileId
            },
            function(html)
            {
                $('#hListId-' + editor.document.listId).append(html);
                editor.document.listEvents();
            }
        );
    },

    unselectPanelDivision : function()
    {
        if (this.selectedPanelDivision && this.selectedPanelDivision.length)
        {
            this.selectedPanelDivision.removeClass('hEditorDocumentSelectePanelDivision');
        }
    },

    unselectPanel : function()
    {
        if (this.selectedPanel && this.selectedPanel.length)
        {
            this.selectedPanel.removeClass('hEditorDocumentSelectedPanel');
        }
    },

    resetPanelTimer : function()
    {
        if (this.panelTimer)
        {
            clearInterval(this.panelTimer);
        }

        this.panelTimer = setInterval('editor.document.panelTracker();', 1000);
    },

    panelTracker : function()
    {
        if (!this.panelActive)
        {
            this.unselectPanelDivision();
            this.unselectPanel();
        }
    },

    plugin : null,

    getPluginValues : function(obj)
    {
        this.plugin = obj;

        this.post += '&hFileId=' + $('input#hFileId').val();

        $([
            'hFileTitle',
            'hFileSubtitle',
            'hFileHeadingTitle',
            'hFileBreadcrumbTitle',
            'hFileMenuTitle',
            'hFileSideboxTitle',
            'hFileTooltopTitle',
            'hGoogleSitemapPriority',
            'hGoogleSitemapChangeFrequency',
            'hFileDescription',
            'hFileKeywords',
            'hDirectoryId',
            'hFileName',
            'hFileParentId',
            'hFileSortIndex',
            'hFileCSS',
            'hFileJavaScript',
            'hFileMIME',
            'hPlugin',
            'hTemplatePath',
            'hFileCalendarId',
            'hFileCalendarCategoryId',
            'hFileCalendarDate',
            'hFileOwner'
        ]).each(
            function(index, value)
            {
                var string = value;
                var method = 'obj.' + string.replace(/^h{1}/, 'get');

                if (eval("typeof(" + method + ") != 'undefined'"))
                {
                    editor.document.post += '&' + value + '=' + encodeURIComponent(eval(method + '()'));
                }
            }
        );

        var listFiles = [];

        if (obj.getListFiles !== undefined)
        {
            listFiles = obj.getListFiles();
        }

        var i = 0;
        var currentList = 0;

        if (listFiles.length)
        {
            $(listFiles).each(
                function(index, value)
                {
                    if (i == 0 || value != currentList)
                    {
                        if (parseInt(value) > 0)
                        {
                            editor.document.post += '&hLists[]=' + value;
                            currentList = value;
                        }
                    }

                    if (parseInt(value) > 0 && parseInt(index) > 0)
                    {
                        editor.document.post += '&hListFiles[' + index + ']=' + value;
                    }

                    $i++;
                }
            );
        }

        var variables = [];

        if (obj.getVariables !== undefined)
        {
            variables = obj.getVariables();
        }

        if (variables.length)
        {
            $(variables).each(
                function(index, value)
                {
                    editor.document.post += '&' + index + '=' + encodeURIComponent(value);
                }
            );
        }

        var permissions = [];
        var permissionsExist = false;

        if (obj.getUserPermissionsGroups !== undefined)
        {
            permissionsExist = true;
            permissions = obj.getUserPermissionsGroups();

            for (var i in permissions)
            {
                editor.document.post += '&hUserPermissionsGroups[' + i + ']=' + permissions[i];
            }
        }

        if (obj.getUserPermissionsWorld !== undefined)
        {
            permissionsExist = true;
            this.post += '&hUserPermissionsWorld=' + obj.getUserPermissionsWorld();
        }

        if (obj.getUserPermissionsOwner !== undefined)
        {
            permissionsExist = true;
            this.post += '&hUserPermissionsOwner=' + obj.getUserPermissionsOwner();
        }

        if (permissionsExist)
        {
            this.post += '&hUserPermissions=1';
        }
    },

    pluginExists : false,
    pluginValidates : true,

    validatePlugin : function()
    {
        if (top.editor.plugin !== undefined && top.editor.plugin.validate !== undefined)
        {
            this.pluginExists = true;

            if (top.editor.plugin.validate())
            {
                this.getPluginValues(top.editor.plugin);
            }
            else
            {
                this.pluginValidates = false;
            }

            if (top.editor.plugin.debug !== undefined)
            {
                this.debug = true;
            }
        }
        else if (editor.plugin !== undefined && editor.plugin.validate !== undefined)
        {
            this.pluginExists = true;

            if (editor.plugin.validate(editor.plugin))
            {
                this.getPluginValues();
            }
            else
            {
                this.pluginValidates = false;
            }

            if (editor.plugin.debug !== undefined)
            {
                this.debug = true;
            }
        }
    },

    debug : false,

    save : function()
    {
        var errors = '';

        this.post = '';

        if (arguments[0])
        {
            $('input#hFileId').val(0);
        }

        switch (true)
        {
            case (editor.ace != null):
            {
                this.post += '&hFileDocument=' + encodeURIComponent(editor.ace.getSession().getValue());
                break;
            };
            case (FCKeditorAPI !== undefined):
            {
                // Using FCKEditor
                var fckeditor = FCKeditorAPI.GetInstance('hFileDocument');
                this.post += '&hFileDocument=' + encodeURIComponent(fckeditor.GetHTML());
                break;
            };
            default:
            {
                this.post += '&hFileDocument=' + encodeURIComponent($('#hFileDocument').val());
            };
        }

        this.validatePlugin();

        if (!this.pluginExists)
        {
            if (!$('input#hFileName').val() || !$('input#hDirectoryPath').val())
            {
                top.$('input#hEditorSave').removeAttr('disabled');
                top.$('input#hEditorSaveAs').removeAttr('disabled');
                top.editor.saveAs();
                return;
            }

            this.getTableValues(this.hFile);
            this.getTableValues(this.hFileDocument);
            this.getTableValues(this.hFileHeaders);
            this.getTableValues(this.hFileProperties);
            this.getTableValues(this.hFileVariables);

            // Get Lists
            if ($('ul.hListFiles').length)
            {
                $('ul.hListFiles').each(
                    function()
                    {
                        var ul = $(this).splitId();

                        editor.document.post += '&hLists[]=' + ul;

                        $(this).find('li.hListFile span.hListFileId').each(
                            function()
                            {
                                editor.document.post += '&hListFiles[' + $(this).text() + ']=' + ul;
                            }
                        );
                    }
                );
            }

            if ($('input#hFileExcludeTemplate').length || $('input#hTemplatePath').length)
            {
                if ($('input#hFileExcludeTemplate:checked').length)
                {
                    this.post += '&hTemplatePath=';
                }
                else if ($('input#hTemplatePath').val())
                {
                    this.post += '&hTemplatePath=' + encodeURIComponent($('input#hTemplatePath').val());
                }
            }

            if ($('#hPluginPrivate').length)
            {
                this.post += '&hPlugin=' + encodeURIComponent($('#hPluginPrivate').val());
            }
            else if ($('#hPlugin').length)
            {
                this.post += '&hPlugin=' + encodeURIComponent($('#hPlugin').val());
            }

            if ($('input#hFileIsServer').length)
            {
                this.post += '&hFileIsServer=' + $('input#hFileIsServer').val();
            }

            if ($('input#hFileIsSystem').length)
            {
                this.post += '&hFileIsSystem=' + ($('input#hFileIsSystem:checked').length? 1 : '');
            }

            if ($('input#hFileDownload').length)
            {
                this.post += '&hFileDownload=' + ($('input#hFileDownload:checked').length? 1 : '');
            }

            $([
                'hFileSubTitle',
                'hFileHeadingTitle',
                'hFileBreadcrumbTitle',
                'hFileMenuTitle',
                'hFileSideboxTitle',
                'hFileTooltipTitle'
            ]).each(
                function()
                {
                    var node = $('input#' + this);

                    if (node.length && node.val())
                    {
                        editor.document.post += '&' + this + '=' + encodeURIComponent(node.val());
                    }
                }
            );

            var node = $('select#hFileCalendarId option:selected');

            if (node.length) {
                node.each(
                    function()
                    {
                        editor.document.post += '&hFileCalendarId[]=' + $(this).val();
                    }
                );
            }

            var node = $('input[name="hFileCalendarCategoryId"]:checked');

            if (node.length)
            {
                this.post += '&hFileCalendarCategoryId=' + node.val();
            }

            var node = $('input#hFileCalendarDate');

            if (node.length)
            {
                this.post += '&hFileCalendarDate=' + node.val();
            }

            if ($('div#hFileParentId span.hFileParentId').length)
            {
                this.post += '&hFileParentId=' + $('div#hFileParentId span.hFileParentId').text();
            }

            this.post +=
                '&hDirectoryPath=' + encodeURIComponent($('input#hDirectoryPath').val()) +
                '&hFileReplaceExisting=' + ($('input#hFileReplaceExisting').val() == '1'? '1' : '0');

            $('input.hFileVariable').each(
                function()
                {
                    if (this.value)
                    {
                        editor.document.post += '&' + this.value + '=' + encodeURIComponent($('input#hFileValue-' + $(this).splitId()).val());
                    }
                }
            );

            var node = $('input#hFileOwner');

            if (node.length)
            {
                this.post += '&hFileOwner=' + encodeURIComponent(node.val());
            }
        }

        if (!this.pluginExists && !errors || this.pluginExists && this.pluginValidates)
        {
            top.application.status.message('Saving Document...');

            if (this.debug)
            {
                this.post += '&hEditorDocumentDebug=1';
            }

            http.post(
                '/hEditor/hEditorDocument/save', {
                    operation : 'Save Document',
                    path : get.path
                },
                this.post,
                function(json)
                {
                    if (editor.document.debug)
                    {
                        hot.debug(data);
                    }

                    var fileId = parseInt(json);

                    top.editor.refreshFileAttributes($('input#hFileName').val(), editor.document.getPath(), fileId);

                    if (top && top.finder && top.finder.tree)
                    {
                        top.finder.tree.refreshBranchByDirectoryPath($('div#hDirectoryInner span').text());
                    }

                    $('input#hFileId').val(fileId);

                    top.application.status.message('Document Saved!', 'Fade');
                    top.$('input#hEditorSave').removeAttr('disabled');
                    top.$('input#hEditorSaveAs').removeAttr('disabled');

                    if (editor.document.plugin && editor.document.plugin.onSaveDocument !== undefined)
                    {
                        editor.document.plugin.onSaveDocument();
                    }
                }
            );
        }
        else
        {
            top.$('input#hEditorSave').removeAttr('disabled');
            top.$('input#hEditorSaveAs').removeAttr('disabled');

            if (errors)
            {
                alert(errors);
            }
        }
    },

    getPath : function()
    {
        var directoryPath = $('div#hDirectoryInner span').text();
        var fileName = $('input#hFileName').val();

        return directoryPath + ((directoryPath != '/')? '/' : '') + fileName;
    },

    getTableValues : function(columns, name)
    {
        $(columns).each(
            function()
            {
                var node = $('#' + this);

                if (node.length)
                {
                    editor.document.post += '&' + this + '=' + encodeURIComponent($('#' + this).val());
                }
            }
        );
    }
};

keyboard
    .shortcut(
        {
            saveDocument : "Command + S, Control + S"
        },
        function()
        {
            top.editor.save();
        }
    )
    .shortcut(
        {
            newDocument : "Command + N, Control + N"
        },
        function()
        {
            top.editor.newDocument();
        }
    )
    .shortcut(
        {
            closeDocument : "Command + W, Control + W"
        },
        function()
        {
            if (top.editor.selectedDocumentTab && top.editor.selectedDocumentTab.length)
            {
                top.editor.selectedDocumentTab.closeDocument(true);
            }
        }
    );

$(document).ready(
    function()
    {
        editor.document.ready();
    }
);
