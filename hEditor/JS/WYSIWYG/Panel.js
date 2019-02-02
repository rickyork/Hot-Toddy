$.fn.extend({

    /**
    * This function allows you to toggle between Photos, Movies and Documents
    * in the side panel.  When you click a tab, the corresponding tree (top)
    * and thumbnail panel (bottom) are changed.
    */
    openSidePanel : function()
    {
        var tab = hot.selected('hEditorTemplateObjectMediaTab');

        if (tab.length)
        {
            var name = tab.splitId();

            hot.unselect('hEditorTemplateObjectMediaTab');
            $('div#hEditorTemplateObjectMedia' + name).hide();

            hot.unselect('hEditorTemplateObjectMediaPanel');
            $('div#hEditorTemplateObjectTree' + name).hide();
        }

        var name = this.splitId();
        this.select('hEditorTemplateObjectMediaTab');

        $('div#hEditorTemplateObjectMedia' + name)
            .show()
            .select('hEditorTemplateObjectMediaPanel');

        $('div#hEditorTemplateObjectTree' + name).show();


        // After opening a new panel, you must simulate a click on the top most item to
        // populate the thumbnails in the thumbnail view below the tree.  This reuses
        // the hFinderTree plugin for the top tree view, then the bottom view is a
        // custom view provided by hPhoto and hMovie plugins so far.  The "Documents"
        // tab does not yet have a defined API.
        switch (name)
        {
            case 'Photos':
            {
                break;
            }
            case 'Movies':
            {
                if (!$('div#hMovieView ul li').length)
                {
                    $('div[data-file-path="/Categories/.Movies"]').click();
                }

                break;
            }
        }

        return this;
    },

    toggleExpandProperty : function()
    {
        var sourceFile = this.sourceFile();
        var tr = this.parents('tr:first');
        var baseProperty = tr.data('property');
        var className = 'hEditorTemplateExpanded hEditorTemplateExpanded-' + baseProperty;

        var open = (sourceFile == 'right.png');

        tr.nextAll('tr.hEditorTemplateExpandProperty').each(
            function()
            {
                if ($(this).data('base-property') == baseProperty)
                {
                    if (open)
                    {
                        $(this).show();
                        $(this).addClass(className);
                    }
                    else
                    {
                        $(this).hide();
                        $(this).removeClass(className);
                    }
                }
            }
        );

        if (open)
        {
            tr.addClass(className);
            this.sourceFile('down.png');
        }
        else
        {
            tr.removeClass(className);
            this.sourceFile('right.png');
        }

        return this;
    },

    setProperties : function()
    {
        if ($('table#hEditorTemplateDimensions').length)
        {
            properties = [
                'width',
                'minWidth',
                'maxWidth',
                'height',
                'minHeight',
                'maxHeight',
                'borderWidth',
                'borderTopLeftRadius',
                'borderTopRightRadius',
                'borderBottomLeftRadius',
                'borderBottomRightRadius',
                'marginTop',
                'marginRight',
                'marginBottom',
                'marginLeft',
                'paddingTop',
                'paddingRight',
                'paddingBottom',
                'paddingLeft'
            ];

            var obj = this;

            $(properties).each(
                function(key, value)
                {
                    var css = obj.css(value);

                    if (css !== undefined)
                    {
                        editor.getPropertyRow(value)
                              .setDimensionProperty(editor.parseMeasurement(css));
                    }
                }
            );
        }
    },

    setDimensionProperty : function(data)
    {
        var checkbox = this.find('input[type="checkbox"]');

        if (data.integer)
        {
            checkbox.attr('checked', true);
        }
        else
        {
            checkbox.removeAttr('checked');
        }

        var range = this.find('input[type="range"]');

        if (data.unit == '%')
        {
            range.val(data.integer);
        }

        var number = this.find('input[type="number"]');

        number.val(data.integer);

        var text = this.find('input[type="text"]');

        text.val(data.unit);
    }
});

$.extend(
    editor, {

        setPropertyData : function()
        {

        },

        getPropertyRow : function(property)
        {
            return $('tr[data-property="' + property + '"]');
        },

        parseMeasurement : function(data)
        {
            return {
                integer : parseInt(data),
                unit : this.filterMeasurementUnit(data.replace(/\d/g, ''))
            };
        },

        filterMeasurementUnit : function(data)
        {
            switch (data)
            {
                case 'px':
                case 'em':
                case 'ex':
                case '%':
                case 'cm':
                case 'mm':
                case 'in':
                case 'pt':
                case 'pc':
                {
                    return data;
                }
                default:
                {
                    return '';
                }
            }
        },

        panelReady : function()
        {
            // Make it possible to click a tab to toggle between side panels.
            this.$('ul#hEditorTemplateObjectMediaTabs li button')
                .click(
                    function(event)
                    {
                        event.preventDefault();

                        $(this)
                            .parents('li:first')
                            .openSidePanel();
                    }
                );

            // Open the first tab by simulating a click on it.
            this.$('ul#hEditorTemplateObjectMediaTabs li:first button')
                .click();

            // This code handles the tabs in the floating "Editor" island containing
            // formatting controls.  Clicking a tab will toggle between Format, Template,
            // Properties, etc.
            this.$('ul.hEditorTemplatePanelTabs li')
                .click(
                    function()
                    {
                        $(this).select('hEditorTemplatePanelTab');

                        var name = $(this).parent().attr('data-name');

                        $('div#hEditorTemplatePanel' + name + $(this).splitId())
                            .select('hEditorTemplatePanelPane');
                    }
                );

            // This simulates a click on the first tab of the floating Editor panel.  "Format".
            this.$('ul.hEditorTemplatePanelTabs li:first')
                .click();

            // This makes the Editor panel floating, you can drag it around on the screen
            // by gripping the <h1> element.
            if (!$('div#hEditorTemplate').hasClass('hEditorTemplateIsEmbedded'))
            {
                $('div.hEditorTemplatePanel').draggable({
                    handle : ' h1.hEditorTemplatePanelTitle',
                    containment : 'document'
                });
            }

            // Double-clicking on the <h1> element hides the Editor panel's tabs and controls.
            this.$('h1.hEditorTemplatePanelTitle')
                .dblclick(
                    function()
                    {
                        $(this).next().slideToggle();
                    }
                );

            this.$('img.hEditorTemplateExpandProperty')
                .click(
                    function()
                    {
                        $(this).toggleExpandProperty();
                    }
                );

            if ($('div#hEditorTemplate').hasClass('hEditorTemplateIsEmbedded'))
            {
                $('div.hEditorTemplatePanel').hide();
            }
        },

        /**
        * Modal dialogues prevent interaction with the layers below the dialogue until
        * the action presented by the dialogue is completed.  The following API deals
        * with showing a <div> elements that prevents interaction with the layers beneath
        * the modal dialogue.
        */
        openModal : function()
        {
            $('div#hEditorTemplateModalDialogueJammer')
                .fadeIn();
        },

        closeModal : function()
        {
            $('div#hEditorTemplateModalDialogueJammer')
                .fadeOut();
        }
    }
);

$(document).ready(
    function()
    {
        editor.panelReady();
    }
);