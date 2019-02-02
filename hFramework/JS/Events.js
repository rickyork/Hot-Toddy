if (typeof(hot) == 'undefined')
{
    var hot = {};
}

$.extend(
    hot, {
        eventList : [],
        
        getEventName : function(obj)
        {
            var _event = obj;
            var name = null;

            if (obj.indexOf('.') != -1)
            {
                var bits = obj.split('.');
    
                _event = bits[0];
                name =  bits[1];
            }

            return {
                event : _event,
                name: name
            };
        },

        events : function(obj)
        {
            this.event(obj);
        },

        event : function(obj)
        {
            if (typeof(obj) == 'object')
            {
                for (var i in obj)
                {
                    this.event(i, obj[i].fn, obj[i].context? obj[i].context : null);
                }
            }
            else if (typeof(obj) == 'string')
            {
                obj = this.getEventName(obj);
    
                if (!this.eventList[obj.event])
                {
                    this.eventList[obj.event] = [];
                }
    
                this.eventList[obj.event].push({
                    fn: arguments[1],
                    name: obj.name,
                    context: arguments[2]? arguments[2] : null
                })
            }
        },

        fire : function(obj)
        {
            obj = hot.getEventName(obj);

            var args = arguments[1]? arguments[1] : null;
            var defaultContext = this;
            var context = defaultContext;
            var eventOutcome = true;
    
            var rtn = null;
    
            // context of "this" may be changed, so don't use it.
            if (hot.eventList[obj.event] && hot.eventList[obj.event].length)
            {
                $(hot.eventList[obj.event]).each(
                    function(key, configuration)
                    {
                        context = configuration.context? configuration.context : defaultContext;

                        rtn = configuration.fn.call(context, args);

                        if (rtn === false)
                        {
                            eventOutcome = false;
                            return false;
                        }
                    }
                );
            }

            if (!eventOutcome)
            {
                return false;
            }

            if (typeof(rtn) == 'object')
            {
                return rtn;
            }

            return context;
        },
        
        removeEvent : function(obj)
        {
            obj = this.getEventName(obj);
            
            if (!this.eventList[obj.event])
            {
                hot.console.error("Event '" + obj.event + "' could not be removed because it does not exist.");
                return;
            }
    
            if (obj.name)
            {
                for (var i in this.eventList[obj.event])
                {
                    if (this.eventList[obj.event][i].name == obj.name)
                    {
                        delete this.eventList[obj.event][i];
                    }
                }
            }
            else 
            {
                delete this.eventList[obj.event];
            }
        }
    }
);