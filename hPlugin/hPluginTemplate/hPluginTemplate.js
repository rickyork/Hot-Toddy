if (typeof(plugin) == 'undefined')
{
    var plugin = {};
}

plugin.template = {
    ready : function()
    {
        $('input#hPluginMethodAdd').click(
            function(e)
            {
                e.preventDefault();

                count = $('input.hPluginListenerMethod').length;

                var tr = $('input#hPluginListenerMethod-0').parents('tr').clone(true);

                tr.find('input.hPluginListenerMethod')
                    .val('')
                    .attr('id', 'hPluginListenerMethod-' + count)
                    .removeClass('hPluginListenerMethodSelected');

                $('input#hPluginMethodAdd').parents('tr').before(tr);
            }
        );

        $('input.hPluginListenerMethod').focus(
            function()
            {
                $(this).select('hPluginListenerMethod');
            }
        );

        $('input#hPluginMethodRemove').click(
            function(e)
            {
                e.preventDefault();

                var input = hot.selected('hPluginListenerMethod');

                if (input.length)
                {
                    if ($('input.hPluginListenerMethod').length == 1)
                    {
                        input.val('');
                    }
                    else
                    {
                        input.parents('tr').remove();
                    }

                    plugin.template.renumberMethodInputs();
                }
            }
        );

        $('input#hPluginSave').click(
            function(e)
            {
                var td = $(this).parents('td');

                $('input.hPluginListenerMethod').each(
                    function(index, obj)
                    {
                        if ($(this).val())
                        {
                            td.append("<input type='hidden' name='hPluginMethods[]' value='" + $(this).val() + "' />\n");
                        }
                    }
                );

                //$(this).parents('form').submit();
            }
        );
    },

    renumberMethodInputs : function()
    {
        i = 0;
        
        $('input.hPluginListenerMethod').each(
            function()
            {
                $(this).attr('id', 'hPluginListenerMethod-' + i);
                i++;
            }
        );
    }
};

$(document).ready(
    function()
    {
        plugin.template.ready();
    }
);