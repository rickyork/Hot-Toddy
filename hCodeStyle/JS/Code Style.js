var code = {
    style : {

    }
};

code.style = hot.factory();

code.style.prototype.init = function(type, source)
{
    var options = typeof arguments[2] !== 'undefined' ? arguments[2] : {};

    this.html = {};

    this.source = '';

    switch (type.toLowerCase())
    {
        case 'html':
        {
            var htmlStyle = new code.style.html(source, options);
            this.source = htmlStyle.get();
            break;
        }
    }

    this.getSource = function()
    {
        return this.source;
    };
};

$(document).ready(
    function()
    {
        if ($('div#hCodeStyleTest').length)
        {
            new code.style('html', $('div#hCodeStyleTest').html());
        }
    }
);