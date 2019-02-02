finder.categories = {

    lastSortBy : null,
    lastView : null,

    ready : function()
    {
        if (get.dialogue)
        {
            return;
        }

        this.onRequestDirectory();

        $('input#hFinderCategorySelectIcon').click(
            function(event)
            {
                event.preventDefault();
                finder.categories.selectIcon();
            }
        );

        $('input#hFinderCategoryAddFiles').click(
            function(event)
            {
                event.preventDefault();
                finder.categories.addFiles();
            }
        );

        $('input#hFinderCategoryRemoveFile').click(
            function(event)
            {
                event.preventDefault();
                finder.categories.removeFile();
            }
        );

        hot.event('requestDirectory', finder.categories.onRequestDirectory, finder.categories);

        hot.event(
            'beforeRequestDirectory',
            function(obj)
            {
                if (!this.sortBy)
                {
                    this.sortBy = 'name';
                }

                // Current path is not a category, next path is a category
                if (!this.beginsPath('/Categories') && this.beginsPath(obj.path, '/Categories'))
                {
                    this.categories.lastSortBy = this.sortBy;
                    this.categories.lastView = this.view;
                    this.sortBy = 'category';
                    this.setView('List', true);
                }

                // Current path is a category, next path is not a category.
                if (this.beginsPath('/Categories') && !this.beginsPath(obj.path, '/Categories'))
                {
                    if (this.categories.lastSortBy)
                    {
                        this.sortBy = this.categories.lastSortBy;
                        this.categories.lastSortBy = '';
                    }

                    if (this.categories.lastView)
                    {
                        this.setView(this.categories.lastView, true);
                        this.categories.lastView = '';
                    }
                }
            },
            finder
        );

        if (finder.beginsPath('/Categories'))
        {
            this.openPanel();
        }
    },

    onRequestDirectory : function()
    {
        if (get.dialogue)
        {
            return;
        }

        if (finder.beginsPath('/Categories'))
        {
            if ($('div.hFinderListInner').length)
            {
                $('div.hFinderListInner').sortable({
                    connectWith : 'div.hFinderListInner',
                    containment : 'div.hFinderFiles',
                    stop : function(e, ui)
                    {
                        if (ui.sender)
                        {

                        }
                        else
                        {
                            finder.categories.saveCategory(ui.item);
                        }
                    }
                });
            }

            if (finder.editFile !== undefined)
            {
                finder.editFile.closePanel();
            }

            if (finder.upload !== undefined)
            {
                finder.upload.closePanel();
            }

            this.openPanel();
            //this.get();
        }
        else
        {
            this.closePanel();
        }
    },

    openPanel : function()
    {
        var height = $('div.hFinderCategories').height();

        $('li.hFinderContextMenuUploadFile, li.hFinderContextMenuNewFile')
            .addClass('hFinderContextMenuItemDisabled');

        $('li.hFinderContextMenuNewFolder')
            .text('New Category');

        if ($('input#hFinderButtonNewFolder').length)
        {
            $('input#hFinderButtonNewFolder').val('New Category');
        }

        $('body').addClass('hFinderCategories');
    },

    closePanel : function()
    {
        $('li.hFinderContextMenuUploadFile, li.hFinderContextMenuNewFile')
            .removeClass('hFinderContextMenuItemDisabled');

        $('li.hFinderContextMenuNewFolder').text('New Folder');

        if ($('input#hFinderButtonNewFolder').length)
        {
            $('input#hFinderButtonNewFolder').val('New Folder');
        }

        $('body').removeClass('hFinderCategories');
    },

    getBottomOffset : function(value)
    {
        if (get.dialogue || $('body').length && $('body').attr('id').indexOf('Dialogue') != -1)
        {
            return value + 19 + 'px';
        }

        return value + 'px';
    },

    addFile : function(fileId, fileIcon, filePath, fileTitle)
    {
        var files = $('div#hFinderCategoryFiles');
        var div = document.createElement('div');

        div.className = 'hFinderCategoryFile hFinderCategoryFileNew';
        div.id = 'hFinderCategoryFile-' + fileId;

        var span = document.createElement('span');

        span.className = 'hFinderCategoryFileId';
        span.appendChild(document.createTextNode(fileId));

        div.appendChild(span);

        var img = document.createElement('img');
        img.src = fileIcon;
        img.className = 'hFinderCategoryFileIcon';
        img.alt = 'File Icon';

        div.appendChild(img);

        var span = document.createElement('span');
        span.className = 'hFinderCategoryFileTitle';
        span.appendChild(document.createTextNode(fileTitle));

        div.appendChild(span);

        var span = document.createElement('span');
        span.className = 'hFinderCategoryFilePath';

        var a = document.createElement('a');
        a.href = filePath;
        a.appendChild(document.createTextNode(filePath));

        span.appendChild(a);
        div.appendChild(span);

        files.append(div);

        $('div.hFinderCategoryFileNew').removeClass('hFinderCategoryFileNew').click(
            function()
            {
                $(this).select('hFinderCategoryFile');
            }
        );
    },

    removeFile : function()
    {
        hot.unselect('hFinderCategoryFile').remove();
        this.saveCategory();
    },

    addFiles : function()
    {
        this.fileDialogue = hot.window(
            '/Applications/Finder/index.html',
            finder.getConfigurationArguments({
                    dialogue : 'Choose',
                    onChooseFile : 'finder.categories.onAddFiles'
                },
                get.categoriesPath? get.categoriesPath : null,
                get.categoriesDiskName? get.categoriesDiskName : null
            ),
            600,
            400,
            'hFinderChooseCategoryIcon', {
                scrollbars : false,
                resizable : true
            }
        );

        this.fileDialogue.moveTo((window.screen.width - 600) / 2, (window.screen.height - 400) / 2);
        this.fileDialogue.focus();
    },

    onAddFiles : function(fileId)
    {
        http.get(
            '/hFile/getFileInformationJSON', {
                 hFileId : fileId
            },
            function(json)
            {
                this.addFile(json.hFileId, json.hFileIcon, json.hFilePath, json.hFileTitle);
                this.saveCategory();
            },
            this
        );
    },

    selectIcon : function()
    {
        this.iconDialogue = window.open(
            hot.path(
                '/Applications/Finder/index.html', {
                    dialogue : 'Choose',
                    onChooseFile : 'finder.categories.onSelectCategoryIcon'
            }),
            'hFinderChooseCategoryIcon',
            'width=600,height=400,scrollbars=no,resizable=yes'
        );

        this.iconDialogue.moveTo((window.screen.width    - 600) / 2, (window.screen.height - 400) / 2);
        this.iconDialogue.focus();
    },

    onSelectCategoryIcon : function(fileId)
    {
        this.iconDialogue.close();
        $('span#hFinderCategoryIconId').text(fileId);

        http.get(
            '/hFile/getFilePath', {
                hFileId : fileId,
                hFileUnique : 'Parent'
            },
            function(json)
            {
                var img = document.createElement('img');

                img.src = json;
                img.alt = 'Category Image';

                $('span#hFinderCategoryIconImage').html('').append(img);

                this.saveCategoryIcon(filePath);
            },
            this
        );
    },

    saveCategoryIcon : function(path)
    {
        http.get(
            '/hFinder/hFinderCategories/saveCategoryIcon', {
                operation : 'Save category icon',
                hFilePath : finder.path,
                hFileIconPath : path
            },
            function(json)
            {

            }
        );
    },

    moveToCategory : function(item, from)
    {
        var to = $(item).parents('div.hFinderList:first').attr('data-file-path');
        var from = $(from).parents('div.hFinderList:first').attr('data-file-path');

        http.post(
            '/hFinder/hFinderCategories/moveToCategory', {
                operation : 'Move to category',
                toCategoryPath : to,
                fromCategoryPath : from,
                hFileId : $(item).splitId()
            },
            function(json)
            {
                finder.categories.saveCategory(item);
            }
        );
    },

    saveCategory : function(item)
    {
        switch (finder.view)
        {
            case 'List':
            {
                var post = 'hFilePath=' + $(item).parents('div.hFinderList:first').attr('data-file-path');

                $(item).parents('div.hFinderListInner:first').children().each(
                    function()
                    {
                        if ($(this).attr('id').indexOf('hCategoryId') != -1)
                        {
                            var id = $(this).splitId();

                            var exp = new RegExp('[0-9]', 'g');
                            var matches = id.match(exp);

                            if (matches && matches.length)
                            {
                                post += '&hCategories[]=' + matches.join('');
                            }
                        }
                        else
                        {
                            post += '&hCategoryFiles[]=' + $(this).splitId();
                        }
                    }
                );

                break;
            };
        }

        http.post({
                url : '/hFinder/hFinderCategories/saveCategory',
                operation : 'Save category'
            },
            post,
            function(json)
            {

            }
        );
    }
};

$(document).ready(
    function()
    {
        finder.categories.ready();
    }
);
