hot.include('/Library/SyntaxHighlighter/Scripts/shCore.js');

var brushes = ['Bash', 'Css', 'JScript', 'Php', 'Sql', 'Xml'];

for (var i = 0; i < brushes.length; i++)
{
    hot.include('/Library/SyntaxHighlighter/Scripts/shBrush' + brushes[i] + '.js');
}

$('head').append(
    "<link rel='stylesheet' type='text/css' href='/Library/SyntaxHighlighter/Styles/shCore.css' />" + 
    "<link rel='stylesheet' type='text/css' href='/Library/SyntaxHighlighter/Styles/shThemeDefault.css' />"
);

$(document).ready(
    function()
    {
        SyntaxHighlighter.config.clipboardSwf = '/Library/SyntaxHighlighter/Scripts/clipboard.swf';
        SyntaxHighlighter.config.tagName = 'code';
        SyntaxHighlighter.all();
    }
);
