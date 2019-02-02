var comments = {
    id : 0,
    node : null,

    ready : function()
    {
        if ($('li.hFileComment').length)
        {
            $('p#hFileCommentsNone').hide();
        }
    
        $(document).on(
            'click',
            'a.hFileCommentDelete',
            function(e)
            {
                e.preventDefault();
                comments.destroy($(this));
            }
        );
        
        if (get.commentDelete)
        {
            this.destroy(get.fileCommentId);
        }

        $(document).on(
            'click',
            'a.hFileCommentApprove',
            function(e)
            {
                e.preventDefault();
                comments.approve($(this));
            }
        );

        if (get.commentApprove)
        {
            this.approve(get.fileCommentId);
        }

        $(document).on(
            'click',
            'a.hFileCommentDeny',
            function(e)
            {
                e.preventDefault();
                comments.deny($(this));
            }
        );

        if (get.commentDeny)
        {
            this.deny(get.fileCommentId);
        }

        $('input#hFileCommentSubmit').click(
            function(e)
            {
                e.preventDefault();
                comments.post();
            }
        );
    },

    destroy : function(node)
    {        
        this.getId(node);

        dialogue.confirm({
                title : 'Delete Comment',
                label : "<p>Are you certain you want to <b>PERMANENTLY</b> delete this comment?</p>\n" + 
                        "<p>This cannot be undone.</p>",
                ok : "Delete This Comment",
                cancel : "Don't Delete This Comment"
            },
            function(confirm)
            {
                if (confirm)
                {
                    http.get(
                        '/hFile/hFileComments/delete', {
                            operation : 'Delete Comment',
                            fileId : hot.fileId,
                            fileCommentId : this.id
                        },
                        function(json)
                        {
                            if (this.node.length)
                            {
                                this.node.remove();
                                
                                this.node = null;
                                this.id = 0;

                                if (!$('li.hFileComment').length)
                                {
                                    $('p#hFileCommentsNone').show();
                                }
                            }
                        },
                        this
                    );
                }
                else
                {
                    this.id = 0;
                    this.node = null;
                }
            },
            this
        );
    },

    approve : function(node)
    {
        this.getId(node);

        dialogue.confirm({
                title : 'Approve Comment',
                label : "<p>Are you certain you want to approve this comment?</p>\n" + 
                        "<p>This will make this comment publicly accessible.</p>",
                ok : "Approve This Comment",
                cancel : "Don't Approve This Comment"
            },
            function(confirm)
            {
                if (confirm)
                {
                    http.get(
                        '/hFile/hFileComments/approve', {
                            operation : 'Approve Comment',
                            fileId : hot.fileId,
                            fileCommentId : this.id
                        },
                        function(json)
                        {
                            if (this.node.length)
                            {
                                this.node.removeClass('hFileCommentDenied');
                                this.node.addClass('hFileCommentApproved');
                            }
                            
                            this.node = null;
                            this.id = 0;
                        },
                        this
                    );
                }
                else
                {
                    comments.id = 0;
                    comments.node = null;
                }
            },
            this
        );
    },
    
    deny : function(node)
    {
        this.getId(node);

        dialogue.confirm({
                title : 'Deny Comment',
                label : "<p>Are you certain you want to deny this comment?</p>\n" + 
                        "<p>This will remove this comment from public view.</p>",
                ok : "Deny This Comment",
                cancel : "Don't Deny This Comment"    
            },
            function(confirm)
            {
                if (confirm)
                {
                    http.get(
                        '/hFile/hFileComments/deny', {
                            operation : 'Deny Comment',
                            fileId : hot.fileId,
                            fileCommentId : this.id
                        },
                        function(json)
                        {
                            if (this.node.length)
                            {
                                this.node.addClass('hFileCommentDenied');
                                this.node.removeClass('hFileCommentApproved');
                            }
                            
                            this.node = null;
                            this.id = 0;
                        },
                        this
                    );
                }
                else
                {
                    this.id = 0;
                    this.node = null;
                }
            },
            this
        );
    },
    
    getId : function(node)
    {
        if (node && node.length && (typeof(node) == 'array' || typeof(node) == 'object'))
        {
            if (node.is('li.hFileComment'))
            {
                this.node = node;
            }
            else
            {
                this.node = node.parents('li.hFileComment:first');
            }

            this.id = parseInt(comments.node.attr('data-file-comment-id'));
        }
        else 
        {
            var id = parseInt(node);
                
            if (!isNaN(id))
            {
                this.id = id;

                $('li.hFileComment').each(
                    function()
                    {
                        if (parseInt($(this).attr('data-file-comment-id')) == id)
                        {
                            comments.node = $(this);
                            return false;
                        }
                    }   
                );
            }
        }
    },

    post : function()
    {
        var error = null;

        if (!$('input#hFileCommentName').val())
        {
            error = "You did not provide your name.";
        }
        
        if (!$('input#hFileCommentEmail').val())
        {
            error = "You did not provide your email address.  Your email address is required, but won't be published with your comment.";
        }
        
        var comment = $('textarea#hFileComment').val();
        
        if (!comment || comment && comment.length < 50)
        {
            error = "You did not provide a comment, or did not provide a comment at least 50 characters in length.";
        }

        if (!error)
        {
            http.post(
                '/hFile/hFileComments/postComment', {
                    operation : 'Post Comment'
                },
                $('form#hFileCommentsForm').serialize() +
                    '&fileId=' + hot.fileId,
                function(json)
                {
                    dialogue.alert({
                        title : "Comment Posted!",
                        label : "<p>Your comment has been submitted and will be reviewed before it is posted.</p>"
                    });

                    if (json && json.comments)
                    {
                        $('ul#hFileComments').append(json.comments);

                        if ($('p#hFileCommentsNone').length)
                        {
                            $('p#hFileCommentsNone').hide();
                        }
                    }
                }
            );
        }
        else
        {
            dialogue.alert({
                title : 'Unable to Post Your Comment',
                label : '<p>' + error + '</p>'
            });
        }
    }
};

$(document).ready(
    function()
    {
        comments.ready();
    }
);
