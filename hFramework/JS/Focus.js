$.focus = function()
{
    // Provide the default jQuery functionality when .focus() is called, 
    // trigger the focus event.
    if (!arguments[0])
    {
        return this.trigger('focus');
    }
    else if (arguments[0])
    {
        switch (typeof(arguments[0]))
        {
            case 'function':
            {
                // If the first argument is a function, find the function
                // to the focus event.
                return this.bind('focus', arguments[0]);
            }
            case 'string':
            {
                // A callback function can be provided, which is called when the 
                // the item receives focus.  Optionally, a context can be provided 
                // for the callback function to define what the 'this' keyword 
                // refers to within the callback function.  If no context is provided,
                // 'this' is set to the node receiving focus.
                var result = null;
                
                if (arguments[1])
                {
                    if (typeof(arguments[1]) == 'function')
                    {
                        result = arguments[1].call(arguments[2]? arguments[2] : this);
                    }
                    else
                    {
                        hot.console.error("The callback function provided for the focus set '" + set + "' cannot be called because it is not a function");
                    }
                }
                
                // The callback function can return false to cancel focus.
                if (result !== false)
                {
                    // If the first argument is a string, implement the framework's 
                    // custom functionality for keeping track of which element is in 
                    // focus.  Only one item per set can be focused at a time.
                    
                    // The set is a unique name for the item being focused.
                    var set = arguments[0];
    
                    // There can be a collection of items that 'focus' can be tracked on, 
                    // the 'set' determines which item we're working with.
                    var focused = focus.list[set];
                    
                    if (focused && focused.length)
                    {
                        // If an item is presently in focus, remove it from focus
                        // and delete it from the collection.
                        focused.blur(set);
                    }

                    // Add this item to the set.
                    focus.list[set] = this;
    
                    // Add a class name to the focused item.
                    this.addClass(set + 'Focused');
                
                    // A Hot Toddy event is automatically fired for the set upon successfully focusing the new element.
                    hot.fire.call(this, set + 'Focused');
                }
                
                return this;
            }
        }
    }
};

$.blur = function()
{
    if (!arguments[0])
    {
        return this.trigger('blur');
    }
    else if (arguments[0])
    {
        switch (typeof(arguments[0]))
        {
            case 'function':
            {
                return this.bind('blur', arguments[0]);
            };
            case 'string':
            {
                var set = arguments[0];

                if (this.hasClass(set + 'Focused'))
                {
                    this.removeClass(set + 'Focused');
                    delete focus.list[set];

                    hot.fire.call(this, set + 'Blurred');
                }

                return this;
            };
        }
    }
};

var focus = {
    /**
    * An array used to keep track of which items are focused.
    */
    list : [],

    /**
    * Another way to unfocus items, using this method, the 
    * specific node does not need to be known, you only 
    * need to pass the name of the set, and whatever item is 
    * presently focused in the set will lose 
    */
    un : function(set)
    {
        var node = focus.list[set];

        if (node && node.length)
        {
            // Remove the 'Focused' class name.
            node.removeClass(set + 'Focused');
            
            // Delete the item from the tracking array.
            delete focus.list[set];

            // Fire a Hot Toddy callback function upon successful blur.
            hot.fire.call(node, set + 'Blurred');
        }

        return node;
    },

    /**
    * Return the currently focused item for the given set.
    */
    ed : function(set)
    {
        return this.list[set] && this.list[set].length? this.list[set] : [];
    }
};