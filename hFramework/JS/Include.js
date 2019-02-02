var include = {

    cssCallback : [],
    cssCallbackContext : [],
    interval : [],
    timeout : [],

    // Argument 0: Path to CSS file
    // Argument 1: (Optional) Callback function called when stylesheet has successfully loaded
    // Argument 2: (Optional) The context to execute the callback function as (e.g., what "this" will refer to)
    getLinkByPath : function(path)
    {
        return $('head link[href*="' + path + '"]');
    },

    getStyleSheetRules : function(link)
    {
        if (link && link.get)
        {
            // If this is a jquery object, get the element itself
            link = link.get(0);
        }

        try
        {
            // This is done slightly differently in IE, so some object detection sorts it out.
            if (link.sheet && link.sheet.cssRules)
            {
                return link.sheet.cssRules;
            }
            else if (link.styleSheet && link.styleSheet.rules)
            {
                return link.styleSheet.rules;
            }
        }
        catch (error)
        {

        }

        return [];
    },

    clearTimers : function(path)
    {
        if (this.interval[path])
        {
            clearInterval(this.interval[path]);
            delete this.interval[path];
        }

        if (this.timeout[path])
        {
            clearTimeout(this.timeout[path]);
            delete this.timeout[path];
        }
    },

    executeStyleSheetLoadedCallback : function(path, styleSheetLoaded)
    {
        if (this.cssCallback[path] instanceof Array)
        {
            for (var callbackCounter = 0; callbackCounter < this.cssCallback[path].length; callbackCounter++)
            {
                if (typeof this.cssCallback[path][callbackCounter] == 'function')
                {
                    var context = null;

                    if (this.cssCallbackContext[path] instanceof Array && typeof this.cssCallbackContext[path][callbackCounter] == 'object')
                    {
                        context = this.cssCallbackContext[path][callbackCounter];
                        delete this.cssCallbackContext[path][callbackCounter];
                    }
                    else if (typeof this.cssCallbackContext[path] == 'object')
                    {
                        context = this.cssCallbackContext[path];
                        delete this.cssCallbackContext[path];
                    }

                    if (context)
                    {
                        this.cssCallback[path][callbackCounter].call(context, styleSheetLoaded);
                    }
                    else
                    {
                        this.cssCallback[path][callbackCounter](styleSheetLoaded);
                    }

                    delete this.cssCallback[path][callbackCounter];
                }
                else if (console && console.warning)
                {
                    console.warning('Unable to execute the stylesheet callback function because it is not a function.');
                }
            }
        }
        else if (typeof this.cssCallback[path] == 'function')
        {
            var callback = this.cssCallback[path];

            if (this.cssCallbackContext[path])
            {
                callback.call(this.cssCallbackContext[path], styleSheetLoaded);
            }
            else
            {
                callback(styleSheetLoaded);
            }
        }
        else if (console && console.warning)
        {
            console.warning('Unable to execute the stylesheet callback function because it is not a function.');
        }

        delete this.cssCallback[path];
        delete this.cssCallbackContext[path];
    },

    // How to include a stylesheet.
    //  Include the script:
    //  var include = require('content/common/include');
    //
    //  Make a call to include a stylesheet by path:
    //  include.css(String path[, Array|Function callback(s)][, Array|Object context(s)][, Object attributes]);
    //
    //  Argument list:
    //    path        The path of the stylesheet to load.
    //
    //    callback    Optional. Can be a function or an array of one or more callback functions to
    //                be executed once the stylesheet has loaded. If an array is provided
    //                each callback is executed successively from first to last.
    //    context     Optional. Can be one or more objects that are used as the context for the
    //                callback function. i.e., what the 'this' object will refer to.  If only
    //                one context is provided, the same context applies to all callback functions.
    //                If no context is provided, no context is set, and whatever context is
    //                default in the callback function will remain.
    //    attributes  Optional. An object of one or more attributes to be applied to the <link>
    //                element.  The attributes set in this object will override any defaults.
    //
    //  This method is designed to be able to accomodate multiple inclusion calls
    //  without destroying inclusions already in progress.
    //
    //  If a stylesheet loads before the timeout, the callback(s) will be called and the
    //  first argument will be true.
    //
    //  A request for a stylesheet will automatically timeout after 15 seconds, if the
    //  stylesheet still has not loaded after 15 seconds, the callback(s) will be called and the
    //  first argument to it will be false.

    css : function(path)
    {
        if (path.indexOf('.css') == -1)
        {
            path += '.css';
        }

        // Stored in a hash so that multiple requests can be occuring
        this.cssCallback[path] = function()
        {

        };

        if (arguments[1] !== undefined && typeof arguments[1] == 'function')
        {
            this.cssCallback[path] = arguments[1];
        }

        this.cssCallbackContext[path] = null;

        if (arguments[2] !== undefined)
        {
            this.cssCallbackContext[path] = arguments[2];
        }

        // Is this stylesheet already loaded?
        if (!this.getLinkByPath(path).length)
        {
            var attributes = {};

            if (arguments[3] !== undefined && typeof arguments[3] == 'object')
            {
                attributes = arguments[3];
            }

            if (document.createStyleSheet)
            {
                // Legacy Internet Explorer
                document.createStyleSheet(path);
            }
            else
            {
                // Create a new <link> element
                var link = document.createElement('link');

                $('head link:last').after(
                    $(link).attr(
                        $.extend({
                                type : 'text/css',
                                rel : 'stylesheet',
                                charset : 'utf-8',
                                media : 'all',
                                href : path
                            },
                            attributes
                        )
                    )
                );
            }

            // If after 15 seconds the style sheet still hasn't loaded, cancel, remove the stylesheet,
            // Notify the callback function that the stylesheet failed to load.
            this.timeout[path] = setTimeout(
                function()
                {
                    var link  = include.getLinkByPath(path);
                    var rules = include.getStyleSheetRules(link);

                    if (rules && rules.length)
                    {
                        // Callback should have already been called by the interval method
                        include.clearTimers(path);
                        return;
                    }

                    include.clearTimers(path);

                    link.remove();

                    include.executeStyleSheetLoadedCallback(path, false);
                },
                15000 // Timeout after 15 seconds
            );

            this.interval[path] = setInterval(
                function()
                {
                    // Reincluding these, since the getStyleSheetRules method
                    // can potentially return an empty array, and this would cause
                    // the user to wait a full 15 seconds.
                    var link  = include.getLinkByPath(path);
                    var rules = include.getStyleSheetRules(link);

                    if (rules && rules.length)
                    {
                        include.clearTimers(path);

                        // Notify the callback function that the style sheet loaded successfully
                        include.executeStyleSheetLoadedCallback(path, true);
                    }
                },
                10 // Check to see if the stylesheet loaded in 10 millisecond intervals
            );
        }
        else
        {
            this.executeStyleSheetLoadedCallback(path, true);
        }

        // For chained calls
        return this;
    },

    // Alias for css()
    styleSheet : function(path)
    {
        return this.css(
            path,
            arguments[1]? arguments[1] : function() {},
            arguments[2]? arguments[2] : this,
            arguments[3]? arguments[3] : {}
        );
    },

    scriptPaths : {},

    // include.js(path[, options])
    js : function(path)
    {
        if (path.indexOf('.js') == -1)
        {
            path += '.js';
        }

        if (this.scriptPaths[path] !== undefined)
        {
            return;
        }

        this.scriptPaths[path] = true;

        var options = {};

        if (arguments[1] !== undefined && typeof arguments[1] == 'object')
        {
            options = arguments[1];
        }

        // Similar to what jQuery.getScript() does, except, this one
        // forces inclusion of the script to occur synchronously instead
        // of asynchronously.  This makes sure the JS fully loads before
        // execution continues.
        $.ajax(
            $.extend(
                {
                    url : path,
                    dataType : 'script',
                    async : false
                },
                options
            )
        );

        return this;
    }
};
