if (typeof application == 'undefined')
{
    var application = {};
}

application.menu = {

    toggleMenu : function(node)
    {
        this.close();
        this.open = true;

        this.selected = node.find('div.hApplicationSubMenu');
        this.selectedItem = node;

        if (this.selected)
        {
            this.selected.addClass('hApplicationSubMenuOpen');
        }

        if (this.selectedItem)
        {
            this.selectedItem.addClass('hApplicationMenuOpen');
        }
    },

    selected : null,
    selectedItem : null,
    open : false,
    active : true,

    ready : function() {

        $('ul.hApplicationMenu li.hApplicationMenu')
            .click(
                function()
                {
                    application.menu.toggleMenu($(this));
                }
            )
            .mouseover(
                function()
                {
                    if (application.menu.open)
                    {
                        application.menu.toggleMenu($(this));
                    }
                }
            );

        $('ul.hApplicationMenu').hover(
            function()
            {
                application.menu.active = true;
            },
            function()
            {
                application.menu.active = false;
            }
        );


        $('li.hApplicationMenuItem').hover(
            function()
            {
                $(this).addClass('hApplicationMenuItemOn');
            },
            function()
            {
                $(this).removeClass('hApplicationMenuItemOn');
            }
        );

        $(document).mousedown(
            function()
            {
                if (!application.menu.active)
                {
                     application.menu.close();
                     application.menu.open = false;
                }
            }
        );
    },

    close : function()
    {
        if (this.selected)
        {
            this.selected.removeClass('hApplicationSubMenuOpen');
            this.selectedItem.removeClass('hApplicationMenuOpen');
        }
    }
};

$(document).ready(
    function()
    {
        application.menu.ready();
    }
);