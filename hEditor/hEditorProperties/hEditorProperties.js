var duplicatePath = '';

if (!editor)
{
    var editor = {};
}

editor.properties = {
    ready : function()
    {
        $('input#hEditorPropertyAddCategory').click(
            function(event)
            {
                event.preventDefault();
                editor.properties.selectCategoryDialogue();
            }
        );

        $('input#hEditorPropertyRemoveCategory').click(
            function(event)
            {
                event.preventDefault();

                var selected = hot.selected('hCategory');

                if (selected.length)
                {
                    selected.remove();
                }
            }
        );

        $('input#hEditorPropertiesSave').click(
            function(event)
            {
                event.preventDefault();
                editor.properties.save();
            }
        );

        $('input#hEditorPropertiesCancel').click(
            function(event)
            {
                event.preventDefault();
                window.close();
            }
        );

        $('iframe#hEditorPropertiesFrame').dblclick(
            function()
            {
                $(this).hide();
            }
        );

        $('select#hUserPermissionsGroups').change(
            function(event)
            {
                if ($(this).find('option:selected').length)
                {
                    $('input#hUserPermissionsWorldRead').removeAttr('checked');
                }
            }
        );

        $('input#hUserPermissionsWorldRead').click(
            function()
            {
                if (this.checked)
                {
                    $('select#hUserPermissionsGroups option:selected').removeAttr('selected');
                }
            }
        );

        $('form').submit(
            function()
            {
                if (hot.userAgent == 'webkit')
                {
                    // This is a work-around for Safari occaisonally hanging when doing an file upload.
                    // This prevent you from having to click the submit button twice.
                    http.get('/hFile/blank');
                }
            }
        );

        $(document).on(
            'click',
            'div#hEditorPropertyCategories ul li',
            function()
            {
                $(this).select('hCategory');
            }
        );
    },

    selectCategoryDialogue : function()
    {
        var selectCategory = hot.window(
            '/Applications/Finder', {
                dialogue : 'Directory',
                onSelectDirectory : 'editor.properties.onSelectCategory',
                path : '/Categories'
            },
            600,
            400,
            'hEditorPropertiesSelectCategory', {
                scrollbars : false,
                resizable : true
            }
        );
    },

    onSelectCategory : function(directoryId, directoryPath)
    {
        var category = directoryPath.split('/').pop();

        $('div#hEditorPropertyCategories ul').append(
            $("<li/>")
                .attr({
                    id : 'hCategoryId-' + directoryId,
                    title : directoryPath
                })
                .append(
                    $('<div/>').append(
                        $("<span/>").text(category)
                    )
                )
        );
    },

    save : function()
    {
        application.status.message('Saving File Properties...');

        // Prep the form...
        if ($('div#hEditorPropertyCategories li').length)
        {
            $('div#hEditorPropertyCategories li').each(
                function()
                {
                    $('form').append(
                        $('<input/>').attr({
                            type : 'hidden',
                            name : 'hCategories[]',
                            value : $(this).splitId()
                        })
                    );
                }
            );
        }
        else
        {
            $('form').append(
                $('<input/>').attr({
                    type : 'hidden',
                    name : 'hCategories',
                    value : ''
                })
            );
        }

        $('form').submit();

        //$('iframe#hEditorPropertiesFrame').show();
    },

    hasErrors : function(json, operation)
    {
        var responseCode = parseInt(json);

        if (!isNaN(responseCode))
        {
            var redirect = false;

            var error = '';

            switch (responseCode)
            {
                case -31:
                {
                    error = "There is a permissions problem with the server.\n\nPlease notify your system administrator about this problem.";
                    break;
                };
                case -32:
                {
                    // File already exists *anywhere* on the server
                    error = "The file already exists on the server at:\n" + duplicatePath + "\n\nYour preferences currently do not allow duplicate files.";
                    duplicatePath = '';
                    break;
                };
                case -6:
                {
                    error = "You are no longer logged into the website, please login and try again.";
                    redirect = true;
                    break;
                };
                case -5:
                {
                    error = "An internal error occurred; required information was missing from the request.";
                    break;
                };
                case -3:
                {
                    // File already exists, but the replace flag was not set.
                    error = "The file already exists in this folder.";
                    break;
                };
                case -1:
                {
                    error = "You don't have permission to perform this action.";
                    break;
                };
                case 0:
                {
                    error = "An undefined error has occurred.";
                    break;
                };
            }

            if (error)
            {
                alert(
                    operation + " Failed!\n\n" +
                    error
                );

                if (redirect)
                {
                    location.reload(true);
                }

                return true;
            }
        }

        return false;
    },

    onSaveProperties : function(response)
    {
        if (!this.hasErrors(response, "Edit File Properties"))
        {
            application.status.message('File Properties Saved!', true);

            if (opener && opener.$$ && opener.finder)
            {
                opener.finder.refresh();
            }
        }
        else
        {
            application.status.message('Unable to Save File Properties', true);
        }
    }
};

$(document).ready(
    function()
    {
        editor.properties.ready();
    }
);
