
var vars = window.location.search.substring(1).split('&');

var get = [];

if (vars.length)
{
    for (var i = 0; i < vars.length; i++)
    {
        var pair = vars[i].split('=');
        get[pair[0]] = decodeURIComponent(pair[1]);
    }
}

$.fn.extend({

    placeholder : function(placeholderText)
    {
        if (hot.placeholderSupported)
        {
            return this.attr('placeholder', placeholderText);
        }
        else
        {
            return this
                .val(placeholderText)
                .css('color', 'rgb(150, 150, 150)')
                .focus(
                    function()
                    {
                        if ($(this).val() == placeholderText)
                        {
                            $(this)
                                .val('')
                                .css('color', '#000');
                        }
                    }
                )
                .blur(
                    function()
                    {
                        if (!$(this).val())
                        {
                            $(this)
                                .val(placeholderText)
                                .css('color', 'rgb(150, 150, 150)');
                        }
                    }
                );
        }
    },

    outerHTML : function()
    {
        if (arguments[0])
        {
            return this.replaceWith(arguments[0]);
        }

        var temporary = $("<div/>").append($(this).clone());
        var html = temporary.html();

        // Don't leak memory.
        temporary.remove();
        return html;
    },

    disabled : function()
    {
        return this.attr('disabled', 'disabled');
    },

    checked : function()
    {
        return this.attr('checked', 'checked');
    },

    selected : function()
    {
        return this.attr('selected', 'selected');
    },

    getFileNameFromSrc : function()
    {
        return this.attr('src').split('/').pop();
    },

    buttons : function()
    {
        if (this.attr('src').indexOf('None') != -1)
        {
            this.hover(
                    function()
                    {
                        $(this).sourceFile(
                            $(this).getFileNameFromSrc()
                                   .replace(/None|Active|Hover/, 'Hover')
                        );
                    },
                    function()
                    {
                        $(this).sourceFile(
                            $(this).getFileNameFromSrc()
                                   .replace(/None|Active|Hover/, 'None')
                        );
                    }
                )
                .mousedown(
                    function()
                    {
                        $(this).sourceFile(
                            $(this).getFileNameFromSrc()
                                   .replace(/None|Active|Hover/, 'Active')
                        );
                    }
                )
                .mouseup(
                    function()
                    {
                        $(this).sourceFile(
                            $(this).getFileNameFromSrc()
                                   .replace(/None|Active|Hover/, 'None')
                        );
                    }
                );
        }
    },

    button : function(mousedownSource, mouseupSource)
    {
        this.mousedown(
            new Function(
                "$(this).sourceFile('" + mousedownSource + "');"
            )
        );

        this.mouseup(
            new Function(
                "$(this).sourceFile('" + mouseupSource + "');"
            )
        );

        if (arguments[2])
        {
            this.click(arguments[2]);
        }
    },

    active : function()
    {
        if (arguments[0])
        {
            this.mousedown(arguments[0]);

            if (arguments[1])
            {
                this.mouseup(arguments[1]);
            }

            if (arguments[2])
            {
                this.click(arguments[2]);
            }
        }
        else
        {
            this.mousedown();
            this.mouseup();
            this.click();
        }

        return this;
    },

    splitId : function()
    {
        var id = !arguments[2]? this.attr('id') : arguments[2];

        if (!id)
        {
            return '';
        }

        if (id.indexOf('-') < 0)
        {
            return '';
        }

        if (arguments[1])
        {
            var bits = id.split('-');
            bits = bits.reverse();

            for (i = 0; i < bits.length; i++)
            {
                if (i == arguments[1])
                {
                    return bits[i];
                }
            }
        }

        return id.split('-').pop();
    },

    splitNumericId : function()
    {
        var id = this.attr('id');
        return id.indexOf('--') != -1 ? -(parseInt(id.split('-').pop())) : parseInt(id.split('-').pop());
    },

    scrollTo : function(element)
    {
        $(this).get(0).scrollLeft = element.offsetLeft;
        $(this).get(0).scrollTop  = element.offsetTop;
    },

    sourceFile : function()
    {
        var src = this.attr('src');

        if (src && src.length)
        {
            var path = this.attr('src').split('/');
            var currentFile = path.pop();

            if (arguments[0])
            {
                var file = arguments[0];

                path.push(file);
                return this.attr('src', path.join('/'));
            }
            else
            {
                return currentFile;
            }
        }
        else
        {
            hot.console.log("Element's src attribute is either empty or does not exist: " + this.outerHTML());
        }
    },

    serializeObject : function()
    {
        var post = {};

        this.find('input, select, textarea').each(
            function()
            {
                if ($(this).is('input'))
                {
                    switch ($(this).attr('type').toLowerCase())
                    {
                        case 'radio':
                        {
                            if ($(this).is(':checked'))
                            {
                                post[$(this).attr('name')] = $(this).val();
                            }

                            break;
                        }
                        case 'checkbox':
                        {
                            if ($(this).is(':checked'))
                            {
                                post[$(this).attr('name')] = $(this).val();
                            }

                            break;
                        }
                        default:
                        {
                            post[$(this).attr('name')] = $(this).val();
                        }
                    }
                }
                else if ($(this).is('select'))
                {
                    if ($(this).attr('multiple') == 'multiple')
                    {

                    }
                    else
                    {

                    }
                }
                else
                {
                    post[$(this).attr('name')] = $(this).val();
                }
            }
        );

        return post;
    }
});

