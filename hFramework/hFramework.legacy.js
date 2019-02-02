// Backward compatibility JS
var $$ = $.fn;

$$.Path = hot.path;
$$.GetSelected = hot.selected;
$$.Select = $$.select;
$$.Unselect = hot.unselect;
$$.SplitId = $$.splitId;
$$.SplitNumericId = $$.splitNumericId;
$$.OuterHTML = $$.outerHTML;

var $hUserAgent = hot.userAgent;
var hUserAgent = hot.userAgent;
var $hUserAgentVersion = hot.userAgentVersion;
var hUserAgentVersion = hot.userAgentVersion;
var $hUserAgentOS = hot.userAgentOS;
var hUserAgentOS = hot.userAgentOS;
var $_GET = get;
var $_SERVER = server;

if (hot.userAgentOS == 'Desktop Application')
{
    var $$$ = Titanium;
}

var PNG = function(img)
{
    if (hot.userAgent == 'ie' && hot.userAgentVersion < 7 && hot.userAgentVersion > 5.2 && img && img.src)
    {
        img.outerHTML =
            '<span src="' + img.src + '"' + ((img.id)? " id='" + img.id + "' " : '') +
                    ((img.className)? " class='" + img.className + "' " : '') +
                    ((img.title)? " title='" + img.title + "' " : '') +
                    ' style="display: inline-block; width: ' + img.width + 'px; height: ' + img.height + "px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + img.src + "', sizingMethod='scale'); " + img.style.cssText + '"></span>';
    }
};

var hFramework = {};

hFramework.IE = hot.IE;
hFramework.IE6 = hot.IE6;

$(document).ready(
    function()
    {
        if (typeof dialogue !== 'undefined')
        {
            $$.OpenDialogue = $$.openDialogue;
            $$.CloseDialogue = $$.closeDialogue;
            $$.ToggleDialogue = $$.toggleDialogue;
        }
    }
);