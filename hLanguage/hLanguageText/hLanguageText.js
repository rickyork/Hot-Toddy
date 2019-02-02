/**
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//\\\       \\\\\\\\|
//\\\ @@    @@\\\\\\| Hot Toddy Language Text
//\\ @@@@  @@@@\\\\\|
//\\\@@@@| @@@@\\\\\|
//\\\ @@ |\\@@\\\\\\| http://www.hframework.com
//\\\\  ||   \\\\\\\| © Copyright 2015 Richard York, All rights Reserved
//\\\\  \\_   \\\\\\|
//\\\\\        \\\\\| Use and redistribution are subject to the terms of the license.
//\\\\\  ----  \@@@@| http://www.hframework.com/license
//@@@@@\       \@@@@|
//@@@@@@\     \@@@@@|
//\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
*/

$$.extend({
  LanguageText : {
  
    hLanguageTextId : 0,
    hLanguages : [],

    Ready : function()
    {
      $('textarea#hLanguageId-1').keyup(
        function() {
          $$.LanguageText.Search(this.value);
        }
      );

      $('input#hLanguageTextSave').click(
        function($e) {
          $$.LanguageText.Save();
          $e.preventDefault();
        }
      );

      $('input#hLanguageTextNew').click(
        function($e) {
          $$.LanguageText.hLanguages = [];
          $$('textarea').val('');
          $e.preventDefault();
        }
      );
    },

    Search : function($text)
    {
      $.get(
        $$.Path(
          '/hLanguage/hLanguageText/search', {
            hLanguageText: $text
          }
        ),
        function(xml)  {
          $(xml).find('hLanguageText').each(
            function() {
              var $hLanguageId     = parseInt($(this).attr('hLanguageId'));
              var $hLanguageTextId = parseInt($(this).attr('hLanguageTextId'));
              var $hLanguageText   = $(this).text();
  
              $$.LanguageText.hLanguages[$hLanguageId] = {
                hLanguageTextId : $hLanguageTextId
              };

              $('#hLanguageId-' + $hLanguageId).val($hLanguageText);
            }
          );
        }
      );
    }, 

    Save : function()
    {
      var $get = '';

      $$('textarea').each(
        function() {
          var $hLanguageId = $(this).SplitId();

          $get += 
            '&hLanguage[' + $hLanguageId + '][hLanguageText]='   + encodeURIComponent($node.value) +
            '&hLanguage[' + $hLanguageId + '][hLanguageTextId]=' + 
                (typeof($$.LanguageText.hLanguages[$hLanguageId]) != 'undefined' && typeof($$.LanguageText.hLanguages[$hLanguageId].hLanguageTextId) != 'undefined'? 
                    $$.LanguageText.hLanguages[$hLanguageId].hLanguageTextId : 0);
        }
      );

      $.get($$.Path('/hLanguage/hLanguageText/save?' + $get));
    }
  }
});

$(document).ready(
  function() {
    $$.LanguageText.Ready();
  }
);