hot.desktopApplication = (hot.userAgentOS == 'Desktop Application');

$.ajaxSetup({
    cache : false
});

$.extend(
    hot, {

    nl2br : function(string)
    {
        return string.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
    },

    // Ripped from John Resig's awesome class instantiation code.
    // http://ejohn.org/blog/simple-class-instantiation/
    factory : function()
    {
        return function(args)
        {
            if (this instanceof arguments.callee)
            {
                if (typeof(this.init) == 'function')
                {
                    this.init.apply(this, args && args.callee? args : arguments);
                }
            }
            else
            {
                return new arguments.callee(arguments);
            }
        }
    },

    path : function(path)
    {
        //if (this.installPath != '/') {
        //  path = this.installPath + path;
        //}

        if (this.userAgentOS == 'Desktop Application')
        {
            path = "http://" + server.host + path;
        }

        if (!arguments[1] && this.userAgentOS == 'Desktop Application')
        {
            arguments[1] = {};
        }

        if (this.userAgentOS == 'Desktop Application')
        {
            arguments[1].hDesktopApplicationStyle = 1;

            if (get.hUserAuthenticationToken)
            {
                arguments[1].hUserAuthenticationToken = get.hUserAuthenticationToken;
            }
            else if (typeof(hUserId) != 'undefined' && typeof(hUserPassword) != 'undefined')
            {
                arguments[1].hUserAuthenticationToken = hUserId + ',' + hUserPassword;
            }
        }

        if (arguments[1] && path && typeof path === 'string')
        {
            if (path && path.indexOf && path.indexOf('?') == -1)
            {
                path += '?';
            }
            else
            {
                path += '&';
            }

            path += this.getQueryString(arguments[1]);
        }

        return path;
    },

    getQueryString : function(data)
    {
        if (typeof(data) == 'string')
        {
            return data;
        }

        if (typeof(data) == 'object')
        {
            var postData = [];

            for (var i in data)
            {
                if (typeof(data[i]) == 'object' || data[i] instanceof Array)
                {
                    for (var n in data[i])
                    {
                        postData.push(i + '[' + encodeURIComponent(n) + ']=' + encodeURIComponent(data[i][n]));
                    }
                }
                else
                {
                    postData.push(i + "=" + encodeURIComponent(data[i]));
                }
            }

            return postData.join('&');
        }

        return '';
    },

    // Deprecated: Use include.js(path) instead
    include : function(path)
    {
        if (arguments[1] !== undefined && typeof arguments[1] == 'function')
        {
            arguments[1] = {
                success : arguments[1]
            };
        }

        include.js.apply(include, arguments);
        return this;
    },

    // Deprecated: Use include.css(path) instead
    includeCSS : function(path)
    {
        include.css.apply(include, arguments);
        return this;
    },

    _windowCounter : 0,

    getOffsetOfArgumentWithType : function(args, type)
    {
        var counter = 0;

        for (var i = 0; i < args.length; i++)
        {
            if (typeof(args[i]) == type)
            {
                return counter;
            }

            counter++;
        }

        return 0;
    },

    ensureValidCallbackFunction : function(string)
    {
        var matches = string.match(/[a-z]|\d|_|\.+/ig);
        return matches && matches.length ? matches.join('') : null;
    },

    countArgumentsOfType : function(args, type)
    {
        var counter = 0;

        for (var i = 0; i < args.length; i++)
        {
            if (typeof(args[i]) == type)
            {
                counter++;
            }
        }

        return counter;
    },

    getArgumentByType : function(args, type, defaultValue)
    {
        var offset = 1;

        if (arguments[3])
        {
            offset = arguments[3];
        }

        var counter = 1;

        for (var i = 0; i < args.length; i++)
        {
            if (typeof(args[i]) == type)
            {
                if (offset == counter)
                {
                    return args[i];
                }
                else
                {
                    counter++;
                }
            }
        }

        return defaultValue;
    },

    window : function(url)
    {
        var getParameters = this.getArgumentByType(arguments, 'object', {});
        var width         = this.getArgumentByType(arguments, 'number', 800);
        var height        = this.getArgumentByType(arguments, 'number', 640, 2);
        var name          = this.getArgumentByType(arguments, 'string', 'window' + this._windowCounter, 2);
        var options       = this.getArgumentByType(arguments, 'object', {}, 2);

        this._windowCounter++;

        options.width = width;
        options.height = height;

        var optionsArray = [];

        for (var i in options)
        {
            if (options[i] === true)
            {
                optionsArray.push(i + '=yes');
            }
            else if (options[i] === false)
            {
                optionsArray.push(i + '=no');
            }
            else
            {
                optionsArray.push(i + '=' + options[i]);
            }
        }

        // 'menubar=no, location=no, statusbar=no, titlebar=no, toolbar=no, scrollbars=yes, resizable=no, alwaysraised=yes, z-lock=yes, width=800, height=640'
        var popup = window.open(hot.path(url, getParameters), name, optionsArray.join(','));
        popup.moveTo((window.screen.width - width) / 2, (window.screen.height - height) / 2);
        popup.focus();

        return popup;
    },

    /**
    * Open applications in a new window.  This method is used by the various key bindings defined
    * near the end of this file.
    */
    openApplicationWindow : function()
    {
        hot.window(
            arguments[0],
            arguments[2] ? arguments[2] : 1200,
            arguments[3] ? arguments[3] : 768,
            arguments[1], {
                path: arguments[1] == 'hFinder'? hot.directoryPath : hot.filePath
            }, {
                scrollbars: false,
                resizable: true
            }
        );
    },

    getCallbackAndContext : function(args, options)
    {
        var fn = function()
        {
            
        };
        
        var context = null;
        
        var callbackOffset = typeof arguments[2] !== 'undefined' ? arguments[2] : 1;
        var contextOffset = typeof arguments[3] !== 'undefined' ? arguments[3] : 2;

        if (typeof options === 'object')
        {
            if (typeof options.callback === 'object')
            {
                if (typeof options.callback.fn !== 'undefined')
                {
                    fn = options.callback.fn;
                }

                if (typeof options.callback.context !== 'undefined')
                {
                    context = options.callback.context;
                }
            }
            else if (typeof options.callback === 'function')
            {
                fn = options.callback;
            }
            else if (typeof (options.fn === 'function'))
            {
                fn = options.fn;
            }
        }
        else
        {
            if (typeof args[callbackOffset] === 'function')
            {
                fn = args[callbackOffset];
            }

            if (typeof args[contextOffset] !== 'undefined')
            {
                context = args[contextOffset];
            }
        }

        return {
            fn : fn,
            context : context
        };
    },

    /**
    * console wrappers do object detection so that browsers not supporting the console object don't choke on it.
    *
    * Reference: http://blogs.msdn.com/b/cdndevs/archive/2011/05/26/console-log-say-goodbye-to-javascript-alerts-for-debugging.aspx
    */
    console : {

        clear : function()
        {
            if (console && console.clear)
            {
                console.clear();
            }
        },

        log : function(message)
        {
            if (console && console.log)
            {
                console.log(message);

                if (arguments[1])
                {
                    console.log(arguments[1]);
                }
            }
        },

        assert : function(expression, message)
        {
            if (console && console.assert)
            {
                console.assert(expression, message);

                if (arguments[1])
                {
                    console.log(arguments[1]);
                }
            }
        },

        dir : function(obj)
        {
            if (console && console.dir)
            {
                console.dir(obj);

                if (arguments[1])
                {
                    console.log(arguments[1]);
                }
            }
        },

        error : function(message)
        {
            if (console && console.error)
            {
                console.error(message);

                if (arguments[1])
                {
                    console.log(arguments[1]);
                }
            }
        },

        warning : function(message)
        {
            if (console && console.warning)
            {
                console.warning(message);
            }
            else if (console && console.log)
            {
                console.log(message);
            }

            if (arguments[1] && console && console.log)
            {
                console.log(arguments[1]);
            }
        }
    },

    /**
    * Apps that have animation should implement a check of this property, which makes it
    * possible to disable animation globally.
    */
    animationEnabled : true,

    IE : (hot.userAgent == 'ie'),

    IE6 : (hot.userAgent == 'ie' && hot.userAgentVersion < 7),

    media : [],

    inputFeatureSupported : function(feature)
    {
        var input = document.createElement('input');

        return typeof input[feature] !== 'undefined';
    },

    setCookie : function(name, value, seconds)
    {
        var time = '';

        if (seconds)
        {
            var date = new Date();

            date.setTime(date.getTime() + (seconds * 1000));

            var time = !seconds? '' : '; expires=' + date.toUTCString();
        }

        document.cookie = name + '=' + escape(value) + time;
    },

    getCookie : function(cookieName)
    {
        var cookies = document.cookie.split(';');

        for (var i = 0;i < cookies.length; i++)
        {
            var currentCookieName = cookies[i].substr(0, cookies[i].indexOf('='));
            var currentCookieValue = unescape(cookies[i].substr(cookies[i].indexOf('=') + 1));

            if ($.trim(cookieName.toLowerCase()) == $.trim(currentCookieName.toLowerCase()))
            {
                return currentCookieValue;
            }
        }

        return '';
    }
});

