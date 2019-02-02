var http = new function() {

    // private properties
    var responseHandler;
    var context;
    var xmlhttp;
    var requestURL;
    var requestType;
    var requestFn;
    // public properties

    this.responseHasErrors = function(response)
    {
        var action = null;
        
        if (arguments[1])
        {
            action = arguments[1];
        }
        
        var responseCode = parseInt(response);

        if (!isNaN(responseCode))
        {
            var error = '';

            switch (responseCode)
            {
                case -31:
                {
                    error = "<p>There is a permissions problem with the server.</p>" + 
                            "<p>Please notify your system administrator about this problem.</p>";
                    break;
                };
                case -32:
                {
                    // File already exists *anywhere* on the server
                    error = "<p>The file already exists on the server at:</p>" + 
                            "<p><a href='" + finder.upload.duplicatePath + "' target='_blank'>" + finder.upload.duplicatePath + "</a></p>" +
                            "<p>Your preferences currently do not allow duplicate files.</p>";

                    finder.upload.duplicatePath = '';
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
                case -24:
                {
                    error = "<p>The email address you provided is not associated with a valid account.</p>";
                    break;
                };
                case -23:
                {
                    error = "<p>The email address you entered is not valid</p>";
                    break;
                };
                case -13:
                {
                    error = "<p>The username you entered already exists, please select a new one.</p>";
                    break;
                };
                case -14:
                {
                    error = "<p>The email address you entered is already registered, please enter a unique email address</p>";
                    break;
                };
                case -6:
                {
                    dialogue.login();
                    return true;

                    //error = "<p>You are no longer logged into the website, please login and try again.</p>";
                    //break;
                };
                case -5:
                {
                    error = "<p>An internal error occurred; required information was missing from the request.</p>";
                    break;
                };
                case -3:
                {
                    // File already exists, but the replace flag was not set.
                    error = "<p>The file already exists in this folder.</p>";
                    break;
                };
                case -1:
                {
                    error = "<p>You don't have permission to perform this action.</p>";
                    break;
                };
                case 0:
                {
                    error = "<p>An undefined error has occurred.</p>";
                    break;
                };
            }

            if (error)
            {
                dialogue.alert({
                    title : 'Error',
                    label : "<p><b>" + action+ " Failed!</b></p>" + error
                });

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
    //         context is defined, it must appear immediately after the callback function.
    this.post = function()
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

        var getArguments, postArguments;

        var obj = getCallbackFunction(arguments);

        var numberOfObjects = hot.countArgumentsOfType(obj.args, 'object');
        var numberOfStrings = hot.countArgumentsOfType(obj.args, 'string');
        
        var items = '';
        
        for (var i = 0; i < obj.args.length; i++)
        {
            items += typeof(obj.args[i]) + "\n";
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

        return request.call(this, 'POST', hot.path(url, getArguments), postArguments, obj.fn, obj.context, options.synchronous);
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
    //         context is defined, it must appear immediately after the callback function.

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

        return request.call(this, 'GET', hot.path(url, getArguments), '', obj.fn, obj.context, options.synchronous);
    };

    // private methods
    var getCallbackFunction = function(args)
    {
        var fnContext = null;
        var fn = hot.getArgumentByType(args, 'function', function() {});
        var functionOffset = hot.getOffsetOfArgumentWithType(args, 'function');

        if (functionOffset && args[functionOffset + 1])
        {
            var fnContext = args[functionOffset + 1];
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
            context : fnContext,
            args : copied
        };
    };

    var request = function(type, url, data, fn, fnContext, synchronous)
    {
        context = fnContext;
        requestURL = url;
        requestType = type;
        responseError = false;
        responseHandler = fn;
        
        data = hot.getQueryString(data);

        // The XMLHttpRequest object must be reset, or Explorer won't work!
        reset();

        if (xmlhttp)
        {
            if (!synchronous)
            {
                xmlhttp.onreadystatechange = function()
                {
                    onStateChange.call(http);
                };
            }

            xmlhttp.open(type, url, synchronous? false : true);
            xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xmlhttp.send(data);
            
            if (synchronous)
            {
                return onStateChange.call(http, true);
            }            
        }

        return null;
    };

    var onStateChange = function()
    {
        var readyState = 4;

        if (!arguments[0])
        {
            readyState = xmlhttp.readyState;
        }

        switch (readyState)
        {
            case 4:
            {
                var mime = xmlhttp.getResponseHeader('Content-Type').toLowerCase();
                var data = '';

                switch (true)
                {
                    case mime.indexOf('text/plain') != -1:
                    case mime.indexOf('text/html') != -1 :
                    {
                        data = xmlhttp.responseText;
                        break;
                    };
                    case mime.indexOf('text/xml') != -1:
                    case mime.indexOf('application/xml') != -1:
                    {
                        data = $(xmlhttp.responseXML);
                        break;
                    };
                    case mime.indexOf('application/javascript') != -1:
                    case mime.indexOf('application/json') != -1:
                    case mime.indexOf('text/javascript') != -1:
                    case mime.indexOf('text/json') != -1:
                    {
                        data = $.parseJSON(xmlhttp.responseText);
                        break;
                    };
                }

                var obj = {
                    url : requestURL,
                    mime : mime,
                    data : data,
                    type : requestType,
                    fn : responseHandler,
                    text : xmlhttp.responseText,
                    context : context 
                };

                hot.fire('serverResponded', obj);

                if (arguments[0])
                {
                    return data;
                }
                else
                {
                    responseHandler.call(context? context : data, data, obj);
                }

                break;
            };
        };
    };

    var reset = function()
    {
        if (window.XMLHttpRequest)
        {
            xmlhttp = new XMLHttpRequest();
        }
        else if (window.ActiveXObject)
        {
            xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
        }
    };
};
