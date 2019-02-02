
FCKCommands.RegisterCommand(
  'hFinderDialogueLink', {
    //create a custom command, I don't want to use the FCKDialogCommand because it uses the default fck layout and not mine
    GetState : function() {
      return FCK_TRISTATE_OFF; //we dont want the button to be toggled
    },

    Execute : function() {
      //open a popup window when the button is clicked
      window.open('/Applications/Finder?dialogue=Link', 'hFinderLinkDialogue', 'width=650,height=400,scrollbars=no,scrolling=no,location=no,toolbar=no');
    }
  }  
);

// Create the "Find" toolbar button.
var $hFinder = new FCKToolbarButton('hFinderDialogueLink', 'Insert/Modify Link');

$hFinder.IconPath = '/images/icons/16x16/sites.png';

FCKToolbarItems.RegisterItem('hFinderDialogueLink', $hFinder);