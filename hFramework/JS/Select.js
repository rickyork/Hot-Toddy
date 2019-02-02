$.fn.extend({

    select : function()
    {
        if (!arguments[0])
        {
            return this.trigger('select');
        }
        else if (arguments[0])
        {
            switch (typeof arguments[0])
            {
                case 'function':
                {
                    return this.on('select', arguments[0]);
                };
                case 'string':
                {
                    // A callback function can be provided, which is called when the
                    // the item is selected.  Optionally, a context can be provided
                    // for the callback function to define what the 'this' keyword
                    // refers to within the callback function.  If no context is provided,
                    // 'this' is set to the node being selected.
                    var result = null;

                    // Allow a callback function
                    if (arguments[1])
                    {
                        if (arguments[1] && typeof arguments[1] === 'function')
                        {
                            result = arguments[1].call(arguments[2] ? arguments[2] : this);
                        }
                        else
                        {
                            hot.console.error(
                                "The callback function provided for the selection set '" + set + "' " +
                                "cannot be called because it is not a function"
                            );
                        }
                    }

                    // The callback function can return false to cancel selection.
                    if (result !== false)
                    {
                        // If the first argument is a string, implement the framework's
                        // custom functionality for keeping track of which element is
                        // selected.  Only one item per set can be selected at a time.

                        // The set is a unique name for a collection of items that can
                        // be selected.
                        var set = arguments[0];

                        var selected = select.list[set];

                        if (selected && selected.length)
                        {
                            selected.unselect(set);
                        }

                        // Assign the new item to the selection set.
                        select.list[set] = this;

                        // Add a class name to the selected item.
                        this.addClass(set + 'Selected');

                        // Fire a Hot Toddy Event upon compeletion of the selection.
                        hot.fire.call(this, set + 'Selected');
                    }

                    return this;
                };
            }
        }
    },

    unselect : function(set)
    {
        // Make sure the item has a 'Selected' class name
        if (this.hasClass(set + 'Selected'))
        {
            // Remove the selected class name.
            this.removeClass(set + 'Selected');

            // Delete the item from the selection tracking array.
            delete select.list[set];

            // Fire a Hot Toddy callback event upon unselection.
            hot.fire.call(this, set + 'Unselected');
        }

        return this;
    }
});

var select = {

    list : [],

    un : function(set)
    {
        if (this.list && this.list[set])
        {
            var node = this.list[set];

            if (node && node.length)
            {
                // Remove the 'Selected' class name from the
                // selected item.
                node.removeClass(set + 'Selected');

                // Delete the selected item.
                delete this.list[set];

                // Fire a Hot Toddy callback function upon unselection.
                hot.fire.call(node, set + 'Unselected');
            }
            else
            {
                hot.console.warning(
                    "Unable to unselect list item in set '" + set + "' " +
                    "because the set does not exist."
                );
            }

            return node;
        }

        return {};
    },

    ed : function(set)
    {
        if (this.list && this.list[set] && this.list[set].length)
        {
            return this.list[set];
        }

        return [];
    }
};

var unselect = select.un;

if (typeof hot === 'undefined')
{
    var hot = {};
}

$.extend(
    hot, {

        unselect : function(set)
        {
            return select.un(set);
        },

        selected : function(set)
        {
            return select.ed(set);
        }
    }
);