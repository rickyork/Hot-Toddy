if (typeof(dashboard) == 'undefined')
{
    var dashboard = {};
}

dashboard.blog = {
    ready : function()
    {
        $('div#hDashboardBlogDialogue').fadeOut(
            'normal',
            function()
            {
                $('div#hDashboardBlogLoading').fadeOut('normal');
            }
        );
        
        $('ul.hDashboardBlog h4 a:first').click(
            function(e)
            {
                e.preventDefault();
                dashboard.blog.get($(this).attr('data-fileId'));
                $('form#hDashboardBlogDialogue').openDialogue(true);
            }
        );
        
        $('input#hDashboardBlogDialogueCancel').click(
            function(e)
            {
                e.preventDefault();
                $('form#hDashboardBlogDialogue').closeDialogue(true);
                dashboard.blog.reset();
            }
        );
        
        $('form#hDashboardBlogDialogue span.hDialogueClose').click(
            function()
            {
                dashboard.blog.reset();
            }
        );
        
        $('input#hDashboardBlogDialogueSave').click(
            function(e)
            {
                e.preventDefault();
                dashboard.blog.save();
            }
        );
        
        $('div#hDashboardBlogNew').click(
            function(e)
            {
                e.preventDefault();
                dashboard.blog.reset();
                $('form#hDashboardBlogDialogue').openDialogue(true);
            }
        );

        $('a.hDashboardBlogDelete').on(
            'click',
            function(e)
            {
                e.preventDefault();
                var fileId = $(this).parents('h4:first').splitId();
                dashboard.blog.deleteBlog(fileId);
            }
        );
        
        $('input#hCalendarDate').datepicker({
            dayNamesMin : ['S', 'M', 'T', 'W', 'T', 'F', 'S']
        });
    },
    
    fileId : 0,
    
    get : function(fileId)
    {
        this.fileId = fileId;

        http.get(
            '/hDashboard/hDashboardBlog/get', {
                operation : "Retrieve News Story",
                fileId : fileId
            },
            function(json)
            {
                $("input#hFileTitle").val(json.hFileTitle);
                $('input#hCalendarDate').val(json.hCalendarDateFormatted);
                
                FCKeditorAPI.GetInstance('hFileDescription').SetHTML(json.hFileDescription);
                FCKeditorAPI.GetInstance('hFileDocument').SetHTML(json.hFileDocument);
                

                if (parseInt(json.hUserPermissionsWorld) == 1)
                {
                    $('input#hUserPermissionsWorld').attr('checked', 'checked');
                }
                else
                {
                    $('input#hUserPermissionsWorld').removeAttr('checked');
                }
                
                if (parseInt(json.hFileCommentsEnabled) == 1)
                {
                    $('input#hFileComments').attr('checked', 'checked');
                }
                else
                {
                    $('input#hFileComments').removeAttr('checked');
                }
            }
        );  
    },

    save : function()
    {
        http.post(
            '/hDashboard/hDashboardBlog/save', {
                operation : "Save News Story",
                fileId : this.fileId
            }, {
                hFileTitle : $('input#hFileTitle').val(),
                hFileDocument : FCKeditorAPI.GetInstance('hFileDocument').GetHTML(),
                hCalendarDate : $('input#hCalendarDate').val(),
                hUserPermissionsWorld : $('input#hUserPermissionsWorld:checked').length? 'r' : ''
            },
            function(json)
            {
                var date = $('input#hCalendarDate').val();
                var title = $('input#hFileTitle').val();
            
                if (!this.fileId)
                {
                    $('ul.hDashboardBlog').prepend(
                        "<li>\n" +
                        "    <h4 id='hDashboardBlog-" + this.fileId + "'><a href='#' data-fileId='" + this.fileId + "'>" + date + ' ' + title + "</a> <a href='#' class='hDashboardBlogDelete'>Delete</a></h4>\n" +
                        "    <div></div>\n" + 
                        "</li>\n"
                    );
                }
                else
                {
                    $('h4#hDashboardBlog-' + this.fileId + ' a').text(date + ' ' + title);
                }

                $('form#hDashboardBlogDialogue').closeDialogue(true);
                this.reset();
            },
            this
        )  
    },
    
    reset : function()
    {
        this.fileId = 0;
        FCKeditorAPI.GetInstance('hFileDescription').SetHTML('');
        FCKeditorAPI.GetInstance('hFileDocument').SetHTML('');
        $('input#hFileTitle').val('');
        $('input#hCalendarDate').val('');
        $('input#hUserPermissionsWorld').removeAttr('checked');
        $('input#hFileComments').removeAttr('checked');
    },
    
    deleteBlog : function(fileId)
    {
        this.fileId = fileId;
    
        var title = $('h4#hDashboardBlog-' + fileId + ' a:first').text();

        dialogue.confirm({
                label : 
                    "<p>\n" + 
                        "Are you certain you want to <b>PERMANENTLY</b> delete:\n" + 
                    "</p>\n" +
                    "<p>\n" +
                        "<b>" + title + "</b>\n" + 
                    "</p>\n",
                ok : "Delete Blog Post",
                cancel : "Don't Delete Blog Post"
            },
            function(response)
            {
                if (response)
                {
                    http.get(
                        '/hDashboard/hDashboardBlog/delete', {
                            operation : "Delete Blog Post",
                            fileId : this.fileId
                        },
                        function(json)
                        {
                             $('h4#hDashboardBlog-' + this.fileId).parents('li:first').remove();
                        },
                        this
                    );
                }
            },
            this
        );
    }
};

$(window).load(
    function()
    {
        dashboard.blog.ready();
    }
);