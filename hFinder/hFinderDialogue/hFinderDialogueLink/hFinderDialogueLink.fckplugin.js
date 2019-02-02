if (window.opener) {
  var $editor     = window.opener;
  var FCK         = $editor.FCK;
  var FCKLang     = $editor.FCKLang;
  var FCKConfig   = $editor.FCKConfig;
  var FCKRegexLib = $editor.FCKRegexLib;
  var FCKTools    = $editor.FCKTools;
}

var hFinderDialogueLink = {
  selectedNode : null,

  events : function()
  {
    hFinder.addEvent(
      'click',
      function() {
        document.querySelector('input#hFinderDialogueLinkPath').value  = hFinder.getFullPath(this);
        document.querySelector('input#hFinderDialogueLinkTitle').value = hFinder.getTitle(this);
      }
    );
    
    document.querySelector('input#hFinderDialogueLinkCancel').addEventListener(
      'click',
      function() {
        self.close();
      }, false
    );

    this.selectedNode = FCK.Selection.MoveToAncestorNode('A');

    if (this.selectedNode) {
      document.querySelector('input#hFinderDialogueLinkPath').value  = this.selectedNode.href;
      document.querySelector('input#hFinderDialogueLinkTitle').value = this.selectedNode.title;
    }

    document.querySelector('input#hFinderDialogueLinkOK').addEventListener(
      'click',
      function() {
        hFinderDialogueLink.save();
        self.close();
      }, false
    );
  },

  save : function()
  {
    var $href = document.querySelector('input#hFinderDialogueLinkPath').value;

    if (this.selectedNode) {
      FCK.Selection.SelectNode(this.selectedNode);
    }

    if (!this.selectedNode) {
      this.selectedNode = FCK.CreateLink($href, true);
    }

    this.selectedNode.href  = $href;
    this.selectedNode.title = document.querySelector('input#hFinderDialogueLinkTitle').value;
  }
};

document.addEventListener(
  'DOMContentLoaded',
  function() {
    hFinderDialogueLink.events();
  }, false
);