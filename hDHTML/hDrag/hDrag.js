var drag = {

    getOffset : function(obj, event)
    {
        var position = this.getPosition(obj);
        var mouse = this.getMouseCoordinates(event);

        return {
            x : mouse.x - position.x,
            y : mouse.y - position.y
        };
    },

    getMouseCoordinates : function(event)
    {
        var x = 0;
        var y = 0;

        if (event.pageX || event.pageY)
        {
            x = event.pageX;
            y = event.pageY;
        }
        else if (event.clientX || event.clientY)
        {
            x = event.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = event.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        return {
            x : x,
            y : y
        };
    },

    getPosition : function(obj)
    {
        var x = (obj.offsetLeft)? obj.offsetLeft : 0;
        var y = (obj.offsetTop)? obj.offsetTop : 0;

        while (obj && (obj = obj.offsetParent))
        {
            x += obj.offsetLeft;
            y += obj.offsetTop;
        }

        return {
            x : x,
            y : y
        };
    }
};
