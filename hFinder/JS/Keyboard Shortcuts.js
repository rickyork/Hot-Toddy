keyboard.globalShortcutsEnabled = false;

finder.shortcuts = {

    metaKey : false,
    ctrlKey : false,
    altKey : false,
    shiftKey : false,

    copyFileNames : false,

    ready : function()
    {
        $(document)
            .keydown(this.trackModifiers)
            .keyup(this.trackModifiers)
            .keypress(this.trackModifiers);
    },

    trackModifiers : function(event)
    {
        finder.shortcuts.metaKey = event.metaKey;
        finder.shortcuts.ctrlKey = event.ctrlKey;
        finder.shortcuts.altKey = event.altKey;
        finder.shortcuts.shiftKey = event.shiftKey;
    },

    directional : function(event)
    {
        var node = hot.selected('hFinder');

        if (node && node.length)
        {
            return node;
        }

        $('div.hFinderNode:first')
            .select('hFinder');

        return false;
    }
};

keyboard
    .shortcut(
        {
            renameFile : 'Return',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = hot.selected('hFinder');

            if (node && node.length)
            {
                node.find('span.hFinderDirectoryName, span.hFinderFileName')
                    .inlineRename();
            }
        }
    )
    .shortcut(
        {
            escape : 'Escape'
        },
        function(event)
        {
            if (!keyboard.isInput(event))
            {
                $('li#hFinder-Action').closeActionMenu();
                $('li#hFinder-Path').closeLocationMenu();

                if (finder.contextMenu)
                {
                    finder.contextMenu.close();
                }
            }
            else
            {
                var target = $(event.target);
                target.is('input') ? target.blur() : target.parents('input:first').blur();
            }
        }
    )
    .shortcut(
        {
            deleteFile : 'Delete, Backspace',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = hot.selected('hFinder');

            if (node && node.length)
            {
                node.deleteFile();
            }
        }
    )
    .shortcut(
        {
            selectAboveFile : 'Up Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = finder.shortcuts.directional(event);

            if (node !== false)
            {
                node.selectAboveFile();
            }
        }
    )
    .shortcut(
        {
            selectNextFile : 'Right Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = finder.shortcuts.directional(event);

            if (node !== false)
            {
                node.selectNextFile();
            }
        }
    )
    .shortcut(
        {
            selectBelowFile : 'Down Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = finder.shortcuts.directional(event);

            if (node !== false)
            {
                node.selectBelowFile();
            }
        }
    )
    .shortcut(
        {
            selectPreviousFile : 'Left Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = finder.shortcuts.directional(event);

            if (node !== false)
            {
                node.selectPreviousFile();
            }
        }
    )
    .shortcut(
        {
            selectNextFileByTab : 'Tab',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var node = hot.selected('hFinder');

            if (!node)
            {
                $('div.hFinderNode:first')
                    .select('hFinder');
            }
            else
            {
                var input = node.find('input');

                if (input.length && input.blur)
                {
                    input.blur();
                }

                $(node).selectNextFile();
            }
        }
    )
    .shortcut(
        {
            openEditor : 'Command + E, Control + E'
        },
        function()
        {
            if (!hot.selected('hFinder').length)
            {
                $('div.hFinderNode:first')
                    .select('hFinder');
            }

            hot.window(
                '/Applications/Editor',
                1200, 768,
                'hEditor',
                {
                    path : hot.selected('hFinder').getFilePath()
                },
                {
                    scrollbars : false,
                    resizable : true
                }
            );
        }
    )
    .shortcut(
        {
            openTemplateEditor : 'Command + Shift + E, Control + Shift + E'
        },
        function()
        {
            if (!hot.selected('hFinder').length)
            {
                $('div.hFinderNode:first')
                    .select('hFinder');
            }

            hot.window(
                hot.selected('hFinder').getFilePath(),
                1200, 768,
                '_blank',
                {
                    hEditorTemplateEnabled : 1
                }
            );
        }
    )
    .shortcut(
        {
            openApplicationsFolder : 'Command + Shift + A, Control + Shift + A'
        },
        function()
        {
            finder.requestDirectory('/Applications');
        }
    )
    .shortcut(
        {
            toggleDuplicateFileRestriction : 'Command + Shift + D, Control + Shift + D'
        },
        function()
        {
            if (typeof(get.hFileSystemAllowDuplicates) == 'undefined')
            {
                get.hFileSystemAllowDuplicates = 1;
            }
            else
            {
                delete get.hFileSystemAllowDuplicates;
            }
        }
    )
    .shortcut(
        {
            toggleCopyFileNames : 'Command + Shift + F, Control + Shift + F'
        },
        function()
        {
            finder.shortcuts.copyFileNames = !finder.shortcuts.copyFileNames;
        }
    )
    .shortcut(
        {
            openHomeFolder : 'Command + Shift + H, Control + Shift + H'
        },
        function()
        {
            $('div.hFinderTreeHome').click();
        }
    )
    .shortcut(
        {
            navigateBack : 'Command + [, Control + ['
        },
        function()
        {
            $('li#hFinder-Back').stepBackward();
        }
    )
    .shortcut(
        {
            navigateForward : 'Command + ], Control + ]'
        },
        function()
        {
            $('li#hFinder-Forward').stepForward();
        }
    )
    .shortcut(
        {
            refresh : 'Command + R, Control + R'
        },
        function(event)
        {
            finder.refresh();
        }
    )
    .shortcut(
        {
            openFolder : 'Command + O, Control + O'
        },
        function(event)
        {
            var node = hot.selected('hFinder');

            if (node && node.length && node.isDirectory())
            {
                node.getDirectory();
            }
        }
    )
    .shortcut(
        {
            openEnclosingFolder : 'Command + Up Arrow, Control + Up Arrow',
            disableShortcutOnInput : true
        },
        function(event)
        {
            var enclosing = $('div#hFinderLocationMenu ul li:first-child').next();

            if (enclosing.length)
            {
                enclosing.click();
            }

            $('li#hFinder-Path').closeLocationMenu();
        }
    );

$(document)
    .ready(
        function()
        {
            finder.shortcuts.ready();
        }
    )
    .on(
        'beforecopy',
        function(event)
        {
            if (event.target.nodeName.toLowerCase() != 'input' && event.target.nodeName.toLowerCase() != 'textarea' && event.target.contentEditable != 'true')
            {
                event.preventDefault();

                if (hot.selected('hFinder').length)
                {
                    var path = '';

                    if (!finder.shortcuts.copyFileNames)
                    {
                        path = finder.decode(
                            hot.selected('hFinder')
                               .getFilePath()
                        );
                    }
                    else
                    {
                        path = hot.selected('hFinder').getFileName();
                    }

                    $('textarea#hFinderCopyTextareaInput')
                        .val(path)
                        .focus()
                        .get(0)
                        .select();
                }
            }
        }
    );
