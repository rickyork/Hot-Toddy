hot.include('/hFinder/hFinderContextMenu/hFinderContextMenu.js');

$.fn.extend({
    openPermissionsMenu : function()
    {
        finder.info.permissions = this;
        finder.info.permissionsType = this.parents('tr:first').attr('data-permissions-type');

        $('div.hFinderInfoPermissionsMenu')
            .css({
                top : $(this).offset().top,
                left : $(this).offset().left
            })
            .show();

        var level = $(this).find('span').text();

        if (level && level.length)
        {
            $('div.hFinderInfoPermissionsMenu li').each(
                function()
                {
                    if (level == $(this).find('span').text())
                    {
                        $(this).select('hFinderInfoPermissionsMenu');
                    }
                }
            );
        }
    }
});

if (typeof(finder) == 'undefined')
{
    var finder = {};
}

finder.info = {

    path : null,

    menuTracker : false,
    
    resourceId : 0,
    resourceKey : 0,

    ready : function()
    {
        hot.event(
            'onCloseContextMenu',
            function()
            {
                if (this.hasClass('hFinderInfoPermissionsMenu'))
                {
                    finder.info.savePermissions();
                }
            }
        );
    
        $('div#hFileComments').get(0).contentEditable = true;

        $('span.hFinderInfoArrow').click(
            function()
            {
                $(this).parents('h4').next().slideToggle('fast');
                $(this).toggleClass('hFinderInfoArrowOff');
             }
         );

         $('span.hFinderInfoCommentsArrow').click(
             function()
             {
                 $(this).parents('h4').next().slideToggle('fast');
                 $(this).toggleClass('hFinderInfoCommentsArrowOff');
             }
         );

         $(document).on(
            'click',
            'div#hFinderInfoPermissions table tbody tr',
             function()
             {
                 if ($(this).find('td:first-child').text().length)
                 {
                    $(this).select('hFinderInfoPermissions');
                    
                    switch (true)
                    {
                        case $(this).hasClass('hFrameworkResourceGroup'):
                        case $(this).hasClass('hFrameworkResourceUser'):
                        {
                            $('button#hFinderInfoRemoveGroup').addClass('hFinderInfoRemoveGroupEnabled');
                            break;
                        }
                        default: 
                        {
                            $('button#hFinderInfoRemoveGroup').removeClass('hFinderInfoRemoveGroupEnabled');
                        }
                    }
                 }
             }
         );

         $(document).on(
            'click',
            'button#hFinderInfoRemoveGroup',
            function(e)
            {
                e.preventDefault();
                e.stopPropagation();
                
                var selected = select.ed('hFinderInfoPermissions');
                
                if (selected && selected.length && (selected.hasClass('hFrameworkResourceGroup') || selected.hasClass('hFrameworkResourceUser')))
                {
                    selected.remove();
                    select.un('hFinderInfoPermissions');
                }

                finder.info.savePermissions();
            }
         );

         $('span.hFileLabel').click(
             function()
             {
                 finder.label.set($('div#hFinderInfoTop').attr('title'), $(this).attr('title'));            
             }
         );

         this.path = $('div#hFinderInfoTop').attr('title');

         opener.finder.setInfo({
                onloadFileName : $('input#hFinderInfoFileName').val(),
                onloadComments : $('div#hFileComments').text(),
                path : this.path
            },
            true
        );

         $('div#hFileComments')
            .keyup(
                function()
                {
                    opener.finder.setInfo({
                        comments : $(this).text(),
                        path : finder.info.path
                    });
                }
            )
            .blur(
                function()
                {
                    opener.finder.saveInfo({
                        comments : $(this).text(),
                        path : finder.info.path
                    });
                }
            );

         $('input#hFinderInfoFileName')
            .keyup(
             function()
             {
                 opener.finder.setInfo({
                     fileName : $(this).val(),
                     path : finder.info.path
                 });
             }
         )
         .blur(
             function()
             {
                 opener.finder.saveInfo({
                     fileName : $(this).val(),
                     path : finder.info.path
                 });
             }
         );

        $(document).on(
            'click',
            'td.hFinderInfoPermissions',
            function()
            {            
                $(this).openPermissionsMenu();
            }
        );

        $('div.hFinderInfoPermissionsMenu li').click(
            function()
            {
                $(this).select('hFinderInfoPermissionsMenu');
                finder.info.permissions.find('span').text($(this).find('span').text());
                $('div.hFinderInfoPermissionsMenu').hide();

                hot.fire.call($('div.hFinderInfoPermissionsMenu'), 'onCloseContextMenu');
            }
        );
        
        $('button#hFinderInfoAddGroup').click(
            function(e)
            {
                e.preventDefault();
                finder.info.openContactDialogue('finder.info.addGroup');
            }
        );
        
        $(document)
            .on(
                'dragover',
                'body',
                function(e)
                {
                    e.preventDefault();
                    e.stopPropagation();
                    e.originalEvent.dataTransfer.dropEffect = 'copy';
                }
            )
            .on(
                'dragleave',
                'body',
                function(e)
                {
                    e.preventDefault();
                }
            )
            .on(
                'drop',
                'body',
                function(e)
                {   
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var html = e.originalEvent.dataTransfer.getData((hot.userAgent == 'ie')? 'Text' : 'text/html');
    
                    // Chrome inserts a <meta> element with the content-type and charset.
                    // Thanks, but I don't need that.
                    html = $(html.replace(/\<(meta).*?\>/gmi, ''));

                    if (html.hasClass('hContactRecord'))
                    {
                        finder.info.addGroup({
                            userId : parseInt(html.find('span.hContactRecordUserId').text()),
                            contactId : parseInt(html.find('span.hContactRecordId').text()),
                            userName : html.find('li.hContactRecordUserName').text(),
                            isGroup : html.hasClass('hContactGroupRecord')
                        });
                    }
                    else if (html.hasClass('hUserGroup'))
                    {
                        finder.info.addGroup({
                            userId : parseInt(html.attr('data-userId')),
                            contactId : parseInt(html.attr('data-contactId')),
                            userName : html.attr('title'),
                            isGroup : true
                        });
                    }
                }
            );
    },
    
    openContactDialogue : function(callback)
    {
        this.contactDialogue = hot.window(
            '/Applications/Contacts/Dialogue.html', { 
                onChooseContact : callback
            }, 831, 400, 'hContactDialogue', {
                scrollbars : false,
                resizable : true   
            }
        );
    },
    
    addGroup : function(contact)
    {   
        var row = 
            "<tr data-permissions-type='group' class='" + (contact.isGroup? 'hFrameworkResourceGroup' : 'hFrameworkResourceUser') + "'>" +
                "<td class='hFinderInfoPermissionsLabel " + (contact.isGroup? 'hFrameworkResourceGroupLabel' : 'hFrameworkResourceUserLabel') + "'><span>" + contact.userName + "</span></td>" +
                "<td class='hFinderInfoPermissions' id='hUserPermissionsGroup-" + contact.userId + "'>" +
                    "<span>No Access</span>" +
                "</td>" +
            "</tr>";
            
        if ($('div#hFinderInfoPermissions tr.hFinderInfoPermissionsEmpty').length)
        {
            $('div#hFinderInfoPermissions tr.hFinderInfoPermissionsEmpty:first').replaceWith(row);
        }
        else
        {
            $('div#hFinderInfoPermissions table tbody').append(row);
        }
        
        this.savePermissions();
    },

    savePermissions : function()
    {
        if (this.resourceId && this.resourceKey)
        {
            var owner = this.getPermissions($('td#hUserPermissionsOwner span').text());
            var world = this.getPermissions($('td#hUserPermissionsWorld span').text());

            var users = [];
            var groups = [];

            var post = '';
            
            $('tr.hFrameworkResourceGroup, tr.hFrameworkResourceUser').each(
                function()
                {
                    var level = finder.info.getPermissions($(this).find('td.hFinderInfoPermissions span').text());

                    if ($(this).hasClass('hFrameworkResourceUser'))
                    {
                        users[$(this).find('td.hFrameworkResourceUserLabel span').text()] = level;
                    }
                    else if ($(this).hasClass('hFrameworkResourceGroup'))
                    {
                        groups[$(this).find('td.hFrameworkResourceGroupLabel span').text()] = level;
                    }
                }
            );

            http.post(
                '/hUser/hUserPermissions/save', {
                    resourceId : this.resourceId, 
                    resourceKey : this.resourceKey
                }, {
                    operation : 'Save Permissions',
                    owner : owner, 
                    world : world,
                    users : users,
                    groups : groups
                },
                function(json)
                {
                    
                }
            );
        }
        else
        {
            hot.console.error('Unable to save permissions: either the resourceId or resourceKey is empty.');
        }
    },
    
    getPermissions : function(label)
    {
        switch ($.trim(label))
        {
            case 'Read & Write':
            case 'Read &amp; Write':
            {
                return 'rw';
            }
            case 'Read only':
            {
                return 'r';
            }
            case 'No Access':
            default:
            {
                return '';
            }
        }
    },

    updateFile : function(obj)
    {
        $('input#hFinderInfoFileName').val(obj.onloadFileName);
        $('div#hFinderInfoName').text(obj.onloadFileName);

        document.title = obj.onloadFileName + ' Info';

        this.path = obj.path;

        $('div#hFinderInfoTop').attr('title', obj.path);
    }
};

$(document).ready(
    function()
    {
        finder.info.ready();
    }
);

$(window).load(
    function()
    {
        window.resizeTo(300, $('div#hFinderInfoWrapper').outerHeight() + 25);
    }
);

$(window).unload(
    function()
    {
        opener.finder.saveInfo({
            path : finder.info.path
        }, true);
    }
);