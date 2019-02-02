if (typeof application == 'undefined')
{
    var application = {};
}

application.status = {

    node : null,

    getNode : function(html)
    {
        this.node = $('div.hApplicationStatus');

        if (!this.node.length)
        {
            $('body').append(
                $("<div/>")
                    .addClass('hApplicationStatus')
                    .append($("<span/>"))
            );

            this.node = $('div.hApplicationStatus');
        }

        this.node
            .addClass('hApplicationStatusOn')
            .unbind('click.hApplicationStatus')
            .bind(
                'click.hApplicationStatus',
                function()
                {
                    $(this).fadeOut('slow');
                }
            )
            .find('span')
            .html(html);

        if (hot.userAgent == 'ie')
        {
            this.node.css({
                background: 'none',
                // Fixes IE's screwy way of handling PNGs via its native support when
                // also applying opacity via a filter.
                filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/images/themes/aqua/backgrounds/status.png', sizingMethod='scale')"
            });
        }
    },

    message : function(html)
    {
        this.getNode(html);

        var fn = (arguments[2] && typeof(arguments[2]) == 'function'? arguments[2] : function() {});

        if (arguments[1])
        {
            this.node.fadeOut('slow', fn);
        }
        else
        {
            this.node.fadeIn('fast', fn);
        }
    }
};