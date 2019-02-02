$$.extend({
  Login : {
    Ready : function()
    {
      $('input#hFrameworkServer')
        .focus(
          function() {
            if ($(this).val() == 'www.example.com') {
              $(this).val('');
              $(this).addClass('hUserLoginDesktopNonDefaultInput');
            }
          }
        )
        .blur(
          function() {
            if (!$(this).val()) {
              $(this).val('www.example.com');
              $(this).removeClass('hUserLoginDesktopNonDefaultInput');
            }
          }
        );

      $('input#hFrameworkPort')
         .focus(
          function() {
            if ($(this).val() == '80') {
              $(this).val('');
              $(this).addClass('hUserLoginDesktopNonDefaultInput');
            }
          }
        )
        .blur(
          function() {
            if (!$(this).val()) {
              $(this).val('80');
              $(this).removeClass('hUserLoginDesktopNonDefaultInput');
            }
          }
        );

      $('div.hUserLoginDesktopProfile').css({
        backgroundColor: 'rgb(224,224,224)',
        border: '1px solid transparent'
      });

      $('div.hUserLoginDesktopProfile').hover(
        function() {
          $(this).animate({
              backgroundColor: 'rgb(210,210,210)',
              border: '1px solid rgb(200, 200, 200)'
            }, 'slow'
          );

          $(this).find('div.hUserLoginDesktopEditProfile').fadeIn('slow');
        },
        function() {
          $(this).animate({
              backgroundColor: 'rgb(224,224,224)',
              border: '1px solid transparent'
            }, 'slow'
          );

          $(this).find('div.hUserLoginDesktopEditProfile').fadeOut('slow');
        }
      );

      $('form#hUserLoginDesktopDialogue').slideDown('slow');

      if (hDesktopApplication) {
        // Attach an event to both the "Login" and "Cancel" buttons.
        $('input#hUserLoginDesktopDialogueLogin').click(
          function($e) {
            $e.preventDefault();
            $$.Login.GetAuthenticationToken();
          }
        );

        $('input#hUserLoginDesktopDialogueCancel').click(
          function($e) {
            $e.preventDefault();
            self.close();
          }
        );

        // Returns something like this:
        // /Users/John Appleseed/Library/Application Support/Titanium/appdata/com.deadmarshes.hottoddy
        var $appDataDirectory = $$$.Filesystem.getApplicationDataDirectory();

        // Does the keychain file exist?
        var $keychainFile = $$$.Filesystem.getFile($appDataDirectory, 'keychain.xml');

        if ($keychainFile.exists()) {
          // There be a keychain
          // Build the "keychain" selector

        } else {
          // There be no keychain
        }
      }
    }
  },

  /**
    keychain.xml

    <keys>
      <key>
        <hServerHost></hServerHost>
        <hServerPort></hServerPort>
        <hUserName></hUserName>         // hUserName
        <hUserPassword></hUserPassword> // hUserPassword (non-encrypted)
        <hServerIsSSL></hServerIsSSL>
        <hUserAuthenticationToken></hUserAuthenticationToken>
      </key>
    </keys>
  **/

  GetAuthenticationToken : function()
  {
    var $hUserName      = $('input#hUserName').val();
    var $hUserPassword  = $('input#hUserPassword').val();
    var $hServerHost    = $('input#hFrameworkServer').val();
    var $hServerIsSSL   = $('input#hFrameworkSSLEnabled').is(':checked');
    var $hServerPort    = $('input#hFrameworkPort').val();
    var $saveToKeychain = $('input#hFrameworkKeychain').is(':checked');

    // If creating a keychain, use OS keychain if Mac OS X, otherwise,
    // save all information directly in the keychain file.
    
    
    // Get the "authentication token" from the server-side so that 
    // 
    

  }
});

$(document).ready(
  function() {
    $$.Login.Ready();
  }
);

/*
div.hUserLoginDesktopProfile:hover {
    background: rgb(210, 210, 210);
    -webkit-border-radius: 5px;
    border: 1px solid rgb(200, 200, 200);

}
*/