finder.search = {
    ready : function()
    {
        $('input#hFinderSearchTerms').keypress(
            function(event)
            {
                if (event.keyCode == 13)
                { // Return key
                    event.preventDefault();
                    finder.search.search($(this).val());
                }
            }
        );
    },

    search : function(fileSearchTerms)
    {
        hot.unselect('hFinderTree');
        hot.unselect('hFinder');

        finder.setView('Icons', true);

        http.get(
            '/hFinder/search', {
                hFileSearchTerms : fileSearchTerms
            },
            finder.onRequestDirectoryCallback,
            finder
        );
    }
};

$(document).ready(
    function()
    {
        finder.search.ready();
    }
);
