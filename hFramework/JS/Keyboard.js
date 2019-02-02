
var keyboard = {

    /**
    * Take a string such as "one, two, three" and split that
    * string on a separator.  For example ',', then return an array
    * with the excess space removed from each item in the array.
    * So tho example becomes ['one', 'two', 'three'] instead of
    * ['one', ' two', ' three']
    *
    */
    splitAndTrim : function(string, separator)
    {
        if (typeof string === 'string')
        {
            if (string.indexOf(separator) != -1)
            {
                var bits = string.split(separator);

                var trimmed = [];

                for (var bitCounter = 0; bitCounter < bits.length; bitCounter++)
                {
                    trimmed[bitCounter] = $.trim(bits[bitCounter]);
                }

                return trimmed;
            }

            return [string];
        }
        else
        {
            hot.console.error(
                "splitAndTrim: Failed to split string, argument 'string' is not a string. " +
                "Actual type is '" +  (typeof(string)) + "'"
            );
        }
    },

    isInput : function(event)
    {
        if (event.target)
        {
            var target = $(event.target);
        }
        else
        {
            return false;
        }

        return (
            target.is('input, textarea, select') ||
            target.parents('input, textarea, select').length ||
            target.attr('contenteditable') == 'true' ||
            target.parents('[contenteditable="true"]').length
        );
    },

    defaultEvent : 'keydown',

    setDefaultEvent : function(defaultEvent)
    {
        this.defaultEvent = defaultEvent;
    },

    getDefaultEvent : function()
    {
        return this.defaultEvent;
    },

    /**
    * The following maps each JavaScript 'keyCode' for a given key to
    * a human-readable label, making it possible to define shortcut
    * shortcuts in more human-readable terms.
    *
    * For example, using this key map, we can take 'Command + E' and
    * translate that to 'event.metaKey' for 'Command' and keyCode '69' for
    * 'E'.
    *
    */
    map : {
        8   : 'Backspace',
        9   : 'Tab',
        12  : 'Clear',
        13  : 'Return',
        16  : 'Shift',
        17  : 'Control',
        18  : 'Option',
        19  : 'Break',
        20  : 'Caps Lock',
        27  : 'Escape',
        32  : 'Space',
        33  : 'Page Up',
        34  : 'Page Down',
        35  : 'End',
        36  : 'Home',
        37  : 'Left Arrow',
        38  : 'Up Arrow',
        39  : 'Right Arrow',
        40  : 'Down Arrow',
        44  : 'Print Screen',
        45  : 'Insert',
        46  : 'Delete',
        48  : 0,                // Shift + 0 = )
        49  : 1,                // Shift + 1 = !
        50  : 2,                // Shift + 2 = @
        51  : 3,                // Shift + 3 = #
        52  : 4,                // Shift + 4 = $
        53  : 5,                // Shift + 5 = %
        54  : 6,                // Shift + 6 = ^
        55  : 7,                // Shift + 7 = &
        56  : 8,                // Shift + 8 = *
        57  : 9,                // Shift + 9 = (
        65  : 'A',
        66  : 'B',
        67  : 'C',
        68  : 'D',
        69  : 'E',
        70  : 'F',
        71  : 'G',
        72  : 'H',
        73  : 'I',
        74  : 'J',
        75  : 'K',
        76  : 'L',
        77  : 'M',
        78  : 'N',
        79  : 'O',
        80  : 'P',
        81  : 'Q',
        82  : 'R',
        83  : 'S',
        84  : 'T',
        85  : 'U',
        86  : 'V',
        87  : 'W',
        88  : 'X',
        89  : 'Y',
        90  : 'Z',
        91  : 'Left Command',
        93  : 'Right Command',
        96  : 'Number Pad 0',
        97  : 'Number Pad 1',
        98  : 'Number Pad 2',
        99  : 'Number Pad 3',
        100 : 'Number Pad 4',
        101 : 'Number Pad 5',
        102 : 'Number Pad 6',
        103 : 'Number Pad 7',
        104 : 'Number Pad 8',
        105 : 'Number Pad 9',
        106 : 'Multiply',       // Asterisk
        107 : 'Add',            // Add/Plus.  The '+' symbol conflicts with key shortcut syntax, so it has to be spelled out.
        110 : 'Decimal Point',  // Decimal Point.  The '.' symbol conflicts with period.
        111 : 'Divide',         // Forward Slash.  The '/' symbol conflicts with forward slash
        112 : 'F1',             // Screen Dimmer
        113 : 'F2',             // Screen Brighter
        114 : 'F3',             // Exposé
        115 : 'F4',             // Dashboard
        116 : 'F5',             // Keyboard Dimmer
        117 : 'F6',             // Keyboard Brighter
        118 : 'F7',             // Previous
        119 : 'F8',             // Play/Pause
        120 : 'F9',             // Next
        121 : 'F10',            // Mute
        122 : 'F11',            // Volume Down / Reveal desktop shortcut on Mac
        123 : 'F12',            // Volume Up / Dashboard shortcut on Mac
        124 : 'F13',            // Eject
        125 : 'F14',
        126 : 'F15',
        127 : 'F16',
        128 : 'F17',
        129 : 'F18',
        130 : 'F19',
        144 : 'Number Lock',
        145 : 'Scroll Lock',
        186 : ';',    // Semi-Colon                 Shift + ; = :
        187 : '=',    // Equal Sign                 Shift + = = +
        188 : ',',    // Comma                      Shift + , = <
        189 : '-',    // Hyphen                     Shift + - = _
        190 : '.',    // Period                     Shift + . = <
        191 : '/',    // Forward Slash              Shift + / = ?
        192 : '`',    // Backtick                   Shift + ` = ~
        219 : '[',    // Left Square Bracket        Shift + [ = {
        220 : '\\',   // Backslash                  Shift + \ = |
        221 : ']',    // Right Square Bracket       Shift + ] = }
        222 : "'"     // Apostrophe / Single Quote  Shift + ' = "
    },

    /**
    * The following properties hold configurations for each key shortcut for
    * each specific key event.  The configurations are later called on when
    * key events occur, and if a configuration matches, it's corresponding
    * callback function is called.
    *
    */
    input : [],
    down : [],
    press : [],
    up : [],

    inputShortcut : function(shortcut, fn)
    {
        shortcut = this.analyzeConfiguration(shortcut);
        shortcut.events = ['input'];

        this.shortcut(shortcut, fn);

        return this;
    },

    /**
    * key shortcut API, identical to the keyShortcut method, but allows key shortcut to the keydown event specifically.
    * The arguments are identical to the keyShortcut method, except that the 'events' argument is omitted, since this
    * method specifies that argument for you as 'keydown'.
    *
    */
    downShortcut : function(shortcut, fn)
    {
        shortcut = this.analyzeConfiguration(shortcut);
        shortcut.events = ['keydown'];

        this.shortcut(shortcut, fn);

        return this;
    },

    /**
    * key shortcut API, identical to the keyShortcut method, but allows key shortcut to the keyup event specifically.
    * The arguments are identical to the keyShortcut method, except that the 'events' argument is omitted, since this
    * method specifies that argument for you as 'keyup'.
    *
    */
    upShortcut : function(shortcut, fn)
    {
        shortcut = this.analyzeConfiguration(shortcut);
        shortcut.events = ['keyup'];

        this.shortcut(shortcut, fn);

        return this;
    },

    /**
    * key shortcut API, identical to the keyShortcut method, but allows key shortcut to the keypress event specifically.
    * The arguments are identical to the keyShortcut method, except that the 'events' argument is omitted, since this
    * method specifies that argument for you as 'keypress'.
    *
    */
    pressShortcut : function(shortcut, fn)
    {
        shortcut = this.analyzeConfiguration(shortcut);
        shortcut.events = ['keypress'];

        this.shortcut(shortcut, fn);

        return this;
    },

    /**
    * Simplified keyboard shortcut API
    *
    * Arguments:
    *  1. (string, object) The key combination to bind.
    *
    *   Examples:  "Command + S",  "Command + S, Control + S"
    *
    *       In this context, an action you would like to occur when the user presses
    *       the command and s keys or the control and s keys.
    *
    *       As an object:
    *       {
    *           saveDocument : "Command + S, Control + S"
    *       }
    *
    *       When using an object the key shortcut is named using the property
    *       "saveDocument" which provides flexibility when removing key shortcuts.
    *
    *       Other options can also be passed in the object.
    *       {
    *           saveDocument : "Command + S, Control + S",
    *           events : [
    *               "keydown"
    *           ],
    *           selector : 'input#someInput',
    *           context : obj
    *       }
    *
    *       events: (string||array)  A list of events the key shortcut should be
    *       applied to, if not specified or null, the default event is 'keydown'.
    *
    *           Example: 'input, keydown, keyup, keypress', or 'keydown'.
    *
    *       context: a DOM object that should be used to specify the context of
    *       the 'this' keyword in the callback function.
    *
    *       selector: a string that is used to limit the applicability of the key
    *       shortcut.  For example a selector of 'input#something' will limit the key
    *       shortcut to executing when typing in an <input> element with id
    *       "something".
    *
    *  2. fn: (callback function)  A function to call when the key combination is
    *     pressed.  The function is passed two arguments, the event and the key
    *     shortcut configuration.
    *
    *     Example:
    *     function(event, configuration)
    *     {
    *
    *     }
    *
    */
    shortcut : function(shortcut, fn)
    {
        // If the key is passed in as an object the property is used to name the
        // key combination.  Naming the key combination can be used to later remove
        // key combinations.
        //
        // {openFinderWindow: "Command + F, Control + F"}
        shortcut = this.analyzeConfiguration(shortcut);

        // key will hold a value like 'Command + S' or 'Command + S, Control + S'.
        // In the case of the latter example, this is an indication that the
        // author would like something to occur when either the key combination
        // 'Command + S' or 'Control + S' has occurred.
        //
        // If a comma is present, that indicates that there are multiple key combinations
        // present.  So this call will transform those combinations into an array
        // ['Command + S', 'Control + S']
        var combinations = this.splitAndTrim(shortcut.shortcut, ',');

        // Now to iterate the list of key combinations.
        for (var combinationsCounter = 0; combinationsCounter < combinations.length; combinationsCounter++)
        {
            var combination = combinations[combinationsCounter];

            // Some defauls are defined for each key combination
            var configuration = {

                // The callback function to execute when the keyShorcut is activated
                fn : fn,

                // If the command (windows key on a PC) key modifier should be present
                // in the key combination,
                command : false,

                // If the control key modifier should be present in the key combination.
                control : false,

                // If the shift key modifier should be present in the key combination.
                shift : false,

                // If the option (alt key on a PC) key modifier should be present in the
                // key combination.
                option : false,

                // A human readable representation of the keyCode
                key : null,

                // The numbered JavaScript keyCode for the key.
                keyCode : 0,

                disableShortcutOnInput : shortcut.disableShortcutOnInput,

                // The selector can be used to limit the context in which the keyShortcut
                // can be used.
                selector : shortcut.selector,

                // The context is used to define the 'this' keyword in the callback function.
                context : shortcut.context,

                // One or more key combinations to use for the key shortcut, i.e.
                // 'Command + S, Control + S'.
                shortcut : shortcut.shortcut,

                // The specific key combination for this configuration, i.e. 'Command + S'.
                //
                // A configuration is created for each individual combination.  For example,
                // 'Command + S' and 'Control + S' in a 'Command + S, Control + S' key
                // shortcut results in two configurations, one for each key combination.
                combination : combination,

                // The name of the key shortcut.
                name : shortcut.name,

                // The events that the shortcut applies to.  i.e., one or more of the
                // following:
                // ['keyup', 'keydown', 'keypress']
                events : shortcut.events
            };

            // Now split the keys in each combination.  So something like
            // 'Option + Shift + S' becomes an array ['Option', 'Shift', 'S']

            var keys = this.splitAndTrim(combination, '+');

            // Iterate through the individual keys.
            for (var keyCounter = 0; keyCounter < keys.length; keyCounter++)
            {
                switch (keys[keyCounter].toLowerCase())
                {
                    case 'command':
                    {
                        // A command key modifier is set aside in a configuration so that
                        // when the event occurs, we can see if event.metaKey is present.
                        configuration.command = true;
                        break;
                    }
                    case 'control':
                    {
                        // Same with the control key modifier, this will be used to
                        // check to see if event.ctrlKey is present.
                        configuration.control = true;
                        break;
                    }
                    case 'shift':
                    {
                        // shift is used to check to see if event.shiftKey is present.
                        configuration.shift = true;
                        break;
                    }
                    case 'option':
                    {
                        // option is used to check to see if event.altKey is present.
                        configuration.option = true;
                        break;
                    }
                    default:
                    {
                        // This will be the last key in the sequence.  If the combination
                        // were 'Command + S', the 'S' will be assigned to configuration.key.
                        configuration.key = keys[keyCounter];

                        // Now to iterate through the map to find the keyCode that corresponds to
                        // the key.  If the key were 'S', that would map to keyCode '83'
                        for (var keyCode in this.map)
                        {
                            // Make letter matching case-insensitive so that combinations can be specified
                            // in any case.

                            // Left will be the key specified in the keyMap
                            var left = this.map[keyCode].toLowerCase ? this.map[keyCode].toLowerCase() : this.map[keyCode];

                            // Right will be the key specified in this key combination.
                            var right = keys[keyCounter].toLowerCase ? keys[keyCounter].toLowerCase() : keys[keyCounter];

                            if (left == right)
                            {
                                // We have a match, assign the resulting keyCode to configuration.keyCode
                                configuration.keyCode = keyCode;
                                break;
                            }
                        }
                    }
                }
            }

            // Finally, the list of events is iterated and the configuration for
            // the key combination is added to the appropriate list.
            for (var eventCounter = 0; eventCounter < shortcut.events.length; eventCounter++)
            {
                switch (shortcut.events[eventCounter])
                {
                    case 'input':
                    {
                        this.input.push(configuration);
                        break;
                    }
                    case 'keyup':
                    {
                        this.up.push(configuration);
                        break;
                    }
                    case 'keypress':
                    {
                        this.press.push(configuration);
                        break;
                    }
                    case 'keydown':
                    default:
                    {
                        this.down.push(configuration);
                    }
                }
            }
        }

        return this;
    },

    /**
    * Return the event list, taking a string like "input, keydown, keypress, keyup"
    * as an array ['input', 'keydown', 'keypress', 'keyup']
    *
    */
    getEvents : function(events)
    {
        if (events)
        {
            if (typeof events === 'object')
            {
                return events;
            }
            else if (typeof events === 'string')
            {
                // Events can be specified in comma separated values, i.e., 'keydown, keypress'
                return this.splitAndTrim(events, ',');
            }
        }

        // If no events are specified, the default event is used.
        return [this.defaultEvent];
    },

    /**
    * Look at the "shortcut" argument and return an object,
    *
    * If key is passed as {openFinderWindow: "Command + F, Control + F"}
    *
    * This returns {key: "Command + F, Control + F", name: "openFinderWindow"}
    *
    * If key is passed as "Command + F, Control + F"
    *
    * This returns {key: "Command + F, Control + F', name: null}
    */
    analyzeConfiguration : function(shortcut)
    {
        // If the key is passed in as an object, the property is used to name the
        // key combination. Naming the key combination can be used to remove key
        // combinations.
        //
        // Example:
        // {
        //     openFinderWindow : "Command + F, Control + F"
        // }

        if (typeof shortcut === 'object')
        {
            var revisedShortcutConfiguration = {};

            for (var shortcutName in shortcut)
            {
                switch (shortcutName)
                {
                    case 'disableShortcutOnInput':
                    {
                        // This option will cancel the shortcut if the key combination takes place within
                        // an input, textarea or select element, or an element with contenteditable="true"
                        //
                        // This is useful for implementing shortcuts with arrow keys, tabs, delete, backspace,
                        // etc.
                        revisedShortcutConfiguration.disableShortcutOnInput = shortcut[shortcutName];
                        break;
                    }
                    case 'context':
                    {
                        // A context for the callback function can be specified.  This will
                        // be what the 'this' keyword refers to.
                        revisedShortcutConfiguration.context = shortcut[shortcutName];
                        break;
                    }
                    case 'selector':
                    {
                        // A selector can be specified.  The selector limits the key event
                        // to a target element.  For example, the shortcut may only work if it
                        // occurred in an input element.  In that case, a selector 'input#someId'
                        // can be provided, which we'll use to examine the event.target property,
                        // to make sure that the event takes place in the proper context.
                        revisedShortcutConfiguration.selector = shortcut[shortcutName];
                        break;
                    }
                    case 'events':
                    {
                        // Get the events this key shortcut applies to: keydown, keyup or keypress.
                        revisedShortcutConfiguration.events = this.getEvents(shortcut[shortcutName]);
                        break;
                    }
                    case 'preventDefault':
                    {
                        // What to set the event's preventDefault property to.
                        revisedShortcutConfiguration.preventDefault = shortcut[shortcutName];
                        break;
                    }
                    case 'stopPropagation':
                    {
                        // What to set the event's stopPropagation property to.
                        revisedShortcutConfiguration.stopPropagation = shortcut[shortcutName];
                        break;
                    }
                    default:
                    {
                        // Otherwise, the property will be used for the name of the shortcut,
                        // and the value will be the shortcut itself.
                        //
                        // e.g., nextItem: "up"
                        revisedShortcutConfiguration.shortcut = shortcut[shortcutName];
                        revisedShortcutConfiguration.name = shortcutName;
                    }
                }
            }

            if (!revisedShortcutConfiguration.events)
            {
                revisedShortcutConfiguration.events = [this.defaultEvent];
            }

            return revisedShortcutConfiguration;
        }
        else
        {
            // If no object has been passed, create a simple default configuration for the shortcut.
            return {
                shortcut : shortcut,
                name : null,
                selector : null,
                events : [this.defaultEvent],
                context : null
            };
        }
    },

    /**
    * Return a key shortcut configuration.
    *
    * The object returned looks something like this:
    *
    * {
    *     fn:          (function)
    *     command:     (bool)
    *     control:     (bool)
    *     shift:       (bool)
    *     option:      (bool)
    *     key:         (string)
    *     keyCode:     (int)
    *     selector:    (string or null)
    *     context:     (object or null)
    *     keyShortcut:  (string)
    *     combination: (string)
    *     name:        (string or null)
    * };
    *
    */
    getShortcut : function(shortcut)
    {
        shortcut = this.analyzeConfiguration(shortcut);

        for (var event in shortcut.events)
        {
            switch (events[event])
            {
                case 'input':
                {
                    return this.returnShortcut(
                        shortcut.shortcut,
                        shortcut.name,
                        this.input
                    );
                }
                case 'keydown':
                {
                    return this.returnShortcut(
                        shortcut.shortcut,
                        shortcut.name,
                        this.down
                    );
                }
                case 'keyup':
                {
                    return this.returnShortcut(
                        shortcut.shortcut,
                        shortcut.name,
                        this.up
                    );
                }
                case 'keypress':
                {
                    return this.returnShortcut(
                        shortcut.shortcut,
                        shortcut.name,
                        this.press
                    );
                }
            }
        }

        return {};
    },

    /**
    * Helper method for getShortcut()
    *
    * Returns the specified key shortcut, by the key shortcut or the name.
    *
    * If multiple key shortcuts exist for a particular key combination, the
    * first matched key shortcut will be returned.
    */
    returnShortcut : function(shortcut, name, keySet)
    {
        for (var key in keySet)
        {
            var configuration = keySet[key];

            if (name && configuration.name && configuration.name === name)
            {
                return keySet[key];
            }
            else if (!name && shortcut && configuration.shortcut === shortcut)
            {
                return keySet[key];
            }
        }

        return {};
    },

    removeShortcuts : function(shortcuts)
    {
        return this.removeShortcut(shortcuts);
    },

    /**
    * Allows you to remove individual keyboard shortcuts.
    *
    * Example: key.removeShortcut({openFinderApplication: "Command + F, Control + F"})
    *
    * If a keyboard shortcut is named, only the named keyboard shortcut will be removed.
    * If a keyboard shortcut is not named, all keyboard shortcuts matching the key
    * combinations will be removed.
    */
    removeShortcut : function(shortcut)
    {
        if (typeof shortcut === 'object' && shortcut instanceof Array)
        {
            for (var shortcutCounter = 0; shortcutCounter < shortcut.length; shortcutCounter++)
            {
                this.removeShortcut(shortcut[shortcutCounter]);
            }

            return this;
        }
        else
        {
            var shortcut = this.analyzeConfiguration(shortcut);

            for (var event in shortcut.events)
            {
                switch (events[event])
                {
                    case 'input':
                    {
                        return this.removeConfiguration(
                            shortcut.shortcut,
                            shortcut.name,
                            this.input
                        );
                    }
                    case 'keydown':
                    {
                        return this.removeConfiguration(
                            shortcut.shortcut,
                            shortcut.name,
                            this.down
                        );
                    }
                    case 'keyup':
                    {
                        return this.removeConfiguration(
                            shortcut.shortcut,
                            shortcut.name,
                            this.up
                        );
                    }
                    case 'keypress':
                    {
                        return this.removeConfiguration(
                            shortcut.shortcut,
                            shortcut.name,
                            this.press
                        );
                    }
                }
            }
        }

        return this;
    },

    /**
    * Helper method for removeShortcut()
    *
    * Removes a key configuration from the speficied set.  If a name is passed in,
    * the configuration will be removed by name.  If no name is passed in, the
    * configuration will be removed by the original key shortcut value.
    * i.e., "Control + S, Command + S"
    *
    * Finally the event set to remove the configuration from must also be specified,
    * this._keydown, this._keyup, or this._keypress
    *
    */
    removeConfiguration : function(shortcut, name, keySet)
    {
        for (var key in keySet)
        {
            var configuration = keySet[key];

            if (name && configuration.name && configuration.name === name)
            {
                delete keySet[key];
            }
            else if (!name && shortcut && configuration.shortcut === shortcut)
            {
                delete keySet[key];
            }
        }

        return this;
    },

    /**
    * This property allows you to cancel keyboard shortcuts defined globally in this
    * file.  For example, the combination 'option + shift + f' to open the Finder
    * application from any webpage.  Setting this property to false will prevent
    * all of the 'option + shift' shortcuts defined in this script from working,
    * whereas you could then reuse those shortcuts in your own apps.
    *
    */
    globalShortcutsEnabled : true,

    /**
    * The following function is called whenever a 'input', 'keydown', 'keypress', or 'keyup'
    * event takes place in the document.  When one of these events occur, the relevant
    * list of key configurations is iterated to see if a shortcut applies.
    *
    */
    event : function(event, keySet)
    {
        for (var key in keySet)
        {
            // Retrive the configuration
            var configuration = keySet[key];

            // See if the keyCode defined in the configuration matches the keycode
            var keyCode = (parseInt(configuration.keyCode) === parseInt(event.keyCode));

            // If the key does not match, let's at least try to not waste more CPU cycles than
            // necessary and directly move on to the next configuration.
            if (!keyCode)
            {
                continue;
            }

            var command, control, shift, option;

            // Does the key combination require the command key to be pressed?
            // If it is required, make sure that the command key is pressed.  If it is
            // not required, make sure it is not pressed explicitly to avoid conflicting
            // with other shortcuts.
            if (!(command = (configuration.command && event.metaKey || !configuration.command && !event.metaKey)))
            {
                continue;
            }

            // Does the key combination require the control key to be pressed?
            if (!(control = (configuration.control && event.ctrlKey || !configuration.control && !event.ctrlKey)))
            {
                continue;
            }

            if (!(shift = (configuration.shift && event.shiftKey || !configuration.shift && !event.shiftKey)))
            {
                continue;
            }

            if (!(option = (configuration.option && event.altKey || !configuration.option && !event.altKey)))
            {
                continue;
            }

            // If the key matches, and the command, control, shift and option conditions check out,
            // execute the condition.
            if (keyCode && command && control && shift && option)
            {
                if (configuration.disableShortcutOnInput)
                {
                    if (!this.isInput(event))
                    {
                        // If the target is not an input element (input, select, textarea or element with contenteditable="true"),
                        // it is usually desired that the default action be canceled.
                        //
                        // This can be overridden by explicitly specifying the "preventDefault" option
                        // in the configuration and setting it false, same goes for the "stopPropagation"
                        // option.
                        if (typeof configuration.preventDefault === 'undefined' || configuration.preventDefault)
                        {
                            event.preventDefault();
                        }

                        if (typeof configuration.stopPropagation === 'undefined' || configuration.stopPropagation)
                        {
                            event.stopPropagation();
                        }
                    }
                    else
                    {
                        // If the target is an "input" element, cancel execution of the shortcut event's callback.
                        return;
                    }
                }

                if (configuration.command || configuration.control || configuration.shift || configuration.option)
                {
                    // By default, if the command, control, shift or option keys are required,
                    // we'll go ahead and cancel the default action.  99% of the time, since
                    // combinations involving those keys invoke something already,  this is
                    // the desired behavior and will save the author additional typing.
                    //
                    // This can be overridden by explicitly specifying the "preventDefault" option
                    // in the configuration and setting it false, same goes for the "stopPropagation"
                    // option.
                    if (typeof configuration.preventDefault === 'undefined' || configuration.preventDefault)
                    {
                        event.preventDefault();
                    }

                    if (typeof configuration.stopPropagation === 'undefined' || configuration.stopPropagation)
                    {
                        event.stopPropagation();
                    }
                }
                else
                {
                    // If a key modifier is not involved, the preventDefault and stopPropagation options
                    // can be set in the configuration, and those actions will be set here.  Though,
                    // this is not required, and can be done in the event callback as well.
                    if (configuration.preventDefault)
                    {
                        event.preventDefault();
                    }

                    if (configuration.stopPropagation)
                    {
                        event.stopPropagation();
                    }
                }

                // Is there a target for the event, if not default to the document.
                var target = event.target ? $(event.target) : $(document);

                if (configuration.selector)
                {
                    // If there is a selector, make sure that either the event target or the event target's
                    // ancestors match that selector.
                    if (target.is(configuration.selector) || target.parents(configuration.selector).length)
                    {
                        // If a context is defined, call the callback function with that context.
                        // If not, call with the target as context.  The callback function is called
                        // with the original event in the first argument and the full configuration
                        // for the key shortcut as the 2nd argument.
                        configuration.fn.call(
                            configuration.context ? configuration.context : target,
                            event,
                            configuration
                        );
                    }
                }
                else
                {
                    configuration.fn.call(
                        configuration.context ? configuration.context : target,
                        event,
                        configuration
                    );
                }
            }
        }

        return this;
    },

    removeEvents : function()
    {
        $(document)
            .off('input.keyboardShortcuts')
            .off('keydown.keyboardShortcuts')
            .off('keypress.keyboardShortcuts')
            .off('keyup.keyboardShortcuts');
    },

    ready : function()
    {
        // Set up key events so that the keyShortcut API just works!
        $(document)
            .on(
                'input.keyboardShortcuts',
                function(event)
                {
                    keyboard.event(event, keyboard.input);
                }
            )
            .on(
                'keydown.keyboardShortcuts',
                function(event)
                {
                    keyboard.event(event, keyboard.down);
                }
            )
            .on(
                'keypress.keyboardShortcuts',
                function(event)
                {
                    keyboard.event(event, keyboard.press);
                }
            )
            .on(
                'keyup.keyboardShortcuts',
                function(event)
                {
                    keyboard.event(event, keyboard.up);
                }
            );
    }
};

$(document).ready(
    function()
    {
        keyboard.ready();
    }
);
