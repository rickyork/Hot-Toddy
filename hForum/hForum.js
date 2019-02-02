$.fn.extend({
    updateTopic : function()
    {
        this.find('td.hForumTopic > a').text($('input#hForumTopic').val());

        if (this.find('td.hForumTopic p').length)
        {
            if (!$('textarea#hForumTopicDescription').val().length)
            {
                this.find('td.hForumTopic p').fadeOut(
                    function()
                    {
                        $(this).remove();
                    }
                );
            }
            else
            {
                this.find('td.hForumTopic p').text($('textarea#hForumTopicDescription').val());
            }
        }
        else if ($('textarea#hForumTopicDescription').val().length)
        {
            this.find('td.hForumTopic a').after("<p>" + $('textarea#hForumTopicDescription').val() + "</p>");
        }

        this.find('span.hForumTopicIsModerated').text($('input#hForumTopicIsModerated:checked').length? 1 : 0);
        this.find('span.hForumTopicIsLocked').text($('input#hForumTopicIsLocked:checked').length? 1 : 0);

        return this;
    }
});

var forum = {
    postId : 0,
    postAttribute: '',
    topicId : 0,

    ready : function()
    {
        $('li.hForum').click(
            function()
            {
                $(this).select('hForum');
            }
        );

        $('li.hForumSubscriptionButton a').click(
            function(event)
            {
                event.preventDefault();
                forum.subscribeToTopic($('div.hForumTopic').splitId());
            }
        );

        $('li.hForumThreadSubscriptionButton a').click(
            function(event)
            {
                event.preventDefault();
                forum.subscribeToThread($('div.hForumThread').splitId());
            }
        );

        $('td.hForumSubscription a').click(
            function(event)
            {
                event.preventDefault();
                forum.subscribeToTopic($(this).parent().splitId());
            }
        );

        $('a.hForumPostLock').click(
            function(event)
            {
                event.preventDefault();
                forum.togglePostAttribute($(this).parents('tr:first').splitId(), 'Lock');
            }
        );

        $('a.hForumPostSticky').click(
            function(event)
            {
                event.preventDefault();
                forum.togglePostAttribute($(this).parents('tr:first').splitId(), 'Stickiness');
            }
        );

        $('a.hForumPostDelete').click(
            function(event)
            {
                event.preventDefault();
                forum.deletePost($(this).parents('tr:first').splitId());
            }
        );

        $('table.hForumTopics tbody tr')
            .click(
                function()
                {
                    if (!$(this).hasClass('hForumNoPosts'))
                    {
                        $(this).select('hForumTopic');
                    }
                }
            )
            .dblclick(
                function()
                {
                    if (!$(this).hasClass('hForumNoPosts'))
                    {
                        $(this).parents('li.hForum').select('hForum');
                        forum.editTopic($(this).splitId());
                    }
                }
            );

        $('a.hForumTopicButtonEdit').click(
            function(event)
            {
                event.preventDefault();

                if (hot.selected('hForumTopic').length)
                {
                    forum.editTopic(hot.selected('hForumTopic').splitId());
                }
            }
        );

        $('a.hForumTopicButtonRemove').click(
            function(event)
            {
                event.preventDefault();

                if (hot.selected('hForumTopic').length)
                {
                    forum.deleteTopic(hot.selected('hForumTopic').splitId());
                }
            }
        );

        $('a.hForumTopicButtonAdd').click(
            function(event)
            {
                event.preventDefault();
                hot.unselect('hForumTopic');

                $(this).parents('li.hForum').select('hForum');
                forum.openTopicDialogue();
            }
        );

        $('input#hForumTopicDialogueCancel').click(
            function(event)
            {
                event.preventDefault();
                forum.closeTopicDialogue();
            }
        );

        $('input#hForumTopicDialogueSave').click(
            function(event)
            {
                event.preventDefault();
                forum.saveTopic();
            }
        );

        if ($('a#hForumNew').length)
        {
            $('div.hForumTitleOuter span')
                .click(
                    function()
                    {
                        this.contentEditable = true;
                    }
                )
                .keyup(
                    function()
                    {
                        forum.renameForum($(this).parents('li.hForum').splitId(), $(this).text());
                    }
                );

            $('a#hForumNew').click(
                function(event)
                {
                    event.preventDefault();
                    forum.newForum();
                }
            );

            $('a#hForumDelete').click(
                function(event)
                {
                    event.preventDefault();
                    forum.deleteForum();
                }
            );

            $('table.hForumTopics tbody').sortable({
                delay : 500,
                connectWith : 'table.hForumTopics tbody',
                stop : function()
                {
                    forum.setNoTopics();
                    forum.sortTopics();
                }
            });

            $('div#hForumWrapper > ul').sortable({
                delay : 500,
                handle: 'th',
                stop : function()
                {
                    forum.sortForums();
                }
            });

            $('span.hForumTopicPermissions').click(
                function(event)
                {
                    event.preventDefault();
                    forum.openTopicPermissionsDialogue($(this).parents('tr:first').splitId());
                }
            );
        }
    },

    deleteForum : function()
    {
        var selected = hot.selected('hForum');

        if (selected.length)
        {
            var forumName = selected.find('div.hForumTitleOuter span').text();

            dialogue.confirm(
                {
                    title : "Confirm Deletion",
                    label : "<p>Are you certain you want to <b>PERMANENTLY</b> delete <i>" + forumName + "</i> and all topics and posts associated with it?</p>" +
                            "<p>This cannot be undone.</p>",
                    ok : "Delete Forum",
                    cancel : "Don't Delete Forum"
                },
                function(confirm)
                {
                    if (confirm)
                    {
                        http.get(
                            '/hForum/deleteForum', {
                                operation : 'Delete Forum',
                                hForumId : hot.selected('hForum').splitId()
                            },
                            function(json)
                            {
                                hot.selected('hForum').remove();
                                hot.unselect('hForum');
                            }
                        );
                    }
                }
            );
        }
    },

    newForum : function()
    {
        http.get(
            '/hForum/newForum', {
                operation : 'Create Forum',
                hFileId : this.fileId,
                hForum : "New Forum"
            },
            function(json)
            {
                forum.setNewForum(json);
            }
        );
    },

    openTopicPermissionsDialogue : function(forumTopicId)
    {
        var permissions = hot.window(
            '/System/Applications/permissions.html', {
                hFrameworkResourceId : 4,
                hFrameworkResourceKey : forumTopicId
            },
            800,
            625,
            'hUserPermissions', {
                menubar : false,
                location : false,
                statusbar : false,
                titlebar : false,
                toolbar : false,
                scrollbars : true,
                resizable : false,
                alwaysraised : true,
                "z-lock" : true
            }
        );
    },

    setNewForum : function(forumId)
    {
        var li = $('li.hForumTemplate').clone(true);

        li.attr('id', 'hForum-' + forumId)
          .removeClass('hForumTemplate')
          .find('div.hForumTitleOuter')
            .attr('id', 'hForumTitle-' + forumId);

        li.find('tbody').attr('id', 'hForumTopics-' + forumId);

        li.find('tr.hForumTopicTemplate').remove();

        $('div#hForumWrapper > ul').append(li);
    },

    renameForum : function(forumId, forum)
    {
        http.get(
            '/hForum/renameForum', {
                operation : 'Rename Forum',
                hForumId: forumId,
                hForum: forum
            },
            function(json)
            {

            }
        );
    },

    setNoTopics : function()
    {
        $('li.hForum:not(.hForumTemplate) table.hForumTopics tbody').each(
            function()
            {
                if (!$(this).find('tr.hForumTopic').length && !$(this).find('tr.hForumNoPosts').length)
                {
                    $(this)
                        .append($('li.hForumTemplate tr.hForumNoPosts').clone(true).hide())
                        .find('tr.hForumNoPosts').fadeIn();

                }
                else if ($(this).find('tr.hForumNoPosts').length && $(this).find('tr.hForumTopic').length)
                {
                    $(this).find('tr.hForumNoPosts').fadeOut(
                        function()
                        {
                            $(this).remove();
                        }
                    );
                }
            }
        );
    },

    openTopicDialogue : function()
    {
        this.topicId = arguments[0]? arguments[0] : 0;
        this.forumId = hot.selected('hForum').splitId();

        $('form#hForumTopicDialogue').openDialogue(true);
    },

    closeTopicDialogue : function()
    {
        $('form#hForumTopicDialogue').closeDialogue(true);
        $('form#hForumTopicDialogue').get(0).reset();
    },

    editTopic : function(forumTopicId)
    {
        var tr = $('tr#hForumTopic-' + forumTopicId);

        $('input#hForumTopic').val(this.getTopicTitle(forumTopicId));

        if (parseInt(tr.find('span.hForumTopicIsModerated').text()) > 0)
        {
            $('input#hForumTopicIsModerated').attr('checked', 'checked');
        }
        else
        {
            $('input#hForumTopicIsModerated').removeAttr('checked');
        }

        if (parseInt(tr.find('span.hForumTopicIsLocked').text()) > 0)
        {
            $('input#hForumTopicIsLocked').attr('checked', 'checked');
        }
        else
        {
            $('input#hForumTopicIsLocked').removeAttr('checked');
        }

        $('textarea#hForumTopicDescription').val(tr.find('td.hForumTopic p').text());

        this.openTopicDialogue(forumTopicId);
    },

    saveTopic : function()
    {
        http.get(
            {
                url : '/hForum/saveTopic?' +
                    $('form#hForumTopicDialogue').serialize() +
                    '&hForumId=' + this.forumId +
                    '&hForumTopicId=' + this.topicId,
                operation : 'Save Forum Topic'
            },
            function(json)
            {
                if (!this.topicId)
                {
                    this.topicId = json;
                    this.newTopic();
                }
                else
                {
                    $('tr#hForumTopic-' + json).updateTopic();
                }

                this.closeTopicDialogue();
            },
            this
        );
    },

    newTopic : function()
    {
        var tr = $('tr.hForumTopicTemplate').clone(true);

        tr.removeClass('hForumTopicTemplate');

        tr.attr('id', 'hForumTopic-' + this.topicId);

        tr.find('td.hForumTopic a')
          .attr('href', this.path + '?hForum=' + this.fileId + '/' + this.forumId + '/' + this.topicId);

        tr.find('td.hForumSubscription')
          .attr('id', 'hForumSubscription-' + this.topicId)
          .find('a')
          .attr('href', '/hForum/toggleTopicSubscription?hForumTopicId=' + this.topicId + '&html=1');

        tr.updateTopic();

        $('li#hForum-' + this.forumId + ' table.hForumTopics tbody').append(tr);

        this.setNoTopics();
    },

    sortTopics : function()
    {
        this.post = '';

        $('li.hForum:not(.hForumTemplate)').each(
            function()
            {
                var i = 1;
                var forumId = $(this).splitId();

                $(this).find('tr.hForumTopic').each(
                    function()
                    {
                        forum.post += '&hForums[' + forumId + '][' + $(this).splitId() + ']=' + i;
                        i++;
                    }
                );
            }
        );

        http.post(
            {
                url : '/hForum/sortTopics',
                operation : 'Sort Forum Topics'
            },
            this.post.substring(1, this.post.length),
            function(json)
            {

            }
        );
    },

    sortForums : function()
    {
        this.post = '';
        var i = 1;

        $('li.hForum:not(.hForumTemplate)').each(
            function()
            {
                forum.post += '&hForums[' + $(this).splitId() + ']=' + i;
                i++;
            }
        );

        http.post(
            {
                url : '/hForum/sortForums',
                operation : 'Sort forums'
            },
            this.post.substring(1, this.post.length),
            function(json)
            {

            }
        );
    },

    deletePost : function(forumPostId)
    {
        this.postId = forumPostId;

        dialogue.confirm(
            {
                title : "Confirm Deletion",
                label : "<p>Are you certain you want to <b>PERMANENTLY</b> delete <i>" +  forum.getPostSubject(forumPostId) + "</i>?</p>" +
                        "<p>This cannot be undone.</p>",
                ok : "Delete Post",
                cancel : "Don't Delete Post"
            },
            function(confirm)
            {
                if (confirm)
                {
                    http.get(
                        '/hForum/deletePost', {
                            operation : 'Delete a forum post',
                            hForumPostId : forum.postId
                        },
                        function(json)
                        {
                            if ($('tr#hForumPost-' + forum.postId).length)
                            {
                                $('tr#hForumPost-' + forum.postId).fadeOut();
                            }
                        }
                    );
                }
            }
        );
    },

    deleteTopic : function(forumTopicId)
    {
        this.topicId = forumTopicId;

        dialogue.confirm(
            {
                title : "Confirm Deletion",
                label : "<p>Are you certain you want to <b>PERMANENTLY</b> delete <i>" + forum.getTopicTitle(forumTopicId) + "</i> and all posts made to it?</p>" +
                        "<p>This cannot be undone.</p>",
                ok : "Delete Topic",
                cancel : "Don't Delete Topic"
            },
            function(confirm)
            {
                if (confirm)
                {
                    http.get(
                        '/hForum/deleteTopic', {
                            operation : 'Delete a forum topic',
                            hForumTopicId : forum.topicId
                        },
                        function(json)
                        {
                            $('tr#hForumTopic-' + forum.topicId).fadeOut(
                                function()
                                {
                                    $(this).remove();
                                    forum.setNoTopics();
                                }
                            );
                        }
                    );
                }
            }
        );
    },

    togglePostAttribute : function(forumPostId, attribute)
    {
        this.postId = forumPostId;
        this.postAttribute = attribute;

        // Lock, Approval, Stickiness
        http.get(
            '/hForum/togglePost' + attribute, {
                operation : attribute + " Post",
                hForumPostId : forumPostId
            },
            function(json)
            {
                var className = forum.postAttribute;

                if (forum.postAttribute == 'Stickiness')
                {
                    className = 'Sticky';
                }

                var node = $('tr#hForumPost-' + forum.postId);
                var attribute = node.find('a.hForumPost' + className);

                if (parseInt(json))
                {
                    attribute.addClass('hForumPostUn' + className.toLowerCase());
                    node.find('span.hForumPostSubject' + className).show();

                    switch (forum.postAttribute)
                    {
                        case 'Lock':
                        {
                            node.find('a.hForumPostReply').hide();
                            break;
                        }
                    }
                }
                else
                {
                    attribute.removeClass('hForumPostUn' + className.toLowerCase());
                    node.find('span.hForumPostSubject' + className).hide();

                    switch (forum.postAttribute)
                    {
                        case 'Lock':
                        {
                            node.find('a.hForumPostReply').show();
                            break;
                        }
                    }
                }
            }
        );
    },

    subscribeToThread : function(forumPostId)
    {
        application.status.message($('a.hForumThreadSubscribe').length? 'Subscribing...' : 'Unsubscribing...');

        http.get(
            '/hForum/toggleThreadSubscription', {
                operation : 'Subscribe To a Thread',
                hForumPostId : forumPostId
            },
            function(json)
            {
                var label = '';
                var message = '';
                var title = forum.getPostSubject();

                if (parseInt(json))
                {
                    label = 'Unsubscribe';
                    message = 'You are subscribed to all posts in ';
                }
                else
                {
                    label = 'Subscribe';
                    message = 'You have been unsubscribed from ';
                }

                $('li.hForumThreadSubscriptionButton a').attr('class', 'hForumThread' + label);
                application.status.message(message + title, true);
            }
        );
    },

    getPostSubject : function()
    {
        if (arguments[0] && $('tr#hForumPost-' + arguments[0]).length)
        {
            return $('tr#hForumPost-' + arguments[0] + ' td.hForumPostSubject a').text();
        }
        else
        {
            return $('h2.hForumPostSubject').text();
        }
    },

    subscribeToTopic : function(forumTopicId)
    {
        this.topicId = forumTopicId;

        application.status.message($('a.hForumThreadSubscribe').length? 'Subscribing...' : 'Unsubscribing...');

        http.get(
            '/hForum/toggleTopicSubscription', {
                operation : 'Subscribe to a forum topic',
                hForumTopicId : forumTopicId
            },
            function(json)
            {
                var label     = '';
                var message = '';
                var title     = forum.getTopicTitle(forum.topicId);

                if (parseInt(json))
                {
                    label = 'Unsubscribe';
                    message = 'You are subscribed to all posts in ' + title;
                }
                else
                {
                    label = 'Subscribe';
                    message = 'You have been unsubscribed from ' + title;
                }

                if ($('td#hForumSubscription-' + forum.topicId).length)
                {
                    $('td#hForumSubscription-' + forum.topicId + ' a').text(label);
                }

                $('li.hForumSubscriptionButton a').attr('class', 'hForum' + label);

                application.status.message(message, true);
            }
        );
    },

    getTopicTitle : function()
    {
        if (arguments[0] && $('td#hForumSubscription-' + arguments[0]).length)
        {
            return $('td#hForumSubscription-' + arguments[0]).parent().find('td.hForumTopic a').text();
        }
        else
        {
            return $('h2.hForumTopic').text();
        }
    }
};

$(document).ready(
    function()
    {
        forum.ready();
    }
);