$(document).ready(
    function()
    {
        if (hot.fileActivityId)
        {
            var date = http.get({
                url : '/time',
                synchronous : true
            });

            hot.networkBenchmark = date - hot.fileAccessedGMT;
        }

        // Empty anchors shouldn't do anything.
        $(document).on(
            'click',
            'a[href="#"]',
            function(event)
            {
                event.preventDefault();
            }
        );

        if ($('video, audio').length && hot.userInterfaceIdiom == 'Desktop')
        {
            var i = 0;

            $('video, audio').each(
                function()
                {
                    var options = {
                        defaultVideoWidth : 480,
                        defaultVideoHeight : 270,
                        loop : false,
                        enableAutosize : true,
                        plugins : [
                            'flash',
                            'silverlight'
                        ],
                        pluginPath : '/Library/MediaElement/build/',
                        flashName : 'flashmediaelement.swf',
                        silverlightName : 'silverlightmediaelement.xap',
                        features : [
                            'playpause',
                            'progress',
                            'current',
                            'duration',
                            'tracks',
                            'volume',
                            'fullscreen'
                        ],
                        iPadUseNativeControls : true,
                        iPhoneUseNativeControls : true,
                        AndroidUseNativeControls : true,
                        pauseOtherPlayers : true
                    };

                    if ($(this).attr('data-force-flash') == 'true')
                    {
                        options.mode = 'shim';
                    }

                    if ($(this).attr('data-force-flash') == 'selectively')
                    {
                        if (hot.userAgent == 'ie' && hot.userAgentVersion < 9 || window.chrome)
                        {
                            options.mode = 'shim';
                        }
                    }

                    var player = new MediaElement(this, options);

                    if (!this.id)
                    {
                         this.id = 'hFrameworkVideoPlayer-' + i;
                    }

                    if (this.id)
                    {
                        hot.media[this.id] = player;
                    }

                    $(this).click(
                        function()
                        {
                            if (hot.media[this.id] && hot.media[this.id].play)
                            {
                                hot.media[this.id].play();
                            }
                        }
                    );

                    i++;
                }
            );
        }

        var input = $('input[src*="/images/themes/aqua/buttons/"]');

        if (input.length)
        {
            input.buttons();
        }

        keyboard
            // Provides a shortcut for launching the Hot Toddy Contacts App in a new window
            .shortcut(
                {
                    openContactApplication : 'Option + Shift + A'
                },
                function(event, configuration)
                {
                    // If you press option + shift + a, you'll open the 'Contacts' application in a new window.
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Contacts', 'hContacts');
                }
            )
            // Provides a shortcut for launching the Hot Toddy Calendar App in a new window
            .shortcut(
                {
                    openContactApplication : 'Option + Shift + R'
                },
                function(event, configuration)
                {
                    // If you press option + shift + a, you'll open the 'Calendar' application in a new window.
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Calendar', 'hCalendar');
                }
            )
            // Provides a shortcut for launching the Hot Toddy Console App in a new window
            .shortcut(
                {
                    openConsoleApplication : 'Option + Shift + C'
                },
                function(event, configuration)
                {
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Console', 'hConsole');
                }
            )
            // Provides a shortcut for launching the Hot Toddy Editor App in a new window
            .shortcut(
                {
                    openEditorApplication : 'Option + Shift + E'
                },
                function(event, configuration)
                {
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Editor', 'hEditor');
                }
            )
            .shortcut(
                {
                    openDocumentEditor : 'Option + Shift + M'
                },
                function(event, configuration)
                {
                    if (!get.hEditorTemplateEnabled)
                    {
                        !keyboard.globalShortcutsEnabled || hot.openApplicationWindow(location.href + '?hEditorTemplateEnabled=1', 'hEditorTemplate');
                    }
                }
            )
            .shortcut(
                {
                    openDocumentEditor : 'Option + Shift + D'
                },
                function(event, configuration)
                {
                    if (!get.hEditorTemplateEnabled)
                    {
                        !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Hot Toddy/Documentation', 'hDocumentation');
                    }
                }
            )
            // Provides a shortcut for launching the Hot Toddy Finder App in a new window
            .shortcut(
                {
                    openFinderApplication : 'Option + Shift + F'
                },
                function(event, configuration)
                {
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Finder', 'hFinder');
                }
            )
            // Provides a shortcut for launching the Hot Toddy Search App in a new window
            .shortcut(
                {
                    openSearchApplication : 'Option + Shift + S'
                },
                function(event, configuration)
                {
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Search', 'hSearch');
                }
            )
            // Provides a shortcut for launching the Hot Toddy Terminal App in a new window
            .shortcut(
                {
                    openTerminalApplication : 'Option + Shift + T'
                },
                function(event, configuration)
                {
                    !keyboard.globalShortcutsEnabled || hot.openApplicationWindow('/Applications/Terminal', 'hTerminal');
                }
            )
            // Provides a shortcut for opening a Hot Toddy login dialogue, allowing login from
            // anywhere within the framework.
            .shortcut(
                {
                    openLoginDialogue : 'Option + Shift + L'
                },
                function(event, configuration)
                {
                    !keyboard.globalShortcutsEnabled || dialogue.login();
                }
            );
    }
);

hot.placeholderSupported = hot.inputFeatureSupported('placeholder');

hot.sid = hot.getCookie('sid');

if (!hot.sid && hot.userSessionId)
{
    hot.setCookie('sid', hot.userSessionId, 0);
}

$(window).load(
    function()
    {
        if (hot.fileActivityId)
        {
            var date = http.get({
                url : '/time',
                synchronous : true
            });

            http.post(
                '/hFile/activity', {
                    fileActivityId : hot.fileActivityId
                }, {
                    screenResolution : screen.width + 'x' + screen.height,
                    colorDepth : screen.colorDepth? screen.colorDepth : (screen.pixelDepth? screen.pixelDepth : 0),
                    networkBenchmark : hot.networkBenchmark,
                    pageLoadBenchmark : date - hot.fileAccessedGMT
                },
                function()
                {

                }
            );
        }
    }
);