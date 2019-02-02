//FCKConfig.Plugins.Add('hFinderDialogueLink', null, '/hFinder/hFinderDialogue/');
//FCKConfig.Plugins.Add('hFinderDialogueImage', 'en', '/hFinder/hFinderDialogue/hFinderDialogueImage');

FCKConfig.ToolbarSets['Basic'] = [
  ['Bold', 'Italic', '-', 
   'OrderedList', 'UnorderedList', '-', 
   'Link', 'Unlink', '-', 
   'Image', '-', 
   'SpecialChar', '-', 
   'SpellCheck', '-', 
   'About'
  ]
];

FCKConfig.ToolbarSets['BasicSmiley'] = [
  ['Bold', 'Italic', '-', 
   'OrderedList', 'UnorderedList', '-', 
   'Outdent','Indent', '-',
   'Link', 'Unlink', '-', 
   'Image', '-', 
   'Smiley', 'SpecialChar', '-', 
   'SpellCheck', '-', 
   'Source', '-',
   'About'
  ]
];

FCKConfig.ToolbarSets['BasicCMS'] = [
  ['Source', '-', 'Bold', 'Italic', '-', 'OrderedList', 'UnorderedList', '-', 'Link', 'Unlink', '-', 'Image', '-', 'SpecialChar', '-', 'SpellCheck', '-', 'PasteWord'],
  ['Style', '-', 'Templates', '-', 'Table']
];

FCKConfig.ToolbarSets['CMS'] = [
  ['Source', 'Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'Subscript','Superscript'],
  ['OrderedList', 'UnorderedList', '-', 'Outdent','Indent','Blockquote'],
  ['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
  ['PasteWord', '-', 'Link','Unlink','Anchor'],
  ['Image', 'Table', 'Rule', 'SpecialChar'],
  '/',
  ['Style', '-', 'Templates', '-', 'SpellCheck']
];

FCKConfig.LinkBrowserURL  = '/Applications/Finder?dialogue=Link';
FCKConfig.ImageBrowserURL = '/Applications/Finder?dialogue=Image';
FCKConfig.LinkUpload      = false;
FCKConfig.ImageUpload     = false;
FCKConfig.SpellChecker    = 'SpellerPages';
FCKConfig.SkinPath        = FCKConfig.BasePath + 'skins/silver/';

FCKConfig.BodyId = 'hWYSIWYGEditor';

if (typeof(top.FCKStylesXMLPath) != 'undefined') {
  FCKConfig.StylesXmlPath   = top.FCKStylesXMLPath;
}

if (top.hFileCSS) {
  FCKConfig.EditorAreaCSS = top.hFileCSS;
}

if (top.FCKFullPage) {
  FCKConfig.FullPage = top.FCKFullPage;
}

if (top.FCKTemplatePath) {
  FCKConfig.TemplatesXmlPath = top.FCKTemplatePath;
}

FCKConfig.FormatOutput = (top.hot.userAgent == 'ie' && top.hot.userAgentVersion < 8);
