var http = new function() {

    this._debug = {};
    this.debuggerEnabled = false;
    this.debugTemplate = '';
    this.debuggerPopupCount = 0;

    this.debug = function(options)
    {
        hot.console.log(options);

        if (typeof(options) == 'object')
        {
            this._debug = {};
            this.debuggerEnabled = false;

            for (var option in options)
            {
                switch (option)
                {
                    case 'log':
                    {
                        this._debug.log = options[i]? true : false;
                        this.debuggerEnabled = true;
                        break
                    }
                    case 'alert':
                    {
                        this._debug.alert = options[i]? true : false;
                        this.debuggerEnabled = true;
                        break
                    }
                    case 'popup':
                    {
                        this._debug.popup = options[i]? true : false;
                        this.debuggerEnabled = true;
                        break;
                    }
                    default:
                    {
                        hot.console.error("Error: invalid HTTP debug option '" + option + "' provided to http.setDebug()");
                    }
                }
            }
        }
        else if (typeof(options) == 'string')
        {
            switch (options)
            {
                case 'enable':
                {
                    this.debuggerEnabled = true;
                    break;
                }
                case 'disable':
                {
                    this.debuggerEnabled = false;
                    break;
                }
            }
        }

        if (!this.debugTemplate)
        {
            include.css('/hHTTP/hHTTP.css');

            this.debugTemplate = http.get({
                url : '/hHTTP/getDebugTemplate',
                synchronous : true
            });
        }
    };

    this.getDebugTemplate = function(request)
    {
        var html = $(this.debugTemplate);

        request = this.makeRequestObjectReadable(request);

        var arguments = request.post.split('&');
        var post = '';

        for (var i in arguments)
        {
            post += "    <li>" + arguments[i] + "</li>\n";
        }

        html.find('td#httpRequestURL')
            .text(request.url);

        html.find('td#httpRequestPost')
            .html("<ul>\n" + post + "</ul>\n");

        html.find('td#httpMime')
            .text(request.mime);

        html.find('td#httpMethod')
            .text(request.method);

        html.find('td#httpCallbackFunction')
            .text(request.fn);

        html.find('td#httpResponse')
            .text(request.response);

        html.find('td#httpOptions')
            .text(request.options);

        return html;
    };

    this.makeRequestObjectReadable = function(request)
    {
        request.response = request.text;

        if (request.json)
        {
            request.response = this.stringify(request.json, request.text);
        }

        request.options = this.stringify(request.options);

        if (typeof(request.json) != 'undefined')
        {
            delete request.json;
        }

        if (typeof(request.text) != 'undefined')
        {
            delete request.text;
        }

        return request;
    };

    this.stringify = function(json)
    {
        if (JSON && JSON.stringify)
        {
            return JSON.stringify(json, null, '    ');
        }

        return arguments[1]? arguments[1] : null;
    };

    this.tempData = [];

    this.executeDebugger = function(request)
    {
        for (var option in this._debug)
        {
            switch (option)
            {
                case 'alert':
                {
                    dialogue.alert({
                        title : 'HTTP Request',
                        label : this.getDebugTemplate(request)
                    });

                    break;
                }
                case 'popup':
                {
                    var popup = hot.window(
                        '/hHTTP/getPopupDebugTemplate?debuggerPopupCount=' + this.debuggerPopupCount, {

                        },
                        900,
                        600,
                        'httpDebugger' + this.debuggerPopupCount, {
                            resizable : true,
                            scrollable : true
                        }
                    );

                    this.tempData[this.debuggerPopupCount] = request;

                    var debugData = this.getDebugTemplate(request);
                    this.debuggerPopupCount++;
                    break;
                }
                case 'log':
                {
                    hot.console.log(this.makeRequestObjectReadable(request));
                    break;
                }
            }
        }
    };

    this.debuggerPopupCallback = function(debuggerPopupCount)
    {
        var request = this.tempData[debuggerPopupCount];
        delete this.tempData[debuggerPopupCount];
        return this.getDebugTemplate(request);
    };

    // public properties
    this.responseHasErrors = function(response)
    {
        var action = null;

        if (arguments[1])
        {
            action = arguments[1];
        }
        else
        {
            return false;
        }

        var loginRequest = {};

        if (arguments[2])
        {
            loginRequest = arguments[2];
        }

        var responseCode = parseInt(response);

        if (!isNaN(responseCode))
        {
            var error = false;
            var post = {};

            switch (responseCode)
            {
                case -32:
                {
                    post.duplicatePath = finder.upload.duplicatePath;
                    finder.upload.duplicatePath = '';

                    error = true;
                    break;
                };
                case -25:
                {
                    // No security question is defined...
                    if ($('input#hUserFormSubmit').length)
                    {
                        $('input#hUserFormSubmit').parents('form').submit();
                    }

                    break;
                };
                case -6:
                {
                    dialogue.login(loginRequest);
                    return true;
                };
                case -34:
                case -33:
                case -31:
                case -24:
                case -23:
                case -14:
                case -13:
                case -5:
                case -3:
                case -1:
                case 0:
                {
                    error = true;
                    break;
                };
                default:
                {
                    if (responseCode <= 0)
                    {
                        error = true;
                    }
                };
            }

            if (error)
            {
                this.post(
                    '/hHTTP/getErrorMessage', {
                        errorCode : responseCode,
                        action : action
                    },
                    post,
                    function(json)
                    {
                        if (json.error)
                        {
                            dialogue.alert({
                                title : 'Error',
                                label : "<p><b>" + json.action + " Failed!</b></p>" + json.error +
                                        "<p><i>" + responseCode + "</i></p>"
                            });
                        }
                    }
                );

                return true;
            }
        }

        return false;
    };

    // Method signature:
    //
    // url[, getArguments][, postArguments][, callbackFn][, callbackContext]
    //
    // All parameters except url are optional.
    //
    //     * url is required.
    //
    //     * Supplying GET arguments is optional, if GET arguments are supplied, they can
    //         be provided either as JSON or a string and must appear before POST arguments.  If
    //         supplied as JSON each GET argument is automatically encoded with encodeURIComponent.
    //
    //         If you are supplying GET arguments, POST arguments are required.
    //
    //     * POST arguments are optional, unless you specify GET arguments.  If POST arguments
    //         are supplied, they can be supplied as either JSON or a string.  If they are supplied
    //         as JSON each POST argument is automatically encoded with encodeURIComponent.
    //
    //     * Callback function is optional, unless you define context.
    //
    //     * Context (what "this" will refer to in your callback function) is optional.  If
    //         context is defined, it must appear immediately after the callback function,
    //         if context is not supplied, "this" will refer to the response data.
    //
    this.post = function()
    {
        var options = {};

        if (typeof arguments[0] == 'object')
        {
            var options = arguments[0];

            if (options.url)
            {
                var url = options.url;
            }
            else
            {
                dialogue.alert({
                    title : 'Error',
                    label : 'HTTP Request failed because no URL was provided.'
                });
                return;
            }
        }
        else
        {
            var url = arguments[0];
        }

        arguments[0] = null;

        var getArguments, postArguments;

        var obj = getCallbackFunction(arguments);

        var numberOfObjects = hot.countArgumentsOfType(obj.args, 'object');
        var numberOfStrings = hot.countArgumentsOfType(obj.args, 'string');

        var items = '';

        for (var i = 0; i < obj.args.length; i++)
        {
            items += (typeof obj.args[i]) + "\n";
        }

        switch (true)
        {
            case !numberOfObjects && numberOfStrings == 2:
            {
                getArguments = hot.getArgumentByType(obj.args, 'string', null);
                postArguments = hot.getArgumentByType(obj.args, 'string', null, 2);
                break;
            };
            case !numberOfStrings && numberOfObjects == 2:
            {
                getArguments = hot.getArgumentByType(obj.args, 'object', {});
                postArguments = hot.getArgumentByType(obj.args, 'object', {}, 2);
                break;
            };
            case numberOfStrings > 0 && !numberOfObjects:
            {
                getArguments = {};
                postArguments = hot.getArgumentByType(obj.args, 'string', null);
                break;
            };
            case numberOfObjects > 0 && !numberOfStrings:
            {
                getArguments = {};
                postArguments = hot.getArgumentByType(obj.args, 'object', {});
                break;
            };
            case numberOfObjects > 0 && numberOfStrings > 0:
            {
                getArguments = hot.getArgumentByType(obj.args, 'object', {});
                postArguments = hot.getArgumentByType(obj.args, 'string', null);
                break;
            };
            case !numberOfObjects && !numberOfStrings:
            {
                getArguments = {};
                postArguments = {};
                break;
            };
            default:
            {
                dialogue.alert({
                    title : 'Error',
                    label : 'HTTP POST request failed.'
                });

                return;
            };
        }

        if (!options.operation && getArguments.operation)
        {
            options.operation = getArguments.operation;
        }

        if (!options.operation && postArguments.operation)
        {
            options.operation = postArguments.operation;
        }

        var path = hot.path(url, getArguments);

        return this.request(
            'POST',
            path,
            postArguments,
            obj.fn,
            obj.context,
            options.synchronous,
            options
        );
    };

    // Method signature:
    //
    // url[, getArguments][, callbackFn][, callbackContext]
    //
    // All parameters except url are optional.
    //
    //     * url is required.
    //
    //     * Supplying GET arguments is optional, if GET arguments are supplied, they can
    //         be provided either as JSON or a string.    If    supplied as JSON each GET argument is
    //         automatically encoded with encodeURIComponent.
    //
    //     * Callback function is optional, unless you define context.
    //
    //     * Context (what "this" will refer to in your callback function) is optional.    If
    //         context is defined, it must appear immediately after the callback function,
    //         if context is not supplied, "this" will refer to the response data.

    this.get = function()
    {
        var options = {};

        if (typeof(arguments[0]) == 'object')
        {
            var options = arguments[0];

            if (options.url)
            {
                var url = options.url;
            }
            else
            {
                dialogue.alert({
                    title : 'Error',
                    label : 'HTTP Request failed because no URL was provided.'
                });
                return;
            }
        }
        else
        {
            var url = arguments[0];
        }

        arguments[0] = null;

        var obj = getCallbackFunction(arguments);
        var numberOfObjects = hot.countArgumentsOfType(obj.args, 'object');
        var numberOfStrings = hot.countArgumentsOfType(obj.args, 'string');
        var getArguments = {};

        if (numberOfObjects)
        {
            getArguments = hot.getArgumentByType(obj.args, 'object', {});
        }
        else if (numberOfStrings)
        {
            getArguments = hot.getArgumentByType(obj.args, 'string', '');
        }

        if (!options.operation && getArguments.operation)
        {
            options.operation = getArguments.operation;
        }

        var path = hot.path(url, getArguments);

        return this.request(
            'GET',
            path,
            '',
            obj.fn,
            obj.context,
            options.synchronous,
            options
        );
    };

    // See /hFramework/JS/Hot.js
    this.sendRequest = hot.factory();

    // Setting up XMLHTTP requests in this way solves the problem of being able
    // to do multiple XMLHTTP requests at the same time.  Since this object is
    // created freshly for each request, there's never a conflict with request
    // data getting overwritten.
    this.sendRequest.prototype.init = function(method, url, post, fn, context, synchronous, options)
    {
        var xhttp = http.xmlhttp();

        var obj = {
            method : method,
            url : url,
            post : post,
            fn : fn,
            context : context,
            xhttp : xhttp,
            options : options
        };

        if (xhttp)
        {
            xhttp.onreadystatechange = function()
            {
                http.onStateChange.call(obj);
            };

            xhttp.open(method, url, true);

            xhttp.setRequestHeader(
                'Content-Type',
                'application/x-www-form-urlencoded'
            );

            xhttp.send(hot.getQueryString(post));
        }
    };

    // private methods
    var getCallbackFunction = function(args)
    {
        var context = null;

        var fn = hot.getArgumentByType(
            args,
            'function',
            function()
            {

            }
        );

        var functionOffset = hot.getOffsetOfArgumentWithType(
            args,
            'function'
        );

        if (functionOffset && args[functionOffset + 1])
        {
            context = args[functionOffset + 1];
            args[functionOffset + 1] = null;
        }

        if (functionOffset)
        {
            args[functionOffset] = null;
        }

        var copied = [];

        for (var i = 0; i < args.length; i++)
        {
            if (args[i] !== null)
            {
                copied.push(args[i]);
            }
        }

        return {
            fn : fn,
            context : context,
            args : copied
        };
    };

    this.request = function(method, url, post, fn, context, synchronous, options)
    {
        if (!synchronous)
        {
            // Asynchronous requests
            //
            // A new sendRequest object is spawned for each request, which
            // allows many concurrent requests to take place without
            // them stepping on eachother's toes.
            //
            // If you happen to want the object, it's returned.
            // I do wonder whether this will cause memory to leak or
            // if this will be handled automatically via garbage collection.
            return new http.sendRequest(
                method,
                url,
                post,
                fn,
                context,
                synchronous,
                options
            );
        }
        else
        {
            // Synchronous requests, the request is made and execution stops
            // until the response has been received, and upon receiving the
            // request it is returned to the caller, instead of calling a
            // callback function.
            var xhttp = http.xmlhttp();

            xhttp.open(method, url, false);

            xhttp.setRequestHeader(
                'Content-Type',
                'application/x-www-form-urlencoded'
            );

            xhttp.send(hot.getQueryString(post));

            return http.onStateChange.call({
                    xhttp : xhttp,
                    method : method,
                    url : url,
                    post : post,
                    fn : fn,
                    context : context,
                    synchronous : synchronous,
                    options : options
                },
                true
            );
        }

        return null;
    };

    this.xmlhttp = function()
    {
        if (window.XMLHttpRequest)
        {
            return new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            return new ActiveXObject('Microsoft.XMLHTTP');
        }
    };

    this.onStateChange = function()
    {
        var readyState = 4;

        if (!arguments[0])
        {
            readyState = this.xhttp.readyState;
        }

        switch (readyState)
        {
            case 4:
            {
                var mime = '';

                if (this.xhttp && typeof this.xhttp.getResponseHeader === 'function')
                {
                    var mime = this.xhttp.getResponseHeader('Content-Type');

                    if (mime && mime.length)
                    {
                        mime = mime.toLowerCase();
                    }
                }

                if (!mime)
                {
                    mime = 'application/json';
                }

                var response = '';
                var json = '';

                switch (true)
                {
                    case mime.indexOf('text/plain') != -1:
                    case mime.indexOf('text/html') != -1 :
                    {
                        response = this.xhttp.responseText;
                        break;
                    };
                    case mime.indexOf('text/xml') != -1:
                    case mime.indexOf('application/xml') != -1:
                    {
                        response = $(this.xhttp.responseXML);
                        break;
                    };
                    case mime.indexOf('application/javascript') != -1:
                    case mime.indexOf('application/json') != -1:
                    case mime.indexOf('text/javascript') != -1:
                    case mime.indexOf('text/json') != -1:
                    default:
                    {
                        response = $.parseJSON(this.xhttp.responseText);
                        json = response;
                        break;
                    };
                }

                var obj = {
                    url : this.url,
                    mime : mime,
                    post : this.post,
                    method : this.method,
                    fn : this.fn,
                    text : this.xhttp.responseText,
                    context : this.context,
                    options : this.options,
                    json : json
                };

                if (http.debuggerEnabled)
                {
                    http.executeDebugger(obj);
                }

                hot.fire('serverResponded', obj);

                if (arguments[0])
                {
                    return response;
                }
                else if (!http.responseHasErrors(response, this.options && this.options.operation? this.options.operation : '', obj))
                {
                    this.fn.call(
                        this.context? this.context : response,
                        response,
                        obj
                    );
                }

                break;
            };
        };
    };
};